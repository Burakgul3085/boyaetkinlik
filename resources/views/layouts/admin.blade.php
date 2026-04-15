<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-700">
<div class="min-h-screen lg:grid lg:grid-cols-12">
    <aside class="border-r border-slate-800 bg-slate-950 p-5 text-slate-100 lg:col-span-3 xl:col-span-2">
        <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 text-xl font-bold">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">BE</span>
            Admin Panel
        </a>
        <div class="mt-6 space-y-1 text-sm">
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.dashboard') }}">Genel Bakis</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.categories.index') }}">Kategoriler</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.pages.index') }}">Boyama Sayfalari</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.settings.index') }}">Sayfa Ayarlari</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.ads.index') }}">Reklam Alanlari</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.transactions.index') }}">Islemler</a>
        </div>
        <form method="post" action="{{ route('admin.logout') }}" class="mt-8">
            @csrf
            <button class="btn-danger w-full">Cikis</button>
        </form>
    </aside>
    <main class="lg:col-span-9 xl:col-span-10 p-5 lg:p-8">
        @if(session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
</div>
</body>
</html>
