<?php

namespace Themicly\Shopcrafty\Modules\Orders\Services;

use Themicly\Shopcrafty\Modules\Orders\Models\Order;

/**
 * Marks an order paid (from a gateway webhook/callback) and moves it forward:
 * a pending order becomes confirmed, which also commits its stock. Idempotent.
 */
class PaymentReconciler
{
    public function __construct(protected OrderStatusService $status, protected PaymentLogger $logger) {}

    public function markPaid(Order $order): void
    {
        // Idempotent: an already-paid order is a no-op, but still logged so a
        // duplicate webhook/return leaves an audit trail (applied = false).
        if ($order->payment_status === 'paid') {
            $this->logger->record((string) $order->payment_method, 'mark_paid', true, [
                'order' => $order,
                'message' => 'Order already paid — reconciliation skipped (idempotent).',
                'context' => ['order_number' => $order->number, 'idempotent' => true, 'applied' => false],
            ]);

            return;
        }

        $order->update(['payment_status' => 'paid']);

        if ($order->status === 'pending') {
            $this->status->change($order, 'confirmed', 'Payment received', 'system');
        }

        $this->logger->record((string) $order->payment_method, 'mark_paid', true, [
            'order' => $order,
            'message' => 'Order marked paid.',
            'context' => ['order_number' => $order->number, 'idempotent' => false, 'applied' => true],
        ]);
    }
}
