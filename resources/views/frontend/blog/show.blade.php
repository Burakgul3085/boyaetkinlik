@extends('layouts.app')

@section('title', $blog->title)

@section('content')
    <div class="space-y-6">
        <article class="group relative overflow-hidden rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/85 to-indigo-50 p-6 shadow-sm transition-all duration-500 hover:-translate-y-1 hover:shadow-lg md:p-8">
            <div class="pointer-events-none absolute -right-14 -top-14 h-28 w-28 rounded-full bg-fuchsia-300/25 blur-2xl transition duration-500 group-hover:scale-125"></div>
            <div class="pointer-events-none absolute -bottom-12 -left-12 h-24 w-24 rounded-full bg-indigo-300/20 blur-2xl transition duration-500 group-hover:scale-125"></div>
            <p class="inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-violet-700 shadow-sm transition duration-300 group-hover:bg-violet-100">Blog Detayı</p>
            <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 transition-colors duration-300 group-hover:text-violet-900 md:text-4xl">{{ $blog->title }}</h1>
            <p class="mt-2 text-xs text-slate-500 transition duration-300 group-hover:text-slate-600">{{ $blog->authorFullName() }} · {{ $blog->created_at?->format('d.m.Y H:i') }}</p>
            <p class="mt-4 rounded-2xl border border-violet-100 bg-white/90 p-4 text-sm leading-relaxed text-slate-700 transition-all duration-300 group-hover:border-violet-200 group-hover:bg-white">
                {{ $blog->excerpt }}
            </p>
        </article>

        @if($blog->image_path)
            <section class="mx-auto flex w-fit max-w-full items-center justify-center">
                <div class="group rounded-[1.45rem] bg-gradient-to-br from-sky-300 via-cyan-300 to-indigo-300 p-[2px] shadow-[0_10px_24px_rgba(56,189,248,0.25),_0_2px_8px_rgba(15,23,42,0.18)] transition-all duration-500 ease-out hover:-translate-y-1.5 hover:scale-[1.03] hover:from-violet-300 hover:via-fuchsia-300 hover:to-sky-300 hover:shadow-[0_20px_34px_rgba(124,58,237,0.34),_0_4px_10px_rgba(15,23,42,0.22)]">
                    <div class="rounded-[1.3rem] bg-transparent p-2 ring-1 ring-white/80">
                        <img
                            src="{{ asset('storage/'.$blog->image_path) }}"
                            alt="{{ $blog->title }} görseli"
                            class="block h-auto max-h-56 w-auto max-w-full cursor-pointer rounded-[1rem] object-contain select-none transition-all duration-500 ease-out group-hover:-translate-y-1 group-hover:rotate-[0.6deg] group-hover:saturate-150 group-hover:brightness-110 group-hover:hue-rotate-6 sm:max-h-60 md:max-h-64"
                            draggable="false"
                            oncontextmenu="return false;"
                        >
                    </div>
                </div>
            </section>
        @endif

        <section
            x-data="{ hoverCard: false }"
            @mouseenter="hoverCard = true"
            @mouseleave="hoverCard = false"
            class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm transition-all duration-500 ease-out md:p-6"
            :style="hoverCard
                ? 'transform: translateY(-8px) scale(1.01); background: linear-gradient(135deg, rgba(237,233,254,0.96), rgba(250,232,255,0.92), rgba(224,231,255,0.94)); border-color: rgba(167,139,250,0.95); box-shadow: 0 22px 40px rgba(124,58,237,0.24);'
                : 'transform: translateY(0) scale(1); background: #ffffff; border-color: rgb(221,214,254); box-shadow: 0 1px 2px rgba(15,23,42,0.06);'"
        >
            <div
                class="prose prose-slate max-w-none leading-relaxed text-slate-700 transition-all duration-500"
                :style="hoverCard ? 'color: rgb(76,29,149);' : 'color: rgb(51,65,85);'"
            >
                {!! nl2br(e($blog->content)) !!}
            </div>
        </section>

        @if($recentBlogs->isNotEmpty())
            <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm transition-all duration-500 hover:-translate-y-0.5 hover:shadow-md md:p-6">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 class="text-lg font-bold text-slate-900">Diğer Blog Yazıları</h2>
                    <a href="{{ route('blog.index') }}" class="rounded-xl bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 transition hover:bg-violet-100">Tümünü Gör</a>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @foreach($recentBlogs as $recentBlog)
                        <a href="{{ route('blog.show', $recentBlog) }}" class="group rounded-xl border border-violet-100 bg-violet-50/50 p-3 transition-all duration-300 hover:-translate-y-0.5 hover:border-violet-300 hover:bg-violet-100/70 hover:shadow-sm">
                            <p class="line-clamp-1 text-sm font-semibold text-slate-900 transition-colors duration-300 group-hover:text-violet-900">{{ $recentBlog->title }}</p>
                            <p class="mt-1 line-clamp-2 text-xs text-slate-600 transition-colors duration-300 group-hover:text-slate-700">{{ $recentBlog->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
