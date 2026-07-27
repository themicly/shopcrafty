<?php

namespace Themicly\Shopcrafty\Modules\Orders\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Themicly\Shopcrafty\Modules\Orders\Actions\CommitStock;
use Themicly\Shopcrafty\Modules\Orders\Actions\RestockOrder;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderStatusChanged;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class OrderStatusService
{
    /** Allowed transitions per current status. */
    public const FLOW = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped' => ['delivered', 'returned'],
        'delivered' => ['returned'],
        'cancelled' => [],
        'returned' => [],
    ];

    /** @return array<int, string> */
    public function allowed(Order $order): array
    {
        return self::FLOW[$order->status] ?? [];
    }

    public function change(Order $order, string $to, ?string $note = null, string $actor = 'admin'): void
    {
        if (! in_array($to, $this->allowed($order), true)) {
            throw new InvalidArgumentException("Cannot move order from {$order->status} to {$to}.");
        }

        $from = $order->status;
        $attributes = ['status' => $to];

        // Mark COD orders paid on delivery.
        if ($to === 'delivered' && $order->payment_method === 'cod') {
            $attributes['payment_status'] = 'paid';
        }

        // Reserve stock the moment an order is confirmed (this is when COD stock
        // is actually committed). May throw if inventory sold out since placement.
        if ($to === 'confirmed') {
            app(CommitStock::class)->handle($order);
        }

        $order->update($attributes);
        $order->history()->create(['from_status' => $from, 'to_status' => $to, 'note' => $note, 'actor' => $actor]);

        // Return committed stock when an order is cancelled or returned.
        if (in_array($to, ['cancelled', 'returned'], true)) {
            app(RestockOrder::class)->handle($order);
        }

        event(new OrderStatusChanged($order, $from, $to));
    }

    /**
     * Fulfill an order: record carrier + tracking, then transition to "shipped".
     * The tracking number flows into the order.shipped notification.
     */
    public function ship(Order $order, ?string $trackingNumber = null, ?string $carrier = null, ?string $note = null): void
    {
        $order->update([
            'carrier' => $carrier ?: null,
            'tracking_number' => $trackingNumber ?: null,
            'shipped_at' => now(),
        ]);

        $this->change($order, 'shipped', $note, 'admin');
    }

    /** COD verification: confirm a pending COD order. */
    public function verifyCod(Order $order, ?string $note = null): void
    {
        // Flag + confirmation share one transaction so a failed stock commit
        // (item sold out since placement) rolls the flag back too (ORD-07).
        DB::transaction(function () use ($order, $note) {
            $order->update(['cod_verification_status' => 'verified']);

            if ($order->status === 'pending') {
                $this->change($order, 'confirmed', $note ?? 'COD verified', 'admin');
            }
        });
    }

    /** COD verification: reject a fake order. */
    public function rejectCod(Order $order, ?string $note = null): void
    {
        $order->update(['cod_verification_status' => 'rejected']);

        if (in_array($order->status, ['pending', 'confirmed'], true)) {
            $this->change($order, 'cancelled', $note ?? 'COD rejected', 'admin');
        }
    }
}
