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

    @if($blog->image_path)
        <section class="group mx-auto mt-6 flex w-full max-w-[26rem] items-center justify-center sm:max-w-[28rem] lg:max-w-[30rem]">
            <img
                src="{{ asset('storage/'.$blog->image_path) }}"
                alt="{{ $blog->title }} görseli"
                class="block max-h-56 w-full rounded-2xl object-contain select-none transition duration-500 ease-out group-hover:-translate-y-1 group-hover:scale-[1.035] group-hover:rotate-[0.4deg] group-hover:saturate-125 group-hover:brightness-110 sm:max-h-60 md:max-h-64"
                draggable="false"
                oncontextmenu="return false;"
            >
        </section>
    @endif

    <section class="group mt-6 rounded-2xl border border-violet-100 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-violet-200 hover:bg-violet-50/40 hover:shadow-md md:p-6">
        <div class="prose prose-slate max-w-none leading-relaxed transition duration-300 group-hover:text-slate-800">
            {!! nl2br(e($blog->content)) !!}
        </div>
    </section>

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
