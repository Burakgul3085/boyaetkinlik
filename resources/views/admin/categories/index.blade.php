@extends('layouts.admin')

@section('title', 'Kategoriler')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Kategori Yönetimi</h1>
    <form method="post" action="{{ route('admin.categories.store') }}" class="card mt-5 grid gap-3 p-5 md:grid-cols-3">
        @csrf
        <input name="name" placeholder="Kategori adı" class="input-ui">
        <input name="slug" placeholder="Slug (opsiyonel)" class="input-ui">
        <button class="btn-primary">Ekle</button>
        <textarea name="description" placeholder="Açıklama" class="input-ui md:col-span-3"></textarea>
    </form>

    <div class="mt-6 space-y-3">
        @foreach($categories as $category)
            <form method="post" action="{{ route('admin.categories.update', $category) }}" class="card p-4">
                @csrf @method('PUT')
                <div class="grid gap-3 md:grid-cols-3">
                    <input name="name" value="{{ $category->name }}" class="input-ui">
                    <input name="slug" value="{{ $category->slug }}" class="input-ui">
                    <div class="flex gap-2">
                        <button class="btn-primary w-full">Güncelle</button>
                    </div>
                </div>
                <textarea name="description" class="input-ui mt-3">{{ $category->description }}</textarea>
            </form>
            <form method="post" action="{{ route('admin.categories.destroy', $category) }}" class="mt-2">
                @csrf
                @method('DELETE')
                <button onclick="return confirm('Silinsin mi?')" class="btn-danger">Sil</button>
            </form>
        @endforeach
    </div>
@endsection
