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

        <div class="online-paint-workspace">
            <aside
                class="online-paint-toolbar"
                aria-label="Boyama araçları"
                x-data="{
                    openTools: true,
                    openColor: true,
                    openBrush: true,
                    openView: true,
                    openEdit: true
                }"
            >
                <div class="online-paint-toolbar__head">
                    <div>
                        <p class="online-paint-toolbar__title">Stüdyo</p>
                        <p class="online-paint-toolbar__sub">Online boya araçları</p>
                    </div>
                    <span class="online-paint-toolbar__badge">Pro</span>
                </div>

                {{-- Araçlar --}}
                <section class="online-paint-panel">
                    <button type="button" class="online-paint-panel__toggle" @click="openTools = !openTools">
                        <span>Araçlar</span>
                        <span class="online-paint-panel__chevron" :class="openTools && 'online-paint-panel__chevron--open'">›</span>
                    </button>
                    <div class="online-paint-panel__body" x-show="openTools" x-cloak>
                        <div class="online-paint-tools online-paint-tools--icons">
                            <button type="button" class="online-paint-tool online-paint-tool--icon" data-paint-tool="brush" title="Yumuşak fırça">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/></svg>
                                <span>Fırça</span>
                            </button>
                            <button type="button" class="online-paint-tool online-paint-tool--icon" data-paint-tool="pencil" title="İnce kalem">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20l4-1 9-9-3-3-9 9-1 4z"/><path d="M14 6l4 4"/></svg>
                                <span>Kalem</span>
                            </button>
                            <button type="button" class="online-paint-tool online-paint-tool--icon" data-paint-tool="marker" title="Keçeli">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 20h12"/><path d="M8 20l2-14 4 14"/></svg>
                                <span>Keçeli</span>
                            </button>
                            <button type="button" class="online-paint-tool online-paint-tool--icon" data-paint-tool="spray" title="Sprey">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="2"/><circle cx="14" cy="6" r="1.5"/><circle cx="11" cy="13" r="2"/></svg>
                                <span>Sprey</span>
                            </button>
                            <button type="button" class="online-paint-tool online-paint-tool--icon" data-paint-tool="fill" title="Doldur">
                                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.56 5.44l-1.45 1.45A5 5 0 1 0 14 10.9V12h2v-1.1a5 5 0 0 0 1.11-7.46zM7 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>
                                <span>Doldur</span>
                            </button>
                            <button type="button" class="online-paint-tool online-paint-tool--icon" data-paint-tool="eraser" title="Silgi">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 20H7L3 16l11-11 6 6-5 5"/></svg>
                                <span>Silgi</span>
                            </button>
                            <button type="button" class="online-paint-tool online-paint-tool--icon online-paint-tool--icon-wide" data-paint-tool="picker" title="Pipet">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l10-10 3 3L10 20l-3 3z"/></svg>
                                <span>Pipet</span>
                            </button>
                        </div>
                        <p class="online-paint-mini-label">Hazır fırça</p>
                        <div class="online-paint-preset-row">
                            <button type="button" class="online-paint-chip" data-paint-brush-preset="detail">İnce</button>
                            <button type="button" class="online-paint-chip" data-paint-brush-preset="normal">Normal</button>
                            <button type="button" class="online-paint-chip" data-paint-brush-preset="wide">Geniş</button>
                            <button type="button" class="online-paint-chip" data-paint-brush-preset="spraySoft">Sprey</button>
                        </div>
                    </div>
                </section>

                {{-- Renk --}}
                <section class="online-paint-panel">
                    <button type="button" class="online-paint-panel__toggle" @click="openColor = !openColor">
                        <span>Renk & palet</span>
                        <span class="online-paint-panel__chevron" :class="openColor && 'online-paint-panel__chevron--open'">›</span>
                    </button>
                    <div class="online-paint-panel__body" x-show="openColor" x-cloak>
                        <div class="online-paint-color-preview" id="paint-color-preview">
                            <span class="online-paint-color-preview__swatch" id="paint-color-preview-swatch"></span>
                            <div class="min-w-0 flex-1">
                                <span class="online-paint-color-preview__hex" id="paint-color-preview-hex">#EF4444</span>
                                <button type="button" id="paint-random-color" class="online-paint-link-btn">Rastgele renk</button>
                            </div>
                        </div>

                        <p class="online-paint-mini-label">Tema setleri</p>
                        <div class="online-paint-preset-row online-paint-preset-row--themes">
                            <button type="button" class="online-paint-chip" data-paint-theme="pastel">Pastel</button>
                            <button type="button" class="online-paint-chip" data-paint-theme="vivid">Canlı</button>
                            <button type="button" class="online-paint-chip" data-paint-theme="nature">Doğa</button>
                            <button type="button" class="online-paint-chip" data-paint-theme="skin">Ten</button>
                        </div>

                        <div id="paint-theme-strip" class="online-paint-theme-strip hidden"></div>

                        <p class="online-paint-mini-label mt-3">Ana palet</p>
                        <div class="online-paint-swatches">
                            @foreach ([
                                '#ef4444','#f97316','#f59e0b','#eab308','#84cc16','#22c55e',
                                '#10b981','#14b8a6','#06b6d4','#0ea5e9','#3b82f6','#6366f1',
                                '#8b5cf6','#a855f7','#d946ef','#ec4899','#f43f5e','#78716c',
                                '#44403c','#000000','#ffffff','#fca5a5','#fde047','#86efac',
                            ] as $hex)
                                <button type="button" class="online-paint-swatch" data-paint-color="{{ $hex }}" style="background-color: {{ $hex }}" title="{{ $hex }}"></button>
                            @endforeach
                        </div>

                        <label class="online-paint-field mt-3">
                            <span>Özel renk</span>
                            <input type="color" id="paint-color-custom" value="#ef4444" class="online-paint-color-input">
                        </label>

                        <p class="online-paint-mini-label mt-3">Son kullanılan</p>
                        <div id="paint-recent-colors" class="online-paint-recent">
                            <span class="online-paint-recent__empty">Henüz yok</span>
                        </div>
                    </div>
                </section>

                {{-- Fırça ayarları --}}
                <section class="online-paint-panel">
                    <button type="button" class="online-paint-panel__toggle" @click="openBrush = !openBrush">
                        <span>Fırça & doldur</span>
                        <span class="online-paint-panel__chevron" :class="openBrush && 'online-paint-panel__chevron--open'">›</span>
                    </button>
                    <div id="paint-brush-settings" class="online-paint-panel__body" x-show="openBrush" x-cloak>
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
                        <label class="online-paint-slider-row">
                            <span>Doldur hassasiyeti</span>
                            <input type="range" id="paint-fill-tolerance" min="8" max="72" value="40" class="online-paint-range flex-1">
                            <strong id="paint-fill-tolerance-label">40</strong>
                        </label>
                    </div>
                </section>

                {{-- Görünüm --}}
                <section class="online-paint-panel">
                    <button type="button" class="online-paint-panel__toggle" @click="openView = !openView">
                        <span>Görünüm</span>
                        <span class="online-paint-panel__chevron" :class="openView && 'online-paint-panel__chevron--open'">›</span>
                    </button>
                    <div class="online-paint-panel__body" x-show="openView" x-cloak>
                        <div class="online-paint-zoom-row">
                            <button type="button" id="paint-zoom-out" class="online-paint-icon-btn" title="Uzaklaştır">−</button>
                            <span id="paint-zoom-label" class="online-paint-zoom-label">100%</span>
                            <button type="button" id="paint-zoom-in" class="online-paint-icon-btn" title="Yakınlaştır">+</button>
                        </div>
                        <button type="button" id="paint-zoom-fit" class="online-paint-action-btn mt-2 w-full">Tuvali sığdır</button>

                        <p class="online-paint-mini-label mt-3">Tuval döndür</p>
                        <div class="online-paint-preset-row">
                            <button type="button" id="paint-rotate-left" class="online-paint-chip" title="90° sola">↺ 90°</button>
                            <button type="button" id="paint-rotate-right" class="online-paint-chip" title="90° sağa">↻ 90°</button>
                            <button type="button" id="paint-rotate-reset" class="online-paint-chip">0°</button>
                        </div>
                        <p class="text-center text-[11px] font-semibold text-violet-700"><span id="paint-rotate-label">0°</span></p>

                        <label class="online-paint-toggle-row mt-3">
                            <input type="checkbox" id="paint-orbit-toggle" checked class="rounded border-violet-300 text-violet-600">
                            <span>Tuval etrafında dönen halka</span>
                        </label>
                        <label class="online-paint-toggle-row">
                            <input type="checkbox" id="paint-glow-toggle" checked class="rounded border-violet-300 text-violet-600">
                            <span>Işıltılı çerçeve animasyonu</span>
                        </label>
                        <button type="button" id="paint-fullscreen" class="online-paint-action-btn mt-2 w-full">Tam ekran tuval</button>
                    </div>
                </section>

                {{-- Düzenle --}}
                <section class="online-paint-panel">
                    <button type="button" class="online-paint-panel__toggle" @click="openEdit = !openEdit">
                        <span>Düzenle</span>
                        <span class="online-paint-panel__chevron" :class="openEdit && 'online-paint-panel__chevron--open'">›</span>
                    </button>
                    <div class="online-paint-panel__body" x-show="openEdit" x-cloak>
                        <div class="online-paint-actions">
                            <button type="button" id="paint-undo" class="online-paint-action-btn">↶ Geri al</button>
                            <button type="button" id="paint-redo" class="online-paint-action-btn">↷ İleri al</button>
                            <button type="button" id="paint-clear" class="online-paint-action-btn online-paint-action-btn--warn col-span-2">Tüm boyayı temizle</button>
                        </div>
                    </div>
                </section>

                <p class="online-paint-hint">Kısayol: Doldur kapalı alanlar · Sprey gölge · Pipet renk alır</p>
            </aside>

            <div class="online-paint-stage-wrap">
                <div id="canvas-wrap" class="online-paint-canvas-wrap">
                    <div id="paint-loader" class="online-paint-loader">
                        <span class="online-paint-spinner"></span>
                        <p>Çizim yükleniyor…</p>
                    </div>
                    <p id="paint-error" class="online-paint-error hidden"></p>
                    <div id="canvas-orbit-zone" class="online-paint-orbit-zone online-paint-orbit-zone--active">
                        <div id="paint-orbit-glow" class="online-paint-orbit-glow online-paint-orbit-glow--on" aria-hidden="true"></div>
                        <div id="paint-orbit-ring" class="online-paint-orbit-ring online-paint-orbit-ring--on" aria-hidden="true">
                            @for ($i = 0; $i < 8; $i++)
                                <span class="online-paint-orbit-dot" style="--orbit-i: {{ $i }}"></span>
                            @endfor
                        </div>
                        <div id="canvas-scaler" class="online-paint-canvas-scaler">
                            <div id="canvas-stage" class="online-paint-canvas-stage">
                                <canvas id="paint-canvas" class="online-paint-canvas online-paint-canvas--paint"></canvas>
                                <canvas id="line-canvas" class="online-paint-canvas online-paint-canvas--lines"></canvas>
                                <div id="paint-hit-layer" class="online-paint-hit-layer" aria-hidden="true"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="online-paint-export" aria-label="Dışa aktar">
                <div class="online-paint-toolbar__head">
                    <div>
                        <p class="online-paint-toolbar__title">Sonuç</p>
                        <p class="online-paint-toolbar__sub">Boyadıktan sonra paylaşın</p>
                    </div>
                </div>

                <p class="text-xs leading-relaxed text-slate-500">İndirin, yazdırın veya boyanmış çalışmayı e-posta ile gönderin. Sunucuya kayıt yapılmaz.</p>

                @if(session('paint_email_sent'))
                    <div class="online-paint-alert online-paint-alert--success mt-3">
                        Boyanmış çalışmanız belirttiğiniz e-posta adresine gönderildi.
                    </div>
                @endif

                <section class="online-paint-export-panel mt-4">
                    <p class="online-paint-section-label">İndir</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" class="online-paint-export-btn" data-paint-download="png">PNG</button>
                        <button type="button" class="online-paint-export-btn" data-paint-download="jpg">JPG</button>
                        <button type="button" class="online-paint-export-btn col-span-2" data-paint-download="pdf">PDF</button>
                    </div>
                    <button type="button" id="paint-print" class="btn-secondary mt-3 w-full">Yazdır</button>
                </section>

                <section class="online-paint-export-panel online-paint-export-panel--email mt-4">
                    <p class="online-paint-section-label">E-posta ile gönder</p>
                    <p class="text-[11px] leading-relaxed text-slate-500">Boyama bittikten sonra güncel tuval görüntüsü ek olarak iletilir.</p>

                    <form id="paint-email-form" method="post" action="{{ $emailUrl }}" class="mt-3 space-y-3">
                        @csrf
                        <div>
                            <label for="paint-email-to" class="online-paint-field-label">Alıcı e-posta</label>
                            <input
                                id="paint-email-to"
                                type="email"
                                name="email"
                                required
                                autocomplete="email"
                                class="input-ui w-full text-sm"
                                placeholder="ornek@eposta.com"
                                value="{{ old('email') }}"
                            >
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="paint-email-format" class="online-paint-field-label">Ek formatı</label>
                            <select id="paint-email-format" name="format" class="input-ui w-full text-sm">
                                @foreach($exportFormats as $fmt)
                                    <option value="{{ $fmt }}" @selected(old('format', 'png') === $fmt)>
                                        {{ strtoupper($fmt === 'jpeg' ? 'jpg' : $fmt) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('format')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @error('email_send')
                            <p class="rounded-lg bg-red-50 px-3 py-2 text-xs text-red-700">{{ $message }}</p>
                        @enderror
                        <button type="submit" class="btn-primary w-full text-sm" id="paint-email-submit">
                            Boyanmış çalışmayı gönder
                        </button>
                    </form>
                </section>
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
