@extends('layouts.app')

@section('title', $coloringPage->title)

@section('content')
    <x-public-ad-rail>
    <div x-data="{ previewOpen: false }">
        <div class="grid min-w-0 gap-6 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-8 card p-5 lg:p-6">
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
            <h1 class="mt-4 break-words text-2xl font-bold text-slate-900">{{ $coloringPage->title }}</h1>
            <p class="mt-2 text-slate-600">{{ $coloringPage->description }}</p>
            <div class="mt-6">
                {!! \App\Models\Setting::getValue('ads_product_detail') ?: '<div class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">Ürün detay reklam alanı</div>' !!}
            </div>
            </div>

            <div class="min-w-0 lg:col-span-4 card p-5" x-data="{ verificationInfoOpen: false }">
                <p class="text-sm text-slate-500">Kategori: {{ $coloringPage->category?->name ?? 'Kategorisiz' }}</p>
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
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
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
                <button
                    type="button"
                    onclick="directPrint('{{ route('products.print.free', ['coloringPage' => $coloringPage, 'format' => $originalFormat]) }}')"
                    class="btn-secondary mt-3 w-full"
                >
                    Dosyayı Yazdır
                </button>
                @guest
                    <div class="mt-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">E-posta ile gönder</p>
                        <p class="mt-1 text-xs text-slate-500">Ücretsiz dosyayı istediğiniz adrese ek olarak gönderelim.</p>
                        <form method="post" action="{{ route('products.free.email', $coloringPage) }}" class="mt-3 space-y-3">
                            @csrf
                            <div>
                                <label for="free-email" class="mb-1 block text-sm font-medium text-slate-700">E-posta</label>
                                <input
                                    id="free-email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autocomplete="email"
                                    class="input-ui w-full"
                                    placeholder="ornek@eposta.com"
                                >
                                @error('email')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="free-format" class="mb-1 block text-sm font-medium text-slate-700">Dosya formatı</label>
                                <select id="free-format" name="format" class="input-ui w-full">
                                    @foreach ($downloadFormats as $format)
                                        <option value="{{ $format }}" @selected(old('format', $originalFormat) === $format)>
                                            {{ strtoupper($format) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('format')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            @error('email_send')
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            @if (session('free_email_sent'))
                                <p class="rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-800">Dosya e-posta adresinize gönderildi.</p>
                            @endif
                            <button type="submit" class="btn-primary w-full">E-postaya gönder</button>
                        </form>
                    </div>
                @endguest
                @else
                    @php
                        $shopierProductUrl = trim((string) ($coloringPage->shopier_product_url ?? ''));
                    @endphp
                    <a href="{{ route('purchase.verification.show') }}" class="btn-secondary mt-5 w-full text-center">Satın Alım Doğrula</a>
                    <div class="mt-2">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-white px-3 py-1.5 text-xs font-medium text-violet-700 transition hover:bg-violet-50"
                            @click="verificationInfoOpen = !verificationInfoOpen"
                            :aria-expanded="verificationInfoOpen ? 'true' : 'false'"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M18 10A8 8 0 1 1 2 10a8 8 0 0 1 16 0ZM9.25 7a.75.75 0 0 1 1.5 0v.25a.75.75 0 0 1-1.5 0V7Zm0 3a.75.75 0 0 1 .75-.75h.01a.75.75 0 0 1 0 1.5H10a.75.75 0 0 1-.75-.75Zm.75 2.5a.75.75 0 0 1 .75.75v1.25a.75.75 0 0 1-1.5 0v-1.25a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>
                            </svg>
                            Satın alım doğrulama bilgisi
                        </button>
                        <div
                            x-show="verificationInfoOpen"
                            x-transition.opacity.duration.150ms
                            x-cloak
                            class="mt-2 rounded-xl border border-violet-100 bg-violet-50/60 p-3 text-xs leading-relaxed text-slate-700"
                        >
                            Bu ürünü satın aldığınızda Shopier ürünü otomatik olarak e-posta adresinize gönderir.
                            Ayrıca buradan doğrulama yaparak ürünü tekrar tekrar indirebilir veya indirme bağlantısını e-posta ile alabilirsiniz.
                            Doğrulama için Shopier sipariş no, ürün adı, ödeme sırasında kullandığınız e-posta ve telefon bilgileri gereklidir.
                        </div>
                    </div>
                    @auth
                        @if(!auth()->user()->is_admin && session('member_code_verified', false))
                            <form method="post" action="{{ route('member.cart.add') }}" class="mt-5">
                                @csrf
                                <input type="hidden" name="coloring_page_id" value="{{ $coloringPage->id }}">
                                <button class="btn-secondary w-full">Sepete Ekle</button>
                            </form>
                            <a href="{{ route('member.cart') }}" class="btn-primary mt-3 w-full">Sepetime Git</a>
                        @else
                            @if($shopierProductUrl !== '')
                                <a href="{{ $shopierProductUrl }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5 w-full text-center">Satın Al (Shopier)</a>
                            @else
                                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">Bu ürün için Shopier satış linki henüz tanımlanmadı.</div>
                            @endif
                        @endif
                    @else
                        @if($shopierProductUrl !== '')
                            <a href="{{ $shopierProductUrl }}" target="_blank" rel="noopener noreferrer" class="btn-primary mt-5 w-full text-center">Satın Al (Shopier)</a>
                        @else
                            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">Bu ürün için Shopier satış linki henüz tanımlanmadı.</div>
                        @endif
                    @endauth
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
    </x-public-ad-rail>
    <script>
        function directPrint(url) {
            var params = new URL(url, window.location.href).searchParams;
            var fmt = (params.get('format') || '').toLowerCase();
            var isPdf = fmt === 'pdf' || url.toLowerCase().indexOf('.pdf') !== -1;

            if (isPdf) {
                // PDF için: blob olarak çek, gizli iframe içinde render et, print() çağır
                fetch(url, { credentials: 'same-origin' })
                    .then(function (r) { return r.blob(); })
                    .then(function (blob) {
                        var blobUrl = URL.createObjectURL(blob);
                        var iframe = document.createElement('iframe');
                        iframe.style.cssText = 'position:fixed;top:-200%;left:-200%;width:100vw;height:100vh;border:none;visibility:hidden;';
                        document.body.appendChild(iframe);
                        var printed = false;
                        var doPrint = function () {
                            if (printed) return;
                            printed = true;
                            try {
                                iframe.contentWindow.focus();
                                iframe.contentWindow.print();
                            } catch (e) {}
                            setTimeout(function () {
                                if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
                                URL.revokeObjectURL(blobUrl);
                            }, 6000);
                        };
                        iframe.onload = function () { setTimeout(doPrint, 800); };
                        setTimeout(doPrint, 5000);
                        iframe.src = blobUrl;
                    })
                    .catch(function () {
                        var win = window.open(url, '_blank', 'width=900,height=700,menubar=no,toolbar=no');
                        if (win) { setTimeout(function () { win.print(); }, 2000); }
                    });
            } else {
                // Görsel için: gizli iframe + header/footer gizleme
                var iframe = document.createElement('iframe');
                iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:none;';
                document.body.appendChild(iframe);

                var html = '<!DOCTYPE html><html><head><title></title>'
                    + '<style>'
                    + '@page{margin:0;size:auto;}'
                    + 'html,body{margin:0;padding:0;background:#fff;}'
                    + 'img{display:block;width:100%;height:100vh;object-fit:contain;}'
                    + '</style></head><body>'
                    + '<img src="' + url + '" />'
                    + '<scr' + 'ipt>'
                    + 'document.querySelector("img").onload=function(){'
                    + '  window.focus();window.print();'
                    + '};'
                    + '<\/scr' + 'ipt>'
                    + '</body></html>';

                var doc = iframe.contentDocument || iframe.contentWindow.document;
                doc.open();
                doc.write(html);
                doc.close();

                setTimeout(function () {
                    if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
                }, 8000);
            }
        }
    </script>
@endsection
