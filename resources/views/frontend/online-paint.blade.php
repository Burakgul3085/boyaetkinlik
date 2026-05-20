@extends('layouts.app')

@section('title', 'Online Boya — '.$coloringPage->title)

@section('content')
    <div class="online-paint-page mx-auto max-w-[1400px]">
        <header class="online-paint-topbar">
            <div class="min-w-0">
                <a href="{{ route('products.show', $coloringPage) }}" class="online-paint-back">← Ürüne dön</a>
                <h1 class="online-paint-title">{{ $coloringPage->title }}</h1>
                <p class="online-paint-sub">Profesyonel online boya stüdyosu — çizgiler sabit, renkler altta</p>
            </div>
            <span class="online-paint-badge">Ücretsiz</span>
        </header>

        @if(session('paint_email_sent'))
            <div class="mx-4 mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 md:mx-6">
                Boyanmış çalışmanız e-posta adresinize gönderildi.
            </div>
        @endif

        <div class="online-paint-workspace">
            <aside class="online-paint-toolbar" aria-label="Boyama araçları">
                <div class="online-paint-toolbar__head">
                    <p class="online-paint-toolbar__title">Stüdyo</p>
                    <span class="online-paint-toolbar__badge">Pro</span>
                </div>

                <p class="online-paint-section-label">Araçlar</p>
                <div class="online-paint-tools online-paint-tools--grid">
                    <button type="button" class="online-paint-tool" data-paint-tool="brush" title="Yumuşak fırça">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/></svg>
                        <span>Fırça</span>
                    </button>
                    <button type="button" class="online-paint-tool" data-paint-tool="pencil" title="İnce kalem">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20l4-1 9-9-3-3-9 9-1 4z"/><path d="M14 6l4 4"/></svg>
                        <span>Kalem</span>
                    </button>
                    <button type="button" class="online-paint-tool" data-paint-tool="marker" title="Keçeli kalem">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 20h12"/><path d="M8 20l2-14 4 14"/></svg>
                        <span>Keçeli</span>
                    </button>
                    <button type="button" class="online-paint-tool" data-paint-tool="spray" title="Sprey">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="2"/><circle cx="14" cy="6" r="1.5"/><circle cx="11" cy="13" r="2"/><circle cx="17" cy="11" r="1"/></svg>
                        <span>Sprey</span>
                    </button>
                    <button type="button" class="online-paint-tool" data-paint-tool="fill" title="Alan doldur">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.56 5.44l-1.45 1.45A5 5 0 1 0 14 10.9V12h2v-1.1a5 5 0 0 0 1.11-7.46zM7 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>
                        <span>Doldur</span>
                    </button>
                    <button type="button" class="online-paint-tool" data-paint-tool="eraser" title="Silgi">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 20H7L3 16l11-11 6 6-5 5"/><path d="M13.5 6.5l5 5"/></svg>
                        <span>Silgi</span>
                    </button>
                    <button type="button" class="online-paint-tool online-paint-tool--wide" data-paint-tool="picker" title="Renk seç (pipet)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 22l1-1"/><path d="M7 17l10-10 3 3L10 20l-3 3z"/><path d="M18 6l-2-2"/></svg>
                        <span>Pipet</span>
                    </button>
                </div>

                <p class="online-paint-section-label mt-4">Renk paleti</p>
                <div class="online-paint-color-preview" id="paint-color-preview">
                    <span class="online-paint-color-preview__swatch" id="paint-color-preview-swatch"></span>
                    <span class="online-paint-color-preview__hex" id="paint-color-preview-hex">#EF4444</span>
                </div>

                <div class="online-paint-swatches">
                    @foreach ([
                        '#ef4444','#f97316','#f59e0b','#eab308','#84cc16','#22c55e',
                        '#10b981','#14b8a6','#06b6d4','#0ea5e9','#3b82f6','#6366f1',
                        '#8b5cf6','#a855f7','#d946ef','#ec4899','#f43f5e','#78716c',
                        '#44403c','#000000','#ffffff','#fca5a5','#fde047','#86efac',
                    ] as $hex)
                        <button
                            type="button"
                            class="online-paint-swatch"
                            data-paint-color="{{ $hex }}"
                            style="background-color: {{ $hex }}"
                            title="{{ $hex }}"
                        ></button>
                    @endforeach
                </div>

                <label class="online-paint-custom-color mt-2">
                    <span>Özel renk</span>
                    <input type="color" id="paint-color-custom" value="#ef4444" class="h-9 w-full cursor-pointer rounded-lg border border-violet-200">
                </label>

                <p class="online-paint-section-label mt-3">Son kullanılan</p>
                <div id="paint-recent-colors" class="online-paint-recent"></div>

                <div id="paint-brush-settings" class="online-paint-brush-settings mt-4">
                    <p class="online-paint-section-label">Fırça ayarları</p>
                    <label class="online-paint-slider-row">
                        <span>Kalınlık</span>
                        <input type="range" id="paint-size" min="2" max="80" value="18" class="online-paint-range flex-1">
                        <strong id="paint-size-label">18</strong>
                    </label>
                    <label class="online-paint-slider-row">
                        <span>Opaklık</span>
                        <input type="range" id="paint-opacity" min="5" max="100" value="100" class="online-paint-range flex-1">
                        <strong id="paint-opacity-label">100%</strong>
                    </label>
                    <label class="online-paint-slider-row">
                        <span>Yumuşaklık</span>
                        <input type="range" id="paint-softness" min="0" max="100" value="35" class="online-paint-range flex-1">
                        <strong id="paint-softness-label">35%</strong>
                    </label>
                </div>

                <p class="online-paint-section-label mt-4">Görünüm</p>
                <div class="online-paint-zoom-row">
                    <button type="button" id="paint-zoom-out" class="online-paint-icon-btn" title="Uzaklaştır">−</button>
                    <span id="paint-zoom-label" class="online-paint-zoom-label">100%</span>
                    <button type="button" id="paint-zoom-in" class="online-paint-icon-btn" title="Yakınlaştır">+</button>
                    <button type="button" id="paint-zoom-fit" class="online-paint-action-btn flex-1">Sığdır</button>
                </div>

                <p class="online-paint-section-label mt-4">Düzenle</p>
                <div class="online-paint-actions">
                    <button type="button" id="paint-undo" class="online-paint-action-btn">↶ Geri</button>
                    <button type="button" id="paint-redo" class="online-paint-action-btn">↷ İleri</button>
                    <button type="button" id="paint-clear" class="online-paint-action-btn col-span-2">Temizle</button>
                </div>

                <p class="online-paint-hint">İpucu: Doldur ile kapalı alanları, sprey ile gölgelendirme yapın.</p>
            </aside>

            <div class="online-paint-stage-wrap">
                <div id="canvas-wrap" class="online-paint-canvas-wrap">
                    <div id="paint-loader" class="online-paint-loader">
                        <span class="online-paint-spinner"></span>
                        <p>Çizim yükleniyor…</p>
                    </div>
                    <p id="paint-error" class="online-paint-error hidden"></p>
                    <div id="canvas-scaler" class="online-paint-canvas-scaler">
                        <div id="canvas-stage" class="online-paint-canvas-stage">
                            <canvas id="paint-canvas" class="online-paint-canvas online-paint-canvas--paint"></canvas>
                            <canvas id="line-canvas" class="online-paint-canvas online-paint-canvas--lines"></canvas>
                            <div id="paint-hit-layer" class="online-paint-hit-layer" aria-hidden="true"></div>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="online-paint-export" aria-label="Dışa aktar">
                <p class="online-paint-toolbar__title">Sonuç</p>
                <p class="text-xs leading-relaxed text-slate-500">Boyadıktan sonra indirin, yazdırın veya e-posta ile gönderin. Sunucuya kayıt yapılmaz.</p>

                <p class="online-paint-toolbar__title mt-4">İndir</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" class="online-paint-export-btn" data-paint-download="png">PNG</button>
                    <button type="button" class="online-paint-export-btn" data-paint-download="jpg">JPG</button>
                    <button type="button" class="online-paint-export-btn col-span-2" data-paint-download="pdf">PDF</button>
                </div>

                <button type="button" id="paint-print" class="btn-secondary mt-3 w-full">Yazdır</button>

                @guest
                    <div class="mt-5 rounded-xl border border-violet-100 bg-violet-50/50 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">E-posta ile gönder</p>
                        <form id="paint-email-form" method="post" action="{{ $emailUrl }}" class="mt-3 space-y-2">
                            @csrf
                            <input type="email" name="email" required class="input-ui w-full text-sm" placeholder="ornek@eposta.com" value="{{ old('email') }}">
                            <select name="format" class="input-ui w-full text-sm">
                                @foreach($exportFormats as $fmt)
                                    <option value="{{ $fmt }}">{{ strtoupper($fmt === 'jpeg' ? 'jpg' : $fmt) }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-primary w-full text-sm">E-postaya gönder</button>
                        </form>
                    </div>
                @endguest
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        window.__ONLINE_PAINT__ = {
            lineArtUrl: @js($lineArtUrl),
            exportUrl: @js($exportUrl),
            emailUrl: @js($emailUrl),
            csrfToken: @js(csrf_token()),
            fileBase: @js(\Illuminate\Support\Str::slug($coloringPage->title) ?: 'boyama-'.$coloringPage->id),
        };
    </script>
    @vite(['resources/js/online-paint.js'])
@endpush
