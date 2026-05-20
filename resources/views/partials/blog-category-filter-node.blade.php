@foreach($nodes as $node)
    @php
        $cat = $node['category'];
        $children = $node['children'];
        $hasChildren = count($children) > 0;
        $isActive = $activeCategory && (int) $activeCategory->id === (int) $cat->id;
        $isOnPath = in_array($cat->id, $activePathIds, true) && ! $isActive;
        $count = $subtreeCounts[$cat->id] ?? 0;
    @endphp
    <div
        class="blog-filter-branch"
        x-show="match({{ $cat->id }})"
        x-cloak
        data-cat-id="{{ $cat->id }}"
    >
        <div class="flex items-stretch gap-0.5" style="padding-left: {{ $depth * 0.65 }}rem">
            @if($hasChildren)
                <button
                    type="button"
                    class="blog-filter-toggle"
                    @click.stop="toggle({{ $cat->id }})"
                    :aria-expanded="isOpen({{ $cat->id }})"
                    aria-label="Alt kategorileri {{ $cat->name }} için aç/kapat"
                >
                    <svg class="h-3.5 w-3.5 transition-transform duration-150" :class="isOpen({{ $cat->id }}) ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            @else
                <span class="w-7 shrink-0" aria-hidden="true"></span>
            @endif
            <a
                href="{{ route('blog.category', $cat) }}"
                class="blog-filter-link min-w-0 flex-1 {{ $isActive ? 'blog-filter-link--active' : ($isOnPath ? 'blog-filter-link--path' : '') }}"
            >
                <span class="truncate">{{ $cat->name }}</span>
                <span class="blog-filter-count shrink-0 {{ $isActive ? 'blog-filter-count--active' : '' }}">{{ $count }}</span>
            </a>
        </div>
        @if($hasChildren)
            <div x-show="isOpen({{ $cat->id }})" class="mt-0.5 space-y-0.5">
                @include('partials.blog-category-filter-node', [
                    'nodes' => $children,
                    'depth' => $depth + 1,
                    'subtreeCounts' => $subtreeCounts,
                    'activeCategory' => $activeCategory,
                    'activePathIds' => $activePathIds,
                ])
            </div>
        @endif
    </div>
@endforeach
