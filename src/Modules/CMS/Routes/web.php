<?php

/*
|--------------------------------------------------------------------------
| CMS — Storefront Routes
|--------------------------------------------------------------------------
| Loaded automatically under the "web" middleware group by
| CMSServiceProvider (see app/Core/Module/ModuleServiceProvider.php).
*/

use Illuminate\Support\Facades\Route;
use Themicly\Shopcrafty\Modules\CMS\Controllers\StorefrontController;

Route::get('/pages/{slug}', [StorefrontController::class, 'page'])->name('storefront.page');
Route::get('/lp/{slug}', [StorefrontController::class, 'page'])->name('storefront.landing');
