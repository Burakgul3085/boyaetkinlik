@extends('layouts.admin')

@section('title', 'Admin Üyelik Doğrulama')

@section('content')
    <section class="mx-auto max-w-2xl space-y-5">
        <div class="card p-5 md:p-6">
            <h1 class="text-2xl font-bold text-slate-900">Admin Üyelik Doğrulama</h1>
            <p class="mt-2 text-sm text-slate-600">
                <strong>{{ $pendingEmail }}</strong> için oluşturulan admin üyeliğini tamamlamak adına 6 haneli doğrulama kodunu girin.
            </p>
            <p class="mt-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-xs text-indigo-800">
                Kod <strong>{{ $sentToEmail }}</strong> adresine gönderildi. Kod 15 dakika geçerlidir.
            </p>
            <p class="mt-2 text-xs text-slate-500">Kalan deneme hakkı: {{ $attemptsRemaining }}</p>

            <form method="post" action="{{ route('admin.admin-users.create.verify.submit') }}" class="mt-5 space-y-4">
                @csrf
                <label class="input-ui block">
                    Doğrulama Kodu
                    <input type="text" name="verification_code" maxlength="12" class="mt-1 w-full" required placeholder="6 haneli kod">
                </label>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.admin-users.create') }}" class="btn-secondary text-center">Bilgileri düzenle</a>
                    <button class="btn-primary">Doğrula ve Hesabı Oluştur</button>
                </div>
            </form>
        </div>
    </section>
@endsection

