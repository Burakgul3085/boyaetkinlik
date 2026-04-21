@extends('layouts.admin')

@section('title', 'Boyama Sayfaları')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Boyama Sayfası Yönetimi</h1>

    <form method="post" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data" class="card mt-5 grid gap-3 p-5 md:grid-cols-2">
        @csrf
        <input name="title" placeholder="Başlık" class="input-ui">
        <select name="category_id" class="input-ui">
            @foreach($categoryAssignmentOptions as $opt)
                <option value="{{ $opt['id'] }}">{{ str_repeat('— ', $opt['depth']) }}{{ $opt['name'] }}</option>
            @endforeach
        </select>
        <input type="number" step="0.01" name="price" placeholder="Fiyat" class="input-ui">
        <input type="url" name="shopier_product_url" placeholder="Shopier ürün linki (https://...)" class="input-ui md:col-span-2">
        <label class="input-ui">Kapak (PNG/JPG/JPEG) <input type="file" name="cover_image" accept=".png,.jpg,.jpeg" class="mt-1 block w-full text-sm"></label>
        <label class="input-ui md:col-span-2">Dosya (PDF/PNG/JPG/JPEG) <input type="file" name="pdf_file" accept=".pdf,.png,.jpg,.jpeg" class="mt-1 block w-full text-sm"></label>
        <textarea name="description" placeholder="Açıklama" class="input-ui md:col-span-2"></textarea>
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_free" value="1"> Ücretsiz</label>
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1"> Öne Çıkan</label>
        <button class="btn-primary">Kaydet</button>
    </form>

    <div class="card mt-6 overflow-x-auto p-4">
        <table class="min-w-full text-sm">
            <thead><tr class="text-left text-slate-500"><th class="py-2">Başlık</th><th>Kategori</th><th>Fiyat</th><th>Shopier Link</th><th>Tip</th><th>İşlem</th></tr></thead>
            <tbody>
            @foreach($pages as $page)
                <tr class="border-t">
                    <td class="py-2">{{ $page->title }}</td>
                    <td>{{ $page->category?->name ?? '— (kategori yok)' }}</td>
                    <td>{{ number_format($page->price, 2) }} TL</td>
                    <td>
                        @if($page->shopier_product_url)
                            <a href="{{ $page->shopier_product_url }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-violet-700 underline-offset-2 hover:underline">Aç</a>
                        @else
                            <span class="text-xs text-slate-400">Tanımlı değil</span>
                        @endif
                    </td>
                    <td>{{ $page->is_free ? 'Ücretsiz' : 'Ücretli' }}</td>
                    <td class="py-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <details class="group">
                                <summary class="btn-secondary cursor-pointer list-none px-3 py-1.5">
                                    Güncelle
                                </summary>
                                <div class="mt-3 w-[36rem] max-w-[90vw] rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                                    <form method="post" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="grid gap-3 md:grid-cols-2">
                                        @csrf
                                        @method('PUT')
                                        <input name="title" value="{{ $page->title }}" class="input-ui" placeholder="Başlık" required>
                                        <select name="category_id" class="input-ui" required>
                                            @foreach($categoryAssignmentOptions as $opt)
                                                <option value="{{ $opt['id'] }}" @selected($opt['id'] === $page->category_id)>{{ str_repeat('— ', $opt['depth']) }}{{ $opt['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <input type="number" step="0.01" name="price" value="{{ $page->price }}" placeholder="Fiyat" class="input-ui">
                                        <input type="url" name="shopier_product_url" value="{{ $page->shopier_product_url }}" placeholder="Shopier ürün linki (https://...)" class="input-ui md:col-span-2">
                                        <label class="input-ui">Kapak (PNG/JPG/JPEG) <input type="file" name="cover_image" accept=".png,.jpg,.jpeg" class="mt-1 block w-full text-sm"></label>
                                        <label class="input-ui md:col-span-2">Dosya (PDF/PNG/JPG/JPEG) <input type="file" name="pdf_file" accept=".pdf,.png,.jpg,.jpeg" class="mt-1 block w-full text-sm"></label>
                                        <textarea name="description" placeholder="Açıklama" class="input-ui md:col-span-2">{{ $page->description }}</textarea>

                                        <div class="inline-flex items-center gap-2 text-sm">
                                            <input type="hidden" name="is_free" value="0">
                                            <input type="checkbox" name="is_free" value="1" @checked($page->is_free)>
                                            <span>Ücretsiz</span>
                                        </div>
                                        <div class="inline-flex items-center gap-2 text-sm">
                                            <input type="hidden" name="is_featured" value="0">
                                            <input type="checkbox" name="is_featured" value="1" @checked($page->is_featured)>
                                            <span>Öne Çıkan</span>
                                        </div>

                                        <div class="md:col-span-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-slate-500">Yeni dosya yüklemezsen mevcut dosyalar korunur.</span>
                                            <button class="btn-primary px-4 py-2">Güncellemeyi Kaydet</button>
                                        </div>
                                    </form>
                                </div>
                            </details>

                            <form method="post" action="{{ route('admin.pages.destroy', $page) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger px-3 py-1.5">Sil</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
