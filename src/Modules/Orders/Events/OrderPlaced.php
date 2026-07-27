<?php

namespace Themicly\Shopcrafty\Modules\Orders\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class OrderPlaced
{
    use Dispatchable;

    public function __construct(public Order $order) {}
}
