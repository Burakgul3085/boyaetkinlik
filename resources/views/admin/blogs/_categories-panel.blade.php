@php
    $catsById = $blogCategories->keyBy('id');
    $orderedRows = \App\Models\BlogCategory::orderedFlatWithDepth($blogCategories);
    $childCountByParent = $blogCategories->groupBy('parent_id')->map->count();
@endphp
<div class="card p-5 md:p-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-900">Blog Kategorileri</h2>
            <p class="mt-1 max-w-2xl text-sm text-slate-500">İç içe kategori açabilirsiniz (ör. Boyama → Harf Boyama). Üst kategoride ve altında yazı olabilir.</p>
        </div>
        <span class="admin-blog-cat-pill admin-blog-cat-pill--count text-xs">{{ $blogCategories->count() }} kategori</span>
    </div>

    <div class="admin-blog-cat-add mt-5">
        <p class="text-xs font-bold uppercase tracking-wider text-violet-700">Yeni kategori</p>
        <form method="post" action="{{ route('admin.blog-categories.store') }}" class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @csrf
            <input name="name" placeholder="Kategori adı" class="input-ui lg:col-span-2" required>
            <select name="parent_id" class="input-ui" title="Üst kategori">
                <option value="">Ana kategori (üst yok)</option>
                @foreach($parentSelectOptionsCreate as $opt)
                    <option value="{{ $opt['id'] }}">{{ \App\Models\BlogCategory::adminSelectOptionLabel($opt['depth'], $opt['name']) }}</option>
                @endforeach
            </select>
            <input type="number" name="sort_order" value="0" min="0" class="input-ui" placeholder="Sıra" title="Sıra">
            <input name="description" placeholder="Açıklama (opsiyonel)" class="input-ui">
            <button class="btn-primary sm:col-span-2 lg:col-span-5">Kategori Ekle</button>
        </form>
    </div>

    <div
        class="admin-blog-cat-panel mt-5"
        x-data="{
            search: '',
            openIds: {},
            names: @js($blogCategories->pluck('name', 'id')->all()),
            descriptions: @js($blogCategories->pluck('description', 'id')->map(fn ($d) => (string) ($d ?? ''))->all()),
            matchId(id) {
                const q = this.search.trim().toLocaleLowerCase('tr-TR');
                if (!q) return true;
                const name = String(this.names[id] ?? '').toLocaleLowerCase('tr-TR');
                const desc = String(this.descriptions[id] ?? '').toLocaleLowerCase('tr-TR');
                return name.includes(q) || desc.includes(q);
            },
            visibleIds() {
                return Object.keys(this.names).map(Number).filter((id) => this.matchId(id));
            },
            isOpen(id) {
                return !!this.openIds[id];
            },
            toggle(id) {
                this.openIds[id] = !this.openIds[id];
            },
            expandAll() {
                this.visibleIds().forEach((id) => { this.openIds[id] = true; });
            },
            collapseAll() {
                this.openIds = {};
            }
        }"
    >
        <div class="admin-blog-cat-panel__head">
            <div class="min-w-0 flex-1 sm:max-w-md">
                <label for="blog-category-filter" class="text-xs font-bold uppercase tracking-wider text-slate-600">Kategori ağacı</label>
                <input
                    id="blog-category-filter"
                    type="search"
                    x-model.debounce.150ms="search"
                    autocomplete="off"
                    placeholder="Ad veya açıklamada ara..."
                    class="input-ui mt-2 w-full"
                >
                <p class="mt-1.5 text-[11px] text-slate-500">
                    <span x-text="visibleIds().length"></span> / {{ $blogCategories->count() }} gösteriliyor · Satıra tıklayarak düzenleyin
                </p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
                <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="expandAll()">Tümünü aç</button>
                <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="collapseAll()">Tümünü kapat</button>
            </div>
        </div>

        <div class="admin-blog-cat-tree">
            <div class="admin-blog-cat-tree__list">
                @forelse($orderedRows as $row)
                    @php
                        $cat = $catsById[$row['id']] ?? null;
                        $depth = (int) ($row['depth'] ?? 0);
                        $hasChildren = ($childCountByParent[$cat->id] ?? 0) > 0;
                    @endphp
                    @if($cat)
                        <div
                            x-show="matchId({{ $cat->id }})"
                            x-cloak
                            class="admin-blog-cat-item"
                            style="--cat-depth: {{ min($depth, 8) }}"
                            data-depth="{{ $depth }}"
                        >
                            <span class="admin-blog-cat-item__rail" aria-hidden="true"></span>
                            <div
                                class="admin-blog-cat-card"
                                :class="isOpen({{ $cat->id }}) ? 'admin-blog-cat-card--open' : ''"
                            >
                                <button
                                    type="button"
                                    class="admin-blog-cat-card__trigger group"
                                    @click="toggle({{ $cat->id }})"
                                    :aria-expanded="isOpen({{ $cat->id }})"
                                >
                                    <span
                                        class="admin-blog-cat-card__chevron"
                                        :class="isOpen({{ $cat->id }}) ? 'admin-blog-cat-card__chevron--open' : ''"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                    <span class="admin-blog-cat-card__icon" aria-hidden="true">
                                        @if($hasChildren)
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                            </svg>
                                        @else
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                        @endif
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="text-sm font-semibold text-slate-900 group-hover:text-violet-800">{{ $cat->name }}</span>
                                            @if($depth > 0)
                                                <span class="rounded-md bg-violet-50 px-1.5 py-0.5 text-[10px] font-medium text-violet-600">Seviye {{ $depth + 1 }}</span>
                                            @endif
                                        </span>
                                        <span class="admin-blog-cat-card__path" title="{{ $parentBreadcrumbLabels[$cat->id] ?? '' }}">
                                            {{ $parentBreadcrumbLabels[$cat->id] ?? 'Ana kategori' }}
                                        </span>
                                        <span class="admin-blog-cat-card__meta sm:hidden">
                                            <span class="admin-blog-cat-pill admin-blog-cat-pill--count">{{ $subtreeBlogCounts[$cat->id] ?? 0 }} yazı</span>
                                            @if($cat->is_active)
                                                <span class="admin-blog-cat-pill admin-blog-cat-pill--active">Aktif</span>
                                            @else
                                                <span class="admin-blog-cat-pill admin-blog-cat-pill--passive">Pasif</span>
                                            @endif
                                        </span>
                                    </span>
                                    <span class="hidden shrink-0 flex-wrap items-center justify-end gap-1.5 sm:flex">
                                        <span class="admin-blog-cat-pill admin-blog-cat-pill--count">{{ $subtreeBlogCounts[$cat->id] ?? 0 }} yazı</span>
                                        <span class="admin-blog-cat-pill admin-blog-cat-pill--source">{{ $cat->source === 'visitor' ? 'Ziyaretçi' : 'Admin' }}</span>
                                        @if($cat->is_active)
                                            <span class="admin-blog-cat-pill admin-blog-cat-pill--active">Aktif</span>
                                        @else
                                            <span class="admin-blog-cat-pill admin-blog-cat-pill--passive">Pasif</span>
                                        @endif
                                    </span>
                                </button>

                                <div
                                    x-show="isOpen({{ $cat->id }})"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    class="admin-blog-cat-card__body"
                                    x-cloak
                                >
                                    <p class="admin-blog-cat-edit-title">Düzenle — {{ $cat->name }}</p>
                                    <form method="post" action="{{ route('admin.blog-categories.update', $cat) }}" class="grid gap-3 md:grid-cols-2">
                                        @csrf @method('PUT')
                                        <div>
                                            <label class="text-xs font-medium text-slate-600">Kategori adı</label>
                                            <input name="name" value="{{ $cat->name }}" class="input-ui mt-1 w-full" required>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-slate-600">Üst kategori</label>
                                            <select name="parent_id" class="input-ui mt-1 w-full">
                                                <option value="">Ana kategori</option>
                                                @foreach(($parentSelectOptionsForEdit[$cat->id] ?? []) as $opt)
                                                    <option value="{{ $opt['id'] }}" @selected($cat->parent_id == $opt['id'])>{{ \App\Models\BlogCategory::adminSelectOptionLabel($opt['depth'], $opt['name']) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-slate-600">Sıra</label>
                                            <input type="number" name="sort_order" value="{{ $cat->sort_order }}" min="0" class="input-ui mt-1 w-full">
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-slate-600">Açıklama</label>
                                            <input name="description" value="{{ $cat->description }}" placeholder="Opsiyonel" class="input-ui mt-1 w-full text-sm">
                                        </div>
                                        <div class="flex flex-wrap items-center justify-between gap-3 md:col-span-2">
                                            <label class="inline-flex items-center gap-2 rounded-lg border border-violet-100 bg-white px-3 py-2 text-xs text-slate-600">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox" name="is_active" value="1" @checked($cat->is_active)>
                                                Aktif (yeni gönderimde görünür)
                                            </label>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <button type="submit" class="btn-primary px-4 py-1.5 text-xs">Kaydet</button>
                                                @if(($subtreeBlogCounts[$cat->id] ?? 0) > 0)
                                                    <span class="text-xs text-slate-400">Silinemez ({{ $subtreeBlogCounts[$cat->id] }} yazı)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </form>
                                    @if(($subtreeBlogCounts[$cat->id] ?? 0) === 0 && ! $hasChildren)
                                        <form method="post" action="{{ route('admin.blog-categories.destroy', $cat) }}" class="mt-3 flex justify-end border-t border-violet-100/80 pt-3">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Kategori silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Kategoriyi Sil</button>
                                        </form>
                                    @elseif($hasChildren)
                                        <p class="mt-3 rounded-lg border border-amber-100 bg-amber-50/80 px-3 py-2 text-xs text-amber-800">Alt kategori var — önce altları silin veya başka üste taşıyın.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @empty
                    <p class="rounded-xl border border-dashed border-slate-200 bg-white px-4 py-8 text-center text-sm text-slate-500">Henüz blog kategorisi yok.</p>
                @endforelse
                <p
                    x-show="search.trim() !== '' && visibleIds().length === 0"
                    x-cloak
                    class="rounded-xl border border-dashed border-amber-200 bg-amber-50 px-4 py-6 text-center text-sm text-amber-800"
                >
                    Aramanızla eşleşen kategori yok.
                </p>
            </div>
        </div>
    </div>
</div>
