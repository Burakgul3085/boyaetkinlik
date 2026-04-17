@extends('layouts.app')

@section('title', 'Anasayfa')

@section('content')
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
                    <p class="mt-1 text-3xl font-bold text-pink-600">{{ $featuredPages->count() }}</p>
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
                        <a href="{{ route('categories.show', ['slug' => $category->slug]) }}" class="group rounded-2xl border border-slate-200 bg-gradient-to-r from-white to-indigo-50/40 p-4 transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="text-base font-semibold text-slate-900">{{ $category->name }}</h3>
                                <span class="rounded-full bg-white px-2.5 py-1 text-[11px] font-semibold text-violet-700 shadow-sm transition group-hover:bg-violet-100">Keşfet</span>
                            </div>
                            <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-500">
                                {{ $category->description ?: 'Yaşa ve seviyeye uygun boyama, çizgi ve etkinlik içeriklerini keşfedin.' }}
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="mt-7 rounded-2xl border border-pink-100 bg-white p-4 shadow-sm md:p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-2xl font-bold text-slate-900">Öne Çıkan Boyamalar</h2>
                    <span class="rounded-full bg-pink-50 px-3 py-1 text-xs font-semibold text-pink-700">Popüler Seçimler</span>
                </div>

                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($featuredPages as $page)
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
                    @endforeach
                </div>
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
