<?php

use App\Http\Controllers\Admin\AdController as AdminAdController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ColoringPageController as AdminColoringPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController as AdminMemberController;
use App\Http\Controllers\MemberPurchaseSupportController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\PurchaseVerificationController as AdminPurchaseVerificationController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\VisitorFeedbackController as AdminVisitorFeedbackController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ColoringPageController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\GuestPurchaseRecoveryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MemberAccountController;
use App\Http\Controllers\MemberAuthController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PurchaseVerificationController;
use App\Http\Controllers\ShopierController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\VisitorFeedbackController;
use Illuminate\Support\Facades\Route;

$adminPath = trim((string) config('app.admin_path', 'yonetim-981400-panel'), '/');

Route::get('/', HomeController::class)->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/login', fn () => abort(404))->name('login');
Route::get('/yonetim', fn () => abort(404));
Route::get('/admin-giris', fn () => abort(404));
Route::any('/admin', fn () => abort(404));
Route::any('/admin/{any}', fn () => abort(404))->where('any', '.*');
Route::get('/kategoriler/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/iletisim', [ContactController::class, 'show'])->name('contact.show');
Route::post('/iletisim', [ContactController::class, 'send'])->name('contact.send');
Route::post('/iletisim/whatsapp', [ContactController::class, 'sendWhatsApp'])->name('contact.whatsapp');
Route::post('/e-bulten/kayit', [NewsletterController::class, 'store'])->name('newsletter.store');
Route::post('/ziyaretci-geri-bildirim', [VisitorFeedbackController::class, 'store'])
    ->middleware('throttle:8,1')
    ->name('visitor-feedback.store');
Route::get('/boyama/{coloringPage}', [ColoringPageController::class, 'show'])->name('products.show');
Route::get('/boyama/{coloringPage}/preview-image', [ColoringPageController::class, 'previewImage'])->name('products.preview-image');
Route::post('/boyama/{coloringPage}/buy', [ColoringPageController::class, 'buy'])->name('products.buy');
Route::get('/boyama/{coloringPage}/free-download', [ColoringPageController::class, 'downloadFree'])->name('products.download.free');
Route::get('/boyama/{coloringPage}/free-print', [ColoringPageController::class, 'printFree'])->name('products.print.free');
Route::post('/boyama/{coloringPage}/ucretsiz-eposta', [ColoringPageController::class, 'sendFreeToEmail'])
    ->middleware('throttle:6,1')
    ->name('products.free.email');
Route::get('/shopier/{transaction}/redirect', [ShopierController::class, 'redirect'])->name('shopier.redirect');
Route::post('/shopier/callback', [ShopierController::class, 'callback'])->name('shopier.callback');
Route::get('/download/{token}', [DownloadController::class, 'paid'])->name('download.paid');
Route::get('/download/{token}/print', [DownloadController::class, 'printPaid'])->name('download.paid.print');

Route::get('/indirme-linki-tekrar', [GuestPurchaseRecoveryController::class, 'show'])->name('guest.purchase.recovery');
Route::post('/indirme-linki-tekrar', [GuestPurchaseRecoveryController::class, 'send'])
    ->middleware('throttle:6,1')
    ->name('guest.purchase.recovery.submit');
