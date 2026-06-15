@extends('layouts.app')

@section('title', 'Görüntülü Boyama')

@section('content')
<section class="mx-auto max-w-3xl">
    <div class="card overflow-hidden p-0">
        <div class="bg-gradient-to-br from-violet-600 via-indigo-600 to-teal-500 px-6 py-8 text-white md:px-8">
            <p class="text-xs font-bold uppercase tracking-widest text-white/80">Yeni özellik</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight">Görüntülü Boyama</h1>
            <p class="mt-3 max-w-xl text-sm text-white/90">
                Üye olarak oda oluşturun; arkadaşınız PIN veya davet linki ile katılsın. Beraber boyama ve görüntülü sohbet.
            </p>
        </div>

        <div class="space-y-5 p-6 md:p-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-violet-100 bg-violet-50/60 p-5">
                    <h2 class="text-lg font-bold text-slate-900">Oda oluştur</h2>
                    <p class="mt-2 text-sm text-slate-600">Yalnızca kayıtlı üyeler oda açabilir. Oda en fazla 2 kişi ve 30 dakika sürer.</p>
                    @if($canCreate)
                        <form method="post" action="{{ route('paint-room.create') }}" class="mt-4">
                            @csrf
                            <button type="submit" class="btn-primary w-full">Oda oluştur</button>
                        </form>
                    @else
                        <p class="mt-4 text-sm text-slate-500">Oda açmak için giriş yapın.</p>
                        <a href="{{ route('member.login') }}?redirect={{ urlencode(route('paint-room.index')) }}" class="btn-primary mt-3 inline-flex w-full justify-center">Giriş yap</a>
                        <a href="{{ route('member.register') }}" class="btn-secondary mt-2 inline-flex w-full justify-center text-sm">Üye ol</a>
                    @endif
                </div>

                <div class="rounded-2xl border border-teal-100 bg-teal-50/50 p-5">
                    <h2 class="text-lg font-bold text-slate-900">Odaya katıl</h2>
                    <p class="mt-2 text-sm text-slate-600">Üye olmanız gerekmez. Davet linkine tıklayın veya 6 haneli PIN girin.</p>
                    <a href="{{ route('paint-room.join.form') }}" class="btn-secondary mt-4 inline-flex w-full justify-center">PIN ile katıl</a>
                </div>
            </div>

            <ul class="rounded-xl border border-slate-200 bg-slate-50/80 p-4 text-sm text-slate-600">
                <li class="flex gap-2"><span class="text-violet-600">•</span> Davet linki oda açıkken tekrar kullanılabilir; oda kapanınca geçersiz olur.</li>
                <li class="flex gap-2"><span class="text-violet-600">•</span> PIN, oda kapanana veya süre dolana kadar geçerlidir.</li>
                <li class="flex gap-2"><span class="text-violet-600">•</span> Oda sahibi ayrılırsa oda kapanır.</li>
                <li class="flex gap-2"><span class="text-violet-600">•</span> Misafirler için KVKK onayı zorunludur.</li>
            </ul>
        </div>
    </div>
</section>
@endsection
