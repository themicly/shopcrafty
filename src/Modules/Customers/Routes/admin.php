<?php

/*
|--------------------------------------------------------------------------
| Customers — Admin Routes
|--------------------------------------------------------------------------
| Loaded under the "web" + "auth" group with an "/admin" prefix and "admin."
| name prefix by CustomersServiceProvider.
*/

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;

Route::prefix('customers')->name('customers.')->middleware('can:manage-customers')->group(function () {
    Route::view('/', 'admin.customers.index')->name('index');
    Route::view('/create', 'admin.customers.create')->name('create');
    Route::get('/export', function () {
        $callback = function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Mobile', 'Status', 'Joined']);
            Customer::latest()->chunk(200, function ($customers) use ($out) {
                foreach ($customers as $c) {
                    fputcsv($out, [$c->name, $c->email, $c->mobile, $c->status, $c->created_at?->toDateString()]);
                }
            });
            fclose($out);
        };

        return response()->streamDownload($callback, 'customers-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    })->middleware('can:manage-config')->name('export'); // full PII dump is owner-only (CUS-06)
    Route::get('/{customer}', fn ($customer) => View::make('admin.customers.show', ['customerId' => (int) $customer]))
        ->name('show')->whereNumber('customer');
});
