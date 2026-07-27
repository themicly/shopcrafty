<?php

namespace Themicly\Shopcrafty\Modules\Customers;

use Illuminate\Support\Facades\Event;
use Themicly\Shopcrafty\Core\Module\ModuleServiceProvider;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderPlaced;

class CustomersServiceProvider extends ModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Customers';
    }

    protected function modulePath(): string
    {
        return __DIR__;
    }

    protected function bootModule(): void
    {
        // Keep last_order_at fresh when a customer places an order (event-driven,
        // no direct coupling from the Orders module).
        Event::listen(OrderPlaced::class, function (OrderPlaced $event) {
            if ($event->order->customer_id) {
                Customer::whereKey($event->order->customer_id)->update(['last_order_at' => now()]);
            }
        });
    }
}
