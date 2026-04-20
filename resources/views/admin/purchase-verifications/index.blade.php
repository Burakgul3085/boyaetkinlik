@extends('layouts.admin')

@section('title', 'Satın Alım Doğrulama')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Satın Alım Doğrulama Talepleri</h1>
    <p class="mt-1 text-sm text-slate-600">Shopier üzerinden ödeme yaptığını bildiren kullanıcıları buradan onaylayabilir veya reddedebilirsiniz.</p>

    <div class="card mt-6 overflow-x-auto p-4">
        <table class="min-w-full text-sm">
            <thead>
            <tr class="text-left text-slate-500">
                <th class="py-2">Tarih</th>
                <th>Sipariş No</th>
                <th>Ürün</th>
                <th>E-posta</th>
                <th>Telefon</th>
                <th>Durum</th>
                <th>İşlem</th>
            </tr>
            </thead>
            <tbody>
            @forelse($requests as $item)
                <tr class="border-t align-top">
                    <td class="py-2">{{ $item->created_at?->format('d.m.Y H:i') }}</td>
                    <td>{{ $item->order_no }}</td>
                    <td>{{ $item->coloringPage?->title ?? '—' }}</td>
                    <td>{{ $item->email }}</td>
                    <td>{{ $item->phone ?: '—' }}</td>
                    <td>
                        @if($item->status === 'approved')
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Onaylandı</span>
                        @elseif($item->status === 'rejected')
                            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">Reddedildi</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Bekliyor</span>
                        @endif
                        @if($item->reviewer)
                            <p class="mt-1 text-[11px] text-slate-500">{{ $item->reviewer->display_name }}</p>
                        @endif
                    </td>
                    <td class="min-w-[260px] py-2">
                        @if($item->status === 'pending')
                            <form method="post" action="{{ route('admin.purchase-verifications.approve', $item) }}" class="space-y-2">
                                @csrf
                                <textarea name="admin_note" rows="2" class="input-ui w-full text-xs" placeholder="Not (opsiyonel)"></textarea>
                                <button class="btn-primary w-full">Onayla ve link gönder</button>
                            </form>
                            <form method="post" action="{{ route('admin.purchase-verifications.reject', $item) }}" class="mt-2 space-y-2">
                                @csrf
                                <textarea name="admin_note" rows="2" class="input-ui w-full text-xs" placeholder="Reddetme nedeni" required></textarea>
                                <button class="btn-danger w-full">Reddet</button>
                            </form>
                        @else
                            @if($item->transaction?->download_token)
                                <a href="{{ route('download.paid', ['token' => $item->transaction->download_token]) }}" target="_blank" rel="noopener noreferrer" class="btn-secondary inline-flex">İndirme linkini aç</a>
                            @endif
                            @if($item->admin_note)
                                <p class="mt-2 text-xs text-slate-500">Not: {{ $item->admin_note }}</p>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-6 text-center text-slate-500">Henüz doğrulama talebi yok.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
@endsection
