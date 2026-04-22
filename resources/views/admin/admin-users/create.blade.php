@extends('layouts.admin')

@section('title', 'Admin Üye Ol')

@section('content')
    <section class="mx-auto max-w-3xl space-y-5">
        <div class="card p-5 md:p-6">
            <h1 class="text-2xl font-bold text-slate-900">Admin Üye Ol</h1>
            <p class="mt-2 text-sm text-slate-600">
                Yeni admin hesabı oluşturmak için bilgileri girin. Üyelik hemen tamamlanmaz; güvenli adrese gelen doğrulama kodu girildikten sonra hesap aktif olur.
            </p>

            <form method="post" action="{{ route('admin.admin-users.create.submit') }}" class="mt-5 grid gap-4 md:grid-cols-2">
                @csrf

                <label class="input-ui">
                    Ad
                    <input type="text" name="first_name" value="{{ old('first_name') }}" class="mt-1 w-full" required>
                </label>

                <label class="input-ui">
                    Soyad
                    <input type="text" name="last_name" value="{{ old('last_name') }}" class="mt-1 w-full" required>
                </label>

                <label class="input-ui md:col-span-2">
                    E-posta
                    <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full" required>
                </label>

                <label class="input-ui md:col-span-2">
                    Telefon (opsiyonel)
                    <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 w-full" placeholder="05XXXXXXXXX veya +90...">
                </label>

                <label class="input-ui">
                    Şifre
                    <input type="password" name="password" class="mt-1 w-full" required>
                </label>

                <label class="input-ui">
                    Şifre (tekrar)
                    <input type="password" name="password_confirmation" class="mt-1 w-full" required>
                </label>

                <div class="md:col-span-2 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.admin-users.index') }}" class="btn-secondary text-center">Admin yönetimi</a>
                    <button class="btn-primary">Üye Ol ve Doğrulama Kodu Gönder</button>
                </div>
            </form>
        </div>
    </section>
@endsection

