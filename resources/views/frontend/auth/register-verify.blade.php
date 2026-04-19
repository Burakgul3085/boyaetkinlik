@extends('layouts.app')

@section('title', 'Üyelik Doğrulama')

@section('content')
<section class="mx-auto max-w-xl">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">Üyelik Doğrulama</h1>
        <p class="mt-1 text-sm text-slate-500">E-postanıza gelen 6 haneli kodu girerek üyeliği tamamlayın.</p>

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

        <form method="post" action="{{ route('member.register.verify.submit') }}" class="mt-5 space-y-4">
            @csrf
            <label class="block text-sm font-medium text-slate-700">
                Doğrulama Kodu
                <input type="text" name="verification_code" inputmode="numeric" autocomplete="one-time-code" required class="input-ui mt-1">
            </label>
            <button class="btn-primary w-full">Üyeliği Tamamla</button>
        </form>
    </div>
</section>
@endsection
