@extends('layouts.app')

@section('title', 'Giriş Yap')

@section('content')
<section class="mx-auto max-w-xl">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">Giriş Yap</h1>
        <p class="mt-1 text-sm text-slate-500">Üye hesabınız ile giriş yapın. Sonrasında doğrulama kodu gönderilir.</p>

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('member.login.submit') }}" class="mt-5 space-y-4">
            @csrf
            <label class="block text-sm font-medium text-slate-700">
                E-posta
                <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" class="input-ui mt-1">
            </label>
            <label class="block text-sm font-medium text-slate-700">
                Şifre
                <input type="password" name="password" required class="input-ui mt-1" autocomplete="current-password">
            </label>
            <p class="text-right text-sm">
                <a href="{{ route('member.forgot-password') }}" class="font-medium text-indigo-600 transition hover:text-indigo-800">Şifremi unuttum</a>
            </p>
            <button class="btn-primary w-full">Kod Gönder ve Devam Et</button>
        </form>
    </div>
</section>
@endsection
