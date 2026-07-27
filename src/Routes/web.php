<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Http\Controllers\Auth\LoginController;
use Themicly\Shopcrafty\Http\Middleware\ApplyThemePreview;
use Themicly\Shopcrafty\Http\Middleware\SetStorefrontLocale;
use Themicly\Shopcrafty\Modules\Themes\Controllers\StorefrontController;

Route::middleware(['web', SetStorefrontLocale::class])->group(function () {
    Route::get('/', [StorefrontController::class, 'home'])
        ->middleware(ApplyThemePreview::class)
        ->name('shopcrafty.storefront');

    Route::view('/admin/login', 'shopcrafty::auth.login')
        ->middleware('guest')
        ->name('login');

    Route::view('/admin/forgot-password', 'shopcrafty::auth.forgot-password')
        ->middleware('guest')
        ->name('password.request');

    Route::post('/admin/login', [LoginController::class, 'store'])
        ->middleware('throttle:auth')
        ->name('login.store');

    Route::post('/admin/logout', [LoginController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');

    Route::get('/admin/search', function (Request $request, AddonRegistry $addons) {
        return response()->json([]);
    })->middleware(['auth', 'can:access-admin'])->name('admin.search');

    Route::view('/admin', 'shopcrafty::admin.dashboard')
        ->middleware(['auth', 'can:access-admin'])
        ->name('admin.dashboard');
});
