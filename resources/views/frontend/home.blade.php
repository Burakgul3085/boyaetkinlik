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
                <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">
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
                <div class="rounded-2xl border border-indigo-100 bg-white/90 p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Toplam Kategori</p>
                    <p class="mt-1 text-3xl font-bold text-violet-700">{{ $categories->count() }}</p>
                </div>
                <div class="rounded-2xl border border-indigo-100 bg-white/90 p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Öne Çıkan İçerik</p>
                    <p class="mt-1 text-3xl font-bold text-pink-600">{{ $featuredCount }}</p>
                </div>
                <div class="col-span-2 rounded-2xl border border-indigo-100 bg-white/90 p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Neden Bu Platform?</p>
                    <p class="mt-1 text-sm font-medium text-slate-700">
                        Çocuklara uygun temiz tasarım, net kategori yapısı ve canlı yayında reklamlarla uyumlu düzen.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
        <div class="overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3">
            <div class="min-h-[92px] w-full overflow-hidden rounded-lg bg-white text-center text-sm text-slate-500 [&_*]:max-w-full">
                {!! $adsHeader ?: '<div class="flex min-h-[92px] items-center justify-center px-3">Header reklam alanı</div>' !!}
            </div>
        </div>
    </section>

    <div class="grid gap-5 lg:grid-cols-12">
        <aside class="hidden lg:col-span-2 lg:block">
            <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <p class="mb-2 text-center text-xs font-semibold text-slate-500">Sol Reklam</p>
                <div class="min-h-[540px] overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 text-center text-sm text-slate-500 [&_*]:max-w-full">
                    {!! $adsLeft ?: '<div class="flex min-h-[540px] items-center justify-center px-2">Sol reklam alanı</div>' !!}
                </div>
            </div>
        </aside>

        <section class="lg:col-span-8">
            <div class="rounded-2xl border border-indigo-100 bg-white p-4 shadow-sm md:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-2xl font-bold text-slate-900">Yaş ve Seviye Kategorileri</h2>
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">{{ $categories->count() }} kategori</span>
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
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-2xl font-bold text-slate-900">Boyama İçeriklerini Filtrele</h2>
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">{{ $filteredPages->total() }} sonuç</span>
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

                <form method="get" action="{{ route('home') }}" class="js-live-filter-form mt-4 rounded-2xl border border-violet-100 bg-violet-50/40 p-3 md:p-4" data-live-target="#home-filter-panel">
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
                                    onerror="this.onerror=null;this.src='https://placehold.co/600x400/e2e8f0/334155?text=Boya+Sayfasi';"
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
                    <div class="mt-6">
                        {{ $filteredPages->links() }}
                    </div>
                @endif
            </div>
        </section>

        <aside class="hidden lg:col-span-2 lg:block">
            <div class="sticky top-24 rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
                <p class="mb-2 text-center text-xs font-semibold text-slate-500">Sağ Reklam</p>
                <div class="min-h-[540px] overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 text-center text-sm text-slate-500 [&_*]:max-w-full">
                    {!! $adsRight ?: '<div class="flex min-h-[540px] items-center justify-center px-2">Sağ reklam alanı</div>' !!}
                </div>
            </div>
        </aside>
    </div>
@endsection
