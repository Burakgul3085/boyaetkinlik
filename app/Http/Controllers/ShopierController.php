<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ShopierController extends Controller
{
    public function redirect(Transaction $transaction)
    {
        abort_if($transaction->status !== 'pending', 403, 'Bu sipariş durumu ödeme için uygun değil.');

        $shopierConfig = $this->shopierConfig();
        $apiKey = $shopierConfig['api_key'];
        $apiSecret = $shopierConfig['api_secret'];
        $websiteIndex = $shopierConfig['website_index'];

        if ($apiKey === '' || $apiSecret === '' || $websiteIndex === '') {
            return view('frontend.shopier-unavailable', [
                'transaction' => $transaction,
            ]);
        }

        $payload = [
            'API_key' => $apiKey,
            'website_index' => $websiteIndex,
            'platform_order_id' => $transaction->order_id,
            'product_name' => $transaction->coloringPage->title,
            'product_type' => 1,
            'buyer_name' => 'Musteri',
            'buyer_surname' => 'Musteri',
            'buyer_email' => $transaction->email,
            'buyer_account_age' => 0,
            'buyer_id_nr' => '11111111111',
            'buyer_phone' => '05000000000',
            'billing_address' => 'Online Satis',
            'billing_city' => 'Istanbul',
            'billing_country' => 'TR',
            'billing_postcode' => '34000',
            'shipping_address' => 'Online Satis',
            'shipping_city' => 'Istanbul',
            'shipping_country' => 'TR',
            'shipping_postcode' => '34000',
            'total_order_value' => number_format((float) $transaction->paid_amount, 2, '.', ''),
            'currency' => 0, // 0 => TRY
            'platform' => 1, // 1 => web
            'is_in_frame' => 0,
            'current_language' => 0, // 0 => Turkish
            'modul_version' => 'laravel-11-basic',
            'random_nr' => (string) random_int(100000, 999999),
            'callback' => route('shopier.callback'),
        ];

        $dataToSign = $payload['random_nr'].$payload['platform_order_id'].$payload['total_order_value'].$payload['currency'];
        $payload['signature'] = base64_encode(hash_hmac('sha256', $dataToSign, $apiSecret, true));

        return view('frontend.shopier-redirect', [
            'transaction' => $transaction,
            'payload' => $payload,
            'shopierUrl' => $shopierConfig['endpoint'],
        ]);
    }

    public function callback(Request $request)
    {
        $orderId = $request->input('platform_order_id');
        $status = $request->input('status');

        $transaction = Transaction::query()->where('order_id', $orderId)->firstOrFail();
        $shopierConfig = $this->shopierConfig();
        $apiSecret = $shopierConfig['api_secret'];

        if ($apiSecret !== '') {
            $localData = $request->input('random_nr').$request->input('platform_order_id').$request->input('total_order_value').$request->input('currency');
            $localSignature = base64_encode(hash_hmac('sha256', $localData, $apiSecret, true));
            if (! hash_equals($localSignature, (string) $request->input('signature'))) {
                Log::warning('Shopier callback signature mismatch', ['order_id' => $orderId, 'payload' => $request->all()]);
                return response('invalid-signature', 400);
            }
        }

        if ($status === 'success' && $transaction->status !== 'paid') {
            $token = Str::random(64);
            $transaction->update([
                'status' => 'paid',
                'download_token' => $token,
                'token_expires_at' => Carbon::now()->addHours(24),
                'payload' => $request->all(),
            ]);

            $url = route('download.paid', ['token' => $token]);
            Mail::raw("Satın alma işleminiz tamamlandı. İndirme linkiniz: {$url}", function ($message) use ($transaction) {
                $message->to($transaction->email)->subject('Boyama Sayfası İndirme Linkiniz');
            });
        } elseif ($status !== 'success') {
            $transaction->update([
                'status' => 'failed',
                'payload' => $request->all(),
            ]);
        }

        return response('OK');
    }

    private function shopierConfig(): array
    {
        $endpoint = (string) (Setting::getValue('shopier_endpoint') ?: config('services.shopier.endpoint'));

        return [
            'api_key' => (string) (Setting::getValue('shopier_api_key') ?: config('services.shopier.api_key')),
            'api_secret' => (string) (Setting::getValue('shopier_api_secret') ?: config('services.shopier.api_secret')),
            'website_index' => (string) (Setting::getValue('shopier_website_index') ?: config('services.shopier.website_index')),
            'endpoint' => $endpoint !== '' ? $endpoint : 'https://www.shopier.com/ShowProduct/api_pay4.php',
        ];
    }
}
