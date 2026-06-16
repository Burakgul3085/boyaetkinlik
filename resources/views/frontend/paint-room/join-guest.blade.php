@extends('layouts.app')

@section('title', 'Odaya Katıl — Misafir')

@section('content')
<section class="mx-auto max-w-lg">
    <div class="card overflow-hidden p-0">
        <div class="bg-gradient-to-br from-teal-500 via-violet-600 to-indigo-600 px-6 py-6 text-white">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/80">
                @if(!empty($viaPin))
                    Adım 2 / 2 — Misafir girişi
                @else
                    Misafir girişi
                @endif
            </p>
            <h1 class="mt-1 text-2xl font-bold">Odaya katıl</h1>
            <p class="mt-2 text-sm text-white/90">
                @if(!empty($ownerName))
                    <strong>{{ $ownerName }}</strong> sizi görüntülü boyama odasına davet etti.
                @else
                    Oda sahibi sizi görüntülü boyama odasına davet etti.
                @endif
            </p>
        </div>

        <div class="p-6 md:p-7">
            <p class="text-sm text-slate-600">
                Adınızı yazın, bilgilendirme metnini onaylayın ve odaya girin. Üyelik gerekmez.
            </p>

            @if($errors->any())
                <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ route('paint-room.join.submit', $inviteToken) }}" class="mt-5 space-y-4">
                @csrf
                <label class="block text-sm font-medium text-slate-700">
                    Adınız
                    <input
                        type="text"
                        name="display_name"
                        value="{{ old('display_name') }}"
                        maxlength="80"
                        required
                        autofocus
                        class="input-ui mt-1"
                        placeholder="Örn: Ayşe"
                    >
                </label>

                @include('frontend.paint-room._room-consent', ['context' => 'guest'])

                <button type="submit" class="btn-primary w-full">Odaya gir</button>
            </form>

            <p class="mt-4 text-center text-xs text-slate-500">
                Davet linkiniz yoksa
                <a href="{{ route('paint-room.join.form') }}" class="font-semibold text-violet-700 hover:underline">PIN ile katılın</a>.
            </p>
        </div>
    </div>
</section>
@endsection
