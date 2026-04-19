@extends('layouts.app')

@section('title', 'Anasayfa')

@section('content')
    @php
        $categoryIcons = [
            'okul öncesi' => '🧸',
            '2 yaş' => '👶',
            '3 yaş' => '🧒',
            '4 yaş' => '🎨',
            '5-6 yaş' => '🧩',
            'özel eğitim' => '🤝',
            'boyama sayfaları' => '🖍️',
            'çizgi çalışmaları' => '✏️',
            'etkinlik sayfaları' => '📘',
            'kelime kartları' => '🔤',
            'ilkokul' => '🏫',
            'ortaokul' => '📚',
            'yetişkin' => '🧠',
        ];
    @endphp

    <section class="relative mb-6 overflow-hidden rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50 to-indigo-50 shadow-sm">
        <div class="pointer-events-none absolute -left-14 -top-16 h-40 w-40 rounded-full bg-pink-200/40 blur-2xl"></div>
        <div class="pointer-events-none absolute -bottom-16 -right-12 h-44 w-44 rounded-full bg-indigo-200/40 blur-2xl"></div>

        <div class="relative grid gap-6 p-6 md:grid-cols-2 md:p-8">
            <div>
                <p class="inline-flex items-center rounded-full bg-white/80 px-3 py-1 text-xs font-semibold text-violet-700 shadow-sm">
                    Çocuklar için eğlenceli ve güvenli içerikler
                </p>
                <h1 class="mt-4 break-words text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">
                    Renkli, Tatlı ve Yaratıcı Boyama Dünyası
                </h1>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-600 md:text-base">
                    Okul öncesinden yetişkin seviyesine kadar düzenli, anlaşılır ve keyifli boyama içeriklerini keşfedin.
                    Ücretsiz içerikleri anında indirebilir, premium içeriklerle arşivinizi büyütebilirsiniz.
                </p>
                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm">2-6 Yas</span>
                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm">İlkokul</span>
                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm">Ortaokul</span>
                    <span class="rounded-full bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm">Yetişkin</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-indigo-100 bg-white/90 p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-medium text-slate-500">Toplam Kategori</p>
                    <p class="js-animated-counter mt-1 text-3xl font-bold text-violet-700" data-counter-target="{{ $categories->count() }}">0</p>
                    <p class="mt-2 text-[11px] font-medium text-violet-500">Aktif kategori sayısı</p>
                </div>
                <div class="rounded-2xl border border-indigo-100 bg-white/90 p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-medium text-slate-500">Öne Çıkan İçerik</p>
                    <p class="js-animated-counter mt-1 text-3xl font-bold text-pink-600" data-counter-target="{{ $featuredCount }}">0</p>
                    <p class="mt-2 text-[11px] font-medium text-pink-500">Vitrin içerikleri</p>
                </div>
                <div class="rounded-2xl border border-emerald-100 bg-white/90 p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-medium text-slate-500">Toplam Ücretsiz</p>
                    <p class="js-animated-counter mt-1 text-3xl font-bold text-emerald-600" data-counter-target="{{ $totalFreePagesCount }}">0</p>
                    <p class="mt-2 text-[11px] font-medium text-emerald-500">Anında erişilebilir içerik</p>
                </div>
                <div class="rounded-2xl border border-violet-100 bg-white/90 p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md">
                    <p class="text-xs font-medium text-slate-500">Toplam Ücretli</p>
                    <p class="js-animated-counter mt-1 text-3xl font-bold text-violet-700" data-counter-target="{{ $totalPaidPagesCount }}">0</p>
                    <p class="mt-2 text-[11px] font-medium text-violet-500">Premium içerikler</p>
                </div>
                <div class="col-span-2 rounded-2xl border border-indigo-100 bg-white/90 p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs font-medium text-slate-500">Toplam Yüklü Boyama Sayfası</p>
                            <p class="js-animated-counter mt-1 text-2xl font-bold text-indigo-700" data-counter-target="{{ $totalPagesCount }}">0</p>
                        </div>
                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Canlı İstatistik</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Ana arama: kategori, yaş, alt başlık veya boyama adı --}}
    <section class="mb-6 rounded-3xl border border-violet-100/90 bg-gradient-to-br from-violet-50 via-fuchsia-50/70 to-indigo-50 px-4 py-8 shadow-sm shadow-violet-100/40">
        <form
            id="home-hero-search-form"
            method="get"
            action="{{ route('home') }}"
            class="js-live-filter-form mx-auto w-full max-w-3xl"
            data-live-target="#home-live-area"
            autocomplete="off"
        >
            @foreach (['mode', 'pricing', 'sort', 'category_id', 'date_from', 'date_to'] as $filterKey)
                <input type="hidden" name="{{ $filterKey }}" value="{{ $activeFilters[$filterKey] ?? '' }}">
            @endforeach

            <label class="sr-only" for="home-hero-q">İçerik ara</label>
            <div class="flex items-center gap-2 rounded-full border-2 border-violet-200/90 bg-white py-1.5 pl-5 pr-1.5 shadow-md shadow-violet-100/60 ring-1 ring-violet-100/50 transition focus-within:border-indigo-200 focus-within:ring-indigo-100">
                <input
                    id="home-hero-q"
                    type="search"
                    name="q"
                    value="{{ $activeFilters['q'] }}"
                    class="min-w-0 flex-1 border-0 bg-transparent py-2 text-sm text-slate-800 placeholder:text-slate-400 focus:ring-0 md:text-[15px]"
                    placeholder="Ne aramak istersiniz? Örn: boyama, kelime kartları, 5-6 yaş, okul öncesi, ortaokul, kaplumbağa..."
                >
                <button
                    type="submit"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-r from-violet-500 to-indigo-500 text-white shadow-md shadow-violet-200 transition hover:scale-105 hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-violet-400 focus-visible:ring-offset-2"
                    aria-label="Ara"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 1 0 3.23 9.96l3.38 3.38a.75.75 0 1 0 1.06-1.06l-3.38-3.38A5.5 5.5 0 0 0 9 3.5ZM4.5 9a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0Z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <p class="mt-3 text-center text-xs text-violet-900/60">
                Yaş grubu, seviye, kategori veya yüklediğiniz boyama başlığıyla arayın; sonuçlar aşağıda listelenir.
            </p>
        </form>
    </section>

    <div id="home-live-area">
        @if($activeFilters['q'] !== '')
            <section class="mb-6 rounded-2xl border border-amber-100 bg-white p-4 shadow-sm md:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-bold text-slate-900">Arama Sonucu: "{{ $activeFilters['q'] }}"</h2>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                        {{ $searchCategoryMatches->count() + $searchPageMatches->count() }} eşleşme
                    </span>
                </div>

                <div class="mt-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori ve Alt Kategori Eşleşmeleri</p>
                    <div class="grid gap-3 md:grid-cols-2">
                        @forelse($searchCategoryMatches as $matchedCategory)
                            @php
                                $matchedIcon = $categoryIcons[mb_strtolower($matchedCategory->name)] ?? '⭐';
                            @endphp
                            <a href="{{ route('categories.show', ['slug' => $matchedCategory->slug]) }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-r from-white to-indigo-50/40 p-4 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        @if($matchedCategory->icon_path)
                                            <span class="inline-flex h-8 w-8 items-center justify-center overflow-hidden rounded-xl bg-violet-100 shadow-sm">
                                                <img src="{{ asset('storage/'.$matchedCategory->icon_path) }}" alt="{{ $matchedCategory->name }} ikonu" class="h-6 w-6 object-contain">
                                            </span>
                                        @else
                                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-violet-100 text-base shadow-sm">{{ $matchedIcon }}</span>
                                        @endif
                                        <div>
                                            <h3 class="text-base font-semibold text-slate-900">{{ $matchedCategory->name }}</h3>
                                            @if($matchedCategory->parent)
                                                <p class="text-xs text-slate-500">Ana kategori: {{ $matchedCategory->parent->name }}</p>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-violet-700 shadow-sm transition group-hover:bg-violet-100">Aç</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500 md:col-span-2">
                                Kategori bazlı eşleşme bulunamadı.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-5">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Boyama Sayfaları Eşleşmeleri</p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @forelse($searchPageMatches as $matchedPage)
                            <a href="{{ route('products.show', $matchedPage) }}" class="group rounded-2xl border border-slate-200 bg-white p-3 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="line-clamp-2 font-semibold text-slate-900">{{ $matchedPage->title }}</p>
                                        <p class="mt-1 text-xs text-slate-500">{{ $matchedPage->category?->name ?? 'Kategori yok' }}</p>
                                    </div>
                                    <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600 group-hover:bg-indigo-100 group-hover:text-indigo-700">Detay</span>
                                </div>
                            </a>
                        @empty
                            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-500 sm:col-span-2">
                                Boyama sayfası eşleşmesi bulunamadı.
                            </div>
                        @endforelse
                    </div>
                </div>
            </section>
        @endif

    @if($paidMarqueePages->isNotEmpty())
        @php
            $paidLoopPages = $paidMarqueePages->concat($paidMarqueePages);
        @endphp
        <section class="mb-6 overflow-hidden rounded-2xl border border-violet-100 bg-white/90 p-3 shadow-sm">
            <div class="mb-3 flex items-center justify-between gap-2 px-1">
                <h2 class="text-sm font-bold tracking-wide text-violet-700">Premium içerikler</h2>
                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700">{{ $totalPaidPagesCount }} ücretli ürün</span>
            </div>

            <div class="paid-marquee">
                <div class="paid-marquee-track">
                    @foreach($paidLoopPages as $paidPage)
                        <a href="{{ route('products.show', $paidPage) }}" class="paid-marquee-item group">
                            <div class="overflow-hidden rounded-xl bg-slate-100">
                                <img
                                    src="{{ route('products.preview-image', $paidPage) }}"
                                    alt="{{ $paidPage->title }}"
                                    class="h-24 w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                    draggable="false"
                                    onerror="this.onerror=null;this.src='https://placehold.co/600x400/e2e8f0/334155?text=Premium+Urun';"
                                >
                            </div>
                            <div class="mt-2">
                                <p class="line-clamp-1 text-sm font-semibold text-slate-900">{{ $paidPage->title }}</p>
                                <div class="mt-1 flex items-center justify-between gap-2">
                                    <p class="line-clamp-1 text-[11px] text-slate-500">{{ $paidPage->category?->name ?? 'Kategori yok' }}</p>
                                    <span class="text-xs font-bold text-violet-700">{{ number_format((float) $paidPage->price, 2) }} TL</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <x-public-ad-rail class="mb-1">
        <div class="space-y-7">
            <div class="rounded-2xl border border-indigo-100 bg-white p-4 shadow-sm md:p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-3">
                    <h2 class="min-w-0 break-words text-xl font-bold text-slate-900 sm:text-2xl">Yaş ve Seviye Kategorileri</h2>
                    <span class="w-fit shrink-0 rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">{{ $categories->count() }} kategori</span>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @foreach($categories as $category)
                        @php
                            $icon = $categoryIcons[mb_strtolower($category->name)] ?? '⭐';
                        @endphp
                        <a href="{{ route('categories.show', ['slug' => $category->slug]) }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-r from-white to-indigo-50/40 p-4 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    @if($category->icon_path)
                                        <span class="inline-flex h-8 w-8 items-center justify-center overflow-hidden rounded-xl bg-violet-100 shadow-sm">
                                            <img src="{{ asset('storage/'.$category->icon_path) }}" alt="{{ $category->name }} ikonu" class="h-6 w-6 object-contain">
                                        </span>
                                    @else
                                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-violet-100 text-base shadow-sm">{{ $icon }}</span>
                                    @endif
                                    <h3 class="text-base font-semibold text-slate-900">{{ $category->name }}</h3>
                                </div>
                                <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-violet-700 shadow-sm transition group-hover:bg-violet-100">Keşfet</span>
                            </div>
                            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-500">
                                {{ $category->description ?: 'Yaşa ve seviyeye uygun boyama, çizgi ve etkinlik içeriklerini keşfedin.' }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div id="home-filter-panel" class="mt-7 rounded-2xl border border-pink-100 bg-white p-4 shadow-sm md:p-5">
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-between sm:gap-3">
                    <h2 class="min-w-0 break-words text-xl font-bold text-slate-900 sm:text-2xl">Boyama İçeriklerini Filtrele</h2>
                    <span class="w-fit shrink-0 rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">{{ $filteredPages->total() }} sonuç</span>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    <a
                        href="{{ route('home', ['mode' => 'all', 'q' => $activeFilters['q'], 'pricing' => $activeFilters['pricing'], 'date_from' => $activeFilters['date_from'], 'date_to' => $activeFilters['date_to'], 'sort' => $activeFilters['sort'], 'category_id' => $activeFilters['category_id']]) }}"
                        class="js-live-filter-link {{ $activeFilters['mode'] === 'all' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-700' }} rounded-full px-3 py-1.5 text-xs font-semibold transition hover:brightness-95"
                    >
                        Tüm İçerikler
                    </a>
                    <a
                        href="{{ route('home', ['mode' => 'featured', 'q' => $activeFilters['q'], 'pricing' => $activeFilters['pricing'], 'date_from' => $activeFilters['date_from'], 'date_to' => $activeFilters['date_to'], 'sort' => $activeFilters['sort'], 'category_id' => $activeFilters['category_id']]) }}"
                        class="js-live-filter-link {{ $activeFilters['mode'] === 'featured' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-700' }} rounded-full px-3 py-1.5 text-xs font-semibold transition hover:brightness-95"
                    >
                        Öne Çıkanlar
                    </a>
                    <a
                        href="{{ route('home', ['mode' => 'latest', 'q' => $activeFilters['q'], 'pricing' => $activeFilters['pricing'], 'date_from' => $activeFilters['date_from'], 'date_to' => $activeFilters['date_to'], 'sort' => $activeFilters['sort'], 'category_id' => $activeFilters['category_id']]) }}"
                        class="js-live-filter-link {{ $activeFilters['mode'] === 'latest' ? 'bg-violet-600 text-white' : 'bg-violet-50 text-violet-700' }} rounded-full px-3 py-1.5 text-xs font-semibold transition hover:brightness-95"
                    >
                        En Yeniler (24 Saat)
                    </a>
                </div>

                <form id="home-inner-filter-form" method="get" action="{{ route('home') }}" class="js-live-filter-form mt-4 rounded-2xl border border-violet-100 bg-violet-50/40 p-3 md:p-4" data-live-target="#home-live-area">
                    <input type="hidden" name="mode" value="{{ $activeFilters['mode'] }}">

                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        <label class="input-ui">
                            Kategori
                            <select name="category_id" class="mt-1 w-full rounded-lg border border-violet-200 bg-white px-3 py-2 text-sm">
                                <option value="">Tüm Kategoriler</option>
                                @foreach($allCategories as $menuCategory)
                                    <option value="{{ $menuCategory->id }}" @selected((int) $activeFilters['category_id'] === $menuCategory->id)>{{ $menuCategory->name }} (Ana)</option>
                                    @foreach($menuCategory->children as $childCategory)
                                        <option value="{{ $childCategory->id }}" @selected((int) $activeFilters['category_id'] === $childCategory->id)>- {{ $childCategory->name }} (Alt)</option>
                                    @endforeach
                                @endforeach
                            </select>
                        </label>

                        <label class="input-ui">
                            Kelime ile ara
                            <input type="text" name="q" value="{{ $activeFilters['q'] }}" class="mt-1 w-full" placeholder="Başlık, kategori veya açıklama (örn. tavşan, ilkokul, kelime kartı)">
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
                        <a href="{{ route('home') }}" class="btn-secondary">Temizle</a>
                    </div>
                </form>

                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse($filteredPages as $page)
                        <a href="{{ route('products.show', $page) }}" class="group rounded-2xl border border-slate-200 bg-white p-3 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md">
                            <div class="overflow-hidden rounded-xl bg-slate-100">
                                <img
                                    src="{{ route('products.preview-image', $page) }}"
                                    class="h-40 w-full object-cover transition duration-300 group-hover:scale-[1.03]"
                                    alt="{{ $page->title }}"
                                    draggable="false"
                                    onerror="this.onerror=null;this.src='https://placehold.co/600x400/e2e8f0/334155?text=Boya%20Sayfas%C4%B1';"
                                >
                            </div>
                            <div class="mt-3 flex items-start justify-between gap-2">
                                <p class="line-clamp-2 font-semibold text-slate-900">{{ $page->title }}</p>
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] text-slate-600 group-hover:bg-indigo-100 group-hover:text-indigo-700">Detay</span>
                            </div>
                            <p class="mt-1 text-sm font-semibold {{ $page->is_free ? 'text-emerald-600' : 'text-violet-600' }}">
                                {{ $page->is_free ? 'Ücretsiz' : number_format($page->price, 2).' TL' }}
                            </p>
                        </a>
                    @empty
                        <div class="card p-5 text-sm text-slate-500 sm:col-span-2 lg:col-span-3">
                            Bu filtreye uygun içerik bulunamadı.
                        </div>
                    @endforelse
                </div>

                @if($filteredPages->hasPages())
                    <div class="mt-6 overflow-x-auto pb-1 [-webkit-overflow-scrolling:touch]">
                        {{ $filteredPages->links() }}
                    </div>
                @endif
            </div>
        </div>
    </x-public-ad-rail>
</div>

    {{-- Ana ızgaradaki içerik sütunu (lg: 8/12, ortada) ile aynı hizada: yan boşluklar üstteki listeyle eşit --}}
    <div class="mt-10 grid w-full gap-5 lg:grid-cols-12 lg:gap-6">
        <div class="min-w-0 lg:col-span-8 lg:col-start-3">
            @include('partials.home-visitor-feedback', ['approvedVisitorFeedback' => $approvedVisitorFeedback])
        </div>
    </div>

@endsection
