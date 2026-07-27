<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Orders\Exceptions\InsufficientStockException;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Services\OrderStatusService;

class OrderList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    #[Url]
    public string $paymentFilter = '';

    #[Url]
    public int $perPage = 25;

    /** @var array<int, int> */
    public array $selected = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPaymentFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    /** Bulk actions → target status. Fulfillment steps use the same state machine. */
    private const BULK_ACTIONS = [
        'confirm' => ['to' => 'confirmed', 'label' => 'confirmed'],
        'process' => ['to' => 'processing', 'label' => 'marked packed'],
        'ship' => ['to' => 'shipped', 'label' => 'marked shipped'],
        'deliver' => ['to' => 'delivered', 'label' => 'marked delivered'],
        'cancel' => ['to' => 'cancelled', 'label' => 'cancelled'],
    ];

    public function bulk(string $action): void
    {
        if (empty($this->selected) || ! isset(self::BULK_ACTIONS[$action])) {
            return;
        }

        $to = self::BULK_ACTIONS[$action]['to'];
        $service = app(OrderStatusService::class);
        $done = 0;
        $skipped = 0;

        // Route every change through the status service so stock is committed/
        // restocked, history is written, and notifications fire — never a raw
        // status update that silently oversells and blanks the audit trail.
        // Skips orders whose current status doesn't allow the transition.
        foreach (Order::whereIn('id', $this->selected)->get() as $order) {
            if (! in_array($to, $service->allowed($order), true)) {
                $skipped++;

                continue;
            }

            try {
                // ship() also stamps shipped_at and fires the shipped notification;
                // tracking numbers are added per-order on the detail screen.
                if ($action === 'ship') {
                    $service->ship($order, note: 'Shipped in bulk');
                } else {
                    $service->change($order, $to, 'Updated in bulk');
                }
                $done++;
            } catch (InsufficientStockException $e) {
                $skipped++;
            }
        }

        $this->selected = [];
        $message = "{$done} order(s) ".self::BULK_ACTIONS[$action]['label'].($skipped ? ", {$skipped} skipped" : '');
        $this->dispatch('toast', message: $message, type: $done ? 'success' : 'warning');
    }

    /** Filter shared by the list and the status counts (everything except the status pill). */
    protected function scopedQuery()
    {
        return Order::query()
            ->when($this->search !== '', fn ($q) => $q->where(function ($w) {
                $w->where('number', 'like', "%{$this->search}%")
                    ->orWhereHas('addresses', function ($a) {
                        $a->where('name', 'like', "%{$this->search}%")
                            ->orWhere('phone', 'like', "%{$this->search}%");
                    });
            }))
            ->when($this->paymentFilter !== '', fn ($q) => $q->where('payment_status', $this->paymentFilter));
    }

    protected function baseQuery()
    {
        return $this->scopedQuery()
            ->with('shippingAddress')
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->latest();
    }

    /** Counts per status for the filter chips (honours search/payment, ignores the status pill). */
    protected function statusCounts(): array
    {
        $rows = $this->scopedQuery()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'all' => (int) $rows->sum(),
            'pending' => (int) ($rows['pending'] ?? 0),
            'confirmed' => (int) ($rows['confirmed'] ?? 0),
            'processing' => (int) ($rows['processing'] ?? 0),
            'shipped' => (int) ($rows['shipped'] ?? 0),
            'delivered' => (int) ($rows['delivered'] ?? 0),
            'returned' => (int) ($rows['returned'] ?? 0),
            'cancelled' => (int) ($rows['cancelled'] ?? 0),
        ];
    }

    public function render()
    {
        return View::make('orders::livewire.order-list', [
            'orders' => $this->baseQuery()->paginate($this->perPage),
            'counts' => $this->statusCounts(),
        ]);
    }
}
