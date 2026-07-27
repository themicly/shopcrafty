<?php

namespace Themicly\Shopcrafty\Modules\Orders\Actions;

use Themicly\Shopcrafty\Modules\Notifications\Actions\SendNotification;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Models\OrderReturn;

/**
 * Customer-initiated return request. Notifies the owner so they can approve and
 * refund from the order screen.
 */
class RequestReturn
{
    /**
     * @param  array<int, array{order_item_id:int, qty:int}>  $items  per-line return quantities (empty = whole-order return)
     * @param  array<int, string>  $photos  stored photo paths/urls
     */
    public function handle(Order $order, ?int $customerId, string $reason, array $items = [], array $photos = []): OrderReturn
    {
        $return = OrderReturn::create([
            'order_id' => $order->id,
            'customer_id' => $customerId,
            'reason' => $reason,
            'photos' => $photos ?: null,
            'status' => 'requested',
        ]);

        foreach ($items as $line) {
            $qty = (int) ($line['qty'] ?? 0);

            if ($qty > 0 && ! empty($line['order_item_id'])) {
                $return->items()->create([
                    'order_item_id' => (int) $line['order_item_id'],
                    'qty' => $qty,
                ]);
            }
        }

        app(SendNotification::class)->handle('order.return-requested', [
            'order' => ['number' => $order->number],
            'customer' => ['name' => $order->shippingAddress?->name ?? 'Customer'],
        ]);

        return $return;
    }
}
