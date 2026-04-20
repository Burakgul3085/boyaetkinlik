<?php

namespace App\Http\Controllers;

use App\Models\ColoringPage;
use App\Models\PurchaseVerificationRequest;
use App\Support\SiteMailer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class PurchaseVerificationController extends Controller
{
    public function show()
    {
        return view('frontend.purchase-verification', [
            'pages' => ColoringPage::query()
                ->where('is_free', false)
                ->orderBy('title')
                ->get(['id', 'title']),
            'verification' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'coloring_page_id' => ['required', 'integer', 'exists:coloring_pages,id'],
            'order_no' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'customer_name' => ['nullable', 'string', 'max:160'],
        ]);

        $orderNo = Str::upper(trim((string) $data['order_no']));
        $email = Str::lower(trim((string) $data['email']));

        $existing = PurchaseVerificationRequest::query()
            ->where('coloring_page_id', $data['coloring_page_id'])
            ->where('order_no', $orderNo)
            ->where('email', $email)
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->route('purchase.verification.status', $existing->verification_token);
        }

        $verification = PurchaseVerificationRequest::query()->create([
            'coloring_page_id' => $data['coloring_page_id'],
            'order_no' => $orderNo,
            'email' => $email,
            'phone' => trim((string) ($data['phone'] ?? '')),
            'customer_name' => trim((string) ($data['customer_name'] ?? '')),
            'status' => 'pending',
            'verification_token' => Str::random(48),
            'payload' => ['source' => 'web-form'],
        ]);

        return redirect()
            ->route('purchase.verification.status', $verification->verification_token)
            ->with('success', 'Doğrulama talebiniz alındı. Onaylandığında indirme ve e-posta seçenekleri açılacaktır.');
    }

    public function status(string $token)
    {
        $verification = PurchaseVerificationRequest::query()
            ->with(['coloringPage', 'transaction'])
            ->where('verification_token', $token)
            ->firstOrFail();

        return view('frontend.purchase-verification', [
            'pages' => ColoringPage::query()
                ->where('is_free', false)
                ->orderBy('title')
                ->get(['id', 'title']),
            'verification' => $verification,
        ]);
    }

    public function sendEmail(Request $request, string $token): RedirectResponse
    {
        $verification = PurchaseVerificationRequest::query()
            ->with(['coloringPage', 'transaction'])
            ->where('verification_token', $token)
            ->firstOrFail();

        abort_if($verification->status !== 'approved' || ! $verification->transaction?->download_token, 403);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $downloadUrl = route('download.paid', ['token' => $verification->transaction->download_token]);
        $title = e((string) ($verification->coloringPage?->title ?? 'Ürün'));
        $appName = e(config('app.name', 'Boya Etkinlik'));

        $html = '<!doctype html>
<html lang="tr">
<head><meta charset="UTF-8"></head>
<body style="font-family:Inter,Arial,sans-serif;color:#0f172a;line-height:1.6;padding:16px;">
    <p>Merhaba,</p>
    <p><strong>'.$appName.'</strong> doğrulaması tamamlanan siparişiniz için indirme bağlantınız hazır.</p>
    <p><strong>Ürün:</strong> '.$title.'</p>
    <p><a href="'.e($downloadUrl).'" style="display:inline-block;margin:8px 0;padding:12px 20px;border-radius:12px;background:#4f46e5;color:#fff;font-weight:600;text-decoration:none;">İndirme sayfasına git</a></p>
</body>
</html>';

        $text = "Merhaba,\n\nDoğrulama tamamlandı. İndirme bağlantınız:\n{$downloadUrl}\n";

        try {
            SiteMailer::send(
                $data['email'],
                config('app.name', 'Boya Etkinlik').' — İndirme Bağlantınız',
                $html,
                $text
            );
        } catch (Throwable $e) {
            return back()->withErrors(['email' => 'E-posta gönderilemedi: '.$e->getMessage()]);
        }

        return back()->with('success', 'İndirme bağlantısı e-posta adresine gönderildi.');
    }
}
