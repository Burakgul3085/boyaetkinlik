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

    $dynamicMenuItems = \App\Models\Category::query()
        ->whereNull('parent_id')
        ->where('show_in_nav', true)
        ->with(['children' => fn ($query) => $query->where('show_in_nav', true)->orderBy('nav_order')->orderBy('name')])
        ->orderBy('nav_order')
        ->orderBy('name')
        ->get()
        ->map(function ($category) {
            return [
                'label' => $category->name,
                'url' => route('categories.show', ['slug' => $category->slug]),
                'children' => $category->children->map(function ($child) {
                    return [
                        'label' => $child->name,
                        'url' => route('categories.show', ['slug' => $child->slug]),
                    ];
                })->values()->all(),
            ];
        })
        ->values();

    $hasHome = $links->contains(fn ($item) => $item['url'] === '/');
    $hasContact = $links->contains(fn ($item) => $item['url'] === '/iletisim');

    $menuItems = collect();

    if ($hasHome) {
        $homeItem = $links->first(fn ($item) => $item['url'] === '/');
        $menuItems->push(['label' => $homeItem['label'], 'url' => $homeItem['url'], 'children' => []]);
    } else {
        $menuItems->push(['label' => 'Anasayfa', 'url' => '/', 'children' => []]);
    }

    foreach ($dynamicMenuItems as $item) {
        $menuItems->push($item);
    }

    if ($hasContact) {
        $contactItem = $links->first(fn ($item) => $item['url'] === '/iletisim');
        $menuItems->push(['label' => $contactItem['label'], 'url' => $contactItem['url'], 'children' => []]);
    } else {
        $menuItems->push(['label' => 'İletişim', 'url' => '/iletisim', 'children' => []]);
    }
@endphp
<header class="sticky top-0 z-40 border-b border-violet-100 bg-white/90 shadow-sm backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 lg:py-4">
        <a href="{{ route('home') }}" class="group inline-flex items-center gap-3 text-slate-800">
            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-sm font-bold text-white shadow-md shadow-indigo-200 transition group-hover:scale-105">BE</span>
            <span class="text-lg font-bold tracking-tight text-slate-900 lg:text-xl">Boya Etkinlik</span>
        </a>
        <div class="flex items-center gap-1.5 rounded-2xl border border-violet-100 bg-violet-50/70 p-1 text-sm font-medium">
            @foreach($menuItems as $item)
                @if(! empty($item['children']))
                    <div class="group relative">
                        <a
                            href="{{ $item['url'] }}"
                            class="inline-flex items-center gap-1 rounded-xl px-3 py-2 text-slate-700 transition hover:bg-white hover:text-violet-700 hover:shadow-sm"
                        >
                            {{ $item['label'] }}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                            </svg>
                        </a>
                        <div class="invisible pointer-events-none absolute left-0 top-full z-50 w-52 pt-2 opacity-0 transition duration-150 group-hover:visible group-hover:pointer-events-auto group-hover:opacity-100">
                            <div class="translate-y-1 rounded-xl border border-violet-200 bg-white p-2 shadow-lg transition duration-150 group-hover:translate-y-0">
                                @foreach($item['children'] as $child)
                                    <a
                                        href="{{ $child['url'] }}"
                                        class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-violet-50 hover:text-violet-700"
                                    >
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <a class="rounded-xl px-3 py-2 text-slate-700 transition hover:bg-white hover:text-violet-700 hover:shadow-sm" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @endif
            @endforeach
            @auth
                @if(auth()->user()->is_admin)
                    <a id="admin-nav-link" class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700" href="{{ route('admin.dashboard') }}">Panel</a>
                @endif
            @endauth
        </div>
    </nav>
</header>

<main class="mx-auto max-w-7xl px-4 py-6">
    @yield('content')
</main>

