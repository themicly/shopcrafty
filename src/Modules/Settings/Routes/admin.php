<?php

/*
|--------------------------------------------------------------------------
| Settings — Admin Routes
|--------------------------------------------------------------------------
| Loaded under the "web" + "auth" group with an "/admin" prefix and "admin."
| name prefix by SettingsServiceProvider.
*/

use Illuminate\Support\Facades\Route;

// Component gallery (dev only — not registered in production).
if (! app()->environment('production')) {
    Route::view('/_ui', 'admin.ui-kitchen-sink')->name('ui');
}

// Media library — shared by content-adjacent modules (product photos, CMS banners).
Route::view('/media', 'admin.media')->name('media.index')->middleware('can:manage-content');

// Store settings — owner-only (staff can't change store configuration).
Route::prefix('settings')->name('settings.')->middleware('can:manage-config')->group(function () {
    Route::view('/', 'admin.settings.general')->name('index');
    Route::view('/appearance', 'admin.settings.appearance')->name('appearance');
    Route::view('/localization', 'admin.settings.localization')->name('localization');
    Route::view('/shipping', 'admin.settings.shipping')->name('shipping');
    Route::view('/locations', 'admin.settings.locations')->name('locations');
    Route::view('/payments', 'admin.settings.payments')->name('payments');
    Route::view('/tax', 'admin.settings.tax')->name('tax');
    Route::view('/staff', 'admin.settings.staff')->name('staff');
    Route::view('/maintenance', 'admin.settings.maintenance')->name('maintenance');
    Route::view('/demo', 'admin.settings.demo')->name('demo');
    Route::view('/audit', 'admin.settings.audit')->name('audit');
    Route::view('/addons', 'admin.settings.addons')->name('addons');
});
