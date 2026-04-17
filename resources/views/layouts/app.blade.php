<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Boya Etkinlik')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@php
    $navbarLinks = \App\Models\Setting::getValue('navbar_links', "Anasayfa|/\nİletişim|/iletisim");
    $contactPhone = \App\Models\Setting::getValue('contact_phone', '+90 555 000 00 00') ?: '+90 555 000 00 00';
    $contactEmail = \App\Models\Setting::getValue('contact_email', 'info@boyaetkinlik.com') ?: 'info@boyaetkinlik.com';
    $contactAddress = \App\Models\Setting::getValue('contact_address', 'Eskişehir, Türkiye') ?: 'Eskişehir, Türkiye';
    $mapEmbedUrl = \App\Models\Setting::getValue('map_embed_url', 'https://maps.google.com/maps?q=Eskisehir%2C%20Turkey&t=&z=12&ie=UTF8&iwloc=&output=embed') ?: 'https://maps.google.com/maps?q=Eskisehir%2C%20Turkey&t=&z=12&ie=UTF8&iwloc=&output=embed';
    $tiktokUrl = \App\Models\Setting::getValue('social_tiktok_url', '');
    $instagramUrl = \App\Models\Setting::getValue('social_instagram_url', '');
    $youtubeUrl = \App\Models\Setting::getValue('social_youtube_url', '');
    $phoneHref = preg_replace('/[^0-9\+]/', '', (string) $contactPhone);
    $isEmbeddableMapUrl = str_contains($mapEmbedUrl, 'output=embed') || str_contains($mapEmbedUrl, '/maps/embed');
    $resolvedMapEmbedUrl = $isEmbeddableMapUrl
        ? $mapEmbedUrl
        : 'https://maps.google.com/maps?q='.urlencode($contactAddress).'&t=&z=12&ie=UTF8&iwloc=&output=embed';
    $mapExternalUrl = $mapEmbedUrl ?: 'https://maps.google.com/?q='.urlencode($contactAddress);

    $links = collect(explode("\n", $navbarLinks))->map(function ($line) {
        [$label, $url] = array_pad(explode('|', $line), 2, '#');
        $label = trim($label);
        $url = trim($url);

        if ($label === '' || $url === '') {
            return null;
        }

        if (strtolower($label) === 'admin') {
            return null;
        }

        return ['label' => $label, 'url' => $url];
    })->filter()->values();

    if (! $links->contains(fn ($item) => $item['url'] === '/iletisim')) {
        $links->push(['label' => 'İletişim', 'url' => '/iletisim']);
    }
@endphp
<header class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/95 shadow-sm backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 lg:py-4">
        <a href="{{ route('home') }}" class="group inline-flex items-center gap-3 text-slate-800">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-md shadow-indigo-200 transition group-hover:scale-105">BE</span>
            <span class="text-lg font-bold tracking-tight text-slate-900 lg:text-xl">Boya Etkinlik</span>
        </a>
        <div class="flex items-center gap-1.5 rounded-2xl border border-slate-200 bg-slate-50/80 p-1 text-sm font-medium">
            @foreach($links as $item)
                <a class="rounded-xl px-3 py-2 text-slate-600 transition hover:bg-white hover:text-slate-900 hover:shadow-sm" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @endforeach
            @auth
                @if(auth()->user()->is_admin)
                    <a id="admin-nav-link" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700" href="{{ route('admin.dashboard') }}">Panel</a>
                @endif
            @endauth
        </div>
    </nav>
</header>

<main class="mx-auto max-w-7xl px-4 py-6">
    @yield('content')
</main>

<footer id="iletisim" class="mt-14 border-t border-slate-800 bg-slate-950 text-slate-200">
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 text-sm lg:grid-cols-3">
        <div>
            <p class="text-base font-semibold text-white">Boya Etkinlik Platformu</p>
            <p class="mt-2 text-slate-300">{{ \App\Models\Setting::getValue('footer_text', 'Tüm hakları saklıdır.') }}</p>
            <p class="mt-4 text-xs text-slate-400">Profesyonel, güvenli ve aile dostu boyama platformu.</p>
            @if($tiktokUrl || $instagramUrl || $youtubeUrl)
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if($tiktokUrl)
                        <a href="{{ $tiktokUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs text-slate-200 transition hover:bg-slate-800 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M16.5 3c.4 2.1 1.6 3.4 3.5 4v3.1c-1.1 0-2.2-.3-3.2-.8v6.2a5.8 5.8 0 1 1-5.3-5.8v3.1a2.7 2.7 0 1 0 2.2 2.7V3h2.8z"/>
                            </svg>
                            TikTok
                        </a>
                    @endif
                    @if($instagramUrl)
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs text-slate-200 transition hover:bg-slate-800 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <rect x="3.5" y="3.5" width="17" height="17" rx="5"/>
                                <circle cx="12" cy="12" r="4"/>
                                <circle cx="17.5" cy="6.5" r="1"/>
                            </svg>
                            Instagram
                        </a>
                    @endif
                    @if($youtubeUrl)
                        <a href="{{ $youtubeUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs text-slate-200 transition hover:bg-slate-800 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M23 12s0-3.2-.4-4.8a2.5 2.5 0 0 0-1.8-1.8C19.2 5 12 5 12 5s-7.2 0-8.8.4a2.5 2.5 0 0 0-1.8 1.8C1 8.8 1 12 1 12s0 3.2.4 4.8a2.5 2.5 0 0 0 1.8 1.8C4.8 19 12 19 12 19s7.2 0 8.8-.4a2.5 2.5 0 0 0 1.8-1.8c.4-1.6.4-4.8.4-4.8zM10 15.5v-7l6 3.5-6 3.5z"/>
                            </svg>
                            YouTube
                        </a>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <p class="font-medium text-white">İletişim</p>
            <div class="mt-4 space-y-2">
                <a href="tel:{{ $phoneHref }}" class="block rounded-lg bg-slate-900 px-3 py-2 text-slate-200 transition hover:bg-slate-800 hover:text-white">
                    Tel: {{ $contactPhone }}
                </a>
                <a href="mailto:{{ $contactEmail }}" class="block rounded-lg bg-slate-900 px-3 py-2 text-slate-200 transition hover:bg-slate-800 hover:text-white">
                    E-posta: {{ $contactEmail }}
                </a>
                <p class="rounded-lg bg-slate-900 px-3 py-2 text-slate-300">Adres: {{ $contactAddress }}</p>
            </div>
        </div>

        <div>
            <p class="font-medium text-white">Konum</p>
            <div class="mt-3 overflow-hidden rounded-xl border border-slate-800 shadow-lg">
                <iframe
                    src="{{ $resolvedMapEmbedUrl }}"
                    class="h-48 w-full"
                    style="border:0;"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    allowfullscreen
                ></iframe>
            </div>
            <a
                href="{{ $mapExternalUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-3 inline-flex rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-slate-200 transition hover:bg-slate-800 hover:text-white"
            >
                Haritada aç
            </a>
        </div>
    </div>
</footer>
</body>
</html>
