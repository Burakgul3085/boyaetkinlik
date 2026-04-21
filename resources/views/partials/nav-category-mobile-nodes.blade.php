{{-- Çok seviyeli kategori: mobil yan menü --}}
<div class="ml-2 mt-1 space-y-0.5 border-l-2 border-violet-100 pl-3 dark:border-slate-600">
    @foreach ($nodes as $node)
        <div>
            <a
                href="{{ $node['url'] }}"
                class="block rounded-lg py-2 pl-1 text-sm text-slate-600 transition hover:text-violet-700 dark:text-slate-300 dark:hover:text-violet-300"
                @click="mobileNavOpen = false"
            >
                {{ $node['label'] }}
            </a>
            @if (! empty($node['children']))
                @include('partials.nav-category-mobile-nodes', ['nodes' => $node['children']])
            @endif
        </div>
    @endforeach
</div>
