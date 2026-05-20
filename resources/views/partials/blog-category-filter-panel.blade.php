@php
    $filterNames = collect();
    $walkNames = function (array $nodes) use (&$walkNames, &$filterNames): void {
        foreach ($nodes as $node) {
            $filterNames[$node['category']->id] = $node['category']->name;
            if (! empty($node['children'])) {
                $walkNames($node['children']);
            }
        }
    };
    $walkNames($categoryFilterTree);
    $defaultOpen = [];
    foreach ($activePathIds as $id) {
        $defaultOpen[$id] = true;
    }
@endphp
<aside
    class="blog-filter-panel overflow-hidden rounded-2xl border border-violet-100 bg-white shadow-sm"
    x-data="{
        search: '',
        openIds: @js($defaultOpen),
        names: @js($filterNames->all()),
        match(id) {
            const q = this.search.trim().toLocaleLowerCase('tr-TR');
            if (!q) return true;
            return String(this.names[id] ?? '').toLocaleLowerCase('tr-TR').includes(q);
        },
        isOpen(id) { return !!this.openIds[id]; },
        toggle(id) { this.openIds[id] = !this.openIds[id]; },
        expandAll() {
            Object.keys(this.names).forEach((id) => { this.openIds[Number(id)] = true; });
        },
        collapseAll() { this.openIds = {}; }
    }"
>
    <div class="border-b border-violet-100 bg-gradient-to-r from-violet-50/90 to-fuchsia-50/50 px-4 py-3">
        <p class="text-sm font-bold text-slate-900">Kategoriler</p>
        <p class="mt-0.5 text-xs text-slate-500">İç içe yapı; üst kategori altındaki tüm yazıları gösterir.</p>
    </div>

    <div class="border-b border-violet-50 px-3 py-3">
        <input
            type="search"
            x-model.debounce.150ms="search"
            placeholder="Kategori ara..."
            autocomplete="off"
            class="input-ui w-full text-sm"
        >
        <div class="mt-2 flex flex-wrap gap-1.5">
            <button type="button" class="rounded-lg border border-violet-100 px-2 py-1 text-[10px] font-semibold text-violet-700 hover:bg-violet-50" @click="expandAll()">Ağacı aç</button>
            <button type="button" class="rounded-lg border border-violet-100 px-2 py-1 text-[10px] font-semibold text-violet-700 hover:bg-violet-50" @click="collapseAll()">Ağacı kapat</button>
        </div>
    </div>

    @if($activeCategory && ! empty($breadcrumbItems))
        <div class="border-b border-violet-50 px-4 py-2.5">
            @include('partials.blog-category-breadcrumb-nav', [
                'breadcrumbItems' => $breadcrumbItems,
                'wrapperClass' => 'flex flex-wrap items-center gap-x-0.5 gap-y-1 text-xs text-slate-600',
            ])
        </div>
    @endif

    <nav class="max-h-[min(28rem,55vh)] space-y-0.5 overflow-y-auto p-2" aria-label="Blog kategori filtresi">
        <a
            href="{{ route('blog.index') }}"
            class="blog-filter-link {{ $activeCategory ? '' : 'blog-filter-link--active' }}"
        >
            <span>Tüm yazılar</span>
            <span class="blog-filter-count {{ $activeCategory ? '' : 'blog-filter-count--active' }}">{{ $totalBlogCount }}</span>
        </a>

        @if(count($categoryFilterTree) > 0)
            @include('partials.blog-category-filter-node', [
                'nodes' => $categoryFilterTree,
                'depth' => 0,
                'subtreeCounts' => $subtreeCounts,
                'activeCategory' => $activeCategory,
                'activePathIds' => $activePathIds,
            ])
        @else
            <p class="px-3 py-4 text-center text-xs text-slate-500">Henüz kategori yok.</p>
        @endif
    </nav>
</aside>
