@extends('layouts.app')

@section('title', 'Shopier Yonlendirme')

@section('content')
    <div class="mx-auto max-w-xl rounded-2xl bg-white p-6 shadow">
        <h1 class="text-xl font-bold text-slate-900">Shopier'e Yonlendiriliyorsunuz</h1>
        <p class="mt-3 text-slate-600">
            Guvenli odeme sayfasina yonlendirme icin asagidaki butona tiklayin.
        </p>

        <div class="mt-6 rounded-xl bg-slate-100 p-4 text-sm">
            <p><strong>Siparis:</strong> {{ $transaction->order_id }}</p>
            <p><strong>Tutar:</strong> {{ number_format($transaction->paid_amount, 2) }} TL</p>
            <p><strong>E-posta:</strong> {{ $transaction->email }}</p>
        </div>

        <form id="shopier-form" method="post" action="{{ $shopierUrl }}" class="mt-5">
            @foreach($payload as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700">
                Shopier Odeme Sayfasina Git
            </button>
        </form>
        <p class="mt-2 text-xs text-slate-500">Butona basmazsaniz 2 saniye icinde otomatik yonlendirme olur.</p>
    </div>

    <script>
        setTimeout(function () {
            const form = document.getElementById('shopier-form');
            if (form) form.submit();
        }, 2000);
    </script>
@endsection
