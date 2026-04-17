@extends('layouts.admin')

@section('title', 'Sayfa Ayarları')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Dinamik Sayfa Ayarları</h1>
    <p class="mt-1 text-sm text-slate-500">Bu alanda yaptığınız değişiklikler mevcut ayarları günceller. Yeni değer yazıp "Değişiklikleri Güncelle" butonuna basmanız yeterlidir.</p>
    <form method="post" action="{{ route('admin.settings.update') }}" class="card mt-5 space-y-4 p-5">
        @csrf
        <textarea name="about" rows="4" class="input-ui" placeholder="Hakkımızda">{{ $settings['about'] ?? '' }}</textarea>
        <div class="grid gap-3 md:grid-cols-2">
            <input name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" class="input-ui" placeholder="İletişim telefonu (örn: +90 555 123 45 67)">
            <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}" class="input-ui" placeholder="İletişim e-posta (örn: info@alanadi.com)">
            <input name="contact_address" value="{{ $settings['contact_address'] ?? '' }}" class="input-ui md:col-span-2" placeholder="Adres (örn: Eskişehir, Türkiye)">
            <input type="url" name="map_embed_url" value="{{ $settings['map_embed_url'] ?? '' }}" class="input-ui md:col-span-2" placeholder="Google Maps Embed URL">
            <input type="url" name="social_tiktok_url" value="{{ $settings['social_tiktok_url'] ?? '' }}" class="input-ui" placeholder="TikTok URL">
            <input type="url" name="social_instagram_url" value="{{ $settings['social_instagram_url'] ?? '' }}" class="input-ui" placeholder="Instagram URL">
            <input type="url" name="social_youtube_url" value="{{ $settings['social_youtube_url'] ?? '' }}" class="input-ui md:col-span-2" placeholder="YouTube URL">
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
