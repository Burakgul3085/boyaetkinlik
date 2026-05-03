@extends('layouts.app')

@section('title', $blog->title)

@section('content')
    <article class="overflow-hidden rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/80 to-indigo-50 p-6 shadow-sm md:p-8">
        <p class="inline-flex items-center rounded-full bg-white/85 px-3 py-1 text-xs font-semibold text-violet-700 shadow-sm">Blog Detayı</p>
        <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">{{ $blog->title }}</h1>
        <p class="mt-2 text-xs text-slate-500">{{ $blog->authorFullName() }} · {{ $blog->created_at?->format('d.m.Y H:i') }}</p>
        <p class="mt-4 rounded-2xl border border-violet-100 bg-white/85 p-4 text-sm leading-relaxed text-slate-700">
            {{ $blog->excerpt }}
        </p>
    </article>

    <div class="group">
        @if($blog->image_path)
            <section class="mx-auto mt-6 flex w-full max-w-[26rem] items-center justify-center sm:max-w-[28rem] lg:max-w-[30rem]">
                <div class="group w-full rounded-[1.35rem] bg-gradient-to-br from-cyan-300 via-sky-300 to-indigo-300 p-[2px] shadow-[0_10px_24px_rgba(56,189,248,0.22)] transition-all duration-500 ease-out hover:-translate-y-1 hover:scale-[1.02] hover:from-violet-300 hover:via-fuchsia-300 hover:to-sky-300 hover:shadow-[0_18px_30px_rgba(124,58,237,0.34)]">
                    <div class="rounded-[1.2rem] bg-transparent p-1.5 ring-1 ring-white/70">
                        <img
                            src="{{ asset('storage/'.$blog->image_path) }}"
                            alt="{{ $blog->title }} görseli"
                            class="block max-h-56 w-full cursor-pointer rounded-[0.95rem] object-contain select-none transition-all duration-500 ease-out group-hover:saturate-150 group-hover:brightness-110 group-hover:hue-rotate-6 sm:max-h-60 md:max-h-64"
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
            class="mt-6 rounded-2xl border border-violet-100 bg-white p-5 shadow-sm transition-all duration-500 ease-out md:p-6"
            :style="hoverCard
                ? 'transform: translateY(-8px) scale(1.01); background: linear-gradient(135deg, rgba(237,233,254,0.95), rgba(250,232,255,0.90), rgba(224,231,255,0.92)); border-color: rgba(167,139,250,0.9); box-shadow: 0 22px 40px rgba(124,58,237,0.24);'
                : 'transform: translateY(0) scale(1); background: #ffffff; border-color: rgb(221,214,254); box-shadow: 0 1px 2px rgba(15,23,42,0.06);'"
        >
            <div
                class="prose prose-slate max-w-none rounded-xl bg-transparent p-0 leading-relaxed text-slate-700 transition-all duration-500"
                :style="hoverCard ? 'color: rgb(76,29,149);' : 'color: rgb(51,65,85);'"
            >
                {!! nl2br(e($blog->content)) !!}
            </div>
        </section>
    </div>

    @if($recentBlogs->isNotEmpty())
        <section class="mt-6 rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-lg font-bold text-slate-900">Diğer Blog Yazıları</h2>
            <div class="mt-4 grid gap-3 md:grid-cols-2">
                @foreach($recentBlogs as $recentBlog)
                    <a href="{{ route('blog.show', $recentBlog) }}" class="rounded-xl border border-violet-100 bg-violet-50/50 p-3 transition hover:border-violet-200 hover:bg-violet-50">
                        <p class="line-clamp-1 text-sm font-semibold text-slate-900">{{ $recentBlog->title }}</p>
                        <p class="mt-1 line-clamp-2 text-xs text-slate-600">{{ $recentBlog->excerpt }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
