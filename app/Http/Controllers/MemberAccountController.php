<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\PurchaseSupportTicket;
use App\Models\Transaction;
use App\Support\FileFormatDownloadService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class MemberAccountController extends Controller
{
    public function account(Request $request)
    {
        return view('frontend.member.account', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:6', 'max:72', 'confirmed'],
        ]);

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->name = trim($data['first_name'].' '.$data['last_name']);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('success', 'Hesap bilgileriniz güncellendi.');
    }

    public function cart(Request $request)
    {
        $items = CartItem::query()
            ->with('coloringPage.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('frontend.member.cart', ['items' => $items]);
    }

    public function addToCart(Request $request)
    {
        $data = $request->validate([
            'coloring_page_id' => ['required', 'integer', 'exists:coloring_pages,id'],
        ]);

        CartItem::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'coloring_page_id' => $data['coloring_page_id'],
        ]);

        return redirect()->route('member.cart')->with('success', 'Ürün sepetinize eklendi.');
    }

    public function checkoutFromCart(Request $request, CartItem $cartItem)
    {
        abort_if($cartItem->user_id !== $request->user()->id, 403);

        $page = $cartItem->coloringPage()->firstOrFail();
        abort_if($page->is_free, 422, 'Ücretsiz ürünler için ödeme gerekmez.');
        $shopierUrl = trim((string) ($page->shopier_product_url ?? ''));
        abort_if($shopierUrl === '', 422, 'Bu ürün için Shopier bağlantısı henüz tanımlanmadı.');

        $alreadyPurchased = Transaction::query()
            ->where('status', 'paid')
            ->where('user_id', $request->user()->id)
            ->where('coloring_page_id', $page->id)
            ->exists();

        if ($alreadyPurchased) {
            return redirect()->route('member.purchases')->with('success', 'Bu ürün zaten satın alınmış görünüyor.');
        }

        return redirect()->away($shopierUrl);
    }

    public function removeFromCart(Request $request, CartItem $cartItem)
    {
        abort_if($cartItem->user_id !== $request->user()->id, 403);
        $cartItem->delete();

        return back()->with('success', 'Ürün sepetten kaldırıldı.');
    }

    public function purchases(Request $request)
    {
        $user = $request->user();

        $purchases = Transaction::query()
            ->with('coloringPage.category')
            ->where('status', 'paid')
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere(function ($legacyQuery) use ($user) {
                        $legacyQuery->whereNull('user_id')
                            ->where('email', $user->email);
                    });
            })
            ->latest()
            ->get();

        $supportByTransaction = PurchaseSupportTicket::query()
            ->where('user_id', $user->id)
            ->with('transaction.coloringPage')
            ->latest()
            ->get()
            ->groupBy(fn (PurchaseSupportTicket $t) => $t->transaction_id === null ? '_general' : (string) $t->transaction_id);

        return view('frontend.member.purchases', [
            'purchases' => $purchases,
            'supportByTransaction' => $supportByTransaction,
        ]);
    }

    public function downloadPurchased(Request $request, Transaction $transaction, FileFormatDownloadService $downloadService)
    {
        $user = $request->user();

        abort_if($transaction->status !== 'paid', 403);
        abort_if(
            (int) ($transaction->user_id ?? 0) !== $user->id && ! ((int) ($transaction->user_id ?? 0) === 0 && $transaction->email === $user->email),
            403
        );

        $requestedFormat = $downloadService->normalizeFormat($request->query('format'));
        $sourceExtension = $downloadService->sourceExtension($transaction->coloringPage->pdf_path);
        $availableFormats = $downloadService->downloadOptions($sourceExtension);

        if ($requestedFormat === null) {
            return view('frontend.member.purchase-download-options', [
                'transaction' => $transaction,
                'downloadFormats' => $availableFormats,
            ]);
        }

        if (! in_array($requestedFormat, $availableFormats, true)) {
            abort(422, 'Geçersiz dosya formatı.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $downloadService->download(
            $disk,
            $transaction->coloringPage->pdf_path,
            $transaction->coloringPage->title,
            $requestedFormat
        );
    }

    public function sendPurchasedToEmail(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        abort_if($transaction->status !== 'paid', 403);
        abort_if(
            (int) ($transaction->user_id ?? 0) !== $user->id && ! ((int) ($transaction->user_id ?? 0) === 0 && $transaction->email === $user->email),
            403
        );

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $shareUrl = URL::temporarySignedRoute(
            'member.purchases.shared-download',
            now()->addDays(30),
            ['transaction' => $transaction->id]
        );

        $title = $transaction->coloringPage->title;
        Mail::raw("Satın aldığınız \"{$title}\" dosyası için indirme bağlantısı:\n{$shareUrl}\n\nBağlantı 30 gün geçerlidir.", function ($message) use ($data, $title) {
            $message->to($data['email'])->subject("Satın alınan dosya bağlantısı - {$title}");
        });

        return back()->with('success', 'İndirme bağlantısı belirtilen e-posta adresine gönderildi.');
    }

    public function sharedDownload(Request $request, Transaction $transaction, FileFormatDownloadService $downloadService)
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_if($transaction->status !== 'paid', 403);

        $requestedFormat = $downloadService->normalizeFormat($request->query('format'));
        $sourceExtension = $downloadService->sourceExtension($transaction->coloringPage->pdf_path);
        $availableFormats = $downloadService->downloadOptions($sourceExtension);

        if ($requestedFormat === null) {
            return view('frontend.member.purchase-download-options', [
                'transaction' => $transaction,
                'downloadFormats' => $availableFormats,
                'sharedDownload' => true,
            ]);
        }

        if (! in_array($requestedFormat, $availableFormats, true)) {
            abort(422, 'Geçersiz dosya formatı.');
        }

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $downloadService->download(
            $disk,
            $transaction->coloringPage->pdf_path,
            $transaction->coloringPage->title,
            $requestedFormat
        );
    }
}
