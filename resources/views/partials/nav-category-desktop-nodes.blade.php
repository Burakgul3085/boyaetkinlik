{{-- Çok seviyeli kategori: masaüstü — tek sütunda dikey ağaç (sınırsız derinlik, yatay taşma yok) --}}
@foreach ($nodes as $node)
    @if (empty($node['children']))
        <a
            href="{{ $node['url'] }}"
            class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-violet-50 hover:text-violet-700 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-violet-300"
        >
            {{ $node['label'] }}
        </a>
    @else
        <div class="rounded-lg py-0.5">
            <a
                href="{{ $node['url'] }}"
                class="flex w-full min-w-0 items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-800 transition hover:bg-violet-50 hover:text-violet-800 dark:text-slate-100 dark:hover:bg-slate-700 dark:hover:text-violet-200"
            >
                <span class="min-w-0 flex-1 truncate">{{ $node['label'] }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 text-violet-400 dark:text-violet-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.512a.75.75 0 0 1-1.08 0L5.21 8.27a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </a>
            <div class="ml-1.5 mt-0.5 space-y-0.5 border-l-2 border-violet-100 py-0.5 pl-2.5 dark:border-slate-600">
                @include('partials.nav-category-desktop-nodes', ['nodes' => $node['children']])
            </div>
        </div>
    @endif
@endforeach
