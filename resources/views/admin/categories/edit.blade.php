@extends('layouts.admin')

@section('title', 'Kategori Düzenle')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Kategori Düzenle</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $category->name }}</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn-secondary text-sm">← Listeye dön</a>
    </div>

    @error('parent_id')
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $message }}</div>
    @enderror

    <form method="post" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="card mt-5 p-5">
        @csrf
        @method('PUT')
        <div class="grid gap-3 md:grid-cols-4">
            <input name="name" value="{{ old('name', $category->name) }}" class="input-ui" required>
            <input name="slug" value="{{ old('slug', $category->slug) }}" class="input-ui">
            <select name="parent_id" class="input-ui @error('parent_id') border-rose-300 @enderror">
                <option value="">Ana kategori</option>
                @foreach($parentSelectOptions as $opt)
                    <option value="{{ $opt['id'] }}" @selected((int) old('parent_id', $category->parent_id) === $opt['id'])>{{ \App\Models\Category::adminSelectOptionLabel($opt['depth'], $opt['name']) }}</option>
                @endforeach
            </select>
            <input type="number" name="nav_order" value="{{ old('nav_order', $category->nav_order ?? 0) }}" min="0" class="input-ui" placeholder="Menü sırası">
        </div>

        <div class="mt-3 grid gap-3 md:grid-cols-4">
            <div class="md:col-span-1">
                @if($category->icon_path)
                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-xl border border-violet-100 bg-violet-50">
                        <img src="{{ asset('storage/'.$category->icon_path) }}" alt="{{ $category->name }} ikonu" class="h-10 w-10 object-contain">
                    </div>
                @else
                    <div class="flex h-14 w-14 items-center justify-center rounded-xl border border-dashed border-slate-300 text-xs text-slate-400">
                        İkon yok
                    </div>
                @endif
            </div>
            <label class="input-ui md:col-span-3">
                İkonu güncelle (opsiyonel)
                <input type="file" name="icon_file" accept=".png,.jpg,.jpeg,.svg,.webp" class="mt-1 w-full text-sm">
            </label>

            <div class="md:col-span-1">
                @if($category->cover_image_path)
                    <div class="h-14 overflow-hidden rounded-xl border border-violet-100 bg-violet-50">
                        <img src="{{ asset('storage/'.$category->cover_image_path) }}" alt="" class="h-full w-full object-cover">
                    </div>
                @else
                    <div class="flex h-14 items-center rounded-xl border border-dashed border-slate-300 px-3 text-xs text-slate-500">
                        Görsel yok
                    </div>
                @endif
            </div>
            <label class="input-ui md:col-span-3">
                Kategori görselini güncelle (opsiyonel)
                <input type="file" name="cover_image_file" accept=".png,.jpg,.jpeg,.webp" class="mt-1 w-full text-sm">
            </label>
        </div>

        <textarea name="description" class="input-ui mt-3" rows="4">{{ old('description', $category->description) }}</textarea>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span class="rounded-full bg-slate-100 px-2 py-1">{{ $parentBreadcrumbLabel }}</span>
                <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                    <input type="hidden" name="show_in_nav" value="0">
                    <input type="checkbox" name="show_in_nav" value="1" @checked(old('show_in_nav', $category->show_in_nav))>
                    Header menüsünde göster
                </label>
            </div>
            <button type="submit" class="btn-primary px-5 py-2">Güncelle</button>
        </div>
    </form>

    <div class="mt-4 flex flex-wrap gap-3">
        <a href="{{ route('categories.show', $category->slug) }}" target="_blank" rel="noopener noreferrer" class="btn-secondary text-sm">Sitede görüntüle ↗</a>
        <form method="post" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Bu kategori silinsin mi?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger text-sm">Kategoriyi Sil</button>
        </form>
    </div>
@endsection
