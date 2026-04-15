<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\ColoringPage;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'categoryCount' => Category::query()->count(),
            'pageCount' => ColoringPage::query()->count(),
            'paidTransactionCount' => Transaction::query()->where('status', 'paid')->count(),
            'totalRevenue' => Transaction::query()->where('status', 'paid')->sum('paid_amount'),
        ]);
    }
}
