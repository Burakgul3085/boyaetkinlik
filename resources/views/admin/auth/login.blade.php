<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Giriş</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-100 to-indigo-100 p-4">
<form method="post" action="{{ route('admin.login.submit') }}" class="w-full max-w-md card p-7">
    @csrf
    <h1 class="text-2xl font-bold text-slate-900">Admin Giriş</h1>
    <p class="mt-1 text-sm text-slate-500">Yönetim paneline erişmek için giriş yapın.</p>
    @if(session('success'))
        <p class="mt-3 rounded-lg bg-emerald-100 p-2 text-sm text-emerald-700">{{ session('success') }}</p>
    @endif
    @if($errors->any())
        <p class="mt-3 rounded-lg bg-rose-100 p-2 text-sm text-rose-700">{{ $errors->first() }}</p>
    @endif
    <label class="mt-5 block text-sm font-medium">E-posta</label>
    <input type="email" name="email" required class="input-ui mt-2">
    <label class="mt-4 block text-sm font-medium">Şifre</label>
    <input type="password" name="password" required class="input-ui mt-2">
    <button class="btn-primary mt-5 w-full py-3">Giriş Yap</button>
    <a href="{{ route('home') }}" class="mt-3 block text-center text-sm font-medium text-indigo-600 hover:underline">Ana sayfaya git</a>
</form>
</body>
</html>
