@extends('layouts.app')

@section('title', 'Üye Ol')

@section('content')
<section class="mx-auto max-w-xl">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">Üye Ol</h1>
        <p class="mt-1 text-sm text-slate-500">Ad, soyad, e-posta ve şifrenizle üyeliğinizi başlatın.</p>

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('member.register.submit') }}" class="mt-5 space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">
                    Ad
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required maxlength="120" class="input-ui mt-1">
                </label>
                <label class="block text-sm font-medium text-slate-700">
                    Soyad
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required maxlength="120" class="input-ui mt-1">
                </label>
            </div>
            <label class="block text-sm font-medium text-slate-700">
                E-posta
                <input type="email" name="email" value="{{ old('email') }}" required maxlength="255" class="input-ui mt-1">
            </label>
            <label class="block text-sm font-medium text-slate-700">
                Şifre
                <input type="password" name="password" required minlength="6" maxlength="72" class="input-ui mt-1">
            </label>
            <label class="block text-sm font-medium text-slate-700">
                Şifre (Tekrar)
                <input type="password" name="password_confirmation" required minlength="6" maxlength="72" class="input-ui mt-1">
            </label>
            <button class="btn-primary w-full">Kayıt Ol</button>
        </form>
    </div>
</section>
@endsection
