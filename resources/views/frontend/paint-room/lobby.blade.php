@extends('layouts.app')

@section('title', 'Boyama Odası')

@push('scripts')
    @vite('resources/js/paint-room.js')
@endpush

@section('content')
<section
    class="paint-room-studio"
    id="paint-room-lobby"
    data-status-url="{{ route('paint-room.status', $room) }}"
    data-signal-poll-url="{{ route('paint-room.signals.poll.post', $room) }}"
    data-signal-send-url="{{ route('paint-room.signals.send', $room) }}"
    data-leave-url="{{ route('paint-room.leave', $room) }}"
    data-index-url="{{ route('paint-room.index') }}"
    data-role="{{ $role }}"
    data-expires-at="{{ $expiresAtIso }}"
    data-csrf="{{ csrf_token() }}"
    data-guest-token="{{ $guestAccessToken }}"
    data-ice-servers='@json($iceServers)'
    data-health-url="{{ route('paint-room.signals.health', $room) }}"
    data-line-art-url="{{ $lineArtUrl }}"
    data-canvas-load-url="{{ route('paint-room.canvas.load', $room) }}"
    data-canvas-save-url="{{ route('paint-room.canvas.save', $room) }}"
>
    <header class="paint-room-studio__topbar">
        <div class="min-w-0 flex-1">
            <p class="text-[10px] font-bold uppercase tracking-widest text-violet-600">Görüntülü boyama</p>
            <h1 class="truncate text-base font-bold text-slate-900 md:text-lg">
                @if($coloringPageTitle)
                    {{ $coloringPageTitle }}
                @elseif($role === 'owner')
                    Odanız hazır
                @else
                    Beraber boyama
                @endif
            </h1>
            <p class="text-xs text-slate-500" id="paint-room-status-text">
                @if($room->hasGuest())
                    Görüntülü bağlantı hazırlanıyor…
                @else
                    Misafir bekleniyor…
                @endif
            </p>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <div class="paint-room-occupancy" id="paint-room-occupancy">
                <span class="paint-room-occupancy__count" id="paint-room-count">{{ $room->participantCount() }}</span>
                <span class="paint-room-occupancy__label">/ 2</span>
            </div>
            <div class="paint-room-pill" id="paint-room-timer" aria-live="polite">30:00</div>
            @if($role === 'owner')
                <button type="button" id="paint-room-info-toggle" class="paint-room-studio__icon-btn" title="PIN ve davet">🔗</button>
            @endif
            <form method="post" action="{{ route('paint-room.leave', $room) }}" class="inline">
                @csrf
                <button type="submit" class="paint-room-studio__leave-btn">
                    {{ $role === 'owner' ? 'Kapat' : 'Ayrıl' }}
                </button>
            </form>
        </div>
    </header>

    @if(session('success'))
        <div class="paint-room-studio__toast">{{ session('success') }}</div>
    @endif

    <div id="room-paint-studio" class="online-paint-workspace paint-room-workspace">
        <aside
            class="online-paint-toolbar paint-room-toolbar"
            aria-label="Boyama araçları"
            x-data="{ openTools: true, openColor: true, openBrush: true, openView: true, openEdit: true }"
        >
            <div class="online-paint-toolbar__head">
                <div>
                    <p class="online-paint-toolbar__title">Stüdyo</p>
                    <p class="online-paint-toolbar__sub">Beraber boyama</p>
                </div>
                <span class="online-paint-toolbar__badge">Canlı</span>
            </div>

            <section class="online-paint-panel">
                <button type="button" class="online-paint-panel__toggle" @click="openTools = !openTools">
                    <span>Araçlar</span>
                    <span class="online-paint-panel__chevron" :class="openTools && 'online-paint-panel__chevron--open'">›</span>
                </button>
                <div class="online-paint-panel__body" x-show="openTools" x-cloak>
                    <div class="online-paint-tools online-paint-tools--icons">
                        <button type="button" class="online-paint-tool online-paint-tool--icon" data-room-tool="brush" title="Fırça">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/></svg>
                            <span>Fırça</span>
                        </button>
                        <button type="button" class="online-paint-tool online-paint-tool--icon" data-room-tool="pencil" title="Kalem">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20l4-1 9-9-3-3-9 9-1 4z"/></svg>
                            <span>Kalem</span>
                        </button>
                        <button type="button" class="online-paint-tool online-paint-tool--icon" data-room-tool="marker" title="Keçeli">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 20h12"/><path d="M8 20l2-14 4 14"/></svg>
                            <span>Keçeli</span>
                        </button>
                        <button type="button" class="online-paint-tool online-paint-tool--icon" data-room-tool="spray" title="Sprey">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="2"/><circle cx="14" cy="6" r="1.5"/></svg>
                            <span>Sprey</span>
                        </button>
                        <button type="button" class="online-paint-tool online-paint-tool--icon" data-room-tool="fill" title="Doldur">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.56 5.44l-1.45 1.45A5 5 0 1 0 14 10.9V12h2v-1.1a5 5 0 0 0 1.11-7.46zM7 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/></svg>
                            <span>Doldur</span>
                        </button>
                        <button type="button" class="online-paint-tool online-paint-tool--icon" data-room-tool="eraser" title="Silgi">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 20H7L3 16l11-11 6 6-5 5"/></svg>
                            <span>Silgi</span>
                        </button>
                        <button type="button" class="online-paint-tool online-paint-tool--icon online-paint-tool--icon-wide" data-room-tool="picker" title="Pipet">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17l10-10 3 3L10 20l-3 3z"/></svg>
                            <span>Pipet</span>
                        </button>
                    </div>
                    <p class="online-paint-mini-label">Hazır fırça</p>
                    <div class="online-paint-preset-row">
                        <button type="button" class="online-paint-chip" data-room-brush-preset="detail">İnce</button>
                        <button type="button" class="online-paint-chip online-paint-chip--active" data-room-brush-preset="normal">Normal</button>
                        <button type="button" class="online-paint-chip" data-room-brush-preset="wide">Geniş</button>
                        <button type="button" class="online-paint-chip" data-room-brush-preset="spraySoft">Sprey</button>
                    </div>
                </div>
            </section>

            <section class="online-paint-panel">
                <button type="button" class="online-paint-panel__toggle" @click="openColor = !openColor">
                    <span>Renk</span>
                    <span class="online-paint-panel__chevron" :class="openColor && 'online-paint-panel__chevron--open'">›</span>
                </button>
                <div class="online-paint-panel__body" x-show="openColor" x-cloak>
                    <div class="online-paint-color-preview">
                        <span class="online-paint-color-preview__swatch" id="room-paint-color-preview-swatch"></span>
                        <div class="min-w-0 flex-1">
                            <span class="online-paint-color-preview__hex" id="room-paint-color-preview-hex">#EF4444</span>
                            <button type="button" id="room-random-color" class="online-paint-link-btn">Rastgele</button>
                        </div>
                    </div>
                    <p class="online-paint-mini-label">Tema</p>
                    <div class="online-paint-preset-row online-paint-preset-row--themes">
                        <button type="button" class="online-paint-chip" data-room-theme="pastel">Pastel</button>
                        <button type="button" class="online-paint-chip" data-room-theme="vivid">Canlı</button>
                        <button type="button" class="online-paint-chip" data-room-theme="nature">Doğa</button>
                        <button type="button" class="online-paint-chip" data-room-theme="skin">Ten</button>
                    </div>
                    <div id="room-theme-strip" class="online-paint-theme-strip hidden"></div>
                    <p class="online-paint-mini-label mt-3">Palet</p>
                    <div class="online-paint-swatches">
                        @foreach (['#ef4444','#f97316','#eab308','#22c55e','#06b6d4','#3b82f6','#8b5cf6','#ec4899','#1e293b','#ffffff','#fca5a5','#86efac'] as $hex)
                            <button type="button" class="online-paint-swatch {{ $loop->first ? 'online-paint-swatch--active' : '' }}" data-room-color="{{ $hex }}" style="background-color: {{ $hex }}"></button>
                        @endforeach
                    </div>
                    <label class="online-paint-field mt-3">
                        <span>Özel renk</span>
                        <input type="color" id="room-paint-color-custom" value="#ef4444" class="online-paint-color-input">
                    </label>
                    <p class="online-paint-mini-label mt-3">Son kullanılan</p>
                    <div id="room-recent-colors" class="online-paint-recent">
                        <span class="online-paint-recent__empty">Henüz yok</span>
                    </div>
                </div>
            </section>

            <section class="online-paint-panel">
                <button type="button" class="online-paint-panel__toggle" @click="openBrush = !openBrush">
                    <span>Fırça & doldur</span>
                    <span class="online-paint-panel__chevron" :class="openBrush && 'online-paint-panel__chevron--open'">›</span>
                </button>
                <div class="online-paint-panel__body" x-show="openBrush" x-cloak>
                    <label class="online-paint-slider-row">
                        <span>Kalınlık</span>
                        <input type="range" id="room-paint-size" min="2" max="80" value="18" class="online-paint-range flex-1">
                        <strong id="room-paint-size-label">18</strong>
                    </label>
                    <label class="online-paint-slider-row">
                        <span>Opaklık</span>
                        <input type="range" id="room-paint-opacity" min="5" max="100" value="100" class="online-paint-range flex-1">
                        <strong id="room-paint-opacity-label">100%</strong>
                    </label>
                    <label class="online-paint-slider-row">
                        <span>Yumuşaklık</span>
                        <input type="range" id="room-paint-softness" min="0" max="100" value="35" class="online-paint-range flex-1">
                        <strong id="room-paint-softness-label">35%</strong>
                    </label>
                    <label class="online-paint-slider-row">
                        <span>Doldur hassasiyeti</span>
                        <input type="range" id="room-paint-fill-tolerance" min="8" max="72" value="40" class="online-paint-range flex-1">
                        <strong id="room-paint-fill-tolerance-label">40</strong>
                    </label>
                </div>
            </section>

            <section class="online-paint-panel">
                <button type="button" class="online-paint-panel__toggle" @click="openView = !openView">
                    <span>Görünüm</span>
                    <span class="online-paint-panel__chevron" :class="openView && 'online-paint-panel__chevron--open'">›</span>
                </button>
                <div class="online-paint-panel__body" x-show="openView" x-cloak>
                    <div class="online-paint-zoom-row">
                        <button type="button" id="room-paint-zoom-out" class="online-paint-icon-btn">−</button>
                        <span id="room-paint-zoom-label" class="online-paint-zoom-label">100%</span>
                        <button type="button" id="room-paint-zoom-in" class="online-paint-icon-btn">+</button>
                    </div>
                    <button type="button" id="room-paint-zoom-fit" class="online-paint-action-btn mt-2 w-full">Tuvali sığdır</button>
                </div>
            </section>

            <section class="online-paint-panel">
                <button type="button" class="online-paint-panel__toggle" @click="openEdit = !openEdit">
                    <span>Düzenle</span>
                    <span class="online-paint-panel__chevron" :class="openEdit && 'online-paint-panel__chevron--open'">›</span>
                </button>
                <div class="online-paint-panel__body" x-show="openEdit" x-cloak>
                    <div class="online-paint-actions">
                        <button type="button" id="room-paint-undo" class="online-paint-action-btn">↶ Geri al</button>
                        <button type="button" id="room-paint-redo" class="online-paint-action-btn">↷ İleri al</button>
                        <button type="button" id="room-paint-clear" class="online-paint-action-btn online-paint-action-btn--warn col-span-2">Tuvali temizle</button>
                    </div>
                </div>
            </section>

            <p class="online-paint-hint">İkiniz de aynı anda boyayabilirsiniz</p>
        </aside>

        <div class="online-paint-stage-wrap paint-room-stage-wrap">
            <div id="room-canvas-wrap" class="online-paint-canvas-wrap paint-room-canvas-wrap">
                <div id="room-paint-loader" class="online-paint-loader paint-room-canvas-loader">
                    <span class="online-paint-spinner"></span>
                    <p>Boyama yükleniyor…</p>
                </div>
                <p id="room-paint-error" class="online-paint-error hidden"></p>
                <div id="room-canvas-scaler" class="online-paint-canvas-scaler">
                    <div id="room-paint-stage" class="online-paint-canvas-stage paint-room-canvas-stage">
                        <canvas id="room-paint-canvas" class="online-paint-canvas online-paint-canvas--paint"></canvas>
                        <canvas id="room-line-canvas" class="online-paint-canvas online-paint-canvas--lines"></canvas>
                        <div id="room-paint-hit" class="online-paint-hit-layer" aria-hidden="true"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Yüzen görüntülü sohbet (PiP) --}}
    <div id="paint-room-pip" class="paint-room-pip">
        <div class="paint-room-pip__head">
            <span class="paint-room-pip__title">Görüntülü</span>
            <p class="paint-room-pip__status" id="paint-room-webrtc-status">Bağlanıyor…</p>
            <button type="button" id="paint-room-pip-expand" class="paint-room-pip__collapse" title="Büyüt/küçült">⤢</button>
            <button type="button" id="paint-room-pip-toggle" class="paint-room-pip__collapse" title="Gizle/göster">−</button>
        </div>
        <p class="px-2 py-1 font-mono text-[9px] leading-relaxed text-slate-400" id="paint-room-debug"></p>
        <div class="paint-room-pip__videos">
            <div class="paint-room-pip__tile">
                <video id="paint-room-local" class="paint-room-pip__video" autoplay playsinline muted></video>
                <span class="paint-room-pip__label">Siz</span>
            </div>
            <div class="paint-room-pip__tile">
                <video id="paint-room-remote" class="paint-room-pip__video" autoplay playsinline muted></video>
                <audio id="paint-room-remote-audio" class="sr-only" autoplay playsinline></audio>
                <span class="paint-room-pip__label" id="paint-room-remote-label">Karşı taraf</span>
            </div>
        </div>
        <div class="paint-room-pip__actions">
            <button type="button" id="paint-room-unlock-audio" class="paint-room-pip__btn hidden">🔊 Sesi aç</button>
            <button type="button" id="paint-room-toggle-mic" class="paint-room-pip__btn hidden">🎤</button>
            <button type="button" id="paint-room-toggle-cam" class="paint-room-pip__btn hidden">📷</button>
        </div>
    </div>

    @if($role === 'owner')
        <div id="paint-room-info-panel" class="paint-room-info-panel hidden">
            <div class="paint-room-info-panel__inner">
                <button type="button" id="paint-room-info-close" class="paint-room-info-panel__close">×</button>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-violet-100 bg-violet-50/80 p-4">
                        <p class="text-xs font-bold uppercase text-slate-500">PIN</p>
                        <p class="mt-2 font-mono text-2xl font-bold tracking-[0.3em] text-violet-800" id="paint-room-pin">{{ $pin }}</p>
                        <button type="button" class="btn-secondary mt-3 text-xs" data-copy-target="paint-room-pin">PIN kopyala</button>
                    </div>
                    <div class="rounded-2xl border border-teal-100 bg-teal-50/80 p-4">
                        <p class="text-xs font-bold uppercase text-slate-500">Davet linki</p>
                        <input type="text" readonly value="{{ $inviteUrl }}" id="paint-room-invite-url" class="input-ui mt-2 text-xs">
                        <button type="button" class="btn-secondary mt-3 text-xs" data-copy-target="paint-room-invite-url">Link kopyala</button>
                        <p class="mt-2 text-xs text-slate-500">Oda açık kaldığı sürece misafir bu linki tekrar kullanabilir.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
@endsection
