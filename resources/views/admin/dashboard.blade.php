@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Genel Bakış</h1>
    <p class="mt-1 text-sm text-slate-500">Platformdaki içerik ve satış verilerini buradan takip edebilirsiniz.</p>

    <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="card p-5"><p class="text-sm text-slate-500">Kategori</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ $categoryCount }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Boyama Sayfası</p><p class="mt-2 text-3xl font-bold text-slate-900">{{ $pageCount }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Başarılı Ödeme</p><p class="mt-2 text-3xl font-bold text-emerald-600">{{ $paidTransactionCount }}</p></div>
        <div class="card p-5"><p class="text-sm text-slate-500">Toplam Ciro</p><p class="mt-2 text-3xl font-bold text-indigo-600">{{ number_format($totalRevenue, 2) }} TL</p></div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-2">
        <div class="card p-5">
            <p class="text-sm text-slate-500">Son 30 gün ciro</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($last30Revenue, 2) }} TL</p>
            <p class="mt-1 text-sm {{ $revenueChangeRate >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $revenueChangeRate >= 0 ? '+' : '' }}{{ number_format($revenueChangeRate, 1) }}% önceki 30 güne göre
            </p>
        </div>
        <div class="card p-5">
            <p class="text-sm text-slate-500">Son 30 gün başarılı ödeme</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $last30PaidCount }}</p>
            <p class="mt-1 text-sm {{ $paidChangeRate >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $paidChangeRate >= 0 ? '+' : '' }}{{ number_format($paidChangeRate, 1) }}% önceki 30 güne göre
            </p>
        </div>
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-3">
        <div class="card p-5 xl:col-span-2">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Aylık Ciro Trendi</h2>
                <span class="rounded-lg bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700">Son 6 ay</span>
            </div>
            <div class="mt-4 h-72">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-lg font-semibold text-slate-900">Ödeme Durum Dağılımı</h2>
            <div class="mt-4 h-72">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-4 xl:grid-cols-2">
        <div class="card p-5">
            <h2 class="text-lg font-semibold text-slate-900">Kategori Performansı</h2>
            <p class="mt-1 text-xs text-slate-500">Ciroya göre en iyi kategoriler</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-slate-500">
                            <th class="py-2">Kategori</th>
                            <th class="py-2">Sayfa</th>
                            <th class="py-2">Ödeme</th>
                            <th class="py-2">Ciro</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categoryPerformance as $item)
                            <tr class="border-t">
                                <td class="py-2 font-medium text-slate-800">{{ $item->name }}</td>
                                <td class="py-2">{{ $item->page_count }}</td>
                                <td class="py-2">{{ $item->paid_count }}</td>
                                <td class="py-2 font-semibold text-indigo-700">{{ number_format((float) $item->revenue, 2) }} TL</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-4 text-slate-500">Henüz kategori verisi yok.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-lg font-semibold text-slate-900">Son Başarılı Ödemeler</h2>
            <p class="mt-1 text-xs text-slate-500">Sistemdeki en güncel 8 başarılı ödeme</p>
            <div class="mt-4 space-y-3">
                @forelse($recentPaidTransactions as $transaction)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-900">{{ $transaction->coloringPage?->title ?? 'Silinmiş ürün' }}</p>
                            <span class="text-sm font-bold text-indigo-700">{{ number_format((float) $transaction->paid_amount, 2) }} TL</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-xs text-slate-500">
                            <span>{{ $transaction->email }}</span>
                            <span>{{ $transaction->created_at?->format('d.m.Y H:i') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-500">Henüz başarılı ödeme yok.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const monthlyLabels = @json($monthlyLabels);
        const monthlyRevenue = @json($monthlyRevenue);
        const statusCounts = @json($statusCounts);

        const revenueCtx = document.getElementById('revenueChart');
        if (revenueCtx) {
            new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Ciro (TL)',
                        data: monthlyRevenue,
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 4,
                        pointHoverRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (value) => value + ' TL'
                            }
                        }
                    }
                }
            });
        }

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Başarılı', 'Beklemede', 'Başarısız', 'Diğer'],
                    datasets: [{
                        data: [statusCounts.paid, statusCounts.pending, statusCounts.failed, statusCounts.other],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#64748b'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '68%'
                }
            });
        }
    </script>
@endsection
