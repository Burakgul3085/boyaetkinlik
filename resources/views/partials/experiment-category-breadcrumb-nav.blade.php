@php
    $wrapperClass = $wrapperClass ?? 'flex flex-wrap items-center gap-x-0.5 gap-y-1 text-sm text-slate-600';
@endphp
<nav class="{{ $wrapperClass }}" aria-label="Deney konumu">
    <a href="{{ route('experiments.index') }}" class="rounded px-0.5 transition hover:text-violet-700">Deneyler</a>
    @foreach ($breadcrumbItems as $crumb)
        <span class="select-none px-0.5 font-normal text-violet-300" aria-hidden="true">›</span>
        @if (! empty($crumb['url']))
            <a href="{{ $crumb['url'] }}" class="min-w-0 rounded px-0.5 transition hover:text-violet-700">{{ $crumb['label'] }}</a>
        @else
            <span class="min-w-0 break-words font-semibold text-slate-900">{{ $crumb['label'] }}</span>
        @endif
    @endforeach
</nav>
