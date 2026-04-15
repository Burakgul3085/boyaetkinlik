@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Genel Bakis</h1>
    <p class="mt-1 text-sm text-slate-500">Platformdaki icerik ve satis verilerini buradan takip edebilirsiniz.</p>
    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5"><p class="text-sm text-slate-500">Kategori</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ $categoryCount }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Boyama Sayfasi</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ $pageCount }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Basarili Odeme</p><p class="mt-2 text-3xl font-bold text-emerald-600">{{ $paidTransactionCount }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Toplam Ciro</p><p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($totalRevenue, 2) }} TL</p></div>
    </div>
@endsection