Route::get('/satin-alim-dogrula', [PurchaseVerificationController::class, 'show'])->name('purchase.verification.show');
Route::post('/satin-alim-dogrula', [PurchaseVerificationController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('purchase.verification.store');
Route::get('/satin-alim-dogrula/{token}', [PurchaseVerificationController::class, 'status'])->name('purchase.verification.status');
Route::post('/satin-alim-dogrula/{token}/eposta', [PurchaseVerificationController::class, 'sendEmail'])
    ->middleware('throttle:6,1')
    ->name('purchase.verification.email');

Route::middleware('guest')->group(function () {
    Route::get('/uye-ol', [MemberAuthController::class, 'showRegister'])->name('member.register');
    Route::post('/uye-ol', [MemberAuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('member.register.submit');
    Route::get('/giris-yap', [MemberAuthController::class, 'showLogin'])->name('member.login');
    Route::post('/giris-yap', [MemberAuthController::class, 'login'])->name('member.login.submit');
    Route::get('/sifremi-unuttum', [MemberAuthController::class, 'showForgotPassword'])->name('member.forgot-password');
    Route::post('/sifremi-unuttum', [MemberAuthController::class, 'sendForgotPassword'])
        ->middleware('throttle:5,1')
        ->name('member.forgot-password.submit');
});

Route::middleware('member')->group(function () {
    Route::get('/uyelik-dogrulama', [MemberAuthController::class, 'showRegisterVerify'])->name('member.register.verify.form');
    Route::post('/uyelik-dogrulama', [MemberAuthController::class, 'verifyRegister'])
        ->middleware('throttle:24,1')
        ->name('member.register.verify.submit');

    Route::get('/giris-dogrulama', [MemberAuthController::class, 'showLoginVerify'])->name('member.login.verify.form');
    Route::post('/giris-dogrulama', [MemberAuthController::class, 'verifyLogin'])
        ->middleware('throttle:24,1')
        ->name('member.login.verify.submit');
});

Route::middleware(['member', 'member.code'])->group(function () {
    Route::get('/hesabim', [MemberAccountController::class, 'account'])->name('member.account');
    Route::post('/hesabim', [MemberAccountController::class, 'update'])->name('member.account.update');

    Route::get('/sepetim', [MemberAccountController::class, 'cart'])->name('member.cart');
    Route::post('/sepetim', [MemberAccountController::class, 'addToCart'])->name('member.cart.add');
    Route::post('/sepetim/{cartItem}/odeme', [MemberAccountController::class, 'checkoutFromCart'])->name('member.cart.checkout');
    Route::delete('/sepetim/{cartItem}', [MemberAccountController::class, 'removeFromCart'])->name('member.cart.remove');

    Route::get('/satin-alinanlar', [MemberAccountController::class, 'purchases'])->name('member.purchases');
    Route::get('/satin-alinanlar/{transaction}/indir', [MemberAccountController::class, 'downloadPurchased'])->name('member.purchases.download');
    Route::post('/satin-alinanlar/{transaction}/eposta-gonder', [MemberAccountController::class, 'sendPurchasedToEmail'])->name('member.purchases.email');
    Route::post('/satin-alinanlar/destek', [MemberPurchaseSupportController::class, 'store'])
        ->middleware('throttle:8,1')
        ->name('member.purchases.support.store');
});

Route::get('/satin-alinanlar/paylas/{transaction}/indir', [MemberAccountController::class, 'sharedDownload'])
    ->name('member.purchases.shared-download');

Route::post('/hesabim/cikis', [MemberAuthController::class, 'logout'])
    ->middleware('member')
    ->name('member.logout');

Route::prefix($adminPath)->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/giris', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/giris', [AdminAuthController::class, 'login'])->name('login.submit');
        Route::get('/kayit', [AdminAuthController::class, 'showRegister'])->name('register');
        Route::post('/kayit', [AdminAuthController::class, 'register'])
            ->middleware('throttle:8,1')
            ->name('register.submit');
        Route::get('/kayit-dogrulama', [AdminAuthController::class, 'showRegisterVerify'])->name('register.verify.form');
        Route::post('/kayit-dogrulama', [AdminAuthController::class, 'verifyRegister'])
            ->middleware('throttle:24,1')
            ->name('register.verify.submit');
        Route::get('/sifremi-unuttum', [AdminAuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('/sifremi-unuttum', [AdminAuthController::class, 'sendForgotPassword'])
            ->middleware('throttle:5,1')
            ->name('forgot-password.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/kod-dogrulama', [AdminAuthController::class, 'showVerify'])->name('verify.form');
        Route::post('/kod-dogrulama', [AdminAuthController::class, 'verify'])
            ->middleware('throttle:24,1')
            ->name('verify.submit');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    });

    Route::middleware(['auth', 'admin', 'admin.code'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/uyeler', [AdminMemberController::class, 'index'])->name('members.index');
        Route::get('/uyeler/{user}', [AdminMemberController::class, 'show'])->name('members.show');
        Route::post('/uyeler/{user}/destek/{ticket}/yanit', [AdminMemberController::class, 'replyPurchaseSupport'])
            ->middleware('throttle:30,1')
            ->name('members.support.reply');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/pages', [AdminColoringPageController::class, 'index'])->name('pages.index');
        Route::post('/pages', [AdminColoringPageController::class, 'store'])->name('pages.store');
        Route::put('/pages/{coloringPage}', [AdminColoringPageController::class, 'update'])->name('pages.update');
        Route::delete('/pages/{coloringPage}', [AdminColoringPageController::class, 'destroy'])->name('pages.destroy');

        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

        Route::get('/ads', [AdminAdController::class, 'index'])->name('ads.index');
        Route::post('/ads', [AdminAdController::class, 'update'])->name('ads.update');

        Route::get('/transactions', [AdminTransactionController::class, 'index'])->name('transactions.index');
        Route::post('/transactions/{transaction}/approve', [AdminTransactionController::class, 'approve'])->name('transactions.approve');
        Route::post('/transactions/{transaction}/reject', [AdminTransactionController::class, 'reject'])->name('transactions.reject');
        Route::get('/purchase-verifications', [AdminPurchaseVerificationController::class, 'index'])->name('purchase-verifications.index');
        Route::post('/purchase-verifications/{purchaseVerification}/approve', [AdminPurchaseVerificationController::class, 'approve'])->name('purchase-verifications.approve');
        Route::post('/purchase-verifications/{purchaseVerification}/reject', [AdminPurchaseVerificationController::class, 'reject'])->name('purchase-verifications.reject');

        Route::get('/newsletter', [AdminNewsletterController::class, 'index'])->name('newsletter.index');
        Route::post('/newsletter/send', [AdminNewsletterController::class, 'sendToSubscriber'])->name('newsletter.send');
        Route::post('/newsletter/send-bulk', [AdminNewsletterController::class, 'sendBulk'])->name('newsletter.send.bulk');
        Route::delete('/newsletter/{subscriber}', [AdminNewsletterController::class, 'destroy'])->name('newsletter.destroy');

        Route::get('/visitor-feedback', [AdminVisitorFeedbackController::class, 'index'])->name('visitor-feedback.index');
        Route::post('/visitor-feedback/ayarlar', [AdminVisitorFeedbackController::class, 'updateSettings'])->name('visitor-feedback.settings');
        Route::post('/visitor-feedback/{visitorFeedback}/onayla', [AdminVisitorFeedbackController::class, 'approve'])->name('visitor-feedback.approve');
        Route::post('/visitor-feedback/{visitorFeedback}/eposta-gorunurluk', [AdminVisitorFeedbackController::class, 'toggleEmail'])->name('visitor-feedback.toggle-email');
        Route::post('/visitor-feedback/{visitorFeedback}/yanit', [AdminVisitorFeedbackController::class, 'updateReply'])->name('visitor-feedback.reply');
        Route::post('/visitor-feedback/{visitorFeedback}/yanit-yayinla', [AdminVisitorFeedbackController::class, 'publishReply'])->name('visitor-feedback.publish-reply');
        Route::delete('/visitor-feedback/{visitorFeedback}', [AdminVisitorFeedbackController::class, 'destroy'])->name('visitor-feedback.destroy');

        Route::get('/admin-yonetimi', [AdminManagementController::class, 'index'])->name('admin-users.index');
        Route::post('/admin-yonetimi/yeni-admin', [AdminManagementController::class, 'store'])->name('admin-users.create.submit');
        Route::get('/admin-yonetimi/yeni-admin/dogrulama', [AdminManagementController::class, 'showCreateVerify'])->name('admin-users.create.verify.form');
        Route::post('/admin-yonetimi/yeni-admin/dogrulama', [AdminManagementController::class, 'verifyCreate'])
            ->middleware('throttle:24,1')
            ->name('admin-users.create.verify.submit');
        Route::put('/admin-yonetimi/{user}/profil', [AdminManagementController::class, 'updateProfile'])->name('admin-users.profile.update');
        Route::post('/admin-yonetimi/{user}/sifre', [AdminManagementController::class, 'updatePassword'])->name('admin-users.password.update');
        Route::delete('/admin-yonetimi/{user}', [AdminManagementController::class, 'destroy'])->name('admin-users.destroy');
    });
});

Route::get('/_mail-fpm-test/{secret}', function (string $secret) {
    $configured = trim((string) config('app.mail_web_test_secret', ''));

    if ($configured === '') {
        return response(
            "MAIL_WEB_TEST_SECRET .env içinde tanımlı değil; bu yüzden önce 404 görüyordunuz.\n\n"
            ."Sunucuda:\n"
            ."1) nano /var/www/boyaetkinlik/.env\n"
            ."2) Satır ekle (örnek): MAIL_WEB_TEST_SECRET=benim-gizli-anahtar\n"
            ."3) php artisan config:clear && sudo systemctl restart php8.4-fpm\n"
            ."4) Tarayıcı: http://IP/_mail-fpm-test/benim-gizli-anahtar\n\n"
            ."URL’deki son parça, .env’deki değer ile birebir aynı olmalı.\n"
            ."Test bitince MAIL_WEB_TEST_SECRET satırını silin.\n",
            503,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    if (! hash_equals($configured, $secret)) {
        abort(404);
    }

    $to = trim((string) (\App\Models\Setting::getValue('contact_email', '') ?? ''));
    if ($to === '') {
        return response(
            "HATA: contact_email bos\nSAPI: ".PHP_SAPI."\ndisable_functions: ".ini_get('disable_functions'),
            500,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    try {
        \App\Support\SiteMailer::send(
            $to,
            'Web (PHP-FPM) SMTP test',
            '<p>Tarayıcı / FPM üzerinden test.</p>',
            'Tarayıcı / FPM üzerinden test.'
        );

        return response(
            "OK: FPM ile SiteMailer gönderdi. Gelen kutusu: {$to}\nSAPI: ".PHP_SAPI,
            200,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    } catch (\Throwable $e) {
        return response(
            'HATA: '.$e->getMessage()."\nSınıf: ".get_class($e)."\nSAPI: ".PHP_SAPI
            ."\ndisable_functions: ".ini_get('disable_functions'),
            500,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }
})->where('secret', '[^/]+');
