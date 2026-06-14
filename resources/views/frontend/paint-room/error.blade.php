@extends('layouts.app')

@section('title', $title)

@section('content')
<section class="mx-auto max-w-lg">
    <div class="card p-6 text-center md:p-8">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-100 text-2xl">!</div>
        <h1 class="mt-4 text-2xl font-bold text-slate-900">{{ $title }}</h1>
        <p class="mt-3 text-sm text-slate-600">{{ $message }}</p>
        @if(! empty($actionUrl))
            <a href="{{ $actionUrl }}" class="btn-primary mt-6 inline-flex">{{ $actionLabel ?? 'Devam' }}</a>
        @endif
        <a href="{{ route('paint-room.index') }}" class="btn-secondary mt-3 inline-flex text-sm">Ana sayfaya dön</a>
    </div>
</section>
@endsection
