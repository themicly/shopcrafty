<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Listeners;

use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Notifications\Actions\SendNotification;
use Themicly\Shopcrafty\Modules\Orders\Events\DigitalOrderReady;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderPlaced;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderStatusChanged;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

/**
 * Translates order domain events into notification event keys. A thin adapter —
 * the pipeline (SendNotification) owns channels, recipients, templates and
 * gateways; this only builds the message context from an Order.
 */
class OrderNotifications
{
    public function __construct(protected SendNotification $notifier) {}

    public function placed(OrderPlaced $event): void
    {
        $this->notifier->handle('order.placed', $this->context($event->order));
    }

    public function statusChanged(OrderStatusChanged $event): void
    {
        $key = match ($event->to) {
            'confirmed' => 'order.confirmed',
            'shipped' => 'order.shipped',
            'delivered' => 'order.delivered',
            'cancelled' => 'order.cancelled',
            default => null,
        };

        if ($key) {
            $this->notifier->handle($key, $this->context($event->order));
        }
    }

    /** Digital goods are ready — send the buyer their download link. */
    public function digitalReady(DigitalOrderReady $event): void
    {
        $context = $this->context($event->order);
        $context['downloads'] = ['url' => url('/order/'.$event->order->number.'/downloads')];

        $this->notifier->handle('order.digital-ready', $context);
    }

    /** @return array<string, mixed> */
    protected function context(Order $order): array
    {
        $order->loadMissing('shippingAddress');
        $address = $order->shippingAddress;

        $customer = [
            'name' => $address?->name ?? 'Customer',
            'email' => $address?->email,
            'phone' => $address?->phone,
        ];

        if ($order->customer_id && ($record = Customer::find($order->customer_id))) {
            $customer = [
                'name' => $record->name,
                'email' => $record->email ?: $address?->email,
                'phone' => $record->mobile ?: $address?->phone,
            ];
        }

        return [
            'order' => [
                'number' => $order->number,
                'total' => format_money($order->grand_total),
                'status' => $order->status,
                'tracking_number' => $order->tracking_number ?? '',
                'carrier' => $order->carrier ?? '',
            ],
            'customer' => $customer,
            'track' => ['url' => url('/order/'.$order->number)],
        ];
    }
}
