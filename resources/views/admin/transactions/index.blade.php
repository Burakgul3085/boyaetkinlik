@extends('layouts.admin')

@section('title', 'Islemler')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Siparis ve Islem Listesi</h1>
    <div class="card mt-5 overflow-x-auto p-4">
        <table class="min-w-full text-sm">
            <thead><tr class="text-left text-slate-500"><th class="py-2">Siparis</th><th>Urun</th><th>E-posta</th><th>Tutar</th><th>Durum</th></tr></thead>
            <tbody>
            @foreach($transactions as $transaction)
                <tr class="border-t">
                    <td class="py-2">{{ $transaction->order_id }}</td>
                    <td>{{ $transaction->coloringPage->title }}</td>
                    <td>{{ $transaction->email }}</td>
                    <td>{{ number_format($transaction->paid_amount, 2) }} TL</td>
                    <td>
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $transaction->status === 'paid' ? 'bg-emerald-100 text-emerald-700' : ($transaction->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-rose-100 text-rose-700') }}">
                            {{ $transaction->status }}
                        </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>
@endsection
