<?php

namespace Themicly\Shopcrafty\Modules\Reports\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Reports\Concerns\HasDateRange;

class CustomersReport extends Component
{
    use HasDateRange;

    /** "No purchase in the last N days" window — for a re-engagement email list. */
    #[Url]
    public int $inactiveDays = 30;

    /** Top customers ranked by 'spend' (lifetime value) or 'orders' (frequency). */
    #[Url]
    public string $topSort = 'spend';

    /** Financial/customer reports are owner-only, like the rest of Reports (RPT-08). */
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);
    }

    /** New signups per day, zero-filled across the range. */
    protected function newCustomersSeries(Carbon $from, Carbon $to): array
    {
        $rows = Customer::whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw('DATE(created_at) as d, COUNT(*) as v')
            ->groupBy('d')
            ->pluck('v', 'd');

        $series = [];
        for ($date = $from->copy(); $date <= $to; $date->addDay()) {
            $series[] = (int) ($rows[$date->toDateString()] ?? 0);
        }

        return $series;
    }

    /**
     * All-time order count + lifetime spend (revenue-eligible statuses only)
     * per customer, joined once rather than per-row — same shape as the
     * customer list's VIP/repeat segment filters, reused here for ranking.
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

    /** Top customers by lifetime spend or order count — the LTV leaderboard. */
    protected function topCustomers(int $limit = 10)
    {
        $sortColumn = $this->topSort === 'orders' ? 'orders_summary.orders_count' : 'orders_summary.revenue_sum';

        return Customer::query()
            ->leftJoinSub($this->ordersSummarySubquery(), 'orders_summary', 'orders_summary.customer_id', '=', 'customers.id')
            ->addSelect('customers.*')
            ->selectRaw('coalesce(orders_summary.orders_count, 0) as orders_count')
            ->selectRaw('coalesce(orders_summary.revenue_sum, 0) as revenue_sum')
            ->where('orders_summary.orders_count', '>', 0)
            ->orderByDesc($sortColumn)
            ->limit($limit)
            ->get();
    }

    /** Customers who have ordered before but not within the chosen window — a re-engagement list. */
    protected function inactiveCustomers(int $limit = 12)
    {
        return Customer::whereNotNull('last_order_at')
            ->where('last_order_at', '<', now()->subDays($this->inactiveDays))
            ->orderBy('last_order_at')
            ->limit($limit)
            ->get();
    }

    public function render()
    {
        [$from, $to] = $this->period();

        return View::make('reports::livewire.customers-report', [
            'newCustomersSeries' => $this->newCustomersSeries($from, $to),
            'newCustomersTotal' => Customer::whereBetween('created_at', [$from, $to->copy()->endOfDay()])->count(),
            'topCustomers' => $this->topCustomers(),
            'inactiveCustomers' => $this->inactiveCustomers(),
        ]);
    }
}
