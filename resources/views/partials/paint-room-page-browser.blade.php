@php
    $browserId = $browserId ?? 'paint-room-page-browser';
    $compact = ! empty($compact);
    $selectedPageId = $selectedPageId ?? '';
    $categoryTree = $categoryTree ?? [];
    $freePagesUrl = $freePagesUrl ?? '';
@endphp
<div
    id="{{ $browserId }}"
    class="paint-room-page-browser{{ $compact ? ' paint-room-page-browser--compact' : '' }}"
    data-paint-room-page-browser
    data-category-tree='@json($categoryTree)'
    data-pages-url="{{ $freePagesUrl }}"
    data-selected-page-id="{{ $selectedPageId }}"
    data-compact="{{ $compact ? '1' : '0' }}"
>
    <nav class="paint-room-page-browser__breadcrumb" data-browser-breadcrumb aria-label="Kategori yolu"></nav>
    <div class="paint-room-page-browser__categories" data-browser-categories></div>
    <div class="paint-room-page-browser__pages-wrap" data-browser-pages></div>
    <p class="paint-room-page-browser__status" data-browser-status>Kategori seçerek başlayın.</p>
</div>
