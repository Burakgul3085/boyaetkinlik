<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Üye Ol</title>
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
<video class="pointer-events-none absolute inset-0 z-0 h-full w-full object-cover" autoplay muted loop playsinline>
    <source src="{{ asset('videos/admin-auth-bg.mp4') }}" type="video/mp4">
</video>
<div class="pointer-events-none absolute inset-0 z-0 bg-gradient-to-br from-slate-950/86 via-slate-900/72 to-indigo-950/82"></div>
<div class="pointer-events-none absolute inset-0 z-0 backdrop-blur-[1.2px]"></div>
<button type="button" class="theme-toggle-btn auth-page-toggle" data-theme-toggle>Dark Mode</button>

<div class="relative z-10 w-full max-w-md">
    <form method="post" action="{{ route('admin.register.submit') }}" class="w-full rounded-3xl border border-white/20 bg-slate-900/55 p-7 text-slate-100 shadow-2xl shadow-black/45 backdrop-blur-md">
        @csrf
        <p class="inline-flex items-center rounded-full border border-violet-300/40 bg-violet-500/20 px-3 py-1 text-[11px] font-semibold tracking-wide text-violet-100">Admin üye ol</p>
        <h1 class="mt-2 text-3xl font-bold text-white">Yeni Admin Kaydı</h1>
        <p class="mt-1 text-sm text-slate-300">Bilgileri girin, doğrulama kodu güvenli adrese gönderilsin, kodu girdikten sonra admin hesabınız aktif olsun.</p>

        @if($errors->any())
            <p class="mt-3 rounded-lg bg-rose-100 p-2 text-sm text-rose-700">{{ $errors->first() }}</p>
        @endif

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <input type="text" name="register_first_name" value="{{ old('register_first_name') }}" required placeholder="Ad" class="w-full rounded-xl border border-slate-500/45 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-400 focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-400/30">
            <input type="text" name="register_last_name" value="{{ old('register_last_name') }}" required placeholder="Soyad" class="w-full rounded-xl border border-slate-500/45 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-400 focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-400/30">
        </div>
        <input type="email" name="register_email" value="{{ old('register_email') }}" required placeholder="E-posta" class="mt-3 w-full rounded-xl border border-slate-500/45 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-400 focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-400/30">
        <input type="text" name="register_phone" value="{{ old('register_phone') }}" placeholder="Telefon (opsiyonel)" class="mt-3 w-full rounded-xl border border-slate-500/45 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-400 focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-400/30">
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            <input type="password" name="register_password" required placeholder="Şifre" class="w-full rounded-xl border border-slate-500/45 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-400 focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-400/30">
            <input type="password" name="register_password_confirmation" required placeholder="Şifre tekrar" class="w-full rounded-xl border border-slate-500/45 bg-slate-900/70 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-400 focus:border-violet-300 focus:outline-none focus:ring-2 focus:ring-violet-400/30">
        </div>
        <button class="btn-primary mt-5 w-full py-3">Üye Ol ve Kodu Gönder</button>
        <a href="{{ route('admin.login') }}" class="mt-3 block text-center text-sm font-medium text-indigo-200 hover:text-indigo-100 hover:underline">Admin girişe dön</a>
    </form>
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

