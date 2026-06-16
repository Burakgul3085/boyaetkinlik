@extends('layouts.admin')

@section('title', 'Boyama Sayfaları')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Boyama Sayfası Yönetimi</h1>
    <p class="mt-1 text-sm text-slate-500">Toplam {{ number_format($pages->total(), 0, ',', '.') }} kayıt — sayfa başına 30 listelenir.</p>

    <form method="post" action="{{ route('admin.pages.store') }}" enctype="multipart/form-data" class="card mt-5 grid gap-3 p-5 md:grid-cols-2">
        @csrf
        <input name="title" placeholder="Başlık" class="input-ui" required>
        <select name="category_id" class="input-ui" required>
            @foreach($categoryAssignmentOptions as $opt)
                <option value="{{ $opt['id'] }}">{{ \App\Models\Category::adminSelectOptionLabel($opt['depth'], $opt['name']) }}</option>
            @endforeach
        </select>
        <input type="number" step="0.01" name="price" placeholder="Fiyat" class="input-ui">
        <input type="url" name="shopier_product_url" placeholder="Shopier ürün linki (https://...)" class="input-ui md:col-span-2">
        <label class="input-ui">Kapak (PNG/JPG/JPEG) <input type="file" name="cover_image" accept=".png,.jpg,.jpeg" class="mt-1 block w-full text-sm"></label>
        <label class="input-ui md:col-span-2">Dosya (PDF/PNG/JPG/JPEG) <input type="file" name="pdf_file" accept=".pdf,.png,.jpg,.jpeg" class="mt-1 block w-full text-sm" required></label>
        <textarea name="description" placeholder="Açıklama" class="input-ui md:col-span-2"></textarea>
        @include('admin.pages._meta-fields')
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_free" value="1"> Ücretsiz</label>
        <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1"> Öne Çıkan</label>
        <button class="btn-primary">Kaydet</button>
    </form>

    <form method="get" action="{{ route('admin.pages.index') }}" class="card mt-6 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-[16rem] flex-1">
            <label for="admin-pages-filter" class="mb-1 block text-xs font-medium text-slate-600">Başlığa göre ara</label>
            <input
                id="admin-pages-filter"
                type="search"
                name="q"
                value="{{ $search }}"
                autocomplete="off"
                placeholder="Başlığa göre filtrele..."
                class="input-ui w-full"
            >
        </div>
        <button type="submit" class="btn-primary">Ara</button>
        @if($search !== '')
            <a href="{{ route('admin.pages.index') }}" class="btn-secondary">Temizle</a>
        @endif
    </form>

    <div class="card mt-4 overflow-x-auto p-4">
        <table class="min-w-full text-sm">
            <thead>
                <tr class="text-left text-slate-500">
                    <th class="py-2 pr-2">Başlık</th>
                    <th class="pr-2">Kategori</th>
                    <th class="pr-2">Fiyat</th>
                    <th class="pr-2">Shopier</th>
                    <th class="pr-2">Tip</th>
                    <th class="pr-2">Tarih</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse($pages as $page)
                <tr class="border-t border-slate-100">
                    <td class="py-2 pr-2 font-medium text-slate-900">{{ $page->title }}</td>
                    <td class="pr-2 text-slate-600">{{ $page->category?->name ?? '—' }}</td>
                    <td class="pr-2 whitespace-nowrap">{{ number_format($page->price, 2) }} TL</td>
                    <td class="pr-2">
                        @if($page->shopier_product_url)
                            <a href="{{ $page->shopier_product_url }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-violet-700 underline-offset-2 hover:underline">Aç</a>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="pr-2">
                        @if($page->is_free)
                            <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-800">Ücretsiz</span>
                        @else
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700">Ücretli</span>
                        @endif
                        @if($page->is_featured)
                            <span class="ml-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-800">Öne çıkan</span>
                        @endif
                    </td>
                    <td class="pr-2 whitespace-nowrap text-xs text-slate-500">{{ $page->created_at?->format('d.m.Y') }}</td>
                    <td class="text-right whitespace-nowrap">
                        <a href="{{ route('admin.pages.edit', $page) }}" class="btn-secondary inline-flex px-3 py-1.5 text-xs">Düzenle</a>
                        <form method="post" action="{{ route('admin.pages.destroy', $page) }}" class="mt-1 inline-block" onsubmit="return confirm('Bu boyama sayfası silinsin mi?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="py-10 text-center text-slate-500">
                        @if($search !== '')
                            Aramanızla eşleşen boyama sayfası bulunamadı.
                        @else
                            Henüz boyama sayfası yok.
                        @endif
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if($pages->hasPages())
            <div class="mt-4 border-t border-slate-100 pt-4">
                {{ $pages->links() }}
            </div>
        @endif
    </div>
@endsection
