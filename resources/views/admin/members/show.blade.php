@extends('layouts.admin')

@section('title', 'Üye: '.$member->display_name)

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm text-slate-500"><a href="{{ route('admin.members.index') }}" class="text-indigo-600 hover:underline">← Üyeler</a></p>
            <h1 class="mt-2 text-3xl font-bold text-slate-900">{{ $member->display_name }}</h1>
            <p class="mt-1 text-sm text-slate-500">Üye #{{ $member->id }} · Kayıt {{ $member->created_at?->format('d.m.Y H:i') }}</p>
        </div>
        <a href="{{ route('admin.members.index') }}" class="btn-secondary">Listeye dön</a>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="card p-5 lg:col-span-1">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Profil</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">Ad</dt>
                    <dd class="font-medium text-slate-900">{{ $member->first_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Soyad</dt>
                    <dd class="font-medium text-slate-900">{{ $member->last_name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Görünen ad (name)</dt>
                    <dd class="font-medium text-slate-900">{{ $member->name ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">E-posta</dt>
                    <dd class="break-all font-medium text-slate-900">{{ $member->email }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">E-posta doğrulama</dt>
                    <dd>
                        @if($member->email_verified_at)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">{{ $member->email_verified_at->format('d.m.Y H:i') }}</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Henüz doğrulanmadı</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Şifre</dt>
                    <dd class="text-slate-600">Güvenlik nedeniyle görüntülenmez (yalnızca hash saklanır).</dd>
                </div>
            </dl>
        </div>

        <div class="card p-5 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Sepet</h2>
                <span class="text-sm text-slate-600">
                    Tahmini tutar: <strong class="text-indigo-700">{{ number_format($cartTotal, 2) }} TL</strong>
                    <span class="text-xs text-slate-400">(ürün fiyatları üzerinden)</span>
                </span>
            </div>

            @if($member->cartItems->isEmpty())
                <p class="mt-6 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Sepette ürün yok.</p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="py-2">Ürün</th>
                                <th>Kategori</th>
                                <th>Fiyat</th>
                                <th>Eklenme</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($member->cartItems as $item)
                            <tr class="border-t border-slate-100">
                                <td class="py-2 font-medium text-slate-900">{{ $item->coloringPage->title ?? 'Silinmiş' }}</td>
                                <td class="text-slate-600">{{ $item->coloringPage->category->name ?? '—' }}</td>
                                <td>{{ number_format($item->coloringPage->price ?? 0, 2) }} TL</td>
                                <td class="whitespace-nowrap text-slate-500">{{ $item->created_at?->format('d.m.Y H:i') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-6 card p-5">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Satın alınanlar (ödeme tamamlandı)</h2>
        @if($paidTransactions->isEmpty())
            <p class="mt-4 text-sm text-slate-500">Tamamlanmış satın alma kaydı yok.</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-2">Sipariş</th>
                            <th>Ürün</th>
                            <th>Tutar</th>
                            <th>İndirme</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($paidTransactions as $tx)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-mono text-xs text-slate-700">{{ $tx->order_id }}</td>
                            <td class="font-medium text-slate-900">{{ $tx->coloringPage->title ?? 'Silinmiş ürün' }}</td>
                            <td>{{ number_format($tx->paid_amount, 2) }} TL</td>
                            <td>
                                @if($tx->downloaded_at)
                                    <span class="text-emerald-700">{{ $tx->downloaded_at->format('d.m.Y H:i') }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-slate-600">{{ $tx->created_at?->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    @if($otherTransactions->isNotEmpty())
        <div class="mt-6 card p-5">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Diğer işlemler (bekleyen / başarısız)</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-2">Sipariş</th>
                            <th>Ürün</th>
                            <th>Durum</th>
                            <th>Tutar</th>
                            <th>Tarih</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($otherTransactions as $tx)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-mono text-xs">{{ $tx->order_id }}</td>
                            <td>{{ $tx->coloringPage->title ?? '—' }}</td>
                            <td>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $tx->status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $tx->status === 'pending' ? 'Beklemede' : ucfirst($tx->status) }}
                                </span>
                            </td>
                            <td>{{ number_format($tx->paid_amount, 2) }} TL</td>
                            <td class="whitespace-nowrap">{{ $tx->created_at?->format('d.m.Y H:i') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="mt-6 card p-5">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Satın alma destek mesajları</h2>
        <p class="mt-1 text-xs text-slate-500">Üyenin satın alımlar sayfasından gönderdiği mesajlar ve yanıtlarınız.</p>

        @forelse($member->purchaseSupportTickets as $st)
            <div class="mt-6 rounded-2xl border border-violet-100 bg-violet-50/30 p-4 first:mt-4">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <p class="text-xs text-slate-500">#{{ $st->id }} · {{ $st->created_at?->format('d.m.Y H:i') }}</p>
                    @if($st->isAnswered())
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Yanıtlandı</span>
                    @else
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">Yanıt bekliyor</span>
                    @endif
                </div>
                @if($st->transaction_id)
                    <p class="mt-2 text-sm text-slate-600">
                        <span class="font-mono text-xs">{{ $st->transaction->order_id ?? '—' }}</span>
                        — {{ $st->transaction->coloringPage->title ?? 'Ürün' }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-slate-600">Genel / sipariş seçilmedi</p>
                @endif
                <div class="mt-3 rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-800 whitespace-pre-wrap">{{ $st->member_message }}</div>
                @if($st->isAnswered())
                    <div class="mt-3 rounded-xl border border-emerald-200 bg-emerald-50/50 p-3">
                        <p class="text-xs font-semibold text-emerald-900">Yanıtınız {{ $st->admin_replied_at?->format('d.m.Y H:i') }}</p>
                        <p class="mt-1 whitespace-pre-wrap text-sm text-slate-800">{{ $st->admin_reply }}</p>
                    </div>
                @endif
                <form method="post" action="{{ route('admin.members.support.reply', [$member, $st]) }}" class="mt-4 space-y-2 border-t border-dashed border-violet-200 pt-4">
                    @csrf
                    <input type="hidden" name="responding_ticket_id" value="{{ $st->id }}">
                    <label class="text-xs font-medium text-slate-600">{{ $st->isAnswered() ? 'Yanıtı güncelle' : 'Yanıt yaz' }} (üyeye e-posta gider)</label>
                    <textarea name="admin_reply" rows="4" required class="input-ui w-full" placeholder="Yanıtınız...">{{ (string) old('responding_ticket_id') === (string) $st->id ? old('admin_reply', $st->admin_reply) : ($st->admin_reply ?? '') }}</textarea>
                    @if((string) old('responding_ticket_id') === (string) $st->id)
                        @error('admin_reply')
                            <p class="text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    @endif
                    <button type="submit" class="btn-primary text-sm">Kaydet ve e-postayla gönder</button>
                </form>
            </div>
        @empty
            <p class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500">Bu üyeden henüz destek mesajı yok.</p>
        @endforelse
    </div>
@endsection
