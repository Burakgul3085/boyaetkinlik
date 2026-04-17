@extends('layouts.app')

@section('title', $coloringPage->title)

@section('content')
    <div x-data="{ previewOpen: false }">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-8 card p-5 lg:p-6">
            <div class="group relative">
                <img
                    src="{{ route('products.preview-image', $coloringPage) }}"
                    class="w-full rounded-xl object-cover"
                    alt="{{ $coloringPage->title }}"
                    draggable="false"
                    onerror="this.onerror=null;this.src='https://placehold.co/900x600/e2e8f0/334155?text=Boya+Onizleme';"
                >

                @if($coloringPage->is_free)
                    <button
                        type="button"
                        @click="previewOpen = true"
                        class="absolute inset-0 flex items-center justify-center rounded-xl bg-slate-900/0 text-white opacity-0 transition duration-200 group-hover:bg-slate-900/25 group-hover:opacity-100"
                        aria-label="Önizlemeyi büyüt"
                    >
                        <span class="inline-flex items-center rounded-full bg-white/90 px-4 py-2 text-sm font-semibold text-slate-800 shadow">
                            Önizlemeyi Büyüt
                        </span>
                    </button>
                @else
                    <div
                        class="absolute inset-0 flex items-center justify-center rounded-xl bg-slate-900/35"
                        aria-label="Ücretli içerik büyütme kapalı"
                    >
                        <span class="inline-flex items-center gap-2 rounded-full bg-white/95 px-4 py-2 text-sm font-semibold text-slate-800 shadow">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M10 1a4 4 0 0 0-4 4v2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-1V5a4 4 0 0 0-4-4Zm2 6V5a2 2 0 1 0-4 0v2h4Z" clip-rule="evenodd" />
                            </svg>
                            Ücretli içerikte büyütme kapalı
                        </span>
                    </div>
                @endif
            </div>
            <h1 class="mt-4 text-2xl font-bold text-slate-900">{{ $coloringPage->title }}</h1>
            <p class="mt-2 text-slate-600">{{ $coloringPage->description }}</p>
            <div class="mt-6">
                {!! \App\Models\Setting::getValue('ads_product_detail') ?: '<div class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">Ürün detay reklam alanı</div>' !!}
            </div>
            </div>

            <div class="lg:col-span-4 card p-5" x-data="{ open: false }">
                <p class="text-sm text-slate-500">Kategori: {{ $coloringPage->category->name }}</p>
                <p class="mt-3 text-2xl font-bold {{ $coloringPage->is_free ? 'text-emerald-600' : 'text-indigo-600' }}">
                    {{ $coloringPage->is_free ? 'Ücretsiz' : number_format($coloringPage->price, 2).' TL' }}
                </p>

                @if($coloringPage->is_free)
                @php
                    $originalFormat = strtolower(pathinfo($coloringPage->pdf_path, PATHINFO_EXTENSION) ?: 'pdf');
                    $singleOriginalPdf = count($downloadFormats) === 1 && strtolower($downloadFormats[0]) === 'pdf' && $originalFormat === 'pdf';
                @endphp
                <div class="mt-5 rounded-xl border border-violet-100 bg-violet-50/50 p-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">İndirme Seçenekleri</p>
                    @if($singleOriginalPdf)
                        <p class="mt-2 text-xs text-slate-500">Dosya zaten PDF formatında, doğrudan indirebilirsiniz.</p>
                        <a
                            href="{{ route('products.download.free', ['coloringPage' => $coloringPage, 'format' => 'pdf']) }}"
                            class="btn-primary mt-3 w-full"
                        >
                            PDF İndir
                        </a>
                    @else
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            @foreach ($downloadFormats as $format)
                                @php
                                    $formatLabel = strtolower($format) === $originalFormat
                                        ? 'Orijinal ('.strtoupper($format).')'
                                        : (strtolower($format) === 'pdf' ? "PDF'e Dönüştür" : strtoupper($format).' Dönüştür');
                                @endphp
                                <a
                                    href="{{ route('products.download.free', ['coloringPage' => $coloringPage, 'format' => $format]) }}"
                                    class="inline-flex items-center justify-center rounded-lg border border-violet-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-violet-300 hover:text-violet-700"
                                >
                                    {{ $formatLabel }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <a
                    href="{{ route('products.print.free', ['coloringPage' => $coloringPage, 'format' => $originalFormat]) }}"
                    target="_blank"
                    rel="noopener"
                    class="btn-secondary mt-3 w-full"
                >
                    Dosyayı Yazdır
                </a>
                @else
                    <button @click="open = true" class="btn-primary mt-5 w-full">Satın Al (Shopier)</button>
                    <div x-show="open" class="mt-4 rounded-xl border border-violet-100 bg-violet-50/50 p-3">
                        <form method="post" action="{{ route('products.buy', $coloringPage) }}">
                            @csrf
                            <label class="mb-2 block text-sm font-medium">E-posta</label>
                            <input type="email" name="email" required class="input-ui">
                            <button class="btn-primary mt-3 w-full">Shopier'e Git</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>

        @if($coloringPage->is_free)
            <div
                x-show="previewOpen"
                x-transition.opacity
                x-cloak
                @click.self="previewOpen = false"
                @keydown.escape.window="previewOpen = false"
                class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/85 p-4"
            >
                <div class="relative w-full max-w-5xl">
                    <button
                        type="button"
                        @click.stop="previewOpen = false"
                        class="absolute right-3 top-3 z-10 rounded-lg bg-white/95 px-3 py-2 text-sm font-semibold text-slate-800 shadow hover:bg-white"
                    >
                        Kapat
                    </button>
                    <img
                        src="{{ route('products.preview-image', $coloringPage) }}"
                        alt="{{ $coloringPage->title }} büyük önizleme"
                        class="max-h-[85vh] w-full rounded-2xl border border-slate-700 bg-white object-contain shadow-2xl"
                        draggable="false"
                    >
                </div>
            </div>
        @endif
    </div>
@endsection