<footer id="iletisim" class="mt-14 border-t border-white/20 bg-gradient-to-br from-indigo-950 via-violet-900 to-fuchsia-900 text-white">
    <div class="mx-auto max-w-7xl px-4 py-7 text-sm">
        <div class="grid gap-4 lg:grid-cols-12">
        <div class="rounded-2xl border border-white/25 bg-gradient-to-br from-white/15 to-white/5 p-4 lg:col-span-3">
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

        <div class="rounded-2xl border border-white/25 bg-gradient-to-br from-white/15 to-white/5 p-4 lg:col-span-3">
            <p class="font-medium text-white">Hızlı Linkler</p>
            <div class="mt-3 max-h-56 space-y-2 overflow-y-auto pr-1">
                @foreach($menuItems as $item)
                    @if(! empty($item['children']))
                        <div class="group rounded-lg border border-white/20 bg-white/10">
                            <a
                                href="{{ $item['url'] }}"
                                class="flex items-center justify-between rounded-lg px-3 py-2 text-slate-200 transition hover:bg-slate-800 hover:text-white group-focus-within:bg-slate-800"
                            >
                                <span>{{ $item['label'] }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400 transition duration-200 group-hover:rotate-180 group-focus-within:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <div class="max-h-0 overflow-hidden px-2 opacity-0 transition-all duration-200 group-hover:max-h-80 group-hover:pb-2 group-hover:opacity-100 group-focus-within:max-h-80 group-focus-within:pb-2 group-focus-within:opacity-100">
                                @foreach($item['children'] as $child)
                                    <a
                                        href="{{ $child['url'] }}"
                                        class="mt-1 block rounded-lg bg-white/10 px-3 py-2 text-slate-100 transition hover:bg-white/20 hover:text-white"
                                    >
                                        - {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a
                            href="{{ $item['url'] }}"
                            class="block rounded-lg bg-white/10 px-3 py-2 text-slate-100 transition hover:bg-white/20 hover:text-white"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-white/25 bg-gradient-to-br from-white/15 to-white/5 p-4 lg:col-span-3">
            <p class="font-medium text-white">İletişim</p>
            <div class="mt-3 space-y-2">
                <a href="tel:{{ $phoneHref }}" class="block rounded-lg bg-white/10 px-3 py-2 text-slate-100 transition hover:bg-white/20 hover:text-white">
                    Tel: {{ $contactPhone }}
                </a>
                <a href="mailto:{{ $contactEmail }}" class="block rounded-lg bg-white/10 px-3 py-2 text-slate-100 transition hover:bg-white/20 hover:text-white">
                    E-posta: {{ $contactEmail }}
                </a>
                <p class="rounded-lg bg-white/10 px-3 py-2 text-slate-100">Adres: {{ $contactAddress }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-white/25 bg-gradient-to-br from-white/15 to-white/5 p-4 lg:col-span-3">
            <p class="font-medium text-white">Konum</p>
            <div class="mt-3 overflow-hidden rounded-xl border border-slate-800 shadow-lg">
                <iframe
                    src="{{ $resolvedMapEmbedUrl }}"
                    class="h-36 w-full"
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
                class="mt-3 inline-flex rounded-lg bg-white/10 px-3 py-2 text-xs font-medium text-slate-100 transition hover:bg-white/20 hover:text-white"
            >
                Haritada aç
            </a>
        </div>

        <div class="rounded-2xl border border-white/25 bg-gradient-to-r from-white/15 to-white/5 p-4 lg:col-span-12">
            <p class="text-sm font-semibold text-white">E-Bülten</p>
            <p class="mt-1 text-xs text-slate-400">Yeni içerik ve duyurular için kayıt olun.</p>

            @if(session('newsletter_success'))
                <div class="mt-3 rounded-lg border border-emerald-700/60 bg-emerald-900/30 px-3 py-2 text-xs text-emerald-200">
                    {{ session('newsletter_success') }}
                </div>
            @endif

            @if($errors->has('first_name') || $errors->has('last_name') || $errors->has('email') || $errors->has('newsletter'))
                <div class="mt-3 rounded-lg border border-rose-700/60 bg-rose-900/30 px-3 py-2 text-xs text-rose-200">
                    {{ $errors->first('first_name') ?: $errors->first('last_name') ?: $errors->first('email') ?: $errors->first('newsletter') }}
                </div>
            @endif

            <form method="post" action="{{ route('newsletter.store') }}" class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                @csrf
                <input
                    type="text"
                    name="first_name"
                    required
                    value="{{ old('first_name') }}"
                    placeholder="İsim"
                    class="w-full rounded-lg border border-white/40 bg-white/10 px-3 py-2 text-xs text-white placeholder:text-white/70 focus:border-white focus:outline-none"
                >
                <input
                    type="text"
                    name="last_name"
                    required
                    value="{{ old('last_name') }}"
                    placeholder="Soyisim"
                    class="w-full rounded-lg border border-white/40 bg-white/10 px-3 py-2 text-xs text-white placeholder:text-white/70 focus:border-white focus:outline-none"
                >
                <input
                    type="email"
                    name="email"
                    required
                    value="{{ old('email') }}"
                    placeholder="E-posta"
                    class="w-full rounded-lg border border-white/40 bg-white/10 px-3 py-2 text-xs text-white placeholder:text-white/70 focus:border-white focus:outline-none"
                >
                <button class="w-full rounded-lg bg-gradient-to-r from-fuchsia-500 via-violet-500 to-indigo-500 px-3 py-2 text-xs font-semibold text-white transition hover:brightness-110">
                    E-Bültene Kaydol
                </button>
            </form>
        </div>
        </div>
    </div>
    <div class="border-t border-white/20">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2 px-4 py-3 text-xs text-indigo-100/90">
            <p>© 2026 Burak Gül tarafından geliştirilmiştir.</p>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="mailto:burakgul3085@gmail.com"
                    class="rounded-md px-2 py-1 font-medium text-white/90 transition hover:bg-white/10 hover:text-white"
                >
                    burakgul3085@gmail.com
                </a>
                <a
                    href="https://www.linkedin.com/in/burakgul1006/"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="rounded-md px-2 py-1 font-medium text-fuchsia-200 transition hover:bg-white/10 hover:text-fuchsia-100"
                >
                    LinkedIn
                </a>
            </div>
        </div>
    </div>
</footer>

<script>
    // Sayfa genelinde sürüklemeyi kapat (görsel/link dahil).
    document.addEventListener('dragstart', function (event) {
        event.preventDefault();
    });

    document.addEventListener('drop', function (event) {
        event.preventDefault();
    });

    document.addEventListener('dragover', function (event) {
        event.preventDefault();
    });
</script>
</body>
</html>
