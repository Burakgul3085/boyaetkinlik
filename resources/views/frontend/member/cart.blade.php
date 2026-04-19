@extends('layouts.app')

@section('title', 'Sepetim')

@section('content')
<section class="mx-auto max-w-5xl">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">Sepetim</h1>
        <p class="mt-1 text-sm text-slate-500">Satın almak istediğiniz ücretli ürünler burada listelenir.</p>

        @if(session('success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mt-6 space-y-3">
            @forelse($items as $item)
                <article class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $item->coloringPage->title }}</p>
                        <p class="text-sm text-slate-500">{{ $item->coloringPage->category?->name }} - {{ number_format((float) $item->coloringPage->price, 2) }} TL</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('products.show', $item->coloringPage) }}" class="btn-secondary">Ürüne Git</a>
                        <form method="post" action="{{ route('member.cart.checkout', $item) }}">
                            @csrf
                            <button class="btn-primary">Ödeme Yap</button>
                        </form>
                        <form method="post" action="{{ route('member.cart.remove', $item) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn-danger">Kaldır</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="rounded-xl border border-violet-100 bg-violet-50/40 p-4 text-sm text-slate-600">Sepetiniz boş.</p>
            @endforelse
        </div>
    </div>
</section>
@endsection
