@extends('layouts.app')

@section('title', 'Online Deney Laboratuvarı')

@section('content')
    <section class="exp-3d-hero relative overflow-hidden rounded-3xl border border-violet-200/80 bg-gradient-to-br from-violet-100 via-fuchsia-50 to-indigo-100 p-6 shadow-[0_20px_50px_-12px_rgba(124,58,237,0.35)] md:p-8">
        <p class="relative z-10 inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-violet-700 shadow-md">Online Deney Laboratuvarı</p>
        <h1 class="relative z-10 mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Deneyleri bilgisayarda dene</h1>
        <p class="relative z-10 mt-3 max-w-3xl text-sm leading-relaxed text-slate-600 md:text-base">
            Aşağıdan bir deney seç, adımları takip et, sonucu ekranda gör. Evde gerçek uygulama için
            <a href="{{ route('experiments.index') }}" class="font-semibold text-violet-700 hover:text-violet-900">deney yazılarına</a> da göz atabilirsin.
        </p>
        <a href="{{ route('experiments.index') }}" class="relative z-10 mt-4 inline-flex text-sm font-semibold text-violet-700 hover:text-violet-900">← Deney yazıları listesi</a>
    </section>

    @if($labCount === 0)
        <div class="mt-6 rounded-2xl border border-violet-100 bg-white p-8 text-center text-sm text-slate-600 shadow-sm">
            Şu an açık online deney yok. Yakında yeni deneyler eklenecek.
        </div>
    @else
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($labs as $lab)
                <article class="exp-3d-card group h-full">
                    <div class="exp-3d-card__inner flex h-full flex-col overflow-hidden rounded-2xl border border-violet-100 bg-white">
                        <div class="flex min-h-[10rem] items-center justify-center bg-gradient-to-br from-violet-100 to-fuchsia-100 p-6">
                            <span class="text-5xl" aria-hidden="true">{{ $lab['icon'] ?? '🧪' }}</span>
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex flex-wrap gap-2 text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                <span class="rounded-full bg-violet-100 px-2 py-0.5 text-violet-700">{{ $lab['age_label'] }}</span>
                                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-indigo-700">{{ $lab['duration_label'] }}</span>
                            </div>
                            <h2 class="mt-2 text-lg font-bold text-slate-900">{{ $lab['title'] }}</h2>
                            <p class="mt-2 line-clamp-3 flex-1 text-sm text-slate-600">{{ $lab['excerpt'] }}</p>
                            <a href="{{ route('experiments.online.play', $lab['slug']) }}" class="btn-primary mt-4 w-full text-center">Deneyi başlat →</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
@endsection
