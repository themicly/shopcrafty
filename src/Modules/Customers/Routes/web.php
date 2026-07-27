<?php

/*
|--------------------------------------------------------------------------
| Customers — Storefront Routes
|--------------------------------------------------------------------------
| Loaded automatically under the "web" middleware group by
| CustomersServiceProvider (see app/Core/Module/ModuleServiceProvider.php).
*/

use Illuminate\Support\Facades\Route;
use Themicly\Shopcrafty\Modules\Customers\Controllers\AccountController;
use Themicly\Shopcrafty\Modules\Customers\Controllers\CustomerAuthController;
use Themicly\Shopcrafty\Modules\Customers\Controllers\CustomerPasswordController;

// Help / support hub — public so guests can reach it too.
Route::view('/support', 'theme::support')->name('storefront.support');

Route::middleware('guest:customer')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('storefront.login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:auth');
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('storefront.register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:auth');

    Route::get('/forgot-password', [CustomerPasswordController::class, 'requestForm'])->name('storefront.password.request');
    Route::post('/forgot-password', [CustomerPasswordController::class, 'sendLink'])->middleware('throttle:auth')->name('storefront.password.email');
    Route::get('/reset-password/{token}', [CustomerPasswordController::class, 'resetForm'])->name('storefront.password.reset');
    Route::post('/reset-password', [CustomerPasswordController::class, 'update'])->middleware('throttle:auth')->name('storefront.password.update');
});

Route::middleware('auth:customer')->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('storefront.logout');

    Route::prefix('account')->name('storefront.account.')->group(function () {
        Route::get('/', [AccountController::class, 'orders'])->name('index');
        Route::get('/downloads', [AccountController::class, 'downloads'])->name('downloads');
        Route::get('/orders/{number}', [AccountController::class, 'orderDetail'])->name('orders.show');
        Route::post('/orders/{number}/reorder', [AccountController::class, 'reorder'])->name('orders.reorder');
        Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');

        // GDPR self-service tools — only reachable when the owner enables them
        // (Settings → Privacy). The controller 404s when the toggle is off.
        Route::get('/data-export', [AccountController::class, 'dataExport'])->name('data-export');
        Route::delete('/delete', [AccountController::class, 'deleteAccount'])->name('delete');
    });
});
