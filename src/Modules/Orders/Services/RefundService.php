<?php

namespace Themicly\Shopcrafty\Modules\Orders\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Themicly\Shopcrafty\Modules\Notifications\Actions\SendNotification;
use Themicly\Shopcrafty\Modules\Orders\Actions\RestockOrder;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Models\OrderRefund;
use Themicly\Shopcrafty\Modules\Orders\Models\OrderReturn;

/**
 * Records refunds against an order and reconciles the order's payment status.
 * Gateway-driven capture/refund is a future extension; today a refund is
 * recorded (money moved out-of-band) and optionally restocks the order.
 */
class RefundService
{
    public function __construct(protected RestockOrder $restock) {}

    /**
     * Issue a refund against an order. Amount is clamped to what's still
     * refundable. Optionally restocks the order's inventory.
     */
    /**
     * @param  array<int, int>|null  $restockQuantities  order_item_id => qty for a partial restock
     */
    public function refund(Order $order, int $amount, bool $restock = false, ?string $reason = null, ?int $returnId = null, ?int $actorId = null, ?array $restockQuantities = null): OrderRefund
    {
        return DB::transaction(function () use ($order, $amount, $restock, $reason, $returnId, $actorId, $restockQuantities) {
            // Locked so concurrent refunds against the same order serialize
            // instead of both reading a stale refundableAmount().
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            // You can only refund money that was actually captured — an unpaid COD
            // order has nothing to refund.
            if ($order->capturedAmount() <= 0) {
                throw new RuntimeException('This order has no captured payment to refund.');
            }

            $amount = max(0, min($amount, $order->refundableAmount()));

            // Already fully refunded — idempotent no-op, no extra ledger row.
            if ($amount <= 0) {
                return $order->refunds()->first() ?? throw new RuntimeException('Nothing left to refund.');
            }

            $refund = OrderRefund::create([
                'order_id' => $order->id,
                'return_id' => $returnId,
                'amount' => $amount,
                'reason' => $reason,
                'restocked' => $restock,
                'created_by' => $actorId,
            ]);

            $order->increment('refunded_total', $amount);
            $order->refresh();
            $order->update(['payment_status' => $this->paymentStatusFor($order)]);

            if ($restock) {
                // A return supplies the exact returned quantities. A manual refund
                // has no line detail, so only a *full* refund releases all stock —
                // a partial manual refund must not restock the whole order.
                if ($restockQuantities !== null) {
                    $this->restock->handle($order, $restockQuantities);
                } elseif ($order->refunded_total >= $order->capturedAmount()) {
                    $this->restock->handle($order);
                }
            }

            app(SendNotification::class)->handle('order.refunded', [
                'order' => [
                    'number' => $order->number,
                    'total' => format_money($order->grand_total),
                    'refund_amount' => format_money($amount),
                ],
                'customer' => $this->customer($order),
            ]);

            return $refund;
        });
    }

    /**
     * Approve a return request: refund only the returned lines and restock only
     * the returned quantities. When $amount is null it's computed from the
     * return's line items (falling back to the full refundable amount for a
     * return that carries no structured items).
     */
    public function approveReturn(OrderReturn $return, ?int $amount = null, bool $restock = true, ?string $note = null, ?int $actorId = null): OrderRefund
    {
        return DB::transaction(function () use ($return, $amount, $restock, $note, $actorId) {
            // Locked so two concurrent "Approve" clicks can't both pass the
            // isOpen() check and double-refund/double-restock the same return.
            $return = OrderReturn::query()->lockForUpdate()->findOrFail($return->id);

            if (! $return->isOpen()) {
                throw new RuntimeException('This return has already been resolved.');
            }

            $return->loadMissing('items.orderItem', 'order');

            $quantities = [];
            $computed = 0;

            foreach ($return->items as $line) {
                $orderItem = $line->orderItem;

                if (! $orderItem) {
                    continue;
                }

                $qty = min((int) $line->qty, (int) $orderItem->qty);
                $quantities[$orderItem->id] = ($quantities[$orderItem->id] ?? 0) + $qty;
                $computed += (int) $orderItem->price * $qty;
            }

            $amount ??= $computed > 0 ? $computed : $return->order->refundableAmount();

            $refund = $this->refund(
                $return->order,
                $amount,
                $restock,
                $note ?: $return->reason,
                $return->id,
                $actorId,
                $quantities !== [] ? $quantities : null,
            );

            $return->update(['status' => 'approved', 'admin_note' => $note, 'resolved_at' => now()]);

            return $refund;
        });
    }

    public function rejectReturn(OrderReturn $return, ?string $note = null): void
    {
        DB::transaction(function () use ($return, $note) {
            $return = OrderReturn::query()->lockForUpdate()->findOrFail($return->id);

            if (! $return->isOpen()) {
                throw new RuntimeException('This return has already been resolved.');
            }

            $return->update(['status' => 'rejected', 'admin_note' => $note, 'resolved_at' => now()]);
        });
    }

    protected function paymentStatusFor(Order $order): string
    {
        if ($order->refunded_total <= 0) {
            return $order->payment_status;
        }

        return $order->refunded_total >= $order->grand_total ? 'refunded' : 'partially_refunded';
    }

    /** @return array<string, mixed> */
    protected function customer(Order $order): array
    {
        $address = $order->shippingAddress;

        return [
            'name' => $address?->name ?? 'Customer',
            'email' => $address?->email,
            'phone' => $address?->phone,
        ];
    }
}
