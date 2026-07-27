<?php

namespace Themicly\Shopcrafty\Modules\Orders;

use Illuminate\Support\Facades\Event;
use Themicly\Shopcrafty\Core\Module\ModuleServiceProvider;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderStatusChanged;
use Themicly\Shopcrafty\Modules\Orders\Listeners\FulfillDigitalOrder;
use Themicly\Shopcrafty\Modules\Orders\Services\CartService;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentRegistry;

class OrdersServiceProvider extends ModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Orders';
    }

    protected function modulePath(): string
    {
        return __DIR__;
    }

    protected function registerModule(): void
    {
        // One cart per request (session-backed).
        $this->app->scoped(CartService::class);
        $this->app->singleton(PaymentRegistry::class);
    }

    protected function bootModule(): void
    {
        // Deliver digital goods the moment an order is paid (confirmed/delivered).
        Event::listen(OrderStatusChanged::class, [FulfillDigitalOrder::class, 'handle']);
    }
}
