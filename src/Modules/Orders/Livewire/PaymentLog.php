<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Orders\Models\PaymentLog as PaymentLogModel;

/**
 * Admin: the payment-gateway audit trail — every session creation, webhook,
 * return-URL confirmation and reconciliation, with the gateway's own message
 * and a sanitized (secret-free) context. Filterable by gateway, action,
 * outcome, order number and date range; each row expands to its full detail.
 */
class PaymentLog extends Component
{
    use WithPagination;

    #[Url]
    public string $gateway = '';

    #[Url]
    public string $action = '';

    #[Url]
    public string $outcome = '';   // success | failed

    #[Url]
    public string $search = '';    // order number

    #[Url]
    public string $from = '';

    #[Url]
    public string $to = '';

    /** Currently expanded row (null = none). */
    public ?int $expanded = null;

    /** Reset pagination when a filter changes (not when merely expanding a row). */
    public function updating($name): void
    {
        if (in_array($name, ['gateway', 'action', 'outcome', 'search', 'from', 'to'], true)) {
            $this->resetPage();
        }
    }

    public function toggle(int $id): void
    {
        $this->expanded = $this->expanded === $id ? null : $id;
    }

    public function resetFilters(): void
    {
        $this->reset('gateway', 'action', 'outcome', 'search', 'from', 'to');
        $this->resetPage();
    }

    public function render()
    {
        $logs = PaymentLogModel::query()
            ->when($this->gateway !== '', fn ($q) => $q->where('gateway', $this->gateway))
            ->when($this->action !== '', fn ($q) => $q->where('action', $this->action))
            ->when($this->outcome !== '', fn ($q) => $q->where('success', $this->outcome === 'success'))
            ->when($this->search !== '', fn ($q) => $q->where('order_number', 'like', "%{$this->search}%"))
            ->when($this->from !== '', fn ($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to !== '', fn ($q) => $q->whereDate('created_at', '<=', $this->to))
            ->latest()
            ->paginate(25);

        return View::make('orders::livewire.payment-log', [
            'logs' => $logs,
            'total' => $logs->total(),
        ]);
    }
}
