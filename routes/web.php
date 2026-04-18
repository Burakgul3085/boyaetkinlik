<?php

use App\Http\Controllers\Admin\AdController as AdminAdController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ColoringPageController as AdminColoringPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\NewsletterController as AdminNewsletterController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\Admin\VisitorFeedbackController as AdminVisitorFeedbackController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ColoringPageController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ShopierController;
use App\Http\Controllers\VisitorFeedbackController;
use Illuminate\Support\Facades\Route;

$adminPath = trim((string) config('app.admin_path', 'yonetim-981400-panel'), '/');

Route::get('/', HomeController::class)->name('home');
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
Route::get('/shopier/{transaction}/redirect', [ShopierController::class, 'redirect'])->name('shopier.redirect');
Route::post('/shopier/callback', [ShopierController::class, 'callback'])->name('shopier.callback');
Route::get('/download/{token}', [DownloadController::class, 'paid'])->name('download.paid');
Route::get('/download/{token}/print', [DownloadController::class, 'printPaid'])->name('download.paid.print');

Route::prefix($adminPath)->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/giris', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/giris', [AdminAuthController::class, 'login'])->name('login.submit');
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
    });
});
