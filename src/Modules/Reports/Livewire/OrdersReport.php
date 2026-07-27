<?php

namespace Themicly\Shopcrafty\Modules\Reports\Livewire;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Reports\Concerns\HasDateRange;

class OrdersReport extends Component
{
    use HasDateRange;

    /** Every status an order can carry, in lifecycle order (the two exit branches last). */
    protected const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'];

    /** One of the 6 semantic tokens the admin's badge/color system supports (RPT design). */
    protected const STATUS_COLOR = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'processing' => 'primary',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger',
        'returned' => 'danger',
    ];

    /** Financial/operational reports are owner-only, like the rest of Reports (RPT-08). */
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);
    }

    /** Status counts + share of total, in fixed lifecycle order for a stable legend/bar. */
    protected function statusBreakdown(Carbon $from, Carbon $to): array
    {
        $counts = Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total = (int) $counts->sum();

        return collect(self::STATUSES)->map(fn ($status) => [
            'status' => $status,
            'label' => ucfirst($status),
            'color' => self::STATUS_COLOR[$status] ?? 'neutral',
            'count' => (int) ($counts[$status] ?? 0),
            'pct' => $total > 0 ? (int) round(($counts[$status] ?? 0) / $total * 100) : 0,
        ])->values()->all();
    }

    /** Orders placed per day, zero-filled across the whole range. */
    protected function ordersSeries(Carbon $from, Carbon $to): array
    {
        $rows = Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw('DATE(placed_at) as d, COUNT(*) as v')
            ->groupBy('d')
            ->pluck('v', 'd');

        $series = [];
        for ($date = $from->copy(); $date <= $to; $date->addDay()) {
            $series[] = (int) ($rows[$date->toDateString()] ?? 0);
        }

        return $series;
    }

    /**
     * Average hours placed→confirmed (from order_status_history — there's no
     * confirmed_at column) and placed→shipped (a direct column). Averaged in PHP
     * rather than with SQL's TIMESTAMPDIFF (MySQL-only, and this runs against
     * SQLite in tests) — fine at the row counts a manual report page like this
     * deals with. An order that bounced back and forth to "confirmed" more than
     * once would count more than once, but that's rare enough not to warrant a
     * per-order MIN() subquery for this "how are we trending" figure.
     */
    protected function processingTimes(Carbon $from, Carbon $to): array
    {
        $confirmedRows = DB::table('order_status_history as h')
            ->join('orders as o', 'o.id', '=', 'h.order_id')
            ->where('h.to_status', 'confirmed')
            ->whereBetween('o.placed_at', [$from, $to->copy()->endOfDay()])
            ->get(['o.placed_at', 'h.created_at']);

        $shippedRows = Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->whereNotNull('shipped_at')
            ->get(['placed_at', 'shipped_at']);

        return [
            'toConfirmed' => $this->averageHoursBetween($confirmedRows, 'placed_at', 'created_at'),
            'toShipped' => $this->averageHoursBetween($shippedRows, 'placed_at', 'shipped_at'),
        ];
    }

    /** Average hours between two timestamp-ish fields across a set of rows/models. */
    protected function averageHoursBetween(iterable $rows, string $fromKey, string $toKey): ?float
    {
        $hours = collect($rows)->map(function ($row) use ($fromKey, $toKey) {
            // Model::toArray() (not a raw (array) cast, which mangles Eloquent's
            // internal protected properties instead of returning attributes).
            $row = $row instanceof Arrayable ? $row->toArray() : (array) $row;
            $from = Carbon::parse($row[$fromKey]);
            $to = Carbon::parse($row[$toKey]);

            return $from->diffInMinutes($to) / 60;
        });

        return $hours->isEmpty() ? null : round($hours->avg(), 1);
    }

    /**
     * Delivered ÷ (delivered + cancelled + returned) — orders that reached a
     * terminal outcome in range. Still-open orders (pending/confirmed/
     * processing/shipped) are excluded rather than counted against the rate.
     */
    protected function deliverySuccessRate(Carbon $from, Carbon $to): ?float
    {
        $counts = Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('status', ['delivered', 'cancelled', 'returned'])
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $total = (int) $counts->sum();

        return $total > 0 ? round(($counts['delivered'] ?? 0) / $total * 100, 1) : null;
    }

    /** Shipped/delivered orders grouped by the free-text carrier logged at ship time. */
    protected function carrierBreakdown(Carbon $from, Carbon $to): array
    {
        return DB::table('orders')
            ->whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->whereNotNull('carrier')
            ->where('carrier', '!=', '')
            ->selectRaw('carrier, COUNT(*) as orders')
            ->groupBy('carrier')
            ->orderByDesc('orders')
            ->get()
            ->map(fn ($row) => ['carrier' => $row->carrier, 'orders' => (int) $row->orders])
            ->all();
    }

    public function render()
    {
        [$from, $to] = $this->period();

        return View::make('reports::livewire.orders-report', [
            'statusBreakdown' => $this->statusBreakdown($from, $to),
            'ordersSeries' => $this->ordersSeries($from, $to),
            'processingTimes' => $this->processingTimes($from, $to),
            'deliverySuccessRate' => $this->deliverySuccessRate($from, $to),
            'carrierBreakdown' => $this->carrierBreakdown($from, $to),
        ]);
    }
}
