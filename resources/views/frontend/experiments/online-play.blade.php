@extends('layouts.app')

@section('title', 'Online Deney — '.$lab['title'])

@section('content')
    <div class="online-exp-page mx-auto max-w-[1400px]">
        <header class="online-exp-topbar">
            <div class="min-w-0">
                <a href="{{ route('experiments.online.hub') }}" class="online-exp-back">← Laboratuvar</a>
                <h1 class="online-exp-title">{{ $lab['title'] }}</h1>
                <p class="online-exp-sub">Adım adım rehber · 3D animasyonlu model · Gerçek deneyde aynı mantık</p>
            </div>
            @if($articleUrl)
                <a href="{{ $articleUrl }}" class="online-exp-link-article">Deney yazısı</a>
            @endif
        </header>

        <div
            id="online-exp-app"
            class="online-exp-workspace"
            data-lab-type="{{ $labType }}"
            data-article-url="{{ $articleUrl ?? $lab['slug'] }}"
        >
            <aside class="online-exp-guide" aria-label="Deney rehberi">
                <p class="online-exp-panel-title">Rehber</p>
                <div class="online-exp-progress" aria-hidden="true">
                    <div class="online-exp-progress__bar" id="exp-progress-bar"></div>
                </div>
                <p class="online-exp-step-badge" id="exp-step-badge">Adım 1 / 6</p>
                <h2 class="online-exp-step-title" id="exp-step-title">Bu deney ne?</h2>
                <p class="online-exp-step-text" id="exp-step-text"></p>
                <ul class="online-exp-checklist" id="exp-checklist"></ul>
                <div class="online-exp-guide-actions">
                    <button type="button" class="btn-secondary w-full text-sm" id="exp-btn-prev" disabled>Önceki adım</button>
                    <button type="button" class="btn-primary w-full text-sm" id="exp-btn-next">Sonraki adım</button>
                </div>
            </aside>

            <main class="online-exp-stage" aria-label="3D deney sahnesi">
                <p class="online-exp-stage-label">3D deney masası — tıklayarak kur, sonra animasyonu izle</p>
                <div class="online-exp-stage__inner" id="exp-stage-inner">
                    <div class="online-exp-palette" id="exp-palette" hidden>
                        <p class="online-exp-palette-label">① Paletten veya özel renkten seç · ② Parlayan 1, 3, 5, 7 bardaklara tıkla (her bardak farklı renk olabilir)</p>
                        <div class="online-exp-palette-colors" id="exp-palette-colors"></div>
                        <label class="online-exp-custom-color">
                            <span>Özel renk</span>
                            <input type="color" id="exp-color-picker" value="#e11d48" title="İstediğin rengi seç">
                        </label>
                    </div>

                    <div class="online-exp-3d-arena" id="exp-3d-arena">
                        <div class="online-exp-3d-arena__glow" aria-hidden="true"></div>
                        <div class="online-exp-3d-world" id="exp-3d-world">
                            <div class="online-exp-3d-floor" aria-hidden="true"></div>
                            <div class="online-exp-3d-row" id="exp-cups-row"></div>
                        </div>
                        <div class="online-exp-flow-layer" id="exp-flow-layer" aria-hidden="true"></div>
                    </div>

                    <p class="online-exp-stage-hint" id="exp-stage-hint"></p>
                    <button type="button" class="btn-primary online-exp-start-btn" id="exp-btn-start" hidden>
                        Deneyi başlat — renkler yürüsün ✨
                    </button>
                </div>
            </main>

            <aside class="online-exp-side" aria-label="Bilgi ve sonuç">
                <p class="online-exp-panel-title">Bilgi kutusu</p>
                <div class="online-exp-side-body" id="exp-side-body"></div>
                <div class="online-exp-side-actions" id="exp-side-actions" hidden>
                    <button type="button" class="btn-secondary w-full text-sm" id="exp-btn-retry">Baştan yap</button>
                    <button type="button" class="btn-primary w-full text-sm" id="exp-btn-screenshot">Sonucu indir (PNG)</button>
                    @if($articleUrl)
                        <a href="{{ $articleUrl }}" class="btn-secondary w-full text-center text-sm">Deney yazısına git</a>
                    @else
                        <a href="{{ route('experiments.index') }}" class="btn-secondary w-full text-center text-sm">Deney yazılarına git</a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/online-experiment.js'])
@endpush
