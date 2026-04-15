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
    $navbarLinks = \App\Models\Setting::getValue('navbar_links', "Anasayfa|/\nİletişim|#iletisim");
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
@endphp
<header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/90 backdrop-blur">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4">
        <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xl font-bold text-slate-800">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">BE</span>
            Boya Etkinlik
        </a>
        <div class="flex items-center gap-2 text-sm font-medium">
            @foreach($links as $item)
                <a class="rounded-xl px-3 py-2 text-slate-600 transition hover:bg-slate-100 hover:text-slate-900" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
            @endforeach
            <a id="admin-nav-link" class="btn-primary" href="{{ auth()->check() ? route('admin.dashboard') : route('admin.login') }}">Admin</a>
        </div>
    </nav>
</header>

<main class="mx-auto max-w-7xl px-4 py-6">
    <div class="mb-6 rounded-xl border border-indigo-100 bg-indigo-50 px-3 py-2 text-xs text-indigo-800">
        Admin giris kisayolu:
        <a class="font-semibold text-indigo-600 underline" href="{{ auth()->check() ? route('admin.dashboard') : route('admin.login') }}">
            {{ auth()->check() ? '/admin' : '/admin/login' }}
        </a>
    </div>
    @yield('content')
</main>

<footer id="iletisim" class="mt-14 border-t border-slate-800 bg-slate-950 text-slate-200">
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 text-sm md:grid-cols-2">
        <div>
            <p class="text-base font-semibold text-white">Boya Etkinlik Platformu</p>
            <p class="mt-2 text-slate-300">{{ \App\Models\Setting::getValue('footer_text', 'Tüm hakları saklıdır.') }}</p>
        </div>
        <div class="md:text-right">
            <p class="font-medium text-white">İletişim</p>
            <p class="mt-2 text-slate-300">{{ \App\Models\Setting::getValue('contact', 'İletişim bilgilerini admin panelinden girin.') }}</p>
        </div>
    </div>
</footer>
</body>
</html>
