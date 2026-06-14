@extends('layouts.admin')

@section('title', 'Kategoriler')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Kategori Yönetimi</h1>
    <p class="mt-1 text-sm text-slate-500">İç içe kategori yapısı — toplam {{ number_format($categories->total(), 0, ',', '.') }} kayıt, sayfa başına 30.</p>

    @error('parent_id')
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $message }}</div>
    @enderror

    <form method="post" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="card mt-5 grid gap-3 p-5 md:grid-cols-4">
        @csrf
        <input name="name" placeholder="Kategori adı" class="input-ui" required>
        <input name="slug" placeholder="Slug (opsiyonel)" class="input-ui">
        <select name="parent_id" class="input-ui @error('parent_id') border-rose-300 @enderror">
            <option value="">Ana kategori</option>
            @foreach($parentSelectOptionsCreate as $opt)
                <option value="{{ $opt['id'] }}" @selected((string) old('parent_id') === (string) $opt['id'])>{{ \App\Models\Category::adminSelectOptionLabel($opt['depth'], $opt['name']) }}</option>
            @endforeach
        </select>
        <input type="number" name="nav_order" value="{{ old('nav_order', 0) }}" min="0" class="input-ui" placeholder="Menü sırası">
        <label class="input-ui">
            İkon (PNG/JPG/SVG/WEBP)
            <input type="file" name="icon_file" accept=".png,.jpg,.jpeg,.svg,.webp" class="mt-1 w-full text-sm">
        </label>
        <label class="input-ui">
            Kategori görseli (PNG/JPG/WEBP)
            <input type="file" name="cover_image_file" accept=".png,.jpg,.jpeg,.webp" class="mt-1 w-full text-sm">
        </label>
        <textarea name="description" placeholder="Açıklama" class="input-ui md:col-span-2">{{ old('description') }}</textarea>
        <label class="inline-flex items-center gap-2 text-sm md:col-span-2">
            <input type="hidden" name="show_in_nav" value="0">
            <input type="checkbox" name="show_in_nav" value="1" checked>
            Header menüsünde göster
        </label>
        <button class="btn-primary md:col-span-4">Kategori Ekle</button>
    </form>

    <form method="get" action="{{ route('admin.categories.index') }}" class="card mt-6 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[16rem] flex-1">
            <label for="admin-category-filter" class="mb-1 block text-xs font-medium text-slate-600">Kategori adına göre ara</label>
            <input
                id="admin-category-filter"
                type="search"
                name="q"
                value="{{ $search }}"
                autocomplete="off"
                placeholder="Kategori adına yazın..."
                class="input-ui w-full"
            >
        </div>
        <button type="submit" class="btn-primary">Ara</button>
        @if($search !== '')
            <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Temizle</a>
        @endif
    </form>

    <div class="card mt-4 overflow-x-auto p-4">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500">
                    <th class="py-2 pr-2">Kategori</th>
                    <th class="pr-2">Slug</th>
                    <th class="pr-2">Üst kategori</th>
                    <th class="pr-2">Menü</th>
                    <th class="pr-2">Sıra</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($categories as $category)
                <tr class="border-t border-slate-100">
                    <td class="py-2 pr-2">
                        <div class="flex items-center gap-2">
                            @if($category->icon_path)
                                <img src="{{ asset('storage/'.$category->icon_path) }}" alt="" class="h-8 w-8 rounded-lg border border-violet-100 object-contain bg-violet-50">
                            @endif
                            <span class="font-medium text-slate-900">{{ $category->name }}</span>
                        </div>
                    </td>
                    <td class="pr-2 text-xs text-slate-500">{{ $category->slug }}</td>
                    <td class="pr-2 text-xs text-slate-600">{{ $parentBreadcrumbLabels[$category->id] ?? 'Ana kategori' }}</td>
                    <td class="pr-2">
                        @if($category->show_in_nav)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">Menüde</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">Gizli</span>
                        @endif
                    </td>
                    <td class="pr-2 text-slate-600">{{ $category->nav_order ?? 0 }}</td>
                    <td class="text-right whitespace-nowrap">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn-secondary inline-flex px-3 py-1.5 text-xs">Düzenle</a>
                        <form method="post" action="{{ route('admin.categories.destroy', $category) }}" class="mt-1 inline-block" onsubmit="return confirm('Bu kategori silinsin mi? Alt kategoriler ve bağlı sayfalar etkilenebilir.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-10 text-center text-slate-500">
                        @if($search !== '')
                            Aramanızla eşleşen kategori bulunamadı.
                        @else
                            Henüz kategori yok.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if($categories->hasPages())
            <div class="mt-4 border-t border-slate-100 pt-4">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection
