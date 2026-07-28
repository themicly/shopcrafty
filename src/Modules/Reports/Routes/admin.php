<?php

/*
|--------------------------------------------------------------------------
| Reports — Admin Routes
|--------------------------------------------------------------------------
| Loaded under the "web" + "auth" group (see Themicly\Shopcrafty\Core\Module\ModuleServiceProvider)
| with an "/admin" prefix and "admin." name prefix by ReportsServiceProvider.
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

// Admin home / smart dashboard → /admin
Route::get('/', fn () => View::make('admin.dashboard'))->name('dashboard');

// Reports overview → /admin/reports (financials are owner-only, RPT-08)
Route::view('/reports', 'admin.reports.index')->middleware('can:manage-config')->name('reports.index');

// Focused reports — split out of the overview so each stays fast and readable
// (financials are owner-only, same as the overview, RPT-08).
Route::middleware('can:manage-config')->prefix('reports')->name('reports.')->group(function () {
    Route::view('/orders', 'admin.reports.orders')->name('orders');
    Route::view('/inventory', 'admin.reports.inventory')->name('inventory');
    Route::view('/customers', 'admin.reports.customers')->name('customers');
    Route::view('/coupons', 'admin.reports.coupons')->name('coupons');
    Route::view('/refunds', 'admin.reports.refunds')->name('refunds');
});
