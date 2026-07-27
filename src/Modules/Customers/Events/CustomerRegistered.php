<?php

namespace Themicly\Shopcrafty\Modules\Customers\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;

class CustomerRegistered
{
    use Dispatchable;

    public function __construct(public Customer $customer) {}
}
