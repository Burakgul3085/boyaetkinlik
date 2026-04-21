{{-- Kategori hiyerarşisi: Anasayfa › … › güncel sayfa (tüm atalar $breadcrumbItems ile) --}}
@php
    $wrapperClass = $wrapperClass ?? 'flex flex-wrap items-center gap-x-0.5 gap-y-1 text-sm text-slate-600 dark:text-slate-400';
@endphp
<nav class="{{ $wrapperClass }}" aria-label="Sayfa konumu">
    <a href="{{ route('home') }}" class="rounded px-0.5 transition hover:text-violet-700 dark:hover:text-violet-300">Anasayfa</a>
    @foreach ($breadcrumbItems as $crumb)
        <span class="select-none px-0.5 font-normal text-violet-300 dark:text-violet-600" aria-hidden="true">›</span>
        @if (! empty($crumb['url']))
            <a href="{{ $crumb['url'] }}" class="min-w-0 rounded px-0.5 transition hover:text-violet-700 dark:hover:text-violet-300">{{ $crumb['label'] }}</a>
        @else
            <span class="min-w-0 break-words font-semibold text-slate-900 dark:text-slate-100">{{ $crumb['label'] }}</span>
        @endif
    @endforeach
</nav>
