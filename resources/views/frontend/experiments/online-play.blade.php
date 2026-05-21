@extends('layouts.app')

@section('title', 'Online Deney — '.$experiment->title)

@section('content')
    <div class="online-exp-page mx-auto max-w-[1400px]">
        <header class="online-exp-topbar">
            <div class="min-w-0">
                <a href="{{ route('experiments.online.hub') }}" class="online-exp-back">← Laboratuvar</a>
                <h1 class="online-exp-title">{{ $experiment->title }}</h1>
                <p class="online-exp-sub">{{ $labTypeLabel }} — adımları tamamla, deneyi başlat</p>
            </div>
            <a href="{{ route('experiments.show', $experiment) }}" class="online-exp-link-article">Deney yazısı</a>
        </header>

        <div
            id="online-exp-app"
            class="online-exp-workspace"
            data-lab-type="{{ $labType }}"
            data-article-url="{{ route('experiments.show', $experiment) }}"
        >
            <aside class="online-exp-guide" aria-label="Deney rehberi">
                <p class="online-exp-panel-title">Rehber</p>
                <p class="online-exp-step-badge" id="exp-step-badge">Adım 1 / 4</p>
                <h2 class="online-exp-step-title" id="exp-step-title">Hoş geldin!</h2>
                <p class="online-exp-step-text" id="exp-step-text">Renklerin bardaklar arasında nasıl «yürüdüğünü» bilgisayarda göreceksin.</p>
                <ul class="online-exp-checklist" id="exp-checklist" hidden></ul>
                <div class="online-exp-guide-actions">
                    <button type="button" class="btn-secondary w-full text-sm" id="exp-btn-prev" disabled>Önceki</button>
                    <button type="button" class="btn-primary w-full text-sm" id="exp-btn-next">Sonraki</button>
                </div>
            </aside>

            <main class="online-exp-stage" aria-label="Deney sahnesi">
                <div class="online-exp-stage__inner" id="exp-stage-inner">
                    <div class="online-exp-palette" id="exp-palette" hidden>
                        <p class="online-exp-palette-label">Renk seç, sonra bardaklara tıkla (1 · 3 · 5 · 7):</p>
                        <div class="online-exp-palette-colors" id="exp-palette-colors"></div>
                    </div>
                    <div class="online-exp-cups-row" id="exp-cups-row"></div>
                    <div class="online-exp-bridges" id="exp-bridges" hidden></div>
                    <p class="online-exp-stage-hint" id="exp-stage-hint"></p>
                    <button type="button" class="btn-primary online-exp-start-btn" id="exp-btn-start" hidden>Deneyi başlat ✨</button>
                </div>
            </main>

            <aside class="online-exp-side" aria-label="İpucu ve sonuç">
                <p class="online-exp-panel-title">İpucu & sonuç</p>
                <div class="online-exp-side-body" id="exp-side-body">
                    <p class="text-sm text-slate-600">Adımları soldan takip et. Gerçek deney için yazıdaki malzemeleri kullan.</p>
                </div>
                <div class="online-exp-side-actions" id="exp-side-actions" hidden>
                    <button type="button" class="btn-secondary w-full text-sm" id="exp-btn-retry">Yeniden dene</button>
                    <button type="button" class="btn-primary w-full text-sm" id="exp-btn-screenshot">Sonucu indir (PNG)</button>
                    <a href="{{ route('experiments.show', $experiment) }}" class="btn-secondary w-full text-center text-sm">Deney yazısına git</a>
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/online-experiment.js'])
@endpush
