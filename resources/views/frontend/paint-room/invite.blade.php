@extends('layouts.app')

@section('title', 'Davet ile Katıl')

@section('content')
<section class="mx-auto max-w-lg">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">Davet ile katıl</h1>
        <p class="mt-1 text-sm text-slate-500">Oda sahibi sizi davet etti. Bu link tek kullanımlıktır.</p>

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route('paint-room.invite.submit', $inviteToken) }}" class="mt-5 space-y-4">
            @csrf
            <label class="block text-sm font-medium text-slate-700">
                Görünen ad (isteğe bağlı)
                <input type="text" name="display_name" value="{{ old('display_name') }}" maxlength="80" class="input-ui mt-1" placeholder="Örn: Mehmet">
            </label>

            @include('frontend.paint-room._kvkk-field')

            <button type="submit" class="btn-primary w-full">Odaya bağlan</button>
        </form>
    </div>
</section>
@endsection
