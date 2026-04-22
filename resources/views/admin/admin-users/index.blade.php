@extends('layouts.admin')

@section('title', 'Admin Yönetimi')

@section('content')
    <section class="space-y-5">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Admin Yönetimi</h1>
                <p class="mt-1 text-sm text-slate-600">Mevcut admin hesaplarını görüntüleyin ve gerektiğinde şifrelerini güncelleyin.</p>
            </div>
            <a href="{{ route('admin.admin-users.create') }}" class="btn-primary text-center">Yeni Admin Üye Ol</a>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            @forelse($admins as $admin)
                <article class="card p-5">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $admin->display_name }}</h2>
                    <div class="mt-2 space-y-1 text-sm text-slate-600">
                        <p><strong>E-posta:</strong> {{ $admin->email }}</p>
                        <p><strong>Telefon:</strong> {{ $admin->phone ?: '-' }}</p>
                        <p><strong>Kayıt:</strong> {{ optional($admin->created_at)->format('d.m.Y H:i') }}</p>
                    </div>

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
                        <div class="sm:col-span-2 flex justify-end">
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

