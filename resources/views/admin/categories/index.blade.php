@extends('layouts.admin')

@section('title', 'Kategoriler')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Kategori Yönetimi</h1>
    <p class="mt-1 text-sm text-slate-500">Header ana kartları ve alt kartları buradan dinamik olarak yönetebilirsiniz.</p>

    <form method="post" action="{{ route('admin.categories.store') }}" class="card mt-5 grid gap-3 p-5 md:grid-cols-4">
        @csrf
        <input name="name" placeholder="Kategori adı" class="input-ui">
        <input name="slug" placeholder="Slug (opsiyonel)" class="input-ui">
        <select name="parent_id" class="input-ui">
            <option value="">Ana kategori</option>
            @foreach($parentCategories as $parentCategory)
                <option value="{{ $parentCategory->id }}">{{ $parentCategory->name }}</option>
            @endforeach
        </select>
        <input type="number" name="nav_order" value="0" min="0" class="input-ui" placeholder="Menü sırası">
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
            <form method="post" action="{{ route('admin.categories.update', $category) }}" class="card p-4">
                @csrf @method('PUT')
                <div class="grid gap-3 md:grid-cols-4">
                    <input name="name" value="{{ $category->name }}" class="input-ui">
                    <input name="slug" value="{{ $category->slug }}" class="input-ui">
                    <select name="parent_id" class="input-ui">
                        <option value="">Ana kategori</option>
                        @foreach($parentCategories as $parentCategory)
                            @if($parentCategory->id !== $category->id)
                                <option value="{{ $parentCategory->id }}" @selected($category->parent_id === $parentCategory->id)>{{ $parentCategory->name }}</option>
                            @endif
                        @endforeach
                    </select>
                    <input type="number" name="nav_order" value="{{ $category->nav_order ?? 0 }}" min="0" class="input-ui" placeholder="Menü sırası">
                </div>
                <textarea name="description" class="input-ui mt-3">{{ $category->description }}</textarea>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                        <span class="rounded-full bg-slate-100 px-2 py-1">{{ $category->parent?->name ? 'Alt: '.$category->parent->name : 'Ana kategori' }}</span>
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
