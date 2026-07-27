<?php

namespace Themicly\Shopcrafty\Modules\Orders\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class OrderStatusChanged
{
    use Dispatchable;

    public function __construct(
        public Order $order,
        public string $from,
        public string $to,
    ) {}
}
