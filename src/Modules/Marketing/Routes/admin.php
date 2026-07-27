<?php

/*
|--------------------------------------------------------------------------
| Marketing — Admin Routes
|--------------------------------------------------------------------------
| Loaded under the "web" + "auth" group with an "/admin" prefix and "admin."
| name prefix by MarketingServiceProvider.
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

Route::prefix('marketing')->name('marketing.')->middleware('can:manage-marketing')->group(function () {
    Route::prefix('coupons')->name('coupons.')->group(function () {
        Route::view('/', 'admin.marketing.coupons.index')->name('index');
        Route::view('/create', 'admin.marketing.coupons.create')->name('create');
        Route::get('/{coupon}/edit', fn ($coupon) => View::make('admin.marketing.coupons.edit', ['couponId' => (int) $coupon]))
            ->name('edit')->whereNumber('coupon');
    });

    Route::view('/newsletter', 'admin.marketing.newsletter')->name('newsletter.index');
    Route::view('/campaigns', 'admin.marketing.campaigns')->name('campaigns.index');
});
