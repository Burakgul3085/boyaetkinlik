@extends('layouts.app')

@section('title', 'Blog')

@section('content')
    <section class="overflow-hidden rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/80 to-indigo-50 p-6 shadow-sm md:p-8">
        <p class="inline-flex items-center rounded-full bg-white/85 px-3 py-1 text-xs font-semibold text-violet-700 shadow-sm">Topluluk Blogu</p>
        <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Boya Etkinlik Blog Yazıları</h1>
        <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600 md:text-base">
            Topluluktan gelen, admin onaylı blog içeriklerini burada okuyabilirsiniz. Siz de deneyiminizi paylaşmak için blog yazısı gönderebilirsiniz.
        </p>
        <div class="mt-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('blog.create') }}" class="btn-primary">Blog Yazısı Gönder</a>
            <span class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm">{{ $blogs->total() }} onaylı yazı</span>
        </div>
    </section>

    @if($categories->isNotEmpty())
        <section class="mt-4 flex flex-wrap gap-2">
            <a
                href="{{ route('blog.index') }}"
                class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $activeCategory ? 'border border-violet-200 bg-white text-slate-700 hover:bg-violet-50' : 'bg-violet-600 text-white shadow-sm' }}"
            >
                Tümü
            </a>
            @foreach($categories as $cat)
                <a
                    href="{{ route('blog.category', $cat) }}"
                    class="rounded-full px-4 py-2 text-sm font-semibold transition {{ $activeCategory?->id === $cat->id ? 'bg-violet-600 text-white shadow-sm' : 'border border-violet-200 bg-white text-slate-700 hover:bg-violet-50' }}"
                >
                    {{ $cat->name }}
                    <span class="ml-1 text-xs opacity-80">({{ $cat->blogs_count }})</span>
                </a>
            @endforeach
        </section>
    @endif

    @if($activeCategory)
        <p class="mt-3 text-sm text-slate-600">
            Kategori: <strong>{{ $activeCategory->name }}</strong>
        </p>
    @endif

    <section class="mt-6">
        @if($blogs->count() === 0)
            <div class="rounded-2xl border border-violet-100 bg-white p-6 text-center text-sm text-slate-500 shadow-sm">
                Henüz yayınlanmış blog yazısı yok. İlk yazıyı siz gönderin.
            </div>
        @else
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($blogs as $blog)
                    <article class="overflow-hidden rounded-2xl border border-violet-100 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                        @if($blog->image_path)
                            <div class="bg-transparent p-3">
                                <img
                                    src="{{ asset('storage/'.$blog->image_path) }}"
                                    alt="{{ $blog->title }} görseli"
                                    class="h-52 w-full rounded-xl object-contain select-none"
                                    draggable="false"
                                    oncontextmenu="return false;"
                                >
                            </div>
                        @endif
                        <div class="p-4 pt-0">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span>{{ $blog->authorFullName() }} · {{ $blog->created_at?->format('d.m.Y') }}</span>
                                @if($blog->category)
                                    <a href="{{ route('blog.category', $blog->category) }}" class="rounded-full bg-violet-100 px-2 py-0.5 font-semibold text-violet-700 hover:bg-violet-200">{{ $blog->category->name }}</a>
                                @endif
                            </div>
                            <h2 class="mt-2 line-clamp-2 text-lg font-bold text-slate-900">{{ $blog->title }}</h2>
                            <p class="mt-2 line-clamp-3 text-sm text-slate-600">{{ $blog->excerpt }}</p>
                            <a href="{{ route('blog.show', $blog) }}" class="mt-4 inline-flex items-center rounded-xl bg-violet-50 px-3 py-2 text-sm font-semibold text-violet-700 transition hover:bg-violet-100">
                                Blog Detayı
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $blogs->links() }}
            </div>
        @endif
    </section>
@endsection
