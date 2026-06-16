@extends('layouts.app')

@section('title', 'PIN ile Katıl')

@section('content')
<section class="mx-auto max-w-lg">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">Odaya katıl</h1>
        <p class="mt-1 text-sm text-slate-500">6 haneli PIN'i girin. Üyelik gerekmez.</p>

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('paint-room.join.pin') }}" class="mt-5 space-y-4">
            @csrf
            <label class="block text-sm font-medium text-slate-700">
                PIN (6 hane)
                <input
                    type="text"
                    name="pin"
                    value="{{ old('pin') }}"
                    inputmode="numeric"
                    pattern="\d{6}"
                    maxlength="6"
                    required
                    class="input-ui mt-1 text-center text-2xl font-bold tracking-[0.4em]"
                    placeholder="000000"
                    autocomplete="one-time-code"
                >
            </label>
            <label class="block text-sm font-medium text-slate-700">
                Görünen ad (isteğe bağlı)
                <input type="text" name="display_name" value="{{ old('display_name') }}" maxlength="80" class="input-ui mt-1" placeholder="Örn: Ayşe">
            </label>

            @include('frontend.paint-room._room-consent', ['context' => 'guest'])

            <button type="submit" class="btn-primary w-full">Odaya katıl</button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-500">
            <a href="{{ route('paint-room.index') }}" class="font-medium text-violet-700 hover:underline">← Görüntülü boyama</a>
        </p>
    </div>
</section>
@endsection
