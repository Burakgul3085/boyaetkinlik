@extends('layouts.app')

@section('title', 'Anasayfa')

@section('content')
    <section class="mb-6 card-soft p-6">
        <h1 class="text-2xl font-bold text-slate-900 md:text-3xl">Çocuklar ve Yetişkinler için Boyama Sayfaları</h1>
        <p class="mt-2 max-w-3xl text-sm text-slate-600 md:text-base">
            Kategorilere göre filtrele, ücretsiz içerikleri indir veya premium paketleri güvenli şekilde satın al.
        </p>
    </section>

    <div class="mb-6 card p-4">
        {!! $adsHeader ?: '<div class="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">Header reklam alanı</div>' !!}
    </div>

    <div class="grid gap-6 lg:grid-cols-12">
        <aside class="hidden lg:col-span-2 lg:block">
            {!! $adsLeft ?: '<div class="rounded-2xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">Sol reklam</div>' !!}
        </aside>

        <section class="lg:col-span-8">
            <h2 class="text-2xl font-bold text-slate-900">Kategoriler</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-2">
                @foreach($categories as $category)
                    <a href="{{ route('categories.show', $category) }}" class="card p-5 transition hover:-translate-y-0.5 hover:shadow-md">
                        <h3 class="text-lg font-semibold">{{ $category->name }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $category->description }}</p>
                    </a>
                @endforeach
            </div>

            <h2 class="mt-10 text-2xl font-bold text-slate-900">Öne Çıkan Boyamalar</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($featuredPages as $page)
                    <a href="{{ route('products.show', $page) }}" class="card p-4 transition hover:shadow-md">
                        <img
                            src="{{ route('products.preview-image', $page) }}"
                            class="h-40 w-full rounded-xl object-cover"
                            alt="{{ $page->title }}"
                            onerror="this.onerror=null;this.src='https://placehold.co/600x400/e2e8f0/334155?text=Boya+Sayfasi';"
                        >
                        <p class="mt-3 font-semibold">{{ $page->title }}</p>
                        <p class="text-sm {{ $page->is_free ? 'text-emerald-600' : 'text-indigo-600' }}">
                            {{ $page->is_free ? 'Ücretsiz' : number_format($page->price, 2).' TL' }}
                        </p>
                    </a>
                @endforeach
            </div>
        </section>

        <aside class="hidden lg:col-span-2 lg:block">
            {!! $adsRight ?: '<div class="rounded-2xl border border-dashed border-slate-300 p-4 text-center text-sm text-slate-500">Sağ reklam</div>' !!}
        </aside>
    </div>
@endsection
