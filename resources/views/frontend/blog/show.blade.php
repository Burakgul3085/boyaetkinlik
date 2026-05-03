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
        <section class="mx-auto mt-6 max-w-3xl overflow-hidden rounded-2xl border border-violet-100 bg-transparent p-3 shadow-sm">
            <img
                src="{{ asset('storage/'.$blog->image_path) }}"
                alt="{{ $blog->title }} görseli"
                class="mx-auto max-h-[24rem] w-full rounded-xl object-contain select-none md:max-h-[28rem]"
                draggable="false"
                oncontextmenu="return false;"
            >
        </section>
    @endif

    <section class="mt-6 rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
        <div class="prose prose-slate max-w-none leading-relaxed">
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
