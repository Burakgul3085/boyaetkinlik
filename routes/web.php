<?php

use App\Http\Controllers\Admin\AdController as AdminAdController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ColoringPageController as AdminColoringPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TransactionController as AdminTransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColoringPageController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShopierController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
Route::get('/yonetim', fn () => redirect()->route(auth()->check() ? 'admin.dashboard' : 'admin.login'));
Route::get('/admin-giris', fn () => redirect()->route(auth()->check() ? 'admin.dashboard' : 'admin.login'));
Route::get('/kategoriler/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/boyama/{coloringPage}', [ColoringPageController::class, 'show'])->name('products.show');
Route::post('/boyama/{coloringPage}/buy', [ColoringPageController::class, 'buy'])->name('products.buy');
Route::get('/boyama/{coloringPage}/free-download', [ColoringPageController::class, 'downloadFree'])->name('products.download.free');
Route::get('/shopier/{transaction}/redirect', [ShopierController::class, 'redirect'])->name('shopier.redirect');
Route::post('/shopier/callback', [ShopierController::class, 'callback'])->name('shopier.callback');
Route::get('/download/{token}', [DownloadController::class, 'paid'])->name('download.paid');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
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
    });
});
