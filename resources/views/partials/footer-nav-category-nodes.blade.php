{{-- Footer hızlı linkler: kategori alt ağacı --}}
@php
    $depth = $depth ?? 0;
    $plClasses = ['pl-3', 'pl-5', 'pl-7', 'pl-9', 'pl-11', 'pl-[3.25rem]'];
    $pl = $plClasses[min($depth, count($plClasses) - 1)];
@endphp
@foreach ($nodes as $node)
    <a
        href="{{ $node['url'] }}"
        class="{{ $pl }} mt-1 block rounded-lg bg-white/10 py-2 pr-3 text-slate-100 transition hover:bg-white/20 hover:text-white"
    >
        @if ($depth > 0)
            <span class="text-slate-400">— </span>
        @endif
        {{ $node['label'] }}
    </a>
    @if (! empty($node['children']))
        @include('partials.footer-nav-category-nodes', ['nodes' => $node['children'], 'depth' => ($depth ?? 0) + 1])
    @endif
@endforeach
