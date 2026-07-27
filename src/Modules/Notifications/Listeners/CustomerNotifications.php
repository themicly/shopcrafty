<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Listeners;

use Themicly\Shopcrafty\Modules\Customers\Events\CustomerRegistered;
use Themicly\Shopcrafty\Modules\Notifications\Actions\SendNotification;

/**
 * Translates customer domain events into notification event keys.
 */
class CustomerNotifications
{
    public function __construct(protected SendNotification $notifier) {}

    public function welcome(CustomerRegistered $event): void
    {
        $customer = $event->customer;

        $this->notifier->handle('customer.welcome', [
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
                'phone' => $customer->mobile,
            ],
        ]);
    }
}
