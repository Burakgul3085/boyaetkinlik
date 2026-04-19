@extends('layouts.app')

@section('title', 'Şifremi Unuttum')

@section('content')
<section class="mx-auto max-w-xl">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">Şifremi Unuttum</h1>
        <p class="mt-1 text-sm text-slate-500">Kayıtlı e-posta adresinize yalnızca rakamlardan oluşan yeni bir şifre gönderilir. Giriş yaptıktan sonra Hesabım üzerinden şifrenizi istediğiniz gibi güncelleyebilirsiniz.</p>

        @if(session('forgot_status'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('forgot_status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('member.forgot-password.submit') }}" class="mt-5 space-y-4">
            @csrf
            <label class="block text-sm font-medium text-slate-700">
                E-posta
                <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="input-ui mt-1">
            </label>
            <button type="submit" class="btn-primary w-full">Yeni şifre gönder</button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            <a href="{{ route('member.login') }}" class="font-medium text-indigo-600 transition hover:text-indigo-800">← Girişe dön</a>
        </p>
    </div>
</section>
@endsection
