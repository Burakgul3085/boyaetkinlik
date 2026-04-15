@extends('layouts.admin')

@section('title', 'Sayfa Ayarlari')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Dinamik Sayfa Ayarlari</h1>
    <form method="post" action="{{ route('admin.settings.update') }}" class="card mt-5 space-y-4 p-5">
        @csrf
        <textarea name="about" rows="4" class="input-ui" placeholder="Hakkimizda">{{ $settings['about'] ?? '' }}</textarea>
        <textarea name="contact" rows="3" class="input-ui" placeholder="Iletisim">{{ $settings['contact'] ?? '' }}</textarea>
        <textarea name="vision" rows="3" class="input-ui" placeholder="Vizyon">{{ $settings['vision'] ?? '' }}</textarea>
        <textarea name="mission" rows="3" class="input-ui" placeholder="Misyon">{{ $settings['mission'] ?? '' }}</textarea>
        <textarea name="footer_text" rows="2" class="input-ui" placeholder="Footer yazisi">{{ $settings['footer_text'] ?? '' }}</textarea>
        <textarea name="navbar_links" rows="4" class="input-ui" placeholder="Anasayfa|/">{{ $settings['navbar_links'] ?? '' }}</textarea>

        <div class="rounded-xl border border-slate-200 p-4">
            <h2 class="mb-3 text-lg font-semibold text-slate-900">Shopier Ayarlari</h2>
            <div class="grid gap-3 md:grid-cols-2">
                <input name="shopier_api_key" value="{{ $settings['shopier_api_key'] ?? '' }}" class="input-ui" placeholder="Shopier API Key">
                <input name="shopier_website_index" value="{{ $settings['shopier_website_index'] ?? '' }}" class="input-ui" placeholder="Shopier Website Index">
                <input name="shopier_api_secret" value="{{ $settings['shopier_api_secret'] ?? '' }}" class="input-ui md:col-span-2" placeholder="Shopier API Secret">
                <input name="shopier_endpoint" value="{{ $settings['shopier_endpoint'] ?? 'https://www.shopier.com/ShowProduct/api_pay4.php' }}" class="input-ui md:col-span-2" placeholder="Shopier Endpoint">
            </div>
            <p class="mt-2 text-xs text-slate-500">Canliya gectiginizde .env yerine buradan da Shopier bilgilerini guncelleyebilirsin.</p>
        </div>

        <button class="btn-primary">Kaydet</button>
    </form>
@endsection
