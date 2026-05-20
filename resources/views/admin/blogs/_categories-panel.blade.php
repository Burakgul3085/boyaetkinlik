@php
    $catsById = $blogCategories->keyBy('id');
    $orderedRows = \App\Models\BlogCategory::orderedFlatWithDepth($blogCategories);
@endphp
<div class="card p-5">
    <h2 class="text-lg font-bold text-slate-900">Blog Kategorileri</h2>
    <p class="mt-1 text-sm text-slate-500">İç içe kategori açabilirsiniz (ör. Boyama → Harf Boyama). Üst kategoride ve altında yazı olabilir; üst kategori sayfasında alt kategorilerdeki yazılar da listelenir.</p>

    <form method="post" action="{{ route('admin.blog-categories.store') }}" class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-5">
        @csrf
        <input name="name" placeholder="Yeni kategori adı" class="input-ui lg:col-span-2" required>
        <select name="parent_id" class="input-ui">
            <option value="">Ana kategori (üst yok)</option>
            @foreach($parentSelectOptionsCreate as $opt)
                <option value="{{ $opt['id'] }}">{{ \App\Models\BlogCategory::adminSelectOptionLabel($opt['depth'], $opt['name']) }}</option>
            @endforeach
        </select>
        <input type="number" name="sort_order" value="0" min="0" class="input-ui" placeholder="Sıra">
        <input name="description" placeholder="Açıklama (opsiyonel)" class="input-ui">
        <button class="btn-primary lg:col-span-5">Kategori Ekle</button>
    </form>

    <div
        class="mt-5"
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
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:justify-between">
            <div class="min-w-0 flex-1 sm:max-w-md">
                <label for="blog-category-filter" class="text-sm font-medium text-slate-700">Kategoride ara</label>
                <input
                    id="blog-category-filter"
                    type="search"
                    x-model.debounce.150ms="search"
                    autocomplete="off"
                    placeholder="Ad veya açıklamada ara..."
                    class="input-ui mt-2 w-full"
                >
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="expandAll()">Tümünü aç</button>
                <button type="button" class="btn-secondary px-3 py-1.5 text-xs" @click="collapseAll()">Tümünü kapat</button>
            </div>
        </div>
        <p class="mt-2 text-xs text-slate-500">
            <span x-text="visibleIds().length"></span> / {{ $blogCategories->count() }} kategori gösteriliyor. Düzenlemek için satıra tıklayın.
        </p>

        <div class="mt-3 max-h-[28rem] space-y-2 overflow-y-auto pr-1">
            @forelse($orderedRows as $row)
                @php($cat = $catsById[$row['id']] ?? null)
                @if($cat)
                    <div
                        x-show="matchId({{ $cat->id }})"
                        x-cloak
                        class="overflow-hidden rounded-xl border border-violet-100 bg-white shadow-sm transition"
                        :class="isOpen({{ $cat->id }}) ? 'border-violet-300 ring-1 ring-violet-100' : 'hover:border-violet-200'"
                        style="margin-left: {{ min($row['depth'], 6) * 0.75 }}rem"
                    >
                        <button
                            type="button"
                            class="flex w-full items-center gap-2 px-3 py-2.5 text-left text-sm transition hover:bg-violet-50/60"
                            @click="toggle({{ $cat->id }})"
                            :aria-expanded="isOpen({{ $cat->id }})"
                        >
                            <span
                                class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border border-violet-200 bg-violet-50 text-violet-700 transition-transform duration-150"
                                :class="isOpen({{ $cat->id }}) ? 'rotate-90' : ''"
                                aria-hidden="true"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1 font-semibold text-slate-800">{{ $cat->name }}</span>
                            <span class="hidden max-w-[10rem] truncate text-[10px] text-slate-500 sm:inline" title="{{ $parentBreadcrumbLabels[$cat->id] ?? '' }}">
                                {{ $parentBreadcrumbLabels[$cat->id] ?? '' }}
                            </span>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">{{ $subtreeBlogCounts[$cat->id] ?? 0 }} yazı</span>
                            <span class="hidden shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600 sm:inline">
                                {{ $cat->source === 'visitor' ? 'Ziyaretçi' : 'Admin' }}
                            </span>
                            @if($cat->is_active)
                                <span class="shrink-0 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="shrink-0 text-xs font-medium text-slate-500">Pasif</span>
                            @endif
                        </button>

                        <div
                            x-show="isOpen({{ $cat->id }})"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="border-t border-violet-100 bg-violet-50/30 px-3 py-3"
                        >
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
                                    <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked($cat->is_active)>
                                        Aktif (yeni gönderimde görünür)
                                    </label>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <button type="submit" class="btn-secondary px-3 py-1.5 text-xs">Kaydet</button>
                                        @if(($subtreeBlogCounts[$cat->id] ?? 0) > 0)
                                            <span class="text-xs text-slate-400">Silinemez ({{ $subtreeBlogCounts[$cat->id] }} yazı)</span>
                                        @endif
                                    </div>
                                </div>
                            </form>
                            @if(($subtreeBlogCounts[$cat->id] ?? 0) === 0 && ! $cat->children()->exists())
                                <form method="post" action="{{ route('admin.blog-categories.destroy', $cat) }}" class="mt-2 flex justify-end border-t border-violet-100/80 pt-2">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Kategori silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                                </form>
                            @elseif($cat->children()->exists())
                                <p class="mt-2 text-xs text-slate-400">Alt kategori var — önce altları silin veya taşıyın.</p>
                            @endif
                        </div>
                    </div>
                @endif
            @empty
                <p class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center text-sm text-slate-500">Henüz blog kategorisi yok.</p>
            @endforelse
            <p
                x-show="search.trim() !== '' && visibleIds().length === 0"
                x-cloak
                class="rounded-xl border border-dashed border-amber-200 bg-amber-50 px-4 py-4 text-center text-sm text-amber-800"
            >
                Aramanızla eşleşen kategori yok.
            </p>
        </div>
    </div>
</div>
