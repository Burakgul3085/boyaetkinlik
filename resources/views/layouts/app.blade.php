<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Boya Etkinlik')</title>
    {{-- Sekme ikonu + Google arama sonuçlarında site ikonu (tarayıcılar /favicon.ico veya rel=icon kullanır) --}}
    <link rel="icon" type="image/png" href="{{ asset('images/site-logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/site-logo.png') }}">
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('site-theme');
                if (saved === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1312868815768552" crossorigin="anonymous"></script>
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
    $pinterestUrl = \App\Models\Setting::getValue('social_pinterest_url', '');
    $dailymotionUrl = \App\Models\Setting::getValue('social_dailymotion_url', '');
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

    $adminPathTrim = trim((string) config('app.admin_path', 'yonetim-981400-panel'), '/');
    $requestPath = request()->path();
    $onPublicSiteSurface = ! request()->routeIs('contact.show', 'contact.send', 'contact.whatsapp')
        && ! str_starts_with($requestPath, $adminPathTrim);
    $stickyFooterAdHtml = (string) \App\Models\Setting::getValue('ads_footer', '');
    $hasStickyFooterAd = $onPublicSiteSurface && trim($stickyFooterAdHtml) !== '';
    $memberCartCount = (! auth()->check() || auth()->user()->is_admin)
        ? 0
        : \App\Models\CartItem::query()->where('user_id', auth()->id())->count();
    $memberPurchasesCount = (! auth()->check() || auth()->user()->is_admin)
        ? 0
        : \App\Models\Transaction::query()
            ->where('status', 'paid')
            ->where(function ($query) {
                $user = auth()->user();
                $query->where('user_id', $user->id)
                    ->orWhere(function ($legacyQuery) use ($user) {
                        $legacyQuery->whereNull('user_id')
                            ->where('email', $user->email);
                    });
            })
            ->count();
@endphp
{{-- Mobil tam ekran overlay burada tutulur: header içinde backdrop-blur varken fixed çocuklar bazı tarayıcılarda viewport yerine header kutusuna hizalanıyordu. --}}
<div
    x-data="{ mobileNavOpen: false }"
    x-effect="
        if (mobileNavOpen) {
            document.documentElement.classList.add('overflow-hidden');
            document.body.classList.add('overflow-hidden');
        } else {
            document.documentElement.classList.remove('overflow-hidden');
            document.body.classList.remove('overflow-hidden');
        }
    "
>
<header
    class="sticky top-0 z-40 border-b border-violet-100 bg-white/90 shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-900/95"
>
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-3 lg:gap-4 lg:py-4">
        <a href="{{ route('home') }}" class="group flex min-w-0 max-w-[55%] items-center gap-2 text-slate-800 sm:max-w-none sm:gap-3">
            <span class="logo-anim-wrap inline-flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-violet-100 bg-white shadow-md shadow-indigo-200/50 transition group-hover:scale-105 sm:h-12 sm:w-12">
                <img src="{{ asset('images/site-logo.png') }}" alt="Boya Etkinlik Logo" class="logo-anim-img h-full w-full object-cover">
            </span>
            <span class="truncate text-base font-bold tracking-tight text-slate-900 sm:text-lg lg:text-xl">Boya Etkinlik</span>
        </a>

        {{-- Masaüstü: tam menü --}}
        <div class="hidden min-w-0 flex-1 items-center justify-end gap-2 lg:flex">
            <div class="flex max-w-full flex-wrap items-center justify-end gap-1.5 rounded-2xl border border-violet-100 bg-violet-50/70 p-1 text-sm font-medium">
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
            </div>
            <div class="flex items-center gap-1.5 rounded-2xl border border-violet-100 bg-white/90 p-1 text-sm font-medium">
                @auth
                    @if(auth()->user()->is_admin)
                        <a id="admin-nav-link" class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700" href="{{ route('admin.dashboard') }}">Panel</a>
                    @else
                        <div class="relative" x-data="{ open: false }">
                            <button
                                type="button"
                                @click="open = !open"
                                :aria-expanded="open ? 'true' : 'false'"
                                class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-violet-200 bg-white text-violet-700 shadow-sm transition hover:border-violet-300 hover:text-violet-800"
                                aria-label="Profil menüsü"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-6 14a6 6 0 1 1 12 0v.75a.75.75 0 0 1-.75.75h-10.5A.75.75 0 0 1 4 16.75V16Z"/>
                                </svg>
                            </button>

                            <div
                                x-show="open"
                                x-transition.opacity.duration.150ms
                                x-cloak
                                @click.outside="open = false"
                                @keydown.escape.window="open = false"
                                class="absolute right-0 top-full z-50 mt-2 w-48"
                            >
                                <div class="rounded-2xl border border-violet-200 bg-white p-2 shadow-lg">
                                    <a href="{{ route('member.account') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-violet-50 hover:text-violet-700">Hesabım</a>
                                    <a href="{{ route('member.cart') }}" class="mt-1 flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-violet-50 hover:text-violet-700">
                                        <span>Sepetim</span>
                                        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700">{{ $memberCartCount }}</span>
                                    </a>
                                    <a href="{{ route('member.purchases') }}" class="mt-1 flex items-center justify-between rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-violet-50 hover:text-violet-700">
                                        <span>Satın Alınanlar</span>
                                        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700">{{ $memberPurchasesCount }}</span>
                                    </a>
                                    <form method="post" action="{{ route('member.logout') }}" class="mt-1">
                                        @csrf
                                        <button class="block w-full rounded-xl px-3 py-2 text-left text-sm font-medium text-rose-600 transition hover:bg-rose-50 hover:text-rose-700">Çıkış</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <a class="inline-flex items-center justify-center rounded-xl border border-violet-200 bg-white px-3.5 py-2 text-sm font-semibold text-violet-700 transition hover:border-violet-300 hover:text-violet-800 hover:shadow-sm" href="{{ route('member.login') }}">Giriş Yap</a>
                    <a class="inline-flex items-center justify-center rounded-xl bg-violet-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700" href="{{ route('member.register') }}">Üye Ol</a>
                @endauth
            </div>
            <button type="button" class="theme-switch-btn" data-theme-toggle aria-label="Temayı değiştir" title="Koyu / açık tema">
                <span class="theme-switch-thumb">
                    <svg data-theme-icon-sun xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 hidden" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 2a.75.75 0 0 1 .75.75V4a.75.75 0 0 1-1.5 0V2.75A.75.75 0 0 1 10 2ZM10 15.25a.75.75 0 0 1 .75.75v1.25a.75.75 0 0 1-1.5 0V16a.75.75 0 0 1 .75-.75ZM4 9.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H4Zm13.25 0a.75.75 0 0 1 0 1.5H16a.75.75 0 0 1 0-1.5h1.25ZM5.47 4.53a.75.75 0 0 1 1.06 0l.88.88a.75.75 0 1 1-1.06 1.06l-.88-.88a.75.75 0 0 1 0-1.06Zm8 8a.75.75 0 0 1 1.06 0l.88.88a.75.75 0 1 1-1.06 1.06l-.88-.88a.75.75 0 0 1 0-1.06Zm1.94-8a.75.75 0 0 1 0 1.06l-.88.88a.75.75 0 1 1-1.06-1.06l.88-.88a.75.75 0 0 1 1.06 0Zm-8 8a.75.75 0 0 1 0 1.06l-.88.88a.75.75 0 1 1-1.06-1.06l.88-.88a.75.75 0 0 1 1.06 0ZM10 6a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z"/>
                    </svg>
                    <svg data-theme-icon-moon xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M11.55 3.056A7 7 0 1 0 16.944 8.45a.75.75 0 0 0-1.161-.836 5.5 5.5 0 0 1-7.397-7.397.75.75 0 0 0-.836-1.161Z"/>
                    </svg>
                </span>
            </button>
        </div>

        {{-- Mobil: tema + hamburger (menü masaüstünde) --}}
        <div class="flex shrink-0 items-center gap-2 lg:hidden">
            <button type="button" class="theme-switch-btn" data-theme-toggle aria-label="Temayı değiştir" title="Koyu / açık tema">
                <span class="theme-switch-thumb">
                    <svg data-theme-icon-sun xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 hidden" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 2a.75.75 0 0 1 .75.75V4a.75.75 0 0 1-1.5 0V2.75A.75.75 0 0 1 10 2ZM10 15.25a.75.75 0 0 1 .75.75v1.25a.75.75 0 0 1-1.5 0V16a.75.75 0 0 1 .75-.75ZM4 9.25a.75.75 0 0 1 0 1.5H2.75a.75.75 0 0 1 0-1.5H4Zm13.25 0a.75.75 0 0 1 0 1.5H16a.75.75 0 0 1 0-1.5h1.25ZM5.47 4.53a.75.75 0 0 1 1.06 0l.88.88a.75.75 0 1 1-1.06 1.06l-.88-.88a.75.75 0 0 1 0-1.06Zm8 8a.75.75 0 0 1 1.06 0l.88.88a.75.75 0 1 1-1.06 1.06l-.88-.88a.75.75 0 0 1 0-1.06Zm1.94-8a.75.75 0 0 1 0 1.06l-.88.88a.75.75 0 1 1-1.06-1.06l.88-.88a.75.75 0 0 1 1.06 0Zm-8 8a.75.75 0 0 1 0 1.06l-.88.88a.75.75 0 1 1-1.06-1.06l.88-.88a.75.75 0 0 1 1.06 0ZM10 6a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z"/>
                    </svg>
                    <svg data-theme-icon-moon xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M11.55 3.056A7 7 0 1 0 16.944 8.45a.75.75 0 0 0-1.161-.836 5.5 5.5 0 0 1-7.397-7.397.75.75 0 0 0-.836-1.161Z"/>
                    </svg>
                </span>
            </button>
            <button
                type="button"
                class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-violet-200 bg-white text-violet-700 shadow-sm transition hover:border-violet-300 hover:text-violet-800 dark:border-slate-600 dark:bg-slate-800 dark:text-violet-300"
                aria-label="Menüyü aç"
                :aria-expanded="mobileNavOpen ? 'true' : 'false'"
                @click="mobileNavOpen = true"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </nav>
</header>

    {{-- Mobil yan menü: header DIŞINDA (backdrop-blur ile fixed çakışması yok) --}}
    <div
        class="lg:hidden"
        x-show="mobileNavOpen"
        x-cloak
        role="dialog"
        aria-modal="true"
        aria-label="Site menüsü"
    >
        <div class="fixed inset-0 z-[9998] bg-slate-900/50 backdrop-blur-sm" @click="mobileNavOpen = false"></div>
        <div class="fixed inset-y-0 right-0 z-[9999] flex h-screen max-h-[100dvh] w-[min(100%,20rem)] min-h-0 flex-col border-l border-violet-100 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
            <div class="flex shrink-0 items-center justify-between gap-3 border-b border-violet-100 px-4 py-3 dark:border-slate-700">
                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Menü</p>
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-violet-200 bg-violet-50 text-violet-800 transition hover:bg-violet-100 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200"
                    aria-label="Menüyü kapat"
                    @click="mobileNavOpen = false"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <nav class="min-h-0 flex-1 overflow-y-auto overscroll-y-contain px-4 pb-8 pt-2 [-webkit-overflow-scrolling:touch]" style="padding-bottom: max(2rem, env(safe-area-inset-bottom, 0px));">
                @foreach($menuItems as $item)
                    @if(! empty($item['children']))
                        <div class="mb-3">
                            <a href="{{ $item['url'] }}" class="block rounded-xl px-3 py-2.5 text-base font-semibold text-slate-800 transition hover:bg-violet-50 dark:text-slate-100 dark:hover:bg-slate-800" @click="mobileNavOpen = false">{{ $item['label'] }}</a>
                            <div class="ml-2 mt-1 space-y-0.5 border-l-2 border-violet-100 pl-3 dark:border-slate-600">
                                @foreach($item['children'] as $child)
                                    <a href="{{ $child['url'] }}" class="block rounded-lg py-2 pl-1 text-sm text-slate-600 transition hover:text-violet-700 dark:text-slate-300 dark:hover:text-violet-300" @click="mobileNavOpen = false">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['url'] }}" class="mb-1 block rounded-xl px-3 py-2.5 text-base font-medium text-slate-800 transition hover:bg-violet-50 dark:text-slate-100 dark:hover:bg-slate-800" @click="mobileNavOpen = false">{{ $item['label'] }}</a>
                    @endif
                @endforeach

                <div class="mt-6 border-t border-violet-100 pt-4 dark:border-slate-700">
                    @auth
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="btn-primary mb-2 flex w-full justify-center py-3" @click="mobileNavOpen = false">Yönetim paneli</a>
                        @else
                            <a href="{{ route('member.account') }}" class="mb-2 block rounded-xl border border-violet-100 px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-violet-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800" @click="mobileNavOpen = false">Hesabım</a>
                            <a href="{{ route('member.cart') }}" class="mb-2 flex items-center justify-between rounded-xl border border-violet-100 px-3 py-2.5 text-sm font-medium text-slate-700 dark:border-slate-600 dark:text-slate-200" @click="mobileNavOpen = false">
                                <span>Sepetim</span>
                                <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700">{{ $memberCartCount }}</span>
                            </a>
                            <a href="{{ route('member.purchases') }}" class="mb-2 flex items-center justify-between rounded-xl border border-violet-100 px-3 py-2.5 text-sm font-medium text-slate-700 dark:border-slate-600 dark:text-slate-200" @click="mobileNavOpen = false">
                                <span>Satın Alınanlar</span>
                                <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700">{{ $memberPurchasesCount }}</span>
                            </a>
                            <form method="post" action="{{ route('member.logout') }}" class="mt-2">
                                @csrf
                                <button type="submit" class="w-full rounded-xl border border-rose-200 bg-rose-50/50 px-3 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-100 dark:border-rose-900/60 dark:bg-rose-950/30 dark:text-rose-400">Çıkış</button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('member.login') }}" class="mb-2 block rounded-xl border border-violet-200 bg-white px-3 py-3 text-center text-sm font-semibold text-violet-700 shadow-sm transition hover:bg-violet-50 dark:border-slate-600 dark:bg-slate-800 dark:text-violet-300" @click="mobileNavOpen = false">Giriş Yap</a>
                        <a href="{{ route('member.register') }}" class="block rounded-xl bg-violet-600 px-3 py-3 text-center text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700" @click="mobileNavOpen = false">Üye Ol</a>
                    @endauth
                </div>
            </nav>
        </div>
    </div>
