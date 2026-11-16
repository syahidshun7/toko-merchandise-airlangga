<?php

use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Store\CartController;
use App\Http\Controllers\Store\StorefrontController;
use App\Http\Controllers\Store\WhatsAppController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('store.home')->block();
Route::get('/pre-order', [StorefrontController::class, 'preorder'])->name('store.preorder')->block();
Route::get('/katalog', [StorefrontController::class, 'catalog'])->name('store.catalog')->block();
Route::get('/kategori/{slug}', [StorefrontController::class, 'catalog'])->name('store.category')->block();
Route::get('/produk/{slug}', [StorefrontController::class, 'show'])->name('store.product')->block();

Route::prefix('keranjang')->name('store.cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index')->block();
    Route::post('/', [CartController::class, 'store'])->name('store')->block();
    Route::patch('/{item}', [CartController::class, 'update'])->where('item', '[0-9]{1,18}')->name('update')->block();
    Route::delete('/{item}', [CartController::class, 'destroy'])->where('item', '[0-9]{1,18}')->name('destroy')->block();
    Route::delete('/', [CartController::class, 'clear'])->name('clear')->block();
    Route::post('/whatsapp', WhatsAppController::class)->name('whatsapp')->block();
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:20,1,admin-login:')
            ->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
        Route::resource('banner', BannerController::class)->names('banners')->except('show');
        Route::resource('produk', ProductController::class)->parameters(['produk' => 'product'])->names('products');
        Route::resource('kategori', CategoryController::class)->parameters(['kategori' => 'category'])->names('categories')->except('show');
        Route::get('pengaturan-toko', [StoreSettingController::class, 'edit'])->name('settings.edit');
        Route::patch('pengaturan-toko', [StoreSettingController::class, 'update'])->name('settings.update');
        Route::get('profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profil', [ProfileController::class, 'update'])->middleware('throttle:20,1,admin-profile:')->name('profile.update');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});
