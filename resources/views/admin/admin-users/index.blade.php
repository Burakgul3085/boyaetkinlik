@extends('layouts.admin')

@section('title', 'Admin Yönetimi')

@section('content')
    <section class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Admin Yönetimi</h1>
                <p class="mt-1 text-sm text-slate-600">Mevcut admin hesaplarını görüntüleyin, şifrelerini güncelleyin veya hesap silin.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.logs.index') }}" class="btn-secondary text-center">Admin Log Kayıtları</a>
                <a href="#yeni-admin-formu" class="btn-primary text-center">Yeni Admin Üye Ol</a>
            </div>
        </div>

        <div id="yeni-admin-formu" class="card rounded-2xl border border-violet-100 bg-gradient-to-br from-white to-violet-50/40 p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Yeni Admin Ekle</h2>
            <p class="mt-1 text-sm text-slate-600">
                Bilgileri girin, doğrulama kodu güvenli adrese gitsin; kod girildikten sonra admin hesabı aktifleşsin.
            </p>

            <form method="post" action="{{ route('admin.admin-users.create.submit') }}" class="mt-4 grid gap-3 md:grid-cols-2">
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
                <label class="input-ui">
                    Telefon (opsiyonel)
                    <input type="text" name="phone" value="{{ old('phone') }}" class="mt-1 w-full" placeholder="05XXXXXXXXX">
                </label>
                <label class="input-ui">
                    Şifre
                    <input type="password" name="password" class="mt-1 w-full" required>
                </label>
                <label class="input-ui md:col-span-1">
                    Şifre tekrar
                    <input type="password" name="password_confirmation" class="mt-1 w-full" required>
                </label>
                <div class="md:col-span-2 flex justify-end">
                    <button class="btn-primary">Doğrulama Kodu Gönder</button>
                </div>
            </form>
        </div>

        <div class="grid gap-4 xl:grid-cols-2">
            @forelse($admins as $admin)
                <article class="card rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-900">{{ $admin->display_name }}</h2>
                            <p class="text-xs text-slate-500">ID: {{ $admin->id }}</p>
                        </div>
                        @if(auth()->id() !== $admin->id)
                            <form method="post" action="{{ route('admin.admin-users.destroy', $admin) }}" onsubmit="return confirm('Bu admin hesabını silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('delete')
                                <button class="rounded-xl bg-rose-100 px-3 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-200">Sil</button>
                            </form>
                        @endif
                    </div>

                    <div class="mt-3 grid gap-2 rounded-xl border border-slate-100 bg-slate-50 p-3 text-sm text-slate-700 sm:grid-cols-2">
                        <p><strong>E-posta:</strong> {{ $admin->email }}</p>
                        <p><strong>Telefon:</strong> {{ $admin->phone ?: '-' }}</p>
                        <p class="sm:col-span-2"><strong>Kayıt:</strong> {{ optional($admin->created_at)->format('d.m.Y H:i') }}</p>
                    </div>

                    <form method="post" action="{{ route('admin.admin-users.profile.update', $admin) }}" class="mt-4 grid gap-2 sm:grid-cols-2">
                        @csrf
                        @method('put')
                        <label class="input-ui">
                            Ad
                            <input type="text" name="first_name" class="mt-1 w-full" required value="{{ old('first_name', $admin->first_name) }}">
                        </label>
                        <label class="input-ui">
                            Soyad
                            <input type="text" name="last_name" class="mt-1 w-full" required value="{{ old('last_name', $admin->last_name) }}">
                        </label>
                        <label class="input-ui sm:col-span-2">
                            E-posta
                            <input type="email" name="email" class="mt-1 w-full" required value="{{ old('email', $admin->email) }}">
                        </label>
                        <label class="input-ui sm:col-span-2">
                            Telefon
                            <input type="text" name="phone" class="mt-1 w-full" value="{{ old('phone', $admin->phone) }}">
                        </label>
                        <div class="sm:col-span-2 flex justify-end">
                            <button class="btn-secondary">Bilgileri Güncelle</button>
                        </div>
                    </form>

                    <form method="post" action="{{ route('admin.admin-users.password.update', $admin) }}" class="mt-4 grid gap-2 sm:grid-cols-2">
                        @csrf
                        <label class="input-ui">
                            Yeni şifre
                            <input type="password" name="password" class="mt-1 w-full" required minlength="6">
                        </label>
                        <label class="input-ui">
                            Şifre tekrar
                            <input type="password" name="password_confirmation" class="mt-1 w-full" required minlength="6">
                        </label>
                        <div class="sm:col-span-2 flex justify-end gap-2">
                            <button class="btn-secondary">Şifreyi Güncelle</button>
                        </div>
                    </form>
                </article>
            @empty
                <div class="card p-6 text-sm text-slate-600">
                    Henüz admin hesabı bulunmuyor.
                </div>
            @endforelse
        </div>
    </section>
@endsection

