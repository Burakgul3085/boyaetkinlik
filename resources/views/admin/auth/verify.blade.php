<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Kod Doğrulama</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-100 to-indigo-100 p-4">
<form method="post" action="{{ route('admin.verify.submit') }}" class="w-full max-w-md card p-7">
    @csrf
    <h1 class="text-2xl font-bold text-slate-900">Kod Doğrulama</h1>
    <p class="mt-1 text-sm text-slate-500">Devam etmek için e-postanıza gönderilen 6 haneli doğrulama kodunu girin.</p>
    @if(session('admin_verification_sent_to'))
        <p class="mt-3 rounded-lg bg-indigo-50 p-2 text-xs text-indigo-700">
            Kod gönderilen e-posta: {{ session('admin_verification_sent_to') }}
        </p>
    @endif

    @if($errors->any())
        <p class="mt-3 rounded-lg bg-rose-100 p-2 text-sm text-rose-700">{{ $errors->first() }}</p>
    @endif

    <label class="mt-5 block text-sm font-medium">Doğrulama Kodu</label>
    <input type="text" name="verification_code" required class="input-ui mt-2" autocomplete="one-time-code" inputmode="numeric">

    <button class="btn-primary mt-5 w-full py-3">Doğrula ve Devam Et</button>

    <a href="{{ route('admin.logout') }}"
       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
       class="mt-3 block text-center text-sm font-medium text-indigo-600 hover:underline">
        Farklı hesap ile giriş yap
    </a>
</form>

<form id="logout-form" method="post" action="{{ route('admin.logout') }}" class="hidden">
    @csrf
</form>
</body>
</html>
