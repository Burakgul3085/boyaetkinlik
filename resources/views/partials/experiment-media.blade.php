@props([
    'experiment',
    'variant' => 'card',
])

@php
    $hasImage = (bool) $experiment->image_path;
    $hasVideo = (bool) $experiment->youtubeEmbedUrl();
@endphp

@if($hasImage)
    <figure class="exp-media-stage exp-media-stage--{{ $variant }}">
        <div class="exp-media-stage__backdrop" aria-hidden="true"></div>
        <div class="exp-media-stage__frame">
            <img
                src="{{ asset('storage/'.$experiment->image_path) }}"
                alt="{{ $experiment->title }} görseli"
                class="exp-media-img"
                draggable="false"
                loading="lazy"
                decoding="async"
            >
        </div>
        @if($hasVideo && $variant === 'card')
            <span class="exp-media-badge exp-media-badge--video" aria-hidden="true">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                Video
            </span>
        @endif
    </figure>
@elseif($hasVideo && $variant === 'card')
    <figure class="exp-media-stage exp-media-stage--card exp-media-stage--video-only">
        <div class="exp-media-stage__backdrop exp-media-stage__backdrop--dark" aria-hidden="true"></div>
        <img
            src="{{ $experiment->youtubeThumbnailUrl() }}"
            alt="{{ $experiment->title }} video önizleme"
            class="exp-media-img exp-media-img--cover"
            loading="lazy"
        >
        <span class="exp-media-play" aria-hidden="true">
            <span class="exp-media-play__icon">▶</span>
        </span>
    </figure>
@endif
