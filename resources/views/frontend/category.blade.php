@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <section class="overflow-hidden rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/80 to-indigo-50 shadow-sm">
        <div class="grid gap-5 p-6 md:grid-cols-2 md:p-8">
            <div>
                <div class="mb-4">
                    @include('partials.category-breadcrumb-nav', [
                        'breadcrumbItems' => $breadcrumbItems,
                        'wrapperClass' => 'flex flex-wrap items-center gap-x-0.5 gap-y-1 text-sm text-slate-600 dark:text-slate-400',
                    ])
                </div>
                <p class="inline-flex items-center rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700 dark:bg-violet-900/50 dark:text-violet-200">
                    Kategori Detayı
                </p>
                <h1 class="mt-4 break-words text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{{ $category->name }}</h1>
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 md:text-base">
                    {{ $category->description ?: 'Bu kategori için özenle seçilmiş boyama içeriklerini aşağıda inceleyebilirsiniz.' }}
                </p>
                @if($category->cover_image_path)
                    <div class="mt-5 w-full max-w-2xl overflow-hidden rounded-2xl border border-violet-100 bg-white/95 p-3 shadow-sm">
                        <img
                            src="{{ asset('storage/'.$category->cover_image_path) }}"
                            alt="{{ $category->name }} kategori görseli"
                            class="h-44 w-full rounded-xl object-contain select-none md:h-52"
                            draggable="false"
                            oncontextmenu="return false;"
                        >
                    </div>
                @endif
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-violet-100 bg-white/90 p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs text-slate-500">Toplam İçerik</p>
                    <p class="js-animated-counter mt-1 text-2xl font-bold text-violet-700" data-counter-target="{{ $categoryTotalCount }}">0</p>
                    <p class="mt-2 text-[11px] font-medium text-violet-500">Kategoriye ait toplam</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs text-slate-500">Toplam Ücretsiz</p>
                    <p class="js-animated-counter mt-1 text-2xl font-bold text-emerald-600" data-counter-target="{{ $categoryFreeCount }}">0</p>
                    <p class="mt-2 text-[11px] font-medium text-emerald-500">Hızlı erişim içerikleri</p>
                </div>
                <div class="rounded-2xl border border-pink-100 bg-white/90 p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs text-slate-500">Toplam Ücretli</p>
                    <p class="js-animated-counter mt-1 text-2xl font-bold text-pink-600" data-counter-target="{{ $categoryPaidCount }}">0</p>
                    <p class="mt-2 text-[11px] font-medium text-pink-500">Premium içerikler</p>
                </div>
                <div class="rounded-2xl border border-indigo-100 bg-white/90 p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs text-slate-500">Öne Çıkan</p>
                    <p class="js-animated-counter mt-1 text-2xl font-bold text-indigo-600" data-counter-target="{{ $categoryFeaturedCount }}">0</p>
                    <p class="mt-2 text-[11px] font-medium text-indigo-500">Vitrin içerikleri</p>
                </div>
                <div class="col-span-2 rounded-2xl border border-violet-100 bg-white/90 p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs text-slate-500">Filtre Sonucu</p>
                            <p class="js-animated-counter mt-1 text-xl font-bold text-slate-900" data-counter-target="{{ $coloringPages->total() }}">0</p>
                        </div>
                        <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700"><span class="js-animated-counter" data-counter-target="{{ $coloringPages->count() }}">0</span> içerik gösteriliyor</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if($category->children->isNotEmpty())
        <section class="mt-6 overflow-hidden rounded-2xl border border-violet-100 bg-white/90 p-5 shadow-sm">
            <div class="mb-4 rounded-xl border border-violet-100 bg-gradient-to-r from-violet-50/90 to-white px-3 py-2.5 shadow-sm dark:border-violet-900/50 dark:from-violet-950/40 dark:to-slate-900/80">
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-violet-600 dark:text-violet-400">Konumunuz</p>
                @include('partials.category-breadcrumb-nav', [
                    'breadcrumbItems' => $breadcrumbItems,
                    'wrapperClass' => 'flex flex-wrap items-center gap-x-0.5 gap-y-1 text-sm text-slate-700 dark:text-slate-300',
                ])
            </div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">Alt kategoriler</h2>
            <p class="mt-1 text-sm text-slate-600">Bu başlığın altındaki konulara geçmek için bir kart seçin.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($category->children as $child)
                    <a
                        href="{{ route('categories.show', ['slug' => $child->slug]) }}"
                        class="group flex flex-col rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50/80 to-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-md"
                    >
                        <span class="font-semibold text-slate-900 group-hover:text-violet-700">{{ $child->name }}</span>
                        @if($child->description)
                            <span class="mt-1 line-clamp-2 text-sm text-slate-600">{{ $child->description }}</span>
                        @endif
                        <span class="mt-3 inline-flex w-fit items-center rounded-full bg-violet-100 px-3 py-1 text-[11px] font-semibold text-violet-700">Keşfet</span>
                    </a>
                @endforeach
            </div>
        </section>
    @else
        <div class="mt-6 rounded-xl border border-violet-100 bg-gradient-to-r from-violet-50/90 to-white px-4 py-3 shadow-sm dark:border-violet-900/50 dark:from-violet-950/40 dark:to-slate-900/80">
            <p class="mb-1 text-[11px] font-semibold uppercase tracking-wide text-violet-600 dark:text-violet-400">Konumunuz</p>
            @include('partials.category-breadcrumb-nav', [
                'breadcrumbItems' => $breadcrumbItems,
                'wrapperClass' => 'flex flex-wrap items-center gap-x-0.5 gap-y-1 text-sm text-slate-700 dark:text-slate-300',
            ])
        </div>
    @endif

    <x-public-ad-rail>
    <div id="category-live-area">
        <div class="mt-6 rounded-2xl border border-violet-100 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route('categories.show', ['slug' => $category->slug, 'mode' => 'all', 'q' => $activeFilters['q'], 'pricing' => $activeFilters['pricing'], 'date_from' => $activeFilters['date_from'], 'date_to' => $activeFilters['date_to'], 'sort' => $activeFilters['sort']]) }}"
                    class="js-live-filter-link {{ $activeFilters['mode'] === 'all' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-700' }} rounded-full px-3 py-1.5 text-xs font-semibold transition hover:brightness-95"
                >
                    Tüm İçerikler
                </a>
                <a
                    href="{{ route('categories.show', ['slug' => $category->slug, 'mode' => 'featured', 'q' => $activeFilters['q'], 'pricing' => $activeFilters['pricing'], 'date_from' => $activeFilters['date_from'], 'date_to' => $activeFilters['date_to'], 'sort' => $activeFilters['sort']]) }}"
                    class="js-live-filter-link {{ $activeFilters['mode'] === 'featured' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-700' }} rounded-full px-3 py-1.5 text-xs font-semibold transition hover:brightness-95"
                >
                    Öne Çıkanlar
                </a>
                <a
                    href="{{ route('categories.show', ['slug' => $category->slug, 'mode' => 'latest', 'q' => $activeFilters['q'], 'pricing' => $activeFilters['pricing'], 'date_from' => $activeFilters['date_from'], 'date_to' => $activeFilters['date_to'], 'sort' => $activeFilters['sort']]) }}"
                    class="js-live-filter-link {{ $activeFilters['mode'] === 'latest' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-700' }} rounded-full px-3 py-1.5 text-xs font-semibold transition hover:brightness-95"
                >
                    En Yeniler (24 Saat)
                </a>
            </div>

            <form method="get" action="{{ route('categories.show', ['slug' => $category->slug]) }}" class="js-live-filter-form mt-4 rounded-2xl border border-violet-100 bg-violet-50/40 p-3 md:p-4" data-live-target="#category-live-area">
                <input type="hidden" name="mode" value="{{ $activeFilters['mode'] }}">

                <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-5">
                    <label class="input-ui">
                        İsim Ara
                        <input type="text" name="q" value="{{ $activeFilters['q'] }}" class="mt-1 w-full" placeholder="Örn: Tavşan, Araba...">
                    </label>

                    <label class="input-ui">
                        Ücret Filtresi
                        <select name="pricing" class="mt-1 w-full rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm">
                            <option value="all" @selected($activeFilters['pricing'] === 'all')>Tümü</option>
                            <option value="free" @selected($activeFilters['pricing'] === 'free')>Sadece Ücretsiz</option>
                            <option value="paid" @selected($activeFilters['pricing'] === 'paid')>Sadece Ücretli</option>
                        </select>
                    </label>

                    <label class="input-ui">
                        Tarih (Başlangıç)
                        <input type="date" name="date_from" value="{{ $activeFilters['date_from'] }}" class="mt-1 w-full">
                    </label>

                    <label class="input-ui">
                        Tarih (Bitiş)
                        <input type="date" name="date_to" value="{{ $activeFilters['date_to'] }}" class="mt-1 w-full">
                    </label>

                    <label class="input-ui">
                        Sıralama
                        <select name="sort" class="mt-1 w-full rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm">
                            <option value="newest" @selected($activeFilters['sort'] === 'newest')>Tarihe Göre (Yeni -> Eski)</option>
                            <option value="oldest" @selected($activeFilters['sort'] === 'oldest')>Tarihe Göre (Eski -> Yeni)</option>
                            <option value="title_asc" @selected($activeFilters['sort'] === 'title_asc')>A-Z</option>
                            <option value="title_desc" @selected($activeFilters['sort'] === 'title_desc')>Z-A</option>
                            <option value="price_asc" @selected($activeFilters['sort'] === 'price_asc')>Fiyat (Artan)</option>
                            <option value="price_desc" @selected($activeFilters['sort'] === 'price_desc')>Fiyat (Azalan)</option>
                        </select>
                    </label>
                </div>

                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs text-slate-500">Filtreler otomatik uygulanır.</p>
                    <a href="{{ route('categories.show', ['slug' => $category->slug]) }}" class="btn-secondary">Temizle</a>
                </div>
            </form>
        </div>

        <div class="mt-6">
            <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                <h2 class="min-w-0 break-words text-xl font-bold text-slate-900 sm:text-2xl">Boyama Sayfaları</h2>
                <span class="w-fit shrink-0 rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">{{ $coloringPages->total() }} içerik</span>
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
                        onerror="this.onerror=null;this.src='https://placehold.co/600x400/e2e8f0/334155?text=Boya%20Sayfas%C4%B1';"
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

        @if($coloringPages->hasPages())
            <div class="mt-6 overflow-x-auto pb-1 [-webkit-overflow-scrolling:touch]">
                {{ $coloringPages->links() }}
            </div>
        @endif
    </div>
    </x-public-ad-rail>

@endsection
