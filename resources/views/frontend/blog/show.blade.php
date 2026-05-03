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
                <div class="w-full rounded-[1.1rem] bg-gradient-to-br from-violet-300/70 via-fuchsia-300/55 to-indigo-300/70 p-[2px] shadow-md transition-all duration-500 group-hover:shadow-xl">
                    <div class="rounded-2xl bg-white/80 p-1.5 backdrop-blur-[1px]">
                        <img
                            src="{{ asset('storage/'.$blog->image_path) }}"
                            alt="{{ $blog->title }} görseli"
                            class="block max-h-56 w-full cursor-pointer rounded-xl object-contain select-none transition-all duration-500 ease-out group-hover:-translate-y-2 group-hover:scale-110 group-hover:rotate-1 group-hover:saturate-200 group-hover:brightness-125 group-hover:hue-rotate-15 group-hover:drop-shadow-2xl sm:max-h-60 md:max-h-64"
                            draggable="false"
                            oncontextmenu="return false;"
                        >
                    </div>
                </div>
            </section>
        @endif

        <section class="mt-6 rounded-2xl border border-violet-100 bg-white p-5 shadow-sm transition-all duration-500 hover:-translate-y-1.5 hover:scale-[1.015] hover:border-violet-300 hover:bg-violet-50/60 hover:shadow-2xl md:p-6">
            <div class="prose prose-slate max-w-none leading-relaxed transition duration-500 hover:text-slate-900">
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
