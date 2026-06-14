@extends('layouts.admin')

@section('title', 'Boyama Sayfası Düzenle')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Boyama Sayfası Düzenle</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $page->title }}</p>
        </div>
        <a href="{{ route('admin.pages.index') }}" class="btn-secondary text-sm">← Listeye dön</a>
    </div>

    <form method="post" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="card mt-5 grid gap-3 p-5 md:grid-cols-2">
        @csrf
        @method('PUT')
        <input name="title" value="{{ old('title', $page->title) }}" class="input-ui" placeholder="Başlık" required>
        <select name="category_id" class="input-ui" required>
            @foreach($categoryAssignmentOptions as $opt)
                <option value="{{ $opt['id'] }}" @selected((int) old('category_id', $page->category_id) === $opt['id'])>{{ \App\Models\Category::adminSelectOptionLabel($opt['depth'], $opt['name']) }}</option>
            @endforeach
        </select>
        <input type="number" step="0.01" name="price" value="{{ old('price', $page->price) }}" placeholder="Fiyat" class="input-ui">
        <input type="url" name="shopier_product_url" value="{{ old('shopier_product_url', $page->shopier_product_url) }}" placeholder="Shopier ürün linki (https://...)" class="input-ui md:col-span-2">
        <label class="input-ui">
            Kapak (PNG/JPG/JPEG)
            @if($page->cover_image_path)
                <span class="mt-1 block text-xs text-slate-500">Mevcut: {{ basename($page->cover_image_path) }}</span>
            @endif
            <input type="file" name="cover_image" accept=".png,.jpg,.jpeg" class="mt-1 block w-full text-sm">
        </label>
        <label class="input-ui md:col-span-2">
            Dosya (PDF/PNG/JPG/JPEG)
            @if($page->pdf_path)
                <span class="mt-1 block text-xs text-slate-500">Mevcut: {{ basename($page->pdf_path) }}</span>
            @endif
            <input type="file" name="pdf_file" accept=".pdf,.png,.jpg,.jpeg" class="mt-1 block w-full text-sm">
        </label>
        <textarea name="description" placeholder="Açıklama" class="input-ui md:col-span-2">{{ old('description', $page->description) }}</textarea>

        <div class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="is_free" value="0">
            <input type="checkbox" name="is_free" value="1" @checked(old('is_free', $page->is_free))>
            <span>Ücretsiz</span>
        </div>
        <div class="inline-flex items-center gap-2 text-sm">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $page->is_featured))>
            <span>Öne Çıkan</span>
        </div>

        <div class="md:col-span-2 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <span class="text-xs text-slate-500">Yeni dosya yüklemezsen mevcut dosyalar korunur.</span>
            <button type="submit" class="btn-primary px-5 py-2">Güncellemeyi Kaydet</button>
        </div>
    </form>

    <div class="mt-4 flex flex-wrap gap-3">
        <a href="{{ route('products.show', $page) }}" target="_blank" rel="noopener noreferrer" class="btn-secondary text-sm">Sitede görüntüle ↗</a>
        <form method="post" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Bu boyama sayfası kalıcı olarak silinsin mi?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger text-sm">Sayfayı Sil</button>
        </form>
    </div>
@endsection
