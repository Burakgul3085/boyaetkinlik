@extends('layouts.app')

@section('title', 'PIN ile Katıl')

@section('content')
<section class="mx-auto max-w-lg">
    <div class="card overflow-hidden p-0">
        <div class="bg-gradient-to-br from-violet-600 to-indigo-600 px-6 py-6 text-white">
            <p class="text-[10px] font-bold uppercase tracking-widest text-white/80">Adım 1 / 2</p>
            <h1 class="mt-1 text-2xl font-bold">PIN ile katıl</h1>
            <p class="mt-2 text-sm text-white/90">Oda sahibinin verdiği 6 haneli PIN'i girin. Sonraki adımda adınızı yazıp onaylayacaksınız.</p>
        </div>

        <div class="p-6 md:p-7">
            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
            @endif

            <form method="post" action="{{ route('paint-room.join.pin') }}" class="mt-5 space-y-4">
                @csrf
                <label class="block text-sm font-medium text-slate-700">
                    Oda PIN'i (6 hane)
                    <input
                        type="text"
                        name="pin"
                        value="{{ old('pin') }}"
                        inputmode="numeric"
                        maxlength="8"
                        required
                        autofocus
                        class="input-ui mt-1 text-center text-2xl font-bold tracking-[0.4em]"
                        placeholder="000000"
                        autocomplete="one-time-code"
                    >
                </label>

                <button type="submit" class="btn-primary w-full">Devam et</button>
            </form>

            <p class="mt-4 text-center text-sm text-slate-500">
                <a href="{{ route('paint-room.index') }}" class="font-medium text-violet-700 hover:underline">← Görüntülü boyama</a>
            </p>
        </div>
    </div>
</section>
@endsection
