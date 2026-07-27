<?php

/*
|--------------------------------------------------------------------------
| Marketing — Storefront Routes
|--------------------------------------------------------------------------
| Loaded automatically under the "web" middleware group by
| MarketingServiceProvider (see app/Core/Module/ModuleServiceProvider.php).
*/

use Illuminate\Support\Facades\Route;
use Themicly\Shopcrafty\Modules\Marketing\Controllers\NewsletterController;

Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
    ->name('storefront.newsletter.unsubscribe');
