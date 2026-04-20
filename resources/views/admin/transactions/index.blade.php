@extends('layouts.admin')

@section('title', 'İşlemler')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Sipariş ve İşlem Listesi</h1>

    <form method="get" action="{{ route('admin.transactions.index') }}" class="card mt-5 grid gap-3 p-4 md:grid-cols-4">
        <select name="status" class="input-ui">
            <option value="">Tüm durumlar</option>
            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Beklemede</option>
            <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Başarılı</option>
            <option value="failed" @selected(($filters['status'] ?? '') === 'failed')>Başarısız</option>
        </select>
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="input-ui" placeholder="Başlangıç tarihi">
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="input-ui" placeholder="Bitiş tarihi">
        <div class="flex items-center gap-2">
            <button class="btn-primary w-full">Filtrele</button>
            <a href="{{ route('admin.transactions.index') }}" class="btn-secondary w-full">Temizle</a>
        </div>
    </form>

    <div class="card mt-5 overflow-x-auto p-4">
        <table class="min-w-full text-sm">
            <thead><tr class="text-left text-slate-500"><th class="py-2">Sipariş</th><th>Ürün</th><th>E-posta</th><th>Tutar</th><th>Durum</th><th>Tarih</th><th>Detay</th></tr></thead>
            <tbody>
            @foreach($transactions as $transaction)
                @php
                    $statusMap = [
                        'pending' => 'Beklemede',
                        'paid' => 'Başarılı',
                        'failed' => 'Başarısız',
                    ];
                    $statusLabel = $statusMap[$transaction->status] ?? ucfirst($transaction->status);
                    $payloadJson = json_encode($transaction->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                @endphp
                <tr class="border-t">
                    <td class="py-2">{{ $transaction->order_id }}</td>
                    <td>{{ $transaction->coloringPage->title ?? 'Silinmiş ürün' }}</td>
                    <td>{{ $transaction->email }}</td>
                    <td>{{ number_format($transaction->paid_amount, 2) }} TL</td>
                    <td>
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $transaction->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($transaction->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                            {{ $statusLabel }}
                        </span>
                    </td>
                    <td>{{ $transaction->created_at?->format('d.m.Y H:i') }}</td>
                    <td>
                        <details class="group">
                            <summary class="btn-secondary cursor-pointer list-none px-3 py-1.5">Detay</summary>
                            <div class="mt-3 w-[40rem] max-w-[90vw] rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                                <div class="grid gap-2 text-xs text-slate-600 md:grid-cols-2">
                                    <p><span class="font-semibold text-slate-700">Sipariş:</span> {{ $transaction->order_id }}</p>
                                    <p><span class="font-semibold text-slate-700">Durum:</span> {{ $statusLabel }}</p>
                                    <p><span class="font-semibold text-slate-700">Token:</span> {{ $transaction->download_token ?: '-' }}</p>
                                    <p><span class="font-semibold text-slate-700">Token bitiş:</span> {{ $transaction->token_expires_at?->format('d.m.Y H:i') ?: '-' }}</p>
                                    <p><span class="font-semibold text-slate-700">İndirilme:</span> {{ $transaction->downloaded_at?->format('d.m.Y H:i') ?: '-' }}</p>
                                    <p><span class="font-semibold text-slate-700">Kayıt:</span> {{ $transaction->created_at?->format('d.m.Y H:i') ?: '-' }}</p>
                                </div>

                                @if($transaction->status === 'pending')
                                    <div class="mt-3 flex flex-wrap items-center gap-2">
                                        <form method="post" action="{{ route('admin.transactions.approve', $transaction) }}">
                                            @csrf
                                            <button class="btn-primary px-3 py-1.5 text-xs">Ödemeyi Onayla</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.transactions.reject', $transaction) }}" onsubmit="return confirm('Bu işlemi başarısız olarak işaretlemek istediğinize emin misiniz?')">
                                            @csrf
                                            <button class="btn-danger px-3 py-1.5 text-xs">Ödemeyi Reddet</button>
                                        </form>
                                    </div>
                                @endif

                                <div class="mt-3">
                                    <div class="mb-1 flex items-center justify-between gap-2">
                                        <p class="text-xs font-semibold text-slate-700">Callback Payload</p>
                                        <button
                                            type="button"
                                            class="btn-secondary px-2.5 py-1 text-xs copy-json-btn"
                                            data-copy='{{ $payloadJson }}'
                                        >
                                            Kopyala JSON
                                        </button>
                                    </div>
                                    <pre class="max-h-52 overflow-auto rounded-lg border border-slate-200 bg-white p-3 text-[11px] leading-relaxed text-slate-600">{{ $payloadJson }}</pre>
                                </div>
                            </div>
                        </details>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>

    <script>
        document.querySelectorAll('.copy-json-btn').forEach((button) => {
            button.addEventListener('click', async () => {
                const jsonText = button.getAttribute('data-copy') || '';
                try {
                    await navigator.clipboard.writeText(jsonText);
                    const oldText = button.textContent;
                    button.textContent = 'Kopyalandı';
                    setTimeout(() => {
                        button.textContent = oldText;
                    }, 1200);
                } catch (e) {
                    button.textContent = 'Kopyalanamadı';
                    setTimeout(() => {
                        button.textContent = 'Kopyala JSON';
                    }, 1200);
                }
            });
        });
    </script>
@endsection
