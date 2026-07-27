<?php

/*
|--------------------------------------------------------------------------
| Orders — Storefront Routes
|--------------------------------------------------------------------------
| Loaded automatically under the "web" middleware group by
| OrdersServiceProvider (see app/Core/Module/ModuleServiceProvider.php).
*/

use Illuminate\Support\Facades\Route;
use Themicly\Shopcrafty\Modules\Orders\Controllers\DownloadController;
use Themicly\Shopcrafty\Modules\Orders\Controllers\OrderController;

Route::view('/checkout', 'theme::checkout')->name('storefront.checkout');
Route::get('/order/{number}', [OrderController::class, 'thankyou'])->name('storefront.thankyou');
Route::get('/order/{number}/invoice', [OrderController::class, 'invoice'])->name('storefront.invoice');
Route::get('/track', [OrderController::class, 'track'])->name('storefront.track');

// Digital delivery: a per-order downloads page (order number = capability) and
// the signed/authed file stream it links to.
Route::get('/order/{number}/downloads', [DownloadController::class, 'order'])->name('storefront.order.downloads');
Route::get('/downloads/{grant}', [DownloadController::class, 'show'])->name('storefront.download');

// Gateway webhooks (CSRF-exempt; see bootstrap/app.php).
