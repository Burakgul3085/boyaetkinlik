<div class="card p-5">
    <h2 class="text-lg font-bold text-slate-900">Blog Kategorileri</h2>
    <p class="mt-1 text-sm text-slate-500">Ziyaretçi gönderiminde listelenir. İçinde yazı olan kategori silinemez; pasif yapılabilir.</p>

    <form method="post" action="{{ route('admin.blog-categories.store') }}" class="mt-4 grid gap-3 md:grid-cols-4">
        @csrf
        <input name="name" placeholder="Yeni kategori adı" class="input-ui" required>
        <input type="number" name="sort_order" value="0" min="0" class="input-ui" placeholder="Sıra">
        <input name="description" placeholder="Açıklama (opsiyonel)" class="input-ui md:col-span-2">
        <button class="btn-primary md:col-span-4">Kategori Ekle</button>
    </form>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-slate-500">
                    <th class="py-2 pr-3">Ad</th>
                    <th class="py-2 pr-3">Yazı</th>
                    <th class="py-2 pr-3">Kaynak</th>
                    <th class="py-2 pr-3">Durum</th>
                    <th class="py-2">İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($blogCategories as $cat)
                    <tr class="border-t border-slate-100">
                        <td class="py-3 pr-3 align-top">
                            <form method="post" action="{{ route('admin.blog-categories.update', $cat) }}" class="space-y-2">
                                @csrf @method('PUT')
                                <input name="name" value="{{ $cat->name }}" class="input-ui w-full min-w-[10rem]" required>
                                <input name="description" value="{{ $cat->description }}" placeholder="Açıklama" class="input-ui w-full text-xs">
                                <input type="number" name="sort_order" value="{{ $cat->sort_order }}" min="0" class="input-ui w-24 text-xs">
                                <label class="inline-flex items-center gap-2 text-xs text-slate-600">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" @checked($cat->is_active)>
                                    Aktif (yeni gönderimde görünür)
                                </label>
                                <button class="btn-secondary px-3 py-1 text-xs">Kaydet</button>
                            </form>
                        </td>
                        <td class="py-3 pr-3 align-top text-slate-600">{{ $cat->blogs_count }}</td>
                        <td class="py-3 pr-3 align-top">
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ $cat->source === 'visitor' ? 'Ziyaretçi' : 'Admin' }}</span>
                        </td>
                        <td class="py-3 pr-3 align-top">
                            @if($cat->is_active)
                                <span class="text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="text-xs font-medium text-slate-500">Pasif</span>
                            @endif
                        </td>
                        <td class="py-3 align-top">
                            @if($cat->blogs_count > 0)
                                <span class="text-xs text-slate-400">Silinemez</span>
                            @else
                                <form method="post" action="{{ route('admin.blog-categories.destroy', $cat) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Kategori silinsin mi?')" class="btn-danger px-3 py-1 text-xs">Sil</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-slate-500">Henüz blog kategorisi yok.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
