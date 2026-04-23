@extends('layouts.admin')

@section('title', 'Sayfa Ayarları')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Dinamik Sayfa Ayarları</h1>
    <p class="mt-1 text-sm text-slate-500">Bu alanda yaptığınız değişiklikler mevcut ayarları günceller. Yeni değer yazıp "Değişiklikleri Güncelle" butonuna basmanız yeterlidir.</p>
    @php
        $adminLogoPath = $settings['site_logo'] ?? '';
        $adminLogoUrl = ($adminLogoPath !== '' && \Illuminate\Support\Facades\Storage::disk('public')->exists($adminLogoPath))
            ? asset('storage/'.$adminLogoPath)
            : asset('images/site-logo.png');
    @endphp
    <form method="post" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="card mt-5 space-y-4 p-5">
        @csrf
        <div class="rounded-xl border border-slate-200 p-4">
            <h2 class="mb-3 text-lg font-semibold text-slate-900">Üst menü: logo ve site adı</h2>
            <p class="mb-3 text-xs text-slate-500">Logo PNG, JPG veya JPEG olabilir; ön yüzde kutunun içinde kırpılmadan gösterilir. Boş bırakırsanız mevcut logo korunur.</p>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-violet-100 bg-white shadow-sm">
                        <img src="{{ $adminLogoUrl }}" alt="" class="h-full w-full object-contain p-0.5">
                    </span>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600" for="site_logo">Yeni logo yükle</label>
                        <input id="site_logo" type="file" name="site_logo" accept=".png,.jpg,.jpeg,image/png,image/jpeg" class="block w-full max-w-xs text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-violet-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-violet-700 hover:file:bg-violet-100">
                    </div>
                </div>
                <div class="min-w-0 flex-1">
                    <label class="mb-1 block text-xs font-medium text-slate-600" for="header_site_name">Logo yanındaki site adı</label>
                    <input id="header_site_name" name="header_site_name" value="{{ $settings['header_site_name'] ?? '' }}" class="input-ui" placeholder="Örn: Boya Etkinlik">
                </div>
            </div>
            @error('site_logo')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('header_site_name')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <textarea name="about" rows="4" class="input-ui" placeholder="Hakkımızda">{{ $settings['about'] ?? '' }}</textarea>
        <div class="grid gap-3 md:grid-cols-2">
            <input name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" class="input-ui" placeholder="İletişim telefonu (örn: +90 555 123 45 67)">
            <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" class="input-ui" placeholder="İletişim e-posta (örn: info@alanadi.com)">
            <input name="contact_address" value="{{ $settings['contact_address'] ?? '' }}" class="input-ui md:col-span-2" placeholder="Adres (örn: Eskişehir, Türkiye)">
            <input type="url" name="map_embed_url" value="{{ $settings['map_embed_url'] ?? '' }}" class="input-ui md:col-span-2" placeholder="Google Maps Embed URL">
            <input type="url" name="social_tiktok_url" value="{{ $settings['social_tiktok_url'] ?? '' }}" class="input-ui" placeholder="TikTok URL">
            <input type="url" name="social_instagram_url" value="{{ $settings['social_instagram_url'] ?? '' }}" class="input-ui" placeholder="Instagram URL">
            <input type="url" name="social_youtube_url" value="{{ $settings['social_youtube_url'] ?? '' }}" class="input-ui" placeholder="YouTube URL">
            <input type="url" name="social_pinterest_url" value="{{ $settings['social_pinterest_url'] ?? '' }}" class="input-ui" placeholder="Pinterest URL">
            <input type="url" name="social_dailymotion_url" value="{{ $settings['social_dailymotion_url'] ?? '' }}" class="input-ui md:col-span-2" placeholder="Dailymotion URL">
        </div>
        <textarea name="vision" rows="3" class="input-ui" placeholder="Vizyon">{{ $settings['vision'] ?? '' }}</textarea>
        <textarea name="mission" rows="3" class="input-ui" placeholder="Misyon">{{ $settings['mission'] ?? '' }}</textarea>
        <textarea name="footer_text" rows="2" class="input-ui" placeholder="Footer yazısı">{{ $settings['footer_text'] ?? '' }}</textarea>
        <textarea name="navbar_links" rows="4" class="input-ui" placeholder="Anasayfa|/">{{ $settings['navbar_links'] ?? '' }}</textarea>

        <div class="rounded-xl border border-slate-200 p-4">
            <h2 class="mb-3 text-lg font-semibold text-slate-900">İletişim Formu SMTP Ayarları (PHPMailer)</h2>
            <div class="grid gap-3 md:grid-cols-2">
                <input name="smtp_host" value="{{ $settings['smtp_host'] ?? '' }}" class="input-ui" placeholder="SMTP Host (örn: smtp.gmail.com)">
                <input type="number" name="smtp_port" value="{{ $settings['smtp_port'] ?? '587' }}" class="input-ui" placeholder="SMTP Port (587)">
                <input name="smtp_username" value="{{ $settings['smtp_username'] ?? '' }}" class="input-ui md:col-span-2" placeholder="SMTP Kullanıcı Adı (Gmail adresi)">
                <input name="smtp_password" value="{{ $settings['smtp_password'] ?? '' }}" class="input-ui md:col-span-2" placeholder="SMTP Şifre / App Password">
                <select name="smtp_encryption" class="input-ui">
                    <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS</option>
                    <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                </select>
                <input type="email" name="smtp_from_email" value="{{ $settings['smtp_from_email'] ?? '' }}" class="input-ui" placeholder="Gönderen E-posta">
                <input name="smtp_from_name" value="{{ $settings['smtp_from_name'] ?? 'Boya Etkinlik İletişim' }}" class="input-ui md:col-span-2" placeholder="Gönderen İsmi">
            </div>
            <p class="mt-2 text-xs text-slate-500">İletişim formu mesajları, yukarıdaki SMTP ayarlarıyla footerdaki iletişim e-posta adresine gönderilir.</p>
        </div>

        <div class="rounded-xl border border-slate-200 p-4">
            <h2 class="mb-3 text-lg font-semibold text-slate-900">Shopier Ayarları</h2>
            <div class="grid gap-3 md:grid-cols-2">
                <input name="shopier_api_key" value="{{ $settings['shopier_api_key'] ?? '' }}" class="input-ui" placeholder="Shopier API Key">
                <input name="shopier_website_index" value="{{ $settings['shopier_website_index'] ?? '' }}" class="input-ui" placeholder="Shopier Website Index">
                <input name="shopier_api_secret" value="{{ $settings['shopier_api_secret'] ?? '' }}" class="input-ui md:col-span-2" placeholder="Shopier API Secret">
                <input name="shopier_endpoint" value="{{ $settings['shopier_endpoint'] ?? 'https://www.shopier.com/ShowProduct/api_pay4.php' }}" class="input-ui md:col-span-2" placeholder="Shopier Endpoint">
            </div>
            <p class="mt-2 text-xs text-slate-500">Canlıya geçtiğinizde .env yerine buradan da Shopier bilgilerini güncelleyebilirsiniz.</p>
        </div>

        <div class="sticky bottom-3 mt-2 rounded-xl border border-indigo-100 bg-white/90 p-3 shadow-sm backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-slate-500">Örnek: telefon numarasını değiştirip bu butona bastığınızda eski kayıt güncellenir.</p>
                <button class="btn-primary px-5">Değişiklikleri Güncelle</button>
            </div>
        </div>
    </form>
@endsection
