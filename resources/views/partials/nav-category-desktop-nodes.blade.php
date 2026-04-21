{{-- Çok seviyeli kategori: masaüstü — sağa açılan alt paneller --}}
@foreach ($nodes as $node)
    @if (empty($node['children']))
        <a
            href="{{ $node['url'] }}"
            class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-violet-50 hover:text-violet-700 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-violet-300"
        >
            {{ $node['label'] }}
        </a>
    @else
        <div class="group/nest relative">
            <a
                href="{{ $node['url'] }}"
                class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-violet-50 hover:text-violet-700 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-violet-300"
            >
                <span class="min-w-0 truncate">{{ $node['label'] }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400 dark:text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.512 4.25a.75.75 0 0 1 0 1.08l-4.512 4.25a.75.75 0 0 1-1.04-.02Z" clip-rule="evenodd" />
                </svg>
            </a>
            <div
                class="invisible absolute left-full top-0 z-[60] min-w-[13rem] pl-1 opacity-0 transition duration-100 group-hover/nest:visible group-hover/nest:pointer-events-auto group-hover/nest:opacity-100 group-focus-within/nest:visible group-focus-within/nest:pointer-events-auto group-focus-within/nest:opacity-100"
            >
                <div class="rounded-xl border border-violet-200 bg-white p-2 shadow-xl dark:border-slate-600 dark:bg-slate-800">
                    @include('partials.nav-category-desktop-nodes', ['nodes' => $node['children']])
                </div>
            </div>
        </div>
    @endif
@endforeach
