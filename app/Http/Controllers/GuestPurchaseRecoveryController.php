<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use App\Support\SiteMailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class GuestPurchaseRecoveryController extends Controller
{
    public function show()
    {
        return view('frontend.guest-purchase-recovery');
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'order_id' => 'nullable|string|max:64',
        ]);

        $email = $validated['email'];
        $orderId = isset($validated['order_id']) ? trim((string) $validated['order_id']) : '';

        $query = Transaction::query()
            ->where('status', 'paid')
            ->whereNull('user_id')
            ->where('email', $email)
            ->whereNotNull('download_token');

        if ($orderId !== '') {
            $query->where('order_id', $orderId);
        }

        $transactions = $query->with('coloringPage')->orderBy('id', 'desc')->get();

        if ($transactions->isNotEmpty()) {
            $appName = (string) (Setting::getValue('smtp_from_name', 'Boya Etkinlik') ?: 'Boya Etkinlik');
            $recoveryUrl = route('guest.purchase.recovery');

            $lines = [];
            foreach ($transactions as $tx) {
                $title = $tx->coloringPage?->title ?? 'Ürün';
                $url = route('download.paid', ['token' => $tx->download_token]);
                $lines[] = '<p style="margin:12px 0;padding:12px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0;">'
                    .'<strong>'.e($title).'</strong><br>'
                    .'<span style="font-size:12px;color:#64748b;">Sipariş: '.e($tx->order_id).'</span><br>'
                    .'<a href="'.e($url).'" style="display:inline-block;margin-top:8px;color:#4f46e5;font-weight:600;">İndirme sayfasına git</a>'
                    .'</p>';
            }

            $html = '<!doctype html>
<html lang="tr">
<head><meta charset="UTF-8"></head>
<body style="font-family:Inter,Arial,sans-serif;color:#0f172a;line-height:1.6;padding:16px;">
    <p>Merhaba,</p>
    <p><strong>'.e($appName).'</strong> üzerinden misafir olarak tamamladığınız ödemelere ait indirme bağlantıları aşağıdadır. Bu bağlantıları istediğiniz zaman kullanabilirsiniz.</p>
    <div>'.implode("\n", $lines).'</div>
    <p style="color:#64748b;font-size:14px;">Sayfayı kapattıysanız veya e-postayı kaybettiyseniz, aynı adresi kullanarak <a href="'.e($recoveryUrl).'">indirme linki tekrar gönder</a> sayfasından yeniden talep edebilirsiniz.</p>
    <p style="font-size:12px;color:#94a3b8;">Bu e-postayı siz talep etmediyseniz yok sayabilirsiniz.</p>
</body>
</html>';

            $textLines = [];
            foreach ($transactions as $tx) {
                $title = $tx->coloringPage?->title ?? 'Ürün';
                $url = route('download.paid', ['token' => $tx->download_token]);
                $textLines[] = "{$title} ({$tx->order_id})\n{$url}";
            }
            $text = "Merhaba,\n\n{$appName} misafir satın alımlarınız için indirme bağlantıları:\n\n"
                .implode("\n\n", $textLines)
                ."\n\nLinkleri tekrar almak için: {$recoveryUrl}\n";

            try {
                SiteMailer::send(
                    $email,
                    $appName.' — İndirme bağlantılarınız',
                    $html,
                    $text
                );
            } catch (Throwable $e) {
                Log::warning('Misafir indirme linki e-postası gönderilemedi', [
                    'email' => $email,
                    'exception' => $e->getMessage(),
                ]);
            }
        }

        return redirect()
            ->route('guest.purchase.recovery')
            ->with(
                'recovery_status',
                'Bu adrese ait kayıtlı bir misafir satın alımı varsa, indirme bağlantıları e-postanıza gönderildi. Gelen kutunuzu ve spam klasörünü kontrol edin.'
            );
    }
}
