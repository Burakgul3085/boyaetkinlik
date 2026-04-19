@extends('layouts.app')

@section('title', 'Hesabım')

@section('content')
<section class="mx-auto max-w-3xl">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">Hesabım</h1>
        <p class="mt-1 text-sm text-slate-500">Ad, soyad ve şifrenizi güncelleyebilirsiniz.</p>

        @if(session('success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="post" action="{{ route('member.account.update') }}" class="mt-5 space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">
                    Ad
                    <input type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required maxlength="120" class="input-ui mt-1">
                </label>
                <label class="block text-sm font-medium text-slate-700">
                    Soyad
                    <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required maxlength="120" class="input-ui mt-1">
                </label>
            </div>
            <label class="block text-sm font-medium text-slate-700">
                Yeni Şifre (opsiyonel)
                <input type="password" name="password" minlength="6" maxlength="72" class="input-ui mt-1">
            </label>
            <label class="block text-sm font-medium text-slate-700">
                Yeni Şifre (Tekrar)
                <input type="password" name="password_confirmation" minlength="6" maxlength="72" class="input-ui mt-1">
            </label>
            <button class="btn-primary">Bilgileri Güncelle</button>
        </form>
    </div>
</section>
@endsection
