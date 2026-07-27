<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Orders\Exceptions\InsufficientStockException;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Services\OrderStatusService;

/**
 * Triage screen for Cash-on-Delivery orders awaiting verification. Verifying
 * confirms the order (and commits its stock, per the deferred-COD policy);
 * rejecting cancels it.
 */
class CodQueue extends Component
{
    /** @var array<int, string> per-order note keyed by order id */
    public array $notes = [];

    public function verify(int $id): void
    {
        try {
            app(OrderStatusService::class)->verifyCod(Order::findOrFail($id), $this->notes[$id] ?? null);
        } catch (InsufficientStockException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger');

            return;
        }

        unset($this->notes[$id]);
        $this->dispatch('toast', message: 'Order verified', type: 'success');
    }

    public function reject(int $id): void
    {
        app(OrderStatusService::class)->rejectCod(Order::findOrFail($id), $this->notes[$id] ?? null);
        unset($this->notes[$id]);
        $this->dispatch('toast', message: 'Order rejected', type: 'success');
    }

    public function render()
    {
        return View::make('orders::livewire.cod-queue', [
            'orders' => Order::with('shippingAddress')
                ->where('payment_method', 'cod')
                ->where('cod_verification_status', 'unverified')
                ->where('status', 'pending')
                ->latest('placed_at')
                ->get(),
        ]);
    }
}
