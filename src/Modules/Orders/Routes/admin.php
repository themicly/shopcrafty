<?php

/*
|--------------------------------------------------------------------------
| Orders — Admin Routes
|--------------------------------------------------------------------------
| Loaded under the "web" + "auth" group with an "/admin" prefix and "admin."
| name prefix by OrdersServiceProvider.
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

Route::prefix('orders')->name('orders.')->middleware('can:manage-orders')->group(function () {
    Route::view('/', 'admin.orders.index')->name('index');
    Route::view('/create', 'admin.orders.create')->name('create');
    Route::get('/export', function () {
        $callback = function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Number', 'Date', 'Status', 'Payment status', 'Method', 'Customer', 'Total']);
            Order::with('shippingAddress')->latest('placed_at')->chunk(200, function ($orders) use ($out) {
                foreach ($orders as $o) {
                    fputcsv($out, [$o->number, $o->placed_at?->toDateString(), $o->status, $o->payment_status, $o->payment_method, $o->shippingAddress?->name, format_money($o->grand_total)]);
                }
            });
            fclose($out);
        };

        return response()->streamDownload($callback, 'orders-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    })->name('export');
    Route::view('/cod-queue', 'admin.orders.cod-queue')->name('cod-queue');
    Route::view('/payment-log', 'admin.orders.payment-log')->name('payment-log');
    Route::get('/{order}', fn ($order) => View::make('admin.orders.show', ['orderId' => (int) $order]))
        ->name('show')->whereNumber('order');
    Route::get('/{order}/invoice', fn ($order) => View::make('admin.orders.invoice', [
        'order' => Order::with(['items', 'addresses', 'shippingAddress'])->findOrFail($order),
    ]))->name('invoice')->whereNumber('order');
    Route::get('/{order}/packing-slip', fn ($order) => View::make('admin.orders.packing-slip', [
        'order' => Order::with(['items', 'shippingAddress'])->findOrFail($order),
    ]))->name('packing-slip')->whereNumber('order');
});
