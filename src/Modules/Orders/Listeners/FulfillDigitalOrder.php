<?php

namespace Themicly\Shopcrafty\Modules\Orders\Listeners;

use Themicly\Shopcrafty\Modules\Orders\Events\DigitalOrderReady;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderStatusChanged;
use Themicly\Shopcrafty\Modules\Orders\Services\DigitalFulfillmentService;

/**
 * Grants downloads the moment an order is paid. "confirmed" is when prepaid
 * payment has cleared (PaymentReconciler) and stock commits; "delivered" is
 * the belt-and-braces path for a COD digital order. Idempotent, so listening
 * to both is safe.
 */
class FulfillDigitalOrder
{
    public function __construct(protected DigitalFulfillmentService $fulfillment) {}

    public function handle(OrderStatusChanged $event): void
    {
        if (! in_array($event->to, ['confirmed', 'delivered'], true)) {
            return;
        }

        if ($this->fulfillment->fulfill($event->order) > 0) {
            event(new DigitalOrderReady($event->order));
        }
    }
}
