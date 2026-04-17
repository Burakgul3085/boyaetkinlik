@extends('layouts.app')

@section('title', $coloringPage->title)

@section('content')
    <div class="grid gap-6 lg:grid-cols-12">
        <div class="lg:col-span-8 card p-5 lg:p-6">
            <img
                src="{{ route('products.preview-image', $coloringPage) }}"
                class="w-full rounded-xl object-cover"
                alt="{{ $coloringPage->title }}"
                onerror="this.onerror=null;this.src='https://placehold.co/900x600/e2e8f0/334155?text=Boya+Onizleme';"
            >
            <h1 class="mt-4 text-2xl font-bold text-slate-900">{{ $coloringPage->title }}</h1>
            <p class="mt-2 text-slate-600">{{ $coloringPage->description }}</p>
            <div class="mt-6">
                {!! \App\Models\Setting::getValue('ads_product_detail') ?: '<div class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500">Ürün detay reklam alanı</div>' !!}
            </div>
        </div>

        <div class="lg:col-span-4 card p-5" x-data="{ open: false }">
            <p class="text-sm text-slate-500">Kategori: {{ $coloringPage->category->name }}</p>
            <p class="mt-3 text-2xl font-bold {{ $coloringPage->is_free ? 'text-emerald-600' : 'text-indigo-600' }}">
                {{ $coloringPage->is_free ? 'Ücretsiz' : number_format($coloringPage->price, 2).' TL' }}
            </p>

            @if($coloringPage->is_free)
                <a href="{{ route('products.download.free', $coloringPage) }}" class="btn-primary mt-5 w-full">
                    İndir ({{ strtoupper(pathinfo($coloringPage->pdf_path, PATHINFO_EXTENSION) ?: 'PDF') }})
                </a>
                <button onclick="window.print()" class="btn-secondary mt-3 w-full">Yazdır</button>
            @else
                <button @click="open = true" class="btn-primary mt-5 w-full">Satın Al (Shopier)</button>
                <div x-show="open" class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <form method="post" action="{{ route('products.buy', $coloringPage) }}">
                        @csrf
                        <label class="mb-2 block text-sm font-medium">E-posta</label>
                        <input type="email" name="email" required class="input-ui">
                        <button class="btn-primary mt-3 w-full">Shopier'e Git</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
