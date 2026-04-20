@extends('layouts.app')

@section('title', 'Satın Alım Doğrulama')

@section('content')
<section class="mx-auto max-w-4xl space-y-5">
    <div class="card p-5 md:p-6">
        <h1 class="text-2xl font-bold text-slate-900">Satın Alım Doğrulama</h1>
        <p class="mt-2 text-sm text-slate-600">Shopier üzerinden ödediğiniz siparişi burada doğrulama talebine gönderebilirsiniz.</p>

        <form method="post" action="{{ route('purchase.verification.store') }}" class="mt-5 grid gap-3 md:grid-cols-2">
            @csrf
            <label class="input-ui md:col-span-2">
                Ürün
                <select name="coloring_page_id" class="mt-1 w-full rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm" required>
                    <option value="">Ürün seçin</option>
                    @foreach($pages as $page)
                        <option value="{{ $page->id }}" @selected(old('coloring_page_id') == $page->id)>{{ $page->title }}</option>
                    @endforeach
                </select>
            </label>
            <div>
                <input class="input-ui w-full" name="order_no" value="{{ old('order_no') }}" placeholder="Shopier Sipariş No (örn: #492666041)" required>
                <p class="mt-1 text-xs text-slate-500">Sipariş numarasını Shopier hesabınızda <strong>Siparişler</strong> ekranında, sipariş detayında en üstte görebilirsiniz.</p>
            </div>
            <input class="input-ui" name="customer_name" value="{{ old('customer_name') }}" placeholder="Ad Soyad (opsiyonel)">
            <input type="email" class="input-ui" name="email" value="{{ old('email') }}" placeholder="Shopier'de kullandığınız e-posta" required>
            <input class="input-ui" name="phone" value="{{ old('phone') }}" placeholder="Shopier'de kullandığınız telefon (opsiyonel)">
            <div class="md:col-span-2 flex justify-end">
                <button class="btn-primary px-5">Doğrulama Talebi Gönder</button>
            </div>
        </form>
    </div>

    @if($verification)
        <div class="card p-5 md:p-6">
            <h2 class="text-lg font-semibold text-slate-900">Talep Durumu</h2>
            <div class="mt-3 grid gap-2 text-sm text-slate-700 md:grid-cols-2">
                <p><strong>Sipariş No:</strong> {{ $verification->order_no }}</p>
                <p><strong>Ürün:</strong> {{ $verification->coloringPage?->title }}</p>
                <p><strong>E-posta:</strong> {{ $verification->email }}</p>
                <p><strong>Durum:</strong>
                    @if($verification->status === 'approved')
                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Onaylandı</span>
                    @elseif($verification->status === 'rejected')
                        <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">Reddedildi</span>
                    @else
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">İncelemede</span>
                    @endif
                </p>
            </div>

            @if($verification->admin_note)
                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-600">
                    <strong>Admin notu:</strong> {{ $verification->admin_note }}
                </div>
            @endif

            @if($verification->status === 'approved' && $verification->transaction?->download_token)
                <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <p class="text-sm font-medium text-emerald-800">Doğrulama tamamlandı. Aşağıdaki seçenekleri kullanabilirsiniz.</p>
                    <a href="{{ route('download.paid', ['token' => $verification->transaction->download_token]) }}" class="btn-primary mt-3 w-full text-center md:w-auto">İndirme Sayfasına Git</a>
                    <form method="post" action="{{ route('purchase.verification.email', $verification->verification_token) }}" class="mt-3 flex flex-wrap gap-2">
                        @csrf
                        <input type="email" name="email" class="input-ui min-w-[260px] flex-1" value="{{ old('email', $verification->email) }}" required>
                        <button class="btn-secondary">Bağlantıyı E-posta Gönder</button>
                    </form>
                </div>
            @endif
        </div>
    @endif
</section>
@endsection
