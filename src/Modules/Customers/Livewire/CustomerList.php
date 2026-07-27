<?php

namespace Themicly\Shopcrafty\Modules\Customers\Livewire;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class CustomerList extends Component
{
    use WithPagination;

    /** Lifetime spend (minor units) that marks a customer as VIP. */
    protected const VIP_THRESHOLD = 500000;

    #[Url]
    public string $search = '';

    #[Url]
    public string $segment = 'all';

    #[Url]
    public string $tag = '';

    #[Url]
    public int $perPage = 25;

    /** @var array<int, int> */
    public array $selected = [];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSegment(): void
    {
        $this->resetPage();
    }

    public function updatedTag(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function block(int $id): void
    {
        $customer = Customer::findOrFail($id);
        $customer->status = $customer->status === 'active' ? 'blocked' : 'active';
        $customer->save();

        $message = $customer->status === 'active' ? 'Customer unblocked' : 'Customer blocked';
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function delete(int $id): void
    {
        Customer::whereKey($id)->delete();
        $this->selected = array_values(array_diff($this->selected, [$id]));
        $this->dispatch('toast', message: 'Customer deleted', type: 'success');
    }

    public function bulk(string $action): void
    {
        if (empty($this->selected)) {
            return;
        }

        $query = Customer::whereIn('id', $this->selected);

        match ($action) {
            'block' => $query->update(['status' => 'blocked']),
            'activate' => $query->update(['status' => 'active']),
            'delete' => $query->delete(),
            default => null,
        };

        $count = count($this->selected);
        $this->selected = [];
        $this->dispatch('toast', message: "{$count} customer(s) updated", type: 'success');
    }

    /**
     * One row per customer with an order: total order count + lifetime spend
     * (revenue-bearing statuses only). Joined once instead of run as a
     * correlated subquery per customer row — the vip/repeat segment filters
     * used to evaluate that subquery against every row in `customers` before
     * pagination; this replaces it with a single indexed GROUP BY.
     */
    protected function ordersSummarySubquery()
    {
        $statuses = "'".implode("','", Order::REVENUE_STATUSES)."'";

        return Order::query()
            ->selectRaw('customer_id')
            ->selectRaw('count(*) as orders_count')
            ->selectRaw("sum(case when status in ({$statuses}) then grand_total else 0 end) as revenue_sum")
            ->whereNotNull('customer_id')
            ->groupBy('customer_id');
    }

    protected function baseQuery()
    {
        return Customer::query()
            ->leftJoinSub($this->ordersSummarySubquery(), 'orders_summary', 'orders_summary.customer_id', '=', 'customers.id')
            ->addSelect('customers.*')
            ->selectRaw('coalesce(orders_summary.orders_count, 0) as orders_count')
            ->selectRaw('coalesce(orders_summary.revenue_sum, 0) as orders_sum_grand_total')
            ->addSelect(['channel' => Order::select('source')
                ->whereColumn('customer_id', 'customers.id')
                ->latest('placed_at')->limit(1)])
            ->when($this->search !== '', fn ($q) => $q->where(function ($w) {
                $w->where('customers.name', 'like', "%{$this->search}%")
                    ->orWhere('customers.mobile', 'like', "%{$this->search}%")
                    ->orWhere('customers.email', 'like', "%{$this->search}%");
            }))
            ->when($this->segment === 'vip', fn ($q) => $q->where('orders_summary.revenue_sum', '>=', self::VIP_THRESHOLD))
            ->when($this->segment === 'repeat', fn ($q) => $q->where('orders_summary.orders_count', '>=', 2))
            ->when($this->segment === 'new', fn ($q) => $q->where('customers.created_at', '>=', now()->subDays(30)))
            ->when($this->tag !== '', fn ($q) => $q->whereJsonContains('customers.tags', $this->tag))
            ->latest('customers.created_at');
    }

    protected function stats(): array
    {
        return [
            'total' => Customer::count(),
            'active' => Customer::where('status', 'active')->count(),
            'vip' => Customer::query()
                ->leftJoinSub($this->ordersSummarySubquery(), 'orders_summary', 'orders_summary.customer_id', '=', 'customers.id')
                ->where('orders_summary.revenue_sum', '>=', self::VIP_THRESHOLD)
                ->count(),
            'new' => Customer::where('created_at', '>=', now()->subDays(30))->count(),
        ];
    }

    /** Distinct tags across all customers, for the filter dropdown. Cached — this doesn't need to be live on every keystroke. */
    protected function allTags(): array
    {
        return Cache::remember('customers:distinct-tags', 300, fn () => Customer::whereNotNull('tags')
            ->pluck('tags')
            ->flatten()
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all());
    }

    public function render()
    {
        return View::make('customers::livewire.customer-list', [
            'customers' => $this->baseQuery()->paginate($this->perPage),
            'stats' => $this->stats(),
            'allTags' => $this->allTags(),
        ]);
    }
}
