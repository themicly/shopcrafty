<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use Livewire\Component;
use RuntimeException;
use Themicly\Shopcrafty\Modules\Orders\Exceptions\InsufficientStockException;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Models\OrderReturn;
use Themicly\Shopcrafty\Modules\Orders\Services\OrderStatusService;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentReconciler;
use Themicly\Shopcrafty\Modules\Orders\Services\RefundService;

class OrderDetail extends Component
{
    public Order $order;

    public ?string $note = '';

    public string $trackingNumber = '';

    public string $carrier = '';

    public string $refundAmount = '';

    public bool $refundRestock = false;

    public string $refundReason = '';

    public function mount(int $orderId): void
    {
        $this->order = Order::with(['items.product.media', 'addresses', 'history', 'returns.items.orderItem', 'refunds'])->findOrFail($orderId);
    }

    public function changeStatus(string $to): void
    {
        try {
            app(OrderStatusService::class)->change($this->order, $to, $this->note ?: null, 'admin');
        } catch (InsufficientStockException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger');

            return;
        } catch (InvalidArgumentException $e) {
            // The transition is no longer valid (e.g. a double-click after the
            // order already moved on) — refresh to the current state instead of 500ing.
            $this->refreshOrder();
            $this->dispatch('toast', message: 'That status change is no longer available.', type: 'warning');

            return;
        }

        $this->refreshOrder();
        $this->reset('note');
        $this->dispatch('toast', message: 'Order updated', type: 'success');
    }

    public function ship(): void
    {
        app(OrderStatusService::class)->ship(
            $this->order,
            $this->trackingNumber ?: null,
            $this->carrier ?: null,
            $this->note ?: null,
        );

        $this->refreshOrder();
        $this->reset('note', 'trackingNumber', 'carrier');
        $this->dispatch('toast', message: 'Order marked shipped', type: 'success');
    }

    public function verify(): void
    {
        try {
            app(OrderStatusService::class)->verifyCod($this->order, $this->note ?: null);
        } catch (InsufficientStockException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger');

            return;
        }

        $this->refreshOrder();
        $this->reset('note');
        $this->dispatch('toast', message: 'COD verified', type: 'success');
    }

    public function reject(): void
    {
        app(OrderStatusService::class)->rejectCod($this->order, $this->note ?: null);

        $this->refreshOrder();
        $this->reset('note');
        $this->dispatch('toast', message: 'COD rejected', type: 'success');
    }

    public function markPaid(): void
    {
        try {
            // Idempotent: confirms a pending order and commits its stock, which
            // can fail if inventory sold out since the order was placed.
            app(PaymentReconciler::class)->markPaid($this->order);
        } catch (InsufficientStockException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger');

            return;
        }

        $this->refreshOrder();
        $this->dispatch('toast', message: 'Payment recorded', type: 'success');
    }

    /** Refunds get their own permission — routine order fulfillment shouldn't imply moving money. */
    protected function guardRefunds(): void
    {
        abort_unless(Auth::user()?->can('manage-refunds') ?? false, 403);
    }

    public function issueRefund(): void
    {
        $this->guardRefunds();

        $data = $this->validate([
            'refundAmount' => ['required', 'numeric', 'min:0.01'],
            'refundReason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            app(RefundService::class)->refund(
                $this->order,
                $this->toMinor($data['refundAmount']),
                $this->refundRestock,
                $this->refundReason ?: null,
                null,
                Auth::id(),
            );
        } catch (RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger');

            return;
        }

        $this->refreshOrder();
        $this->reset('refundAmount', 'refundReason', 'refundRestock');
        $this->dispatch('toast', message: 'Refund recorded', type: 'success');
    }

    public function approveReturn(int $returnId): void
    {
        $this->guardRefunds();

        $return = OrderReturn::where('order_id', $this->order->id)->findOrFail($returnId);

        try {
            // Amount + restock quantities are derived from the return's line items.
            app(RefundService::class)->approveReturn($return, null, true, $this->note ?: null, Auth::id());
        } catch (RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger');

            return;
        }

        $this->refreshOrder();
        $this->reset('note');
        $this->dispatch('toast', message: 'Return approved and refunded', type: 'success');
    }

    public function rejectReturn(int $returnId): void
    {
        $this->guardRefunds();

        $return = OrderReturn::where('order_id', $this->order->id)->findOrFail($returnId);

        try {
            app(RefundService::class)->rejectReturn($return, $this->note ?: null);
        } catch (RuntimeException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger');

            return;
        }

        $this->refreshOrder();
        $this->reset('note');
        $this->dispatch('toast', message: 'Return rejected', type: 'success');
    }

    protected function toMinor(string $value): int
    {
        $decimals = (int) settings('localization.currency_decimals', 2);

        return (int) round(((float) $value) * (10 ** $decimals));
    }

    protected function refreshOrder(): void
    {
        $this->order = Order::with(['items', 'addresses', 'history', 'returns.items.orderItem', 'refunds'])->findOrFail($this->order->id);
    }

    public function render()
    {
        return View::make('orders::livewire.order-detail', [
            'allowed' => app(OrderStatusService::class)->allowed($this->order),
        ]);
    }
}
