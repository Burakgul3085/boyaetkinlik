<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,paid,failed'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Transaction::query()->with('coloringPage')->latest();

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (! empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        return view('admin.transactions.index', [
            'transactions' => $query->paginate(20)->withQueryString(),
            'filters' => [
                'status' => $validated['status'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
            ],
        ]);
    }

    public function approve(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('warning', 'Sadece beklemede işlemler onaylanabilir.');
        }

        $token = $transaction->download_token ?: Str::random(64);
        $payload = $transaction->payload ?? [];
        $payload['source'] = $payload['source'] ?? 'admin_manual_approval';
        $payload['approved_by_admin_at'] = now()->toDateTimeString();

        $transaction->update([
            'status' => 'paid',
            'download_token' => $token,
            'token_expires_at' => null,
            'payload' => $payload,
        ]);

        if ($transaction->user_id) {
            CartItem::query()
                ->where('user_id', $transaction->user_id)
                ->where('coloring_page_id', $transaction->coloring_page_id)
                ->delete();
        }

        return back()->with('success', 'İşlem onaylandı, ürün satın alınanlara aktarıldı.');
    }

    public function reject(Transaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('warning', 'Sadece beklemede işlemler reddedilebilir.');
        }

        $payload = $transaction->payload ?? [];
        $payload['source'] = $payload['source'] ?? 'admin_manual_rejection';
        $payload['rejected_by_admin_at'] = now()->toDateTimeString();

        $transaction->update([
            'status' => 'failed',
            'payload' => $payload,
        ]);

        return back()->with('success', 'İşlem reddedildi.');
    }
}
