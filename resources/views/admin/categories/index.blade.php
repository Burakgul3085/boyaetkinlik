@extends('layouts.admin')

@section('title', 'Kategoriler')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Kategori Yönetimi</h1>
    <p class="mt-1 text-sm text-slate-500">İç içe kategori yapısı: üst kategori olarak herhangi bir düzey seçebilirsiniz (döngü oluşturmayacak şekilde).</p>

    @error('parent_id')
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $message }}</div>
    @enderror

    <form method="post" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="card mt-5 grid gap-3 p-5 md:grid-cols-4">
        @csrf
        <input name="name" placeholder="Kategori adı" class="input-ui">
        <input name="slug" placeholder="Slug (opsiyonel)" class="input-ui">
        <select name="parent_id" class="input-ui @error('parent_id') border-rose-300 @enderror">
            <option value="">Ana kategori</option>
            @foreach($parentSelectOptionsCreate as $opt)
                <option value="{{ $opt['id'] }}" @selected((string) old('parent_id') === (string) $opt['id'])>{{ \App\Models\Category::adminSelectOptionLabel($opt['depth'], $opt['name']) }}</option>
            @endforeach
        </select>
        <input type="number" name="nav_order" value="0" min="0" class="input-ui" placeholder="Menü sırası">
        <label class="input-ui">
            İkon (PNG/JPG/SVG/WEBP)
            <input type="file" name="icon_file" accept=".png,.jpg,.jpeg,.svg,.webp" class="mt-1 w-full text-sm">
        </label>
        <label class="input-ui">
            Kategori görseli (PNG/JPG/WEBP)
            <input type="file" name="cover_image_file" accept=".png,.jpg,.jpeg,.webp" class="mt-1 w-full text-sm">
        </label>
        <textarea name="description" placeholder="Açıklama" class="input-ui md:col-span-3"></textarea>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="show_in_nav" value="0">
            <input type="checkbox" name="show_in_nav" value="1" checked>
            Header menüsünde göster
        </label>
        <button class="btn-primary md:col-span-4">Kategori Ekle</button>
    </form>

    <div class="mt-6 space-y-3">
        @foreach($categories as $category)
            <form method="post" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="card p-4">
                @csrf @method('PUT')
                <div class="grid gap-3 md:grid-cols-4">
                    <input name="name" value="{{ $category->name }}" class="input-ui">
                    <input name="slug" value="{{ $category->slug }}" class="input-ui">
                    <select name="parent_id" class="input-ui">
                        <option value="">Ana kategori</option>
                        @foreach(($parentSelectOptionsForEdit[$category->id] ?? []) as $opt)
                            <option value="{{ $opt['id'] }}" @selected($category->parent_id === $opt['id'])>{{ \App\Models\Category::adminSelectOptionLabel($opt['depth'], $opt['name']) }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="nav_order" value="{{ $category->nav_order ?? 0 }}" min="0" class="input-ui" placeholder="Menü sırası">
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
                        <div class="flex h-14 items-center rounded-xl border border-dashed border-slate-300 px-3 text-xs text-slate-500">
                            {{ $category->cover_image_path ? 'Kategori görseli var' : 'Kategori görseli yok' }}
                        </div>
                    </div>
                    <label class="input-ui md:col-span-3">
                        Kategori görselini güncelle (opsiyonel)
                        <input type="file" name="cover_image_file" accept=".png,.jpg,.jpeg,.webp" class="mt-1 w-full text-sm">
                    </label>
                </div>
                <textarea name="description" class="input-ui mt-3">{{ $category->description }}</textarea>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        <span class="rounded-full bg-slate-100 px-2 py-1">{{ $category->parentBreadcrumbLabel() }}</span>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input type="hidden" name="show_in_nav" value="0">
                            <input type="checkbox" name="show_in_nav" value="1" @checked($category->show_in_nav)>
                            Header menüsünde göster
                        </label>
                    </div>
                    <button class="btn-primary">Güncelle</button>
                </div>
            </form>
            <form method="post" action="{{ route('admin.categories.destroy', $category) }}" class="mt-2">
                @csrf
                @method('DELETE')
                <button onclick="return confirm('Silinsin mi?')" class="btn-danger">Sil</button>
            </form>
        @endforeach
    </div>
@endsection
