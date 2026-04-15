@extends('layouts.admin')

@section('title', 'Boyama Sayfalari')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Boyama Sayfasi Yonetimi</h1>

    <form method="post" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data" class="card mt-5 grid gap-3 p-5 md:grid-cols-2">
        @csrf
        <input name="title" placeholder="Baslik" class="input-ui">
        <select name="category_id" class="input-ui">
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        <input type="number" step="0.01" name="price" placeholder="Fiyat" class="input-ui">
        <label class="input-ui">Kapak <input type="file" name="cover_image" class="mt-1 block w-full text-sm"></label>
        <label class="input-ui md:col-span-2">PDF <input type="file" name="pdf_file" class="mt-1 block w-full text-sm"></label>
        <textarea name="description" placeholder="Aciklama" class="input-ui md:col-span-2"></textarea>
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_free" value="1"> Ucretsiz</label>
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1"> One Cikan</label>
        <button class="btn-primary">Kaydet</button>
    </form>

    <div class="card mt-6 overflow-x-auto p-4">
        <table class="min-w-full text-sm">
            <thead><tr class="text-left text-slate-500"><th class="py-2">Baslik</th><th>Kategori</th><th>Fiyat</th><th>Tip</th><th>Islem</th></tr></thead>
            <tbody>
            @foreach($pages as $page)
                <tr class="border-t">
                    <td class="py-2">{{ $page->title }}</td>
                    <td>{{ $page->category->name }}</td>
                    <td>{{ number_format($page->price, 2) }} TL</td>
                    <td>{{ $page->is_free ? 'Ucretsiz' : 'Ucretli' }}</td>
                    <td>
                        <form method="post" action="{{ route('admin.pages.destroy', $page) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn-danger px-3 py-1.5">Sil</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
