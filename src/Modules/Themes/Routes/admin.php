<?php

/*
|--------------------------------------------------------------------------
| Themes — Admin Routes
|--------------------------------------------------------------------------
| Loaded under the "web" + "auth" group with an "/admin" prefix and "admin."
| name prefix by ThemesServiceProvider.
*/

use Illuminate\Support\Facades\Route;

Route::prefix('themes')->name('themes.')->middleware('can:manage-content')->group(function () {
    Route::view('/', 'admin.themes.index')->name('index');
    Route::view('/sections', 'admin.themes.sections')->name('sections');
    Route::view('/customize', 'admin.themes.customize')->name('customize');
    Route::view('/text', 'admin.themes.text')->name('text');
    // Site-wide shopper features — owner-only, like the Settings area.
    Route::view('/settings', 'admin.themes.settings')->middleware('can:manage-config')->name('settings');
});

// Banners (storefront slider + promo) — top-level admin.banners.*
Route::view('/banners', 'admin.themes.banners')->middleware('can:manage-content')->name('banners.index');
