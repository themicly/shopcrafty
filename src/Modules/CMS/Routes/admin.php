<?php

/*
|--------------------------------------------------------------------------
| CMS — Admin Routes
|--------------------------------------------------------------------------
| Loaded under the "web" + "auth" group with an "/admin" prefix and "admin."
| name prefix by CMSServiceProvider.
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

Route::prefix('cms')->name('cms.')->middleware('can:manage-content')->group(function () {
    Route::prefix('pages')->name('pages.')->group(function () {
        Route::view('/', 'admin.cms.pages.index')->name('index');
        Route::view('/create', 'admin.cms.pages.create')->name('create');
        Route::get('/{page}/edit', fn ($page) => View::make('admin.cms.pages.edit', ['pageId' => (int) $page]))
            ->name('edit')->whereNumber('page');
    });

    Route::view('/menus', 'admin.cms.menus')->name('menus.index');
});
