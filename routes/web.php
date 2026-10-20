<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryFeeSettingsController;
use App\Http\Controllers\Admin\OrderManagementController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::resource('products', AdminProductController::class);
    Route::get('/settings/delivery-fee', [DeliveryFeeSettingsController::class, 'edit'])->name('settings.delivery-fee.edit');
    Route::put('/settings/delivery-fee', [DeliveryFeeSettingsController::class, 'update'])->name('settings.delivery-fee.update');
    Route::get('/orders', [OrderManagementController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderManagementController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/confirm', [OrderManagementController::class, 'confirm'])->name('orders.confirm');
    Route::post('/orders/{order}/reject', [OrderManagementController::class, 'reject'])->name('orders.reject');
    Route::post('/orders/{order}/mark-cod-paid', [OrderManagementController::class, 'markCodPaid'])->name('orders.mark-cod-paid');
    Route::post('/orders/{order}/complete', [OrderManagementController::class, 'complete'])->name('orders.complete');
});

Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/products/{product}', [CatalogController::class, 'show'])->name('products.show');

Route::middleware('auth')->group(function () {
    Route::get('/checkout/{product}', [OrderController::class, 'create'])->name('checkout.create');
    Route::post('/checkout/{product}', [OrderController::class, 'store'])->name('checkout.store');
    Route::post('/products/{product}/reviews', [ProductReviewController::class, 'store'])->name('products.reviews.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/simulate-payment', [OrderController::class, 'simulatePayment'])->name('orders.simulate-payment');
});

Route::get('/', function () {
    return view('welcome');
});
