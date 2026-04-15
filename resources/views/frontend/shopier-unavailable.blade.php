@extends('layouts.app')

@section('title', 'Odeme Gecici Olarak Kapali')

@section('content')
    <div class="mx-auto max-w-xl rounded-2xl bg-white p-6 shadow">
        <h1 class="text-xl font-bold text-slate-900">Odeme Modulu Henuz Aktif Degil</h1>
        <p class="mt-3 text-slate-600">
            Bu urun icin Shopier ayarlari henuz tamamlanmadi. Site canliya alindiginda odeme akisi aktif edilecektir.
        </p>

        <div class="mt-6 rounded-xl bg-slate-100 p-4 text-sm">
            <p><strong>Siparis:</strong> {{ $transaction->order_id }}</p>
            <p><strong>Tutar:</strong> {{ number_format($transaction->paid_amount, 2) }} TL</p>
            <p><strong>E-posta:</strong> {{ $transaction->email }}</p>
        </div>

        <a href="{{ route('products.show', $transaction->coloringPage) }}" class="mt-5 inline-block rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
            Urune Geri Don
        </a>
    </div>
@endsection
