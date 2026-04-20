<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseVerificationRequest;
use App\Models\Transaction;
use App\Support\SiteMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class PurchaseVerificationController extends Controller
{
    public function index()
    {
        return view('admin.purchase-verifications.index', [
            'requests' => PurchaseVerificationRequest::query()
                ->with(['coloringPage', 'transaction', 'reviewer'])
                ->latest()
                ->get(),
        ]);
    }

    public function approve(Request $request, PurchaseVerificationRequest $purchaseVerification): RedirectResponse
    {
        if ($purchaseVerification->status !== 'pending') {
            return back()->with('warning', 'Bu talep zaten işlenmiş.');
        }

        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $page = $purchaseVerification->coloringPage()->firstOrFail();
        $baseOrder = 'SHP-'.preg_replace('/[^A-Z0-9]/', '', Str::upper($purchaseVerification->order_no));
        $baseOrder = $baseOrder !== 'SHP-' ? $baseOrder : 'SHP-'.Str::upper(Str::random(8));
        $orderId = $baseOrder;
        $counter = 1;

        while (Transaction::query()->where('order_id', $orderId)->exists()) {
            $orderId = $baseOrder.'-'.$counter;
            $counter++;
        }

        $token = Str::random(64);
        $transaction = Transaction::query()->create([
            'user_id' => null,
            'coloring_page_id' => $page->id,
            'order_id' => $orderId,
            'email' => $purchaseVerification->email,
            'paid_amount' => $page->price,
            'status' => 'paid',
            'download_token' => $token,
            'token_expires_at' => null,
            'payload' => [
                'source' => 'manual_shopier_verification',
                'shopier_order_no' => $purchaseVerification->order_no,
                'phone' => $purchaseVerification->phone,
                'customer_name' => $purchaseVerification->customer_name,
            ],
        ]);

        $purchaseVerification->update([
            'status' => 'approved',
            'transaction_id' => $transaction->id,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        $downloadUrl = route('download.paid', ['token' => $token]);

        try {
            SiteMailer::send(
                $purchaseVerification->email,
                config('app.name', 'Boya Etkinlik').' — Sipariş doğrulaması tamamlandı',
                '<p>Merhaba, sipariş doğrulamanız tamamlandı.</p><p><a href="'.e($downloadUrl).'">İndirme bağlantısına git</a></p>',
                "Merhaba,\nSipariş doğrulamanız tamamlandı.\nİndirme: {$downloadUrl}\n"
            );
        } catch (Throwable $e) {
            return back()->with('warning', 'Talep onaylandı ancak e-posta gönderilemedi: '.$e->getMessage());
        }

        return back()->with('success', 'Talep onaylandı ve indirme bağlantısı müşteriye gönderildi.');
    }

    public function reject(Request $request, PurchaseVerificationRequest $purchaseVerification): RedirectResponse
    {
        if ($purchaseVerification->status !== 'pending') {
            return back()->with('warning', 'Bu talep zaten işlenmiş.');
        }

        $data = $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ]);

        $purchaseVerification->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_note' => $data['admin_note'],
        ]);

        return back()->with('success', 'Talep reddedildi.');
    }
}
