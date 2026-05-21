@extends('layouts.app')

@section('title', $experiment->title)

@section('content')
    <div class="space-y-6 exp-3d-detail">
        <article class="exp-3d-hero relative overflow-hidden rounded-3xl border border-violet-200/80 bg-gradient-to-br from-violet-100 via-fuchsia-50 to-indigo-100 p-6 shadow-[0_24px_60px_-16px_rgba(124,58,237,0.4)] md:p-8">
            <div class="exp-3d-orb exp-3d-orb--a pointer-events-none" aria-hidden="true"></div>
            <div class="exp-3d-orb exp-3d-orb--b pointer-events-none" aria-hidden="true"></div>
            <p class="relative z-10 inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-violet-700 shadow-md">Deney Detayı</p>
            <h1 class="relative z-10 mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">{{ $experiment->title }}</h1>
            <p class="relative z-10 mt-2 text-xs text-slate-500">
                {{ $experiment->authorFullName() }} · {{ $experiment->published_at?->format('d.m.Y H:i') ?? $experiment->created_at?->format('d.m.Y H:i') }}
                @if($experiment->category)
                    · <a href="{{ route('experiments.category', $experiment->category) }}" class="font-semibold text-violet-700 hover:text-violet-800">{{ $experiment->category->name }}</a>
                @endif
            </p>
            <p class="relative z-10 mt-4 rounded-2xl border border-violet-100 bg-white/90 p-4 text-sm leading-relaxed text-slate-700 shadow-sm">
                {{ $experiment->excerpt }}
            </p>
            @if($experiment->hasPlayableOnlineLab())
                <div class="relative z-10 mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('experiments.online.play', $experiment) }}" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-violet-700">
                        Online deney yap →
                    </a>
                    <a href="{{ route('experiments.online.hub') }}" class="inline-flex items-center rounded-xl border border-violet-200 bg-white/90 px-4 py-2.5 text-sm font-semibold text-violet-800 transition hover:bg-white">
                        Tüm laboratuvar
                    </a>
                </div>
            @endif
        </article>

        @if($experiment->image_path)
            <section class="exp-media-section">
                @include('partials.experiment-media', ['experiment' => $experiment, 'variant' => 'detail'])
            </section>
        @endif

        @if($experiment->youtubeEmbedUrl())
            @php
                $ytId = \App\Support\YoutubeEmbed::extractId($experiment->youtube_url);
                $ytWatchUrl = $ytId ? 'https://www.youtube.com/watch?v='.$ytId : $experiment->youtube_url;
            @endphp
            <section class="exp-3d-video mx-auto w-full max-w-4xl">
                <p class="mb-2 text-center text-xs font-semibold uppercase tracking-wide text-violet-600">Deney videosu</p>
                <div class="exp-3d-video__frame">
                    <div class="relative aspect-video w-full overflow-hidden rounded-2xl bg-slate-900 shadow-[0_28px_60px_-20px_rgba(15,23,42,0.55)]">
                        <iframe
                            src="{{ $experiment->youtubeEmbedUrl() }}"
                            title="{{ $experiment->title }} — YouTube videosu"
                            class="absolute inset-0 h-full w-full border-0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="strict-origin-when-cross-origin"
                        ></iframe>
                    </div>
                </div>
                <p class="mt-3 text-center text-xs text-slate-500">
                    Video oynatılmıyorsa
                    <a href="{{ $ytWatchUrl }}" target="_blank" rel="noopener noreferrer" class="font-semibold text-violet-700 hover:text-violet-900">YouTube’da aç</a>
                </p>
            </section>
        @endif

        <section class="exp-3d-content-card rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <div class="prose prose-slate max-w-none leading-relaxed text-slate-700">
                {!! nl2br(e($experiment->content)) !!}
            </div>
        </section>

        @if($recentExperiments->isNotEmpty())
            <section class="exp-3d-panel rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-bold text-slate-900">Diğer Deneyler</h2>
                    <a href="{{ route('experiments.index') }}" class="rounded-xl bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 transition hover:bg-violet-100">Tümünü Gör</a>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @foreach($recentExperiments as $recent)
                        <article class="exp-3d-mini-card">
                            <a href="{{ route('experiments.show', $recent) }}" class="block rounded-xl border border-violet-100 bg-violet-50/50 p-3">
                                <h3 class="line-clamp-1 text-sm font-semibold text-slate-900">{{ $recent->title }}</h3>
                                <p class="mt-1 line-clamp-2 text-xs text-slate-600">{{ $recent->excerpt }}</p>
                            </a>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
