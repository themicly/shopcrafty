<?php

namespace Themicly\Shopcrafty\Modules\Orders\Events;

use Themicly\Shopcrafty\Modules\Orders\Models\Order;

/**
 * Fired once an order's digital lines have been fulfilled (grants created).
 * The Notifications module listens for this to send download links.
 */
class DigitalOrderReady
{
    public function __construct(public Order $order) {}
}
