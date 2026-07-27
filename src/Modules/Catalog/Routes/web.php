<?php

/*
|--------------------------------------------------------------------------
| Catalog — Storefront Routes
|--------------------------------------------------------------------------
| Loaded automatically under the "web" middleware group by
| CatalogServiceProvider (see app/Core/Module/ModuleServiceProvider.php).
*/

use Illuminate\Support\Facades\Route;
use Themicly\Shopcrafty\Modules\Catalog\Controllers\StorefrontController;

Route::get('/shop', [StorefrontController::class, 'shop'])->name('storefront.shop');

// Product comparison — session-backed, works for guests and customers alike.
Route::get('/search', [StorefrontController::class, 'search'])->name('storefront.search');
Route::get('/search/suggest', [StorefrontController::class, 'suggest'])->name('storefront.search.suggest');
Route::get('/category/{slug}', [StorefrontController::class, 'category'])->name('storefront.category');
Route::get('/product/{slug}', [StorefrontController::class, 'show'])->name('storefront.product');
