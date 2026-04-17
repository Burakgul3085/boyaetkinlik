@extends('layouts.admin')

@section('title', 'Reklam Alanları')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Google Ads Alanları</h1>
    <p class="mt-1 text-sm text-slate-500">
        Bu ekranda reklam kodlarınızı ekleyip istediğiniz zaman güncelleyebilirsiniz. Kayıtlı kodlar değiştirilince eski kodun üstüne yazılır.
    </p>

    <div class="card mt-4 p-5">
        <h2 class="text-lg font-semibold text-slate-900">Adım Adım Kullanım Rehberi</h2>
        <ol class="mt-3 list-decimal space-y-2 pl-5 text-sm text-slate-600">
            <li>Siteyi canlıya alın ve Google AdSense hesabınızdan domain onayını tamamlayın.</li>
            <li>AdSense panelinden reklam birimi oluşturun (header, sol, sağ, ürün detay gibi).</li>
            <li>AdSense'in verdiği kodu kopyalayıp aşağıdaki ilgili kutuya yapıştırın.</li>
            <li><strong>Değişiklikleri Güncelle</strong> butonuna basın.</li>
            <li>Ana sitede ilgili alanı kontrol edin. Reklamlar bazen Google tarafında gecikmeli yayınlanabilir.</li>
            <li>Kod değişirse aynı kutuya yeni kodu yapıştırıp tekrar güncellemeniz yeterlidir.</li>
        </ol>

        <div class="mt-4 grid gap-3 text-xs text-slate-500 md:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3"><strong>Header reklam:</strong> Anasayfa üst bölüm reklam alanı.</div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3"><strong>Sol reklam:</strong> Anasayfa sol sidebar reklam alanı.</div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3"><strong>Sağ reklam:</strong> Anasayfa sağ sidebar reklam alanı.</div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3"><strong>Ürün detay reklam:</strong> Boyama detay sayfası reklam alanı.</div>
        </div>
    </div>

    <form method="post" action="{{ route('admin.ads.update') }}" class="card mt-5 space-y-4 p-5">
        @csrf
        <textarea name="ads_header" rows="4" class="input-ui" placeholder="Header reklam kodu / placeholder">{{ $settings['ads_header'] ?? '' }}</textarea>
        <textarea name="ads_left" rows="4" class="input-ui" placeholder="Sol reklam">{{ $settings['ads_left'] ?? '' }}</textarea>
        <textarea name="ads_right" rows="4" class="input-ui" placeholder="Sağ reklam">{{ $settings['ads_right'] ?? '' }}</textarea>
        <textarea name="ads_product_detail" rows="4" class="input-ui" placeholder="Ürün detay reklam">{{ $settings['ads_product_detail'] ?? '' }}</textarea>

        <div class="sticky bottom-3 mt-2 rounded-xl border border-indigo-100 bg-white/90 p-3 shadow-sm backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-slate-500">Yeni kod ekleyebilir veya mevcut reklam kodlarını istediğiniz zaman güncelleyebilirsiniz.</p>
                <button class="btn-primary px-5">Değişiklikleri Güncelle</button>
            </div>
        </div>
    </form>
@endsection
