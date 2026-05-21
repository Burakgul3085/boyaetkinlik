@extends('layouts.app')

@section('title', $activeCategory ? $activeCategory->name.' — Deneyler' : 'Deneyler')

@section('content')
    <section class="exp-3d-hero relative overflow-hidden rounded-3xl border border-violet-200/80 bg-gradient-to-br from-violet-100 via-fuchsia-50 to-indigo-100 p-6 shadow-[0_20px_50px_-12px_rgba(124,58,237,0.35)] md:p-8">
        <div class="exp-3d-orb exp-3d-orb--a pointer-events-none" aria-hidden="true"></div>
        <div class="exp-3d-orb exp-3d-orb--b pointer-events-none" aria-hidden="true"></div>
        <p class="relative z-10 inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-violet-700 shadow-md">Boya Etkinlik Deneyleri</p>
        <h1 class="relative z-10 mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">
            @if($activeCategory)
                {{ $activeCategory->name }}
            @else
                Deneyler & Uygulamalar
            @endif
        </h1>
        <p class="relative z-10 mt-3 max-w-3xl text-sm leading-relaxed text-slate-600 md:text-base">
            @if($activeCategory && $activeCategory->description)
                {{ $activeCategory->description }}
            @else
                Eğitim deneyleri, uygulamalı etkinlikler ve video destekli içerikler. Soldaki kategori ağacından filtreleyin.
            @endif
        </p>
        <div class="relative z-10 mt-5 flex flex-wrap items-center gap-2">
            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-md">
                @if($activeCategory)
                    {{ $experiments->total() }} deney bu filtrede
                @else
                    {{ $totalExperimentCount }} yayınlanmış deney
                @endif
            </span>
        </div>
    </section>

    <div class="mt-6 flex flex-col gap-6 lg:grid lg:grid-cols-[minmax(0,17.5rem)_minmax(0,1fr)] lg:items-start">
        @include('partials.experiment-category-filter-panel')

        <div class="min-w-0 exp-3d-grid-perspective">
            @if($activeCategory)
                <p class="mb-4 rounded-xl border border-violet-100 bg-violet-50/50 px-4 py-2.5 text-sm text-violet-900">
                    <strong>{{ $activeCategory->name }}</strong> ve alt kategorilerindeki deneyler listeleniyor.
                </p>
            @endif

            @if($experiments->count() === 0)
                <div class="rounded-2xl border border-violet-100 bg-white p-8 text-center text-sm text-slate-500 shadow-sm">
                    @if($activeCategory)
                        Bu kategoride henüz yayınlanmış deney yok.
                    @else
                        Henüz yayınlanmış deney yok.
                    @endif
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-3">
                    @foreach($experiments as $experiment)
                        <article class="exp-3d-card group">
                            <div class="exp-3d-card__inner overflow-hidden rounded-2xl border border-violet-100/90 bg-white">
                                @if($experiment->youtubeEmbedUrl() && ! $experiment->image_path)
                                    <div class="relative aspect-video overflow-hidden bg-slate-900">
                                        <img
                                            src="{{ $experiment->youtubeThumbnailUrl() }}"
                                            alt="{{ $experiment->title }} video önizleme"
                                            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                            loading="lazy"
                                        >
                                        <span class="absolute inset-0 flex items-center justify-center bg-slate-900/25">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-full bg-red-600 text-white shadow-lg">▶</span>
                                        </span>
                                    </div>
                                @elseif($experiment->image_path)
                                    <div class="bg-gradient-to-br from-violet-50 to-fuchsia-50/50 p-3">
                                        <img
                                            src="{{ asset('storage/'.$experiment->image_path) }}"
                                            alt="{{ $experiment->title }} görseli"
                                            class="h-52 w-full rounded-xl object-contain select-none"
                                            draggable="false"
                                            loading="lazy"
                                        >
                                    </div>
                                @endif
                                <div class="p-4 {{ ($experiment->image_path || $experiment->youtubeEmbedUrl()) ? 'pt-2' : 'pt-4' }}">
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span>{{ $experiment->authorFullName() }} · {{ $experiment->published_at?->format('d.m.Y') ?? $experiment->created_at?->format('d.m.Y') }}</span>
                                        @if($experiment->category)
                                            <a href="{{ route('experiments.category', $experiment->category) }}" class="rounded-full bg-violet-100 px-2 py-0.5 font-semibold text-violet-700 hover:bg-violet-200">{{ $experiment->category->name }}</a>
                                        @endif
                                        @if($experiment->youtubeEmbedUrl())
                                            <span class="rounded-full bg-red-50 px-2 py-0.5 font-semibold text-red-700">Video</span>
                                        @endif
                                    </div>
                                    <h2 class="mt-2 line-clamp-2 text-lg font-bold text-slate-900">{{ $experiment->title }}</h2>
                                    <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $experiment->excerpt }}</p>
                                    <a href="{{ route('experiments.show', $experiment) }}" class="mt-4 inline-flex items-center rounded-xl bg-violet-50 px-3 py-2 text-sm font-semibold text-violet-700 transition hover:bg-violet-100">
                                        Deney Detayı
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $experiments->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
