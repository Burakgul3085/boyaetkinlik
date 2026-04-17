@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <section class="overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-indigo-50 shadow-sm">
        <div class="grid gap-5 p-6 md:grid-cols-2 md:p-8">
            <div>
                <p class="inline-flex items-center rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700">
                    Kategori Detayı
                </p>
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900">{{ $category->name }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 md:text-base">
                    {{ $category->description ?: 'Bu kategori için özenle seçilmiş boyama içeriklerini aşağıda inceleyebilirsiniz.' }}
                </p>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                    <p class="text-xs text-slate-500">Toplam İçerik</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $coloringPages->count() }}</p>
                </div>
                <div class="rounded-xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                    <p class="text-xs text-slate-500">Ücretsiz İçerik</p>
                    <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $coloringPages->where('is_free', true)->count() }}</p>
                </div>
                <div class="col-span-2 rounded-xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                    <p class="text-xs text-slate-500">Kategori Notu</p>
                    <p class="mt-1 text-sm font-medium text-slate-700">İçerikler yeni eklemelerle dinamik olarak güncellenir.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="mt-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-2xl font-bold text-slate-900">Boyama Sayfaları</h2>
            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">{{ $coloringPages->count() }} içerik</span>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($coloringPages as $page)
            <a href="{{ route('products.show', $page) }}" class="card group p-4 transition hover:-translate-y-0.5 hover:shadow-md">
                <img
                    src="{{ route('products.preview-image', $page) }}"
                    class="h-40 w-full rounded-xl object-cover"
                    alt="{{ $page->title }}"
                    draggable="false"
                    onerror="this.onerror=null;this.src='https://placehold.co/600x400/e2e8f0/334155?text=Boya+Sayfasi';"
                >
                <div class="mt-3 flex items-start justify-between gap-2">
                    <p class="font-semibold text-slate-900">{{ $page->title }}</p>
                    <span class="rounded-md bg-violet-50 px-2 py-0.5 text-[11px] text-violet-700 group-hover:bg-violet-100">Detay</span>
                </div>
                <p class="text-sm font-medium {{ $page->is_free ? 'text-emerald-600' : 'text-violet-600' }}">
                    {{ $page->is_free ? 'Ücretsiz' : number_format($page->price, 2).' TL' }}
                </p>
            </a>
        @empty
            <p class="card p-5 text-slate-500">Bu kategoride henüz boyama sayfası yok.</p>
        @endforelse
    </div>
@endsection
