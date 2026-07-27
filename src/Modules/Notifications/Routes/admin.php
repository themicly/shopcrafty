<?php

/*
|--------------------------------------------------------------------------
| Notifications — Admin Routes
|--------------------------------------------------------------------------
| Loaded under "web" + "auth" + "can:access-admin" with an "/admin" prefix and
| "admin." name prefix by NotificationsServiceProvider (see ModuleServiceProvider).
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

// Owner-only — notification gateways hold provider credentials.
Route::prefix('notifications')->name('notifications.')->middleware('can:manage-config')->group(function () {
    Route::view('/', 'notifications::admin.events')->name('index');
    Route::redirect('/gateways', '/admin/notifications/gateways/email');
    Route::get('/gateways/{channel}', fn (string $channel) => View::make('notifications::admin.gateways', ['channel' => $channel]))
        ->where('channel', 'email|sms')
        ->name('gateways');
    Route::view('/logs', 'notifications::admin.logs')->name('logs');
});
