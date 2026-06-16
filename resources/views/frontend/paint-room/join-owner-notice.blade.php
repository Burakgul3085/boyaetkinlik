@extends('layouts.app')

@section('title', 'Davet Linki — Oda Sahibi')

@section('content')
<section class="mx-auto max-w-lg">
    <div class="card overflow-hidden p-0">
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 px-6 py-6 text-white">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/80">Oda sahibi</p>
            <h1 class="mt-1 text-2xl font-bold">Bu link misafirler içindir</h1>
            <p class="mt-2 text-sm text-white/90">
                Siz odayı siz kurdunuz. Bu linke siz tıklarsanız misafir olarak katılmazsınız — odanızda yalnız kalırsınız.
            </p>
        </div>

        <div class="p-6 md:p-7 space-y-4">
            <p class="text-sm text-slate-600">
                Misafirinize <strong>bu davet linkini</strong> veya <strong>PIN: {{ $pin }}</strong> gönderin.
                Misafir linke tıklayınca adını yazıp onaylayacak, sonra sizinle bağlanacak.
            </p>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Davet linki</p>
                <input type="text" readonly value="{{ $inviteUrl }}" id="owner-invite-url" class="input-ui mt-2 text-xs">
                <button type="button" class="btn-secondary mt-2 w-full text-sm" onclick="navigator.clipboard?.writeText(document.getElementById('owner-invite-url').value)">Linki kopyala</button>
            </div>

            <a href="{{ $lobbyUrl }}" class="btn-primary w-full text-center">Odamı aç</a>
            <a href="{{ route('paint-room.index') }}" class="btn-secondary w-full text-center text-sm">← Görüntülü boyama</a>
        </div>
    </div>
</section>
@endsection
