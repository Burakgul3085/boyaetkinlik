@php
    use App\Support\OnlineExperimentLab;
@endphp
@extends('layouts.app')

@section('title', 'Online Deney — '.$lab['title'])

@section('content')
    <div class="online-exp-page mx-auto max-w-[1400px]">
        <header class="online-exp-topbar">
            <div class="min-w-0">
                <a href="{{ route('experiments.online.hub') }}" class="online-exp-back">← Laboratuvar</a>
                <h1 class="online-exp-title">{{ $lab['title'] }}</h1>
                <p class="online-exp-sub">
                    {{ $labTypeLabel }}
                    · Adım adım rehber
                    · {{ OnlineExperimentLab::modeLabelForType($labType) }}
                </p>
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
                <p class="online-exp-step-badge" id="exp-step-badge">Adım 1</p>
                <h2 class="online-exp-step-title" id="exp-step-title">Başla</h2>
                <p class="online-exp-step-text" id="exp-step-text"></p>
                <ul class="online-exp-checklist" id="exp-checklist"></ul>
                <div class="online-exp-guide-actions">
                    <button type="button" class="btn-secondary w-full text-sm" id="exp-btn-prev" disabled>Önceki adım</button>
                    <button type="button" class="btn-primary w-full text-sm" id="exp-btn-next">Sonraki adım</button>
                </div>
            </aside>

            @include(OnlineExperimentLab::stagePartialForType($labType))

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
    @vite([OnlineExperimentLab::viteScriptForType($labType)])
@endpush
