<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Kod Doğrulama</title>
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
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-950 p-4">
<video
    class="pointer-events-none absolute inset-0 z-0 h-full w-full object-cover"
    autoplay
    muted
    loop
    playsinline
>
    <source src="{{ asset('videos/admin-auth-bg.mp4') }}" type="video/mp4">
</video>
<div class="pointer-events-none absolute inset-0 z-0 bg-gradient-to-br from-slate-950/86 via-slate-900/72 to-indigo-950/82"></div>
<div class="pointer-events-none absolute inset-0 z-0 backdrop-blur-[1.2px]"></div>
<button type="button" class="theme-toggle-btn auth-page-toggle" data-theme-toggle>Dark Mode</button>
<div class="relative z-10 flex w-full items-center justify-center">
<form method="post" action="{{ route('admin.verify.submit') }}" class="w-full max-w-md rounded-3xl border border-white/20 bg-slate-900/55 p-7 text-slate-100 shadow-2xl shadow-black/45 backdrop-blur-md">
    @csrf
    <p class="inline-flex items-center rounded-full border border-indigo-300/40 bg-indigo-500/20 px-3 py-1 text-[11px] font-semibold tracking-wide text-indigo-100">Guvenli Dogrulama</p>
    <h1 class="mt-2 text-3xl font-bold text-white">Kod Doğrulama</h1>
    <p class="mt-1 text-sm text-slate-300">Devam etmek için e-postanıza gönderilen 6 haneli doğrulama kodunu girin.</p>
    @if($errors->any())
        <p class="mt-3 rounded-lg bg-rose-100 p-2 text-sm text-rose-700">{{ $errors->first() }}</p>
    @endif

    <label class="mt-5 block text-sm font-medium text-slate-200">Doğrulama Kodu</label>
    <input type="text" name="verification_code" required class="mt-2 w-full rounded-xl border border-slate-500/45 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-400 focus:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-400/30" autocomplete="one-time-code" inputmode="numeric">

    <button class="btn-primary mt-5 w-full py-3">Doğrula ve Devam Et</button>

    <a href="{{ route('admin.logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
       class="mt-3 block text-center text-sm font-medium text-indigo-200 hover:text-indigo-100 hover:underline">
        Farklı hesap ile giriş yap
    </a>
</form>
</div>

<form id="logout-form" method="post" action="{{ route('admin.logout') }}" class="hidden">
    @csrf
</form>
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
