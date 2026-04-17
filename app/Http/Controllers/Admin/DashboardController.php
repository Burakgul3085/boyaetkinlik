<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ColoringPage;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $monthlyRevenueRaw = Transaction::query()
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month_key")
            ->selectRaw('COUNT(*) as paid_count')
            ->selectRaw('COALESCE(SUM(paid_amount), 0) as revenue')
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get()
            ->keyBy('month_key');

        $monthNames = [
            1 => 'Ocak', 2 => 'Subat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayis', 6 => 'Haziran',
            7 => 'Temmuz', 8 => 'Agustos', 9 => 'Eylul', 10 => 'Ekim', 11 => 'Kasim', 12 => 'Aralik',
        ];

        $monthlyLabels = [];
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('Y-m');
            $record = $monthlyRevenueRaw->get($monthKey);

            $monthlyLabels[] = ($monthNames[(int) $date->format('n')] ?? $date->format('M')).' '.$date->format('y');
            $monthlyRevenue[] = (float) ($record->revenue ?? 0);
        }

        $statusCounts = Transaction::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $categoryPerformance = Category::query()
            ->leftJoin('coloring_pages', 'categories.id', '=', 'coloring_pages.category_id')
            ->leftJoin('transactions', function ($join) {
                $join->on('coloring_pages.id', '=', 'transactions.coloring_page_id')
                    ->where('transactions.status', '=', 'paid');
            })
            ->select('categories.name')
            ->selectRaw('COUNT(DISTINCT coloring_pages.id) as page_count')
            ->selectRaw('COUNT(transactions.id) as paid_count')
            ->selectRaw('COALESCE(SUM(transactions.paid_amount), 0) as revenue')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();

        $last30Start = now()->subDays(29)->startOfDay();
        $prev30Start = now()->subDays(59)->startOfDay();
        $prev30End = now()->subDays(30)->endOfDay();

        $last30Revenue = Transaction::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$last30Start, now()])
            ->sum('paid_amount');

        $prev30Revenue = Transaction::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$prev30Start, $prev30End])
            ->sum('paid_amount');

        $last30PaidCount = Transaction::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$last30Start, now()])
            ->count();

        $prev30PaidCount = Transaction::query()
            ->where('status', 'paid')
            ->whereBetween('created_at', [$prev30Start, $prev30End])
            ->count();

        $revenueChangeRate = $prev30Revenue > 0
            ? (($last30Revenue - $prev30Revenue) / $prev30Revenue) * 100
            : ($last30Revenue > 0 ? 100 : 0);

        $paidChangeRate = $prev30PaidCount > 0
            ? (($last30PaidCount - $prev30PaidCount) / $prev30PaidCount) * 100
            : ($last30PaidCount > 0 ? 100 : 0);

        $recentPaidTransactions = Transaction::query()
            ->with('coloringPage:id,title')
            ->where('status', 'paid')
            ->latest()
            ->take(8)
            ->get();

        return view('admin.dashboard', [
            'categoryCount' => Category::query()->count(),
            'pageCount' => ColoringPage::query()->count(),
            'paidTransactionCount' => Transaction::query()->where('status', 'paid')->count(),
            'totalRevenue' => Transaction::query()->where('status', 'paid')->sum('paid_amount'),
            'monthlyLabels' => $monthlyLabels,
            'monthlyRevenue' => $monthlyRevenue,
            'statusCounts' => [
                'paid' => (int) ($statusCounts['paid'] ?? 0),
                'pending' => (int) ($statusCounts['pending'] ?? 0),
                'failed' => (int) ($statusCounts['failed'] ?? 0),
                'other' => (int) $statusCounts->except(['paid', 'pending', 'failed'])->sum(),
            ],
            'categoryPerformance' => $categoryPerformance,
            'last30Revenue' => $last30Revenue,
            'last30PaidCount' => $last30PaidCount,
            'revenueChangeRate' => $revenueChangeRate,
            'paidChangeRate' => $paidChangeRate,
            'recentPaidTransactions' => $recentPaidTransactions,
        ]);
    }
}
