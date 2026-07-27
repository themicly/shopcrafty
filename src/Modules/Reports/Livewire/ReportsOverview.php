<?php

namespace Themicly\Shopcrafty\Modules\Reports\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Themicly\Shopcrafty\Ai\AiRequestException;
use Themicly\Shopcrafty\Ai\AiService;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class ReportsOverview extends Component
{
    /** Preset length in days; 0 means a custom From/To range. */
    #[Url]
    public int $range = 30;

    #[Url(as: 'from')]
    public string $fromDate = '';

    #[Url(as: 'to')]
    public string $toDate = '';

    /** Plain-language AI read on the period (feature-gated, cached per range + day). */
    public ?string $aiSummary = null;

    /** Monthly revenue goal editor — major units (e.g. "5000.00"), blank while unset. */
    public bool $editingGoal = false;

    public string $goalInput = '';

    /** Revenue-eligible statuses — shared with the dashboard (RPT-04). */
    protected const COUNTED = Order::REVENUE_STATUSES;

    /** Published products at/below this many views in-range surface in "Low product views". */
    protected const LOW_VIEWS_THRESHOLD = 5;

    /** Financial reports are owner-only, like store settings (RPT-08). */
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);

        // A shared URL with explicit dates wins over the preset; otherwise the
        // date inputs mirror the active preset.
        if ($this->validDates() && ! $this->datesMatchPreset()) {
            $this->range = 0;
        } else {
            $this->syncDatesToPreset();
        }

        $goal = (int) settings('reports.monthly_revenue_goal', 0);
        $this->goalInput = $goal > 0 ? $this->toMajor($goal) : '';
    }

    protected function decimals(): int
    {
        return (int) settings('localization.currency_decimals', 2);
    }

    protected function toMinor(string $value): int
    {
        return (int) round(((float) $value) * (10 ** $this->decimals()));
    }

    protected function toMajor(int $minor): string
    {
        return number_format($minor / (10 ** $this->decimals()), $this->decimals(), '.', '');
    }

    public function editGoal(): void
    {
        $this->editingGoal = true;
    }

    /** Set (or clear, with a blank value) the monthly revenue goal used by monthlyGoal(). */
    public function saveGoal(): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);

        $this->validate(['goalInput' => ['nullable', 'numeric', 'min:0']]);

        settings()->set('reports.monthly_revenue_goal', $this->goalInput !== '' ? $this->toMinor($this->goalInput) : 0);
        $this->editingGoal = false;
        $this->dispatch('toast', message: 'Monthly goal updated', type: 'success');
    }

    /**
     * This calendar month's revenue against the owner-set goal, plus a
     * pace projection (linear extrapolation of the daily average to month end).
     * Deliberately independent of the range picker above — a monthly target is
     * a fixed business expectation, not something a "last 7 days" filter should
     * reframe.
     */
    protected function monthlyGoal(): array
    {
        $goal = (int) settings('reports.monthly_revenue_goal', 0);
        $monthStart = now()->startOfMonth();
        $daysElapsed = (int) $monthStart->diffInDays(now()) + 1;
        $daysInMonth = now()->daysInMonth;

        $revenue = (int) Order::where('placed_at', '>=', $monthStart)
            ->whereIn('status', self::COUNTED)
            ->sum(DB::raw('grand_total - refunded_total'));

        $projected = $daysElapsed > 0 ? (int) round($revenue / $daysElapsed * $daysInMonth) : 0;

        return [
            'goal' => $goal,
            'revenue' => $revenue,
            'progressPct' => $goal > 0 ? (int) round($revenue / $goal * 100) : null,
            'met' => $goal > 0 && $revenue >= $goal,
            'projected' => $projected,
            'onTrack' => $goal > 0 ? $projected >= $goal : null,
            'daysRemaining' => max(0, $daysInMonth - $daysElapsed),
        ];
    }

    protected function validDates(): bool
    {
        try {
            Carbon::createFromFormat('Y-m-d', $this->fromDate);
            Carbon::createFromFormat('Y-m-d', $this->toDate);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function datesMatchPreset(): bool
    {
        return $this->range > 0
            && $this->fromDate === now()->subDays($this->range - 1)->toDateString()
            && $this->toDate === now()->toDateString();
    }

    /** Presets keep the date inputs in sync so switching to custom starts from them. */
    protected function syncDatesToPreset(): void
    {
        if ($this->range <= 0) {
            $this->range = 30;
        }

        $this->fromDate = now()->subDays($this->range - 1)->toDateString();
        $this->toDate = now()->toDateString();
    }

    /**
     * The active window as [from, to]. Presets anchor on today; a custom range
     * spans the chosen dates in full days. Falls back to 30 days on bad input.
     */
    protected function period(): array
    {
        if ($this->range > 0) {
            return [now()->subDays($this->range - 1)->startOfDay(), now()];
        }

        try {
            $from = Carbon::createFromFormat('Y-m-d', $this->fromDate)->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $this->toDate)->endOfDay();
        } catch (\Throwable) {
            return [now()->subDays(29)->startOfDay(), now()];
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /** Days in the active window — sizes the "previous period" comparison. */
    protected function periodDays(Carbon $from, Carbon $to): int
    {
        return (int) $from->diffInDays($to) + 1;
    }

    /** Cache-key fragment naming the period (presets keep their historical keys). */
    protected function periodKey(): string
    {
        return $this->range > 0 ? (string) $this->range : "custom.{$this->fromDate}.{$this->toDate}";
    }

    /** Daily sums keyed by date, filled across the whole range (zeros for gaps). */
    protected function dailySeries(Carbon $from, Carbon $to, string $agg): array
    {
        $rows = Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('status', self::COUNTED)
            ->selectRaw('DATE(placed_at) as d, '.($agg === 'revenue' ? 'SUM(grand_total - refunded_total)' : 'COUNT(*)').' as v')
            ->groupBy('d')
            ->pluck('v', 'd');

        $series = [];
        for ($date = $from->copy(); $date <= $to; $date->addDay()) {
            $series[$date->toDateString()] = (int) ($rows[$date->toDateString()] ?? 0);
        }

        return $series;
    }

    protected function periodTotal(Carbon $from, Carbon $to, string $agg): int
    {
        $q = Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])->whereIn('status', self::COUNTED);

        return (int) ($agg === 'revenue' ? $q->sum(DB::raw('grand_total - refunded_total')) : $q->count());
    }

    protected function delta(int $current, int $previous): ?int
    {
        if ($previous === 0) {
            return null;
        }

        return (int) round(($current - $previous) / $previous * 100);
    }

    /** Top sellers in a window — shared by the page and the AI summary prompt. */
    protected function topProducts(Carbon $from, Carbon $to, int $limit = 5)
    {
        // Group by product_id (stable) and show the product's current name, not the
        // snapshot copied onto each order item (RPT-05).
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('catalog_products', 'catalog_products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.placed_at', [$from, $to])
            ->whereIn('orders.status', self::COUNTED)
            ->selectRaw('order_items.product_id, COALESCE(MAX(catalog_products.name), MAX(order_items.name)) as name, SUM(order_items.line_total) as revenue, SUM(order_items.qty) as qty')
            ->groupBy('order_items.product_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        // Attach the live product (image + link) — null when the product itself was
        // since deleted; the row still shows via the order-item name snapshot above.
        $products = $this->productsById($rows->pluck('product_id')->filter());

        return $rows->map(function ($row) use ($products) {
            $product = $row->product_id ? $products->get($row->product_id) : null;

            return [
                'name' => $row->name,
                'revenue' => (int) $row->revenue,
                'qty' => (int) $row->qty,
                'product' => $product,
                // Estimated, not historical: order_items has no cost snapshot, so
                // this uses the product's *current* cost_price — accurate unless
                // it changed since these units sold. Null (not shown) when the
                // product has no cost_price set, or was deleted.
                'profit' => $product?->cost_price !== null
                    ? (int) $row->revenue - ((int) $product->cost_price * (int) $row->qty)
                    : null,
            ];
        });
    }

    /** Product models (with media, for a thumbnail) keyed by id, for the given ids. */
    protected function productsById(iterable $ids): Collection
    {
        return Product::with('media')->whereIn('id', $ids)->get()->keyBy('id');
    }

    /**
     * Published products with little or no traffic in the window — the flip side of
     * "top products": nothing to act on if it's not being seen at all. Includes
     * zero-view products (no row in catalog_product_views), not just low but nonzero.
     */
    protected function lowViewProducts(Carbon $from, Carbon $to, int $limit = 8)
    {
        $views = DB::table('catalog_product_views')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('product_id, SUM(count) as views')
            ->groupBy('product_id')
            ->pluck('views', 'product_id');

        return Product::active()->with('media')->get()
            ->map(fn ($product) => ['product' => $product, 'views' => (int) ($views[$product->id] ?? 0)])
            ->filter(fn ($row) => $row['views'] <= self::LOW_VIEWS_THRESHOLD)
            ->sortBy('views')
            ->take($limit)
            ->values();
    }

    /** A summary belongs to one period — drop it when the range changes. */
    public function updatedRange(): void
    {
        $this->aiSummary = null;

        if ($this->range > 0) {
            $this->syncDatesToPreset();
        }
    }

    /** Picking a date switches to a custom range (and drops the stale summary). */
    public function updatedFromDate(): void
    {
        $this->useCustomRange();
    }

    public function updatedToDate(): void
    {
        $this->useCustomRange();
    }

    protected function useCustomRange(): void
    {
        $this->aiSummary = null;

        if ($this->validDates()) {
            $this->range = 0;
        } elseif ($this->range === 0) {
            $this->syncDatesToPreset();
        }
    }

    public function getAiSummaryEnabledProperty(): bool
    {
        return app(AddonRegistry::class)->installed('ai') && app(AiService::class)->featureEnabled('sales_summary');
    }

    /**
     * AI summary of the period's headline numbers. Cached per range + day (and
     * provider/model, so switching providers doesn't serve stale copy).
     */
    public function generateSummary(): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);

        if (! app(AddonRegistry::class)->installed('ai')) {
            abort(404);
        }

        $ai = app(AiService::class);

        if (! $ai->featureEnabled('sales_summary')) {
            $this->dispatch('toast', message: 'Turn on the sales summary in Settings → AI first', type: 'danger');

            return;
        }

        $system = 'You are an ecommerce analyst for a small online store. Given the numbers, write a short plain-language summary (3-5 sentences, no markdown, no bullet points) that reasons like a store owner should: treat conversion rate and average order value as more telling than raw revenue or traffic alone, and call out which top product is actually driving the period rather than just listing them. If a monthly revenue goal and pace projection are given, say plainly whether the store is on track to hit it and how far off — that\'s the owner\'s actual business expectation, not just a nice-to-know figure. Cover what changed versus the previous period, what stands out, and one or two concrete things to act on. Be specific and use the figures given — never invent or estimate a number that isn\'t provided.';

        $cacheKey = sprintf('ai.sales-summary.%s.%s.%s.%s', $this->periodKey(), now()->toDateString(), $ai->provider(), $ai->model());

        try {
            $this->aiSummary = Cache::remember(
                $cacheKey,
                now()->addDay(),
                fn () => $ai->complete($this->summaryFacts(), $system, 500),
            );
        } catch (AiRequestException $e) {
            $this->dispatch('toast', message: 'Could not generate the summary: '.$e->getMessage(), type: 'danger');
        } catch (\Throwable) {
            $this->dispatch('toast', message: 'Could not generate the summary — please try again.', type: 'danger');
        }
    }

    /** The period's headline numbers, rendered as plain text for the prompt. */
    protected function summaryFacts(): string
    {
        [$from, $to] = $this->period();
        $days = $this->periodDays($from, $to);
        $prevFrom = $from->copy()->subDays($days);
        $prevTo = $from->copy()->subDay();

        $revenue = $this->periodTotal($from, $to, 'revenue');
        $orders = $this->periodTotal($from, $to, 'orders');
        $prevRevenue = $this->periodTotal($prevFrom, $prevTo, 'revenue');
        $prevOrders = $this->periodTotal($prevFrom, $prevTo, 'orders');
        $aov = $orders > 0 ? (int) round($revenue / $orders) : 0;
        $newCustomers = Customer::whereBetween('created_at', [$from, $to])->count();
        $visitors = (int) DB::table('storefront_visits')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])->sum('count');
        $conversion = $visitors > 0 ? round($orders / $visitors * 100, 1).'%' : '—';

        $top = $this->topProducts($from, $to)
            ->map(fn ($p) => "{$p['name']} (".format_money($p['revenue']).", {$p['qty']} sold)")
            ->implode('; ');

        $periodLine = $this->range > 0
            ? "Period: last {$this->range} days (vs the {$this->range} days before)"
            : 'Period: '.$from->toDateString().' to '.$to->toDateString()." ({$days} days, vs the {$days} days before)";

        $goal = $this->monthlyGoal();
        $goalLine = $goal['goal'] > 0
            ? sprintf(
                'Monthly revenue goal for %s: %s — %s so far, on pace for %s by month end (%s)',
                now()->format('F'),
                format_money($goal['goal']),
                format_money($goal['revenue']),
                format_money($goal['projected']),
                $goal['onTrack'] ? 'on track' : 'behind pace',
            )
            : 'Monthly revenue goal: not set';

        return implode("\n", [
            $periodLine,
            'Revenue: '.format_money($revenue).' (previous: '.format_money($prevRevenue).')',
            "Orders: {$orders} (previous: {$prevOrders})",
            'Average order value: '.format_money($aov),
            "New customers: {$newCustomers}",
            'Storefront visitors: '.number_format($visitors),
            "Conversion rate (orders ÷ visitors): {$conversion}",
            'Top products by revenue: '.($top !== '' ? $top : 'none'),
            $goalLine,
        ]);
    }

    /**
     * Orders CSV for the selected range. Only revenue-eligible statuses (so the
     * file reconciles with the on-screen numbers) and amounts in major units.
     */
    public function exportOrders()
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);

        [$from, $to] = $this->period();

        $decimals = (int) settings('localization.currency_decimals', 2);
        $major = fn (?int $minor) => number_format((int) $minor / (10 ** $decimals), $decimals, '.', '');

        $callback = function () use ($from, $to, $major) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Number', 'Date', 'Status', 'Payment status', 'Payment method', 'Total', 'Refunded']);

            Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
                ->whereIn('status', self::COUNTED)
                ->latest('placed_at')
                ->chunk(200, function ($orders) use ($out, $major) {
                    foreach ($orders as $o) {
                        fputcsv($out, [
                            $o->number, $o->placed_at?->toDateString(), $o->status, $o->payment_status,
                            $o->payment_method, $major($o->grand_total), $major($o->refunded_total),
                        ]);
                    }
                });

            fclose($out);
        };

        $filename = sprintf('orders-%s-to-%s.csv', $from->toDateString(), $to->toDateString());

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv']);
    }

    /** Revenue + units per category for the window; uncategorized items bucketed. */
    protected function salesByCategory(Carbon $from, Carbon $to)
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('catalog_products', 'catalog_products.id', '=', 'order_items.product_id')
            ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_products.category_id')
            ->whereBetween('orders.placed_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('orders.status', self::COUNTED)
            ->selectRaw("COALESCE(catalog_categories.name, 'Uncategorized') as category, SUM(order_items.line_total) as revenue, SUM(order_items.qty) as units")
            ->groupBy('catalog_categories.name')
            ->orderByDesc('revenue')
            ->get();

        $total = (int) $rows->sum('revenue');

        return $rows->map(fn ($r) => [
            'category' => $r->category,
            'revenue' => (int) $r->revenue,
            'units' => (int) $r->units,
            'share' => $total > 0 ? (int) round($r->revenue / $total * 100) : 0,
        ]);
    }

    /** Revenue by payment method for the window. */
    protected function paymentSplit(Carbon $from, Carbon $to)
    {
        return Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('status', self::COUNTED)
            ->selectRaw("COALESCE(payment_method, 'unknown') as method, COUNT(*) as orders, SUM(grand_total - refunded_total) as revenue")
            ->groupBy('payment_method')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($r) => ['method' => $r->method, 'orders' => (int) $r->orders, 'revenue' => (int) $r->revenue]);
    }

    /**
     * Reconciles the money that makes up the window's revenue figure — gross
     * sales down to net revenue — using columns already on the order (subtotal,
     * discount_total, shipping_total, tax_total, refunded_total). Kept separate
     * from periodTotal()'s single revenue number since a store owner cares about
     * the breakdown, not just the total.
     */
    protected function salesBreakdown(Carbon $from, Carbon $to): array
    {
        $row = DB::table('orders')
            ->whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('status', self::COUNTED)
            ->selectRaw('
                COALESCE(SUM(subtotal), 0) as gross,
                COALESCE(SUM(discount_total), 0) as discounts,
                COALESCE(SUM(shipping_total), 0) as shipping,
                COALESCE(SUM(tax_total), 0) as tax,
                COALESCE(SUM(refunded_total), 0) as refunds,
                COALESCE(SUM(grand_total - refunded_total), 0) as net
            ')
            ->first();

        return [
            'gross' => (int) $row->gross,
            'discounts' => (int) $row->discounts,
            'shipping' => (int) $row->shipping,
            'tax' => (int) $row->tax,
            'refunds' => (int) $row->refunds,
            'net' => (int) $row->net,
        ];
    }

    /**
     * Gateway reliability from the payment_logs audit trail (Stripe session
     * creation, webhooks, return-URL confirms, mark-paid reconciliations) — data
     * that already existed but wasn't surfaced anywhere on a report screen.
     * Cash on Delivery has no gateway calls to log, so it never appears here.
     */
    protected function paymentReliability(Carbon $from, Carbon $to): array
    {
        $row = DB::table('payment_logs')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN success THEN 1 ELSE 0 END) as successes')
            ->first();

        $total = (int) ($row->total ?? 0);
        $successes = (int) ($row->successes ?? 0);
        $failures = $total - $successes;

        $recentFailures = DB::table('payment_logs')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->where('success', false)
            ->latest('created_at')
            ->limit(5)
            ->get(['gateway', 'action', 'message', 'created_at']);

        return [
            'total' => $total,
            'failures' => $failures,
            'successRate' => $total > 0 ? round($successes / $total * 100, 1) : null,
            'recentFailures' => $recentFailures,
        ];
    }

    public function render()
    {
        [$from, $to] = $this->period();
        $days = $this->periodDays($from, $to);
        $prevFrom = $from->copy()->subDays($days);
        $prevTo = $from->copy()->subDay();

        $revenueSeries = $this->dailySeries($from, $to, 'revenue');
        $ordersSeries = $this->dailySeries($from, $to, 'orders');

        $revenue = $this->periodTotal($from, $to, 'revenue');
        $orders = $this->periodTotal($from, $to, 'orders');
        $prevRevenue = $this->periodTotal($prevFrom, $prevTo, 'revenue');
        $prevOrders = $this->periodTotal($prevFrom, $prevTo, 'orders');

        $newCustomers = Customer::whereBetween('created_at', [$from, $to])->count();

        // Average order value for the period.
        $aov = $orders > 0 ? (int) round($revenue / $orders) : 0;

        // All-time repeat rate: share of buyers with 2+ revenue orders.
        $buyerCounts = Order::whereIn('status', self::COUNTED)
            ->whereNotNull('customer_id')
            ->selectRaw('customer_id, count(*) as c')
            ->groupBy('customer_id')
            ->pluck('c');
        $repeatRate = $buyerCounts->count() > 0
            ? (int) round($buyerCounts->filter(fn ($c) => $c >= 2)->count() / $buyerCounts->count() * 100)
            : 0;

        $taxCollected = (int) Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('status', self::COUNTED)
            ->sum('tax_total');

        // Real storefront traffic → sessions-based conversion (orders ÷ visitors).
        $visitors = (int) DB::table('storefront_visits')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])->sum('count');
        $prevVisitors = (int) DB::table('storefront_visits')
            ->whereBetween('date', [$prevFrom->toDateString(), $prevTo->toDateString()])->sum('count');
        $conversion = $visitors > 0 ? round($orders / $visitors * 100, 1) : 0.0;
        $prevConversion = $prevVisitors > 0 ? round($prevOrders / $prevVisitors * 100, 1) : 0.0;

        $topProducts = $this->topProducts($from, $to);

        // Product insights: views, units sold, conversion (top by views).
        $viewsByProduct = DB::table('catalog_product_views')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('product_id, SUM(count) as views')
            ->groupBy('product_id')->orderByDesc('views')->limit(8)->pluck('views', 'product_id');

        $salesByProduct = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('order_items.product_id', $viewsByProduct->keys())
            ->whereBetween('orders.placed_at', [$from, $to])
            ->whereIn('orders.status', self::COUNTED)
            ->selectRaw('product_id, SUM(qty) as units, COUNT(DISTINCT order_id) as orders')
            ->groupBy('product_id')->get()->keyBy('product_id');

        $products = $this->productsById($viewsByProduct->keys());

        $insights = $viewsByProduct->map(fn ($views, $id) => [
            'name' => $products->get($id)->name ?? '—',
            'product' => $products->get($id),
            'views' => (int) $views,
            'units' => (int) ($salesByProduct[$id]->units ?? 0),
            'conversion' => $views > 0 ? round(($salesByProduct[$id]->orders ?? 0) / $views * 100, 1) : 0,
        ])->values();

        return View::make('reports::livewire.reports-overview', [
            'monthlyGoal' => $this->monthlyGoal(),
            'salesBreakdown' => $this->salesBreakdown($from, $to),
            'paymentReliability' => $this->paymentReliability($from, $to),
            'revenueSeries' => array_values($revenueSeries),
            'ordersSeries' => array_values($ordersSeries),
            'revenue' => $revenue,
            'orders' => $orders,
            'revenueDelta' => $this->delta($revenue, $prevRevenue),
            'ordersDelta' => $this->delta($orders, $prevOrders),
            'newCustomers' => $newCustomers,
            'aov' => $aov,
            'repeatRate' => $repeatRate,
            'taxCollected' => $taxCollected,
            'visitors' => $visitors,
            'visitorsDelta' => $this->delta($visitors, $prevVisitors),
            'conversion' => $conversion,
            'conversionDelta' => $this->delta((int) round($conversion * 10), (int) round($prevConversion * 10)),
            'topProducts' => $topProducts,
            'insights' => $insights,
            'lowViewProducts' => $this->lowViewProducts($from, $to),
            'lowViewsThreshold' => self::LOW_VIEWS_THRESHOLD,
            'salesByCategory' => $this->salesByCategory($from, $to),
            'paymentSplit' => $this->paymentSplit($from, $to),
        ]);
    }
}
