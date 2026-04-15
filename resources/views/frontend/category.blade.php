@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <div class="card-soft p-6">
        <h1 class="text-2xl font-bold text-slate-900">{{ $category->name }}</h1>
        <p class="mt-2 text-slate-600">{{ $category->description }}</p>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($category->coloringPages as $page)
            <a href="{{ route('products.show', $page) }}" class="card p-4 transition hover:shadow-md">
                <img src="{{ $page->cover_image_path ? asset('storage/'.$page->cover_image_path) : 'https://placehold.co/600x400/e2e8f0/334155?text=Boya+Sayfasi' }}" class="h-40 w-full rounded-xl object-cover" alt="{{ $page->title }}">
                <p class="mt-3 font-semibold">{{ $page->title }}</p>
                <p class="text-sm {{ $page->is_free ? 'text-emerald-600' : 'text-indigo-600' }}">
                    {{ $page->is_free ? 'Ucretsiz' : number_format($page->price, 2).' TL' }}
                </p>
            </a>
        @empty
            <p class="card p-4 text-slate-500">Bu kategoride henuz boyama sayfasi yok.</p>
        @endforelse
    </div>
@endsection
