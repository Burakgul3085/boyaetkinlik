<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Panel')</title>
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
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-T85B5EZG72"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-T85B5EZG72');
    </script>
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
            <button type="button" class="theme-toggle-btn w-full justify-center !border-slate-600 !bg-slate-900 !text-slate-200" data-theme-toggle>Dark Mode</button>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.dashboard') }}">Genel Bakış</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.members.index') }}">Üyeler</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.categories.index') }}">Kategoriler</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.pages.index') }}">Boyama Sayfaları</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.blogs.index') }}">Blog Yönetimi</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.settings.index') }}">Sayfa Ayarları</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.ads.index') }}">Reklam Alanları</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.transactions.index') }}">İşlemler</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.purchase-verifications.index') }}">Satın alım doğrulama</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.newsletter.index') }}">E-Bülten</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.visitor-feedback.index') }}">Ziyaretçi yorumları</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.admin-users.index') }}">Admin yönetimi</a>
            <a class="block rounded-lg px-3 py-2 text-slate-300 transition hover:bg-slate-800 hover:text-white" href="{{ route('admin.logs.index') }}">Admin log kayıtları</a>
        </div>
        <form method="post" action="{{ route('admin.logout') }}" class="mt-8">
            @csrf
            <button class="btn-danger w-full">Çıkış</button>
        </form>
    </aside>
    <main class="lg:col-span-9 xl:col-span-10 p-5 lg:p-8">
        @if(session('success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">{{ session('warning') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</div>
<script>
    (function () {

        function applyThemeButtonLabel() {
            var isDark = document.documentElement.classList.contains('dark');
            document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
                btn.textContent = isDark ? 'Light Mode' : 'Dark Mode';
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
    })();
</script>
</body>
</html>