</div>

@auth
    @if(!auth()->user()->is_admin && request()->routeIs('home') && session('member_code_verified', false))
        <div class="mx-auto mt-4 max-w-7xl px-4">
            <div class="rounded-2xl border border-violet-200 bg-violet-50/80 px-4 py-3 text-sm font-medium text-violet-800">
                Hoş geldiniz {{ auth()->user()->display_name }}.
            </div>
        </div>
    @endif
@endauth

@if(session('success') && !request()->routeIs('admin.*'))
    <div class="mx-auto mt-4 max-w-7xl px-4">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    </div>
@endif

<main id="site-main" @class([
    'mx-auto w-full min-w-0 max-w-7xl px-4 py-6',
    'pb-28 lg:pb-24' => $hasStickyFooterAd,
])>
    @yield('content')
</main>

@if($hasStickyFooterAd)
    @include('partials.ads-sticky-footer', ['html' => $stickyFooterAdHtml])
@endif

<footer id="iletisim" class="mt-14 border-t border-white/20 bg-gradient-to-br from-indigo-950 via-violet-900 to-fuchsia-900 text-white">
    <div class="mx-auto max-w-7xl px-4 py-7 text-sm">
        <div class="grid gap-4 lg:grid-cols-12">
        <div class="rounded-2xl border border-white/25 bg-gradient-to-br from-white/15 to-white/5 p-4 lg:col-span-3">
            <p class="text-base font-semibold text-white">Boya Etkinlik Platformu</p>
            <p class="mt-2 text-slate-300">{{ \App\Models\Setting::getValue('footer_text', 'Tüm hakları saklıdır.') }}</p>
            <p class="mt-4 text-xs text-slate-400">Profesyonel, güvenli ve aile dostu boyama platformu.</p>
            @if($tiktokUrl || $instagramUrl || $youtubeUrl || $pinterestUrl || $dailymotionUrl)
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
                    @if($pinterestUrl)
                        <a href="{{ $pinterestUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs text-slate-200 transition hover:bg-slate-800 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.724-.359-1.792c0-1.687.988-2.943 2.217-2.943 1.048 0 1.555.796 1.555 1.748 0 1.065-.674 2.653-1.021 4.125-.291 1.234.621 2.242 1.846 2.242 2.209 0 3.904-2.318 3.904-5.673 0-2.995-2.156-5.086-5.238-5.086-3.571 0-5.662 2.674-5.662 5.434 0 1.073.414 2.223.931 2.842.102.123.117.231.087.354-.095.39-.308 1.235-.352 1.409-.056.233-.184.282-.425.169-1.587-.737-2.579-3.044-2.579-4.9 0-3.984 2.899-7.639 8.353-7.639 4.385 0 7.791 3.127 7.791 7.302 0 4.356-2.754 7.868-6.573 7.868-1.287 0-2.497-.67-2.909-1.461l-.791 3.004c-.286 1.092-1.058 2.461-1.574 3.306C9.558 23.59 10.776 24 12.017 24c6.624 0 11.99-5.367 11.99-11.987C24.007 5.367 18.641.001 12.017.001z"/>
                            </svg>
                            Pinterest
                        </a>
                    @endif
                    @if($dailymotionUrl)
                        <a href="{{ $dailymotionUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-lg bg-slate-900 px-3 py-2 text-xs text-slate-200 transition hover:bg-slate-800 hover:text-white">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M4 5a2 2 0 0 1 2-2h5.2c2.76 0 5 2.24 5 5s-2.24 5-5 5H8v6H6V5zm2 2v6h3.2c1.65 0 3-1.35 3-3s-1.35-3-3-3H6zm10.5 0h2v4.25h3.25L16.5 17v-5.25H14V7z"/>
                            </svg>
                            Dailymotion
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
                    placeholder="Soyad"
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
                    E-bültene kayıt ol
                </button>
            </form>
        </div>
        </div>
    </div>
    <div class="border-t border-white/20">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-2 px-4 py-3 text-xs text-indigo-100/90">
            <div class="relative z-20 inline-flex flex-wrap items-center gap-2">
                <span class="select-none">© 2026 Burak Gül tarafından geliştirilmiştir.</span>
                {{-- Gizli giriş: geniş tıklama alanı + belirgin nokta (yönetim giriş sayfası) --}}
                <a
                    href="{{ route('admin.login') }}"
                    class="group relative z-30 -my-0.5 inline-flex min-h-[36px] min-w-[36px] shrink-0 cursor-pointer items-center justify-center rounded-lg border border-white/10 bg-white/[0.04] px-1.5 py-1.5 transition hover:border-white/25 hover:bg-white/[0.08] focus:outline-none focus:ring-1 focus:ring-white/30 active:scale-[0.98]"
                    title=""
                    aria-label="Giriş"
                >
                    <span class="pointer-events-none h-2 w-2 rounded-full bg-white/65 shadow-[0_0_0_1px_rgba(255,255,255,0.12)] transition group-hover:bg-white/90 group-hover:shadow-[0_0_0_1px_rgba(255,255,255,0.25)]"></span>
                </a>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a
                    href="{{ route('guest.purchase.recovery') }}"
                    class="rounded-md px-2 py-1 font-medium text-indigo-100/90 transition hover:bg-white/10 hover:text-white"
                >
                    Misafir indirme linki
                </a>
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

    (function () {
        var activeRequest = null;
        var counterObserver = null;

        function formatCounterValue(value) {
            return value.toLocaleString('tr-TR');
        }

        function animateCounter(counterEl) {
            if (!counterEl || counterEl.dataset.counterAnimated === '1') return;

            var target = Number(counterEl.dataset.counterTarget || 0);
            if (!Number.isFinite(target)) return;

            var duration = 900;
            var start = performance.now();
            counterEl.dataset.counterAnimated = '1';

            function tick(now) {
                var progress = Math.min((now - start) / duration, 1);
                var eased = 1 - Math.pow(1 - progress, 3);
                var current = Math.round(target * eased);
                counterEl.textContent = formatCounterValue(current);

                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else {
                    counterEl.textContent = formatCounterValue(target);
                }
            }

            requestAnimationFrame(tick);
        }

        function initAnimatedCounters(scope) {
            var root = scope || document;
            var counters = root.querySelectorAll('[data-counter-target]');
            if (!counters.length) return;

            if (!('IntersectionObserver' in window)) {
                counters.forEach(animateCounter);
                return;
            }

            if (!counterObserver) {
                counterObserver = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;
                        animateCounter(entry.target);
                        counterObserver.unobserve(entry.target);
                    });
                }, { threshold: 0.35 });
            }

            counters.forEach(function (counterEl) {
                if (counterEl.dataset.counterAnimated === '1') return;
                counterObserver.observe(counterEl);
            });
        }

        function fetchAndSwap(url, targetSelector, pushUrl) {
            if (!targetSelector) return;
            var currentTarget = document.querySelector(targetSelector);
            if (!currentTarget) return;

            if (activeRequest) {
                activeRequest.abort();
            }

            activeRequest = new AbortController();

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeRequest.signal,
            })
                .then(function (response) {
                    return response.text();
                })
                .then(function (html) {
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var freshTarget = doc.querySelector(targetSelector);
                    if (!freshTarget) return;

                    currentTarget.replaceWith(freshTarget);
                    if (pushUrl) {
                        window.history.replaceState({}, '', pushUrl);
                    }

                    if (targetSelector === '#home-filter-panel') {
                        var panel = document.querySelector('#home-filter-panel');
                        var heroInput = document.querySelector('#home-hero-search-form input[name="q"]');
                        if (panel && heroInput) {
                            var synced = panel.querySelector('input[name="q"]');
                            if (synced) {
                                heroInput.value = synced.value;
                            }
                        }
                    }

                    initAnimatedCounters(document);
                })
                .catch(function (error) {
                    if (error && error.name === 'AbortError') return;
                    console.error('Filtre güncellemesi sırasında hata oluştu:', error);
                });
        }

        document.addEventListener('submit', function (event) {
            var form = event.target.closest('.js-live-filter-form');
            if (!form) return;

            event.preventDefault();
            var params = new URLSearchParams(new FormData(form));
            var url = form.action + '?' + params.toString();
            fetchAndSwap(url, form.dataset.liveTarget, url);
        });

        document.addEventListener('change', function (event) {
            var field = event.target;
            if (!field.form || !field.form.classList.contains('js-live-filter-form')) return;
            field.form.requestSubmit();
        });

        document.addEventListener('input', function (event) {
            var field = event.target;
            if (!field.form || !field.form.classList.contains('js-live-filter-form')) return;
            if (field.name !== 'q') return;

            clearTimeout(field._liveTimer);
            field._liveTimer = setTimeout(function () {
                field.form.requestSubmit();
            }, 450);
        });

        document.addEventListener('click', function (event) {
            var link = event.target.closest('.js-live-filter-link');
            if (!link) return;

            var container = link.closest('#home-filter-panel, #category-live-area');
            if (!container || !container.id) return;

            event.preventDefault();
            fetchAndSwap(link.href, '#' + container.id, link.href);
        });


        function applyThemeButtonLabel() {
            var isDark = document.documentElement.classList.contains('dark');
            document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
                btn.setAttribute('aria-label', isDark ? 'Açık temaya geç' : 'Koyu temaya geç');
                btn.setAttribute('title', isDark ? 'Açık tema' : 'Koyu tema');

                var sun = btn.querySelector('[data-theme-icon-sun]');
                var moon = btn.querySelector('[data-theme-icon-moon]');
                if (sun && moon) {
                    sun.classList.toggle('hidden', !isDark);
                    moon.classList.toggle('hidden', isDark);
                    btn.classList.toggle('is-dark', isDark);
                } else {
                    btn.textContent = isDark ? 'Açık tema' : 'Koyu tema';
                }
            });
        }

        document.addEventListener('click', function (event) {
            var toggle = event.target.closest('[data-theme-toggle]');
            if (!toggle) return;

            var root = document.documentElement;
            root.classList.toggle('dark');
            try {
                localStorage.setItem('site-theme', root.classList.contains('dark') ? 'dark' : 'light');
            } catch (e) {}
            applyThemeButtonLabel();
        });

        applyThemeButtonLabel();
        initAnimatedCounters(document);
    })();
</script>
</body>
</html>
