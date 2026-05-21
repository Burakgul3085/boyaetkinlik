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

    @php $onlineLabCount = $onlineLabCount ?? 0; @endphp
    @if($onlineLabCount > 0)
        <section class="mt-5 overflow-hidden rounded-2xl border border-violet-200 bg-gradient-to-r from-violet-600 via-fuchsia-600 to-indigo-600 p-5 text-white shadow-lg md:flex md:items-center md:justify-between md:gap-6 md:p-6">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-white/80">Boya Etkinlik Laboratuvarı</p>
                <h2 class="mt-1 text-xl font-bold md:text-2xl">Online Deney Yap</h2>
                <p class="mt-2 max-w-xl text-sm text-white/90">Bilgisayarında güvenle dene; renklerin birleşmesini izle. {{ $onlineLabCount }} interaktif deney hazır.</p>
            </div>
            <a href="{{ route('experiments.online.hub') }}" class="mt-4 inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-bold text-violet-700 shadow-md transition hover:bg-violet-50 md:mt-0">
                Laboratuvara gir →
            </a>
        </section>
    @endif

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
                <div class="grid items-start gap-5 sm:grid-cols-2 xl:grid-cols-2 2xl:grid-cols-3">
                    @foreach($experiments as $experiment)
                        <article class="exp-3d-card group h-full">
                            <div class="exp-3d-card__inner flex h-full flex-col overflow-hidden rounded-2xl border border-violet-100/90 bg-white">
                                @include('partials.experiment-media', ['experiment' => $experiment, 'variant' => 'card'])
                                <div class="flex flex-1 flex-col p-4 {{ ($experiment->image_path || $experiment->youtubeEmbedUrl()) ? 'pt-3' : 'pt-4' }}">
                                    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                        <span>{{ $experiment->authorFullName() }} · {{ $experiment->published_at?->format('d.m.Y') ?? $experiment->created_at?->format('d.m.Y') }}</span>
                                        @if($experiment->category)
                                            <a href="{{ route('experiments.category', $experiment->category) }}" class="rounded-full bg-violet-100 px-2 py-0.5 font-semibold text-violet-700 hover:bg-violet-200">{{ $experiment->category->name }}</a>
                                        @endif
                                        @if($experiment->hasPlayableOnlineLab())
                                            <span class="rounded-full bg-indigo-100 px-2 py-0.5 font-semibold text-indigo-800">Online dene</span>
                                        @endif
                                        @if($experiment->youtubeEmbedUrl())
                                            <span class="rounded-full bg-red-50 px-2 py-0.5 font-semibold text-red-700">Video</span>
                                        @endif
                                    </div>
                                    <h2 class="mt-2 line-clamp-2 text-lg font-bold text-slate-900">{{ $experiment->title }}</h2>
                                    <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $experiment->excerpt }}</p>
                                    <a href="{{ route('experiments.show', $experiment) }}" class="mt-auto inline-flex items-center rounded-xl bg-violet-50 px-3 py-2 pt-4 text-sm font-semibold text-violet-700 transition hover:bg-violet-100">
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
