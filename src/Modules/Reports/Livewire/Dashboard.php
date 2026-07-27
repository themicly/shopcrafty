<?php

namespace Themicly\Shopcrafty\Modules\Reports\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Ai\AiRequestException;
use Themicly\Shopcrafty\Ai\AiService;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Models\OrderReturn;
use Themicly\Shopcrafty\Modules\Reports\Services\JourneyService;

/**
 * Smart dashboard. Reads across modules (Orders/Catalog/Customers) as a
 * read-model; see docs/03-modules-reports.md.
 */
class Dashboard extends Component
{
    /** Short AI "what needs attention" bullets (feature-gated, cached per day). */
    public ?string $aiInsights = null;

    /** Percentage change vs the previous period; null when there's no baseline. */
    protected function delta(float $current, float $previous): ?int
    {
        if ($previous <= 0) {
            return null;
        }

        return (int) round(($current - $previous) / $previous * 100);
    }

    /** Daily totals across [from, to], zero-filled, as a flat array for sparklines. */
    protected function series(Carbon $from, Carbon $to, string $agg): array
    {
        $rows = Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('status', Order::REVENUE_STATUSES)
            ->selectRaw('DATE(placed_at) as d, '.($agg === 'revenue' ? 'SUM(grand_total - refunded_total)' : 'COUNT(*)').' as v')
            ->groupBy('d')
            ->pluck('v', 'd');

        $series = [];
        for ($date = $from->copy(); $date <= $to; $date->addDay()) {
            $series[] = (int) ($rows[$date->toDateString()] ?? 0);
        }

        return $series;
    }

    /** Active products at or below their low-stock threshold. */
    protected function lowStockQuery()
    {
        return Product::query()
            ->where('track_inventory', true)
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->where('status', 'active');
    }

    /** Revenue share by category over the window — the dashboard's headline breakdown. */
    protected function salesByCategory(Carbon $from, Carbon $to): array
    {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('catalog_products', 'catalog_products.id', '=', 'order_items.product_id')
            ->leftJoin('catalog_categories', 'catalog_categories.id', '=', 'catalog_products.category_id')
            ->whereBetween('orders.placed_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('orders.status', Order::REVENUE_STATUSES)
            ->selectRaw("COALESCE(catalog_categories.name, 'Uncategorized') as category, SUM(order_items.line_total) as revenue")
            ->groupBy('catalog_categories.name')
            ->orderByDesc('revenue')
            ->limit(6)
            ->get();

        $total = (int) $rows->sum('revenue');

        return $rows->map(fn ($r) => [
            'category' => $r->category,
            'revenue' => (int) $r->revenue,
            'share' => $total > 0 ? (int) round($r->revenue / $total * 100) : 0,
        ])->all();
    }

    /** Active products at or below their low-stock threshold, lowest first. */
    protected function lowStockProducts()
    {
        return $this->lowStockQuery()
            ->orderBy('stock_qty')
            ->limit(6)
            ->get();
    }

    /**
     * Today vs yesterday, for the morning row. Revenue and orders use the same
     * revenue-eligible statuses as the 30-day cards so the figures agree.
     */
    protected function todayStats(): array
    {
        $todayStart = now()->startOfDay();
        $yesterdayStart = $todayStart->copy()->subDay();

        $revenueOrders = fn ($from, $before) => Order::where('placed_at', '>=', $from)
            ->when($before, fn ($q) => $q->where('placed_at', '<', $before))
            ->whereIn('status', Order::REVENUE_STATUSES);

        $revenue = (int) $revenueOrders($todayStart, null)->sum(DB::raw('grand_total - refunded_total'));
        $orders = $revenueOrders($todayStart, null)->count();
        $prevRevenue = (int) $revenueOrders($yesterdayStart, $todayStart)->sum(DB::raw('grand_total - refunded_total'));
        $prevOrders = $revenueOrders($yesterdayStart, $todayStart)->count();

        $visitors = (int) DB::table('storefront_visits')->where('date', $todayStart->toDateString())->sum('count');
        $prevVisitors = (int) DB::table('storefront_visits')->where('date', $yesterdayStart->toDateString())->sum('count');

        return [
            'revenue' => $revenue,
            'revenueDelta' => $this->delta($revenue, $prevRevenue),
            'orders' => $orders,
            'ordersDelta' => $this->delta($orders, $prevOrders),
            'visitors' => $visitors,
            'visitorsDelta' => $this->delta($visitors, $prevVisitors),
        ];
    }

    /**
     * Counts + destinations for the "needs attention" strip. Zero-count tiles
     * are dropped; the blade shows an all-clear note when nothing is pending.
     */
    protected function attentionItems(int $pendingCod, int $lowStockCount): array
    {
        // Paid or confirmed but not yet shipped — the fulfillment backlog per
        // the status machine (pending → confirmed → processing → shipped).
        $unfulfilled = Order::where(function ($q) {
            $q->whereIn('status', ['confirmed', 'processing'])
                ->orWhere(fn ($q) => $q->where('status', 'pending')->where('payment_status', 'paid'));
        })->count();

        // Returns are handled on the order detail screen, so the tile jumps to
        // the oldest open request.
        $oldestReturn = OrderReturn::where('status', 'requested')->oldest()->first();
        $openReturns = $oldestReturn ? OrderReturn::where('status', 'requested')->count() : 0;

        $items = [
            ['key' => 'unfulfilled', 'label' => 'Unfulfilled orders', 'count' => $unfulfilled, 'href' => route('admin.orders.index'), 'icon' => 'orders', 'tone' => 'info'],
            ['key' => 'cod', 'label' => 'COD to verify', 'count' => $pendingCod, 'href' => route('admin.orders.cod-queue'), 'icon' => 'check-circle', 'tone' => 'warning'],
            ['key' => 'returns', 'label' => 'Open returns', 'count' => $openReturns, 'href' => $oldestReturn ? route('admin.orders.show', $oldestReturn->order_id) : route('admin.orders.index'), 'icon' => 'arrow-down', 'tone' => 'danger'],
            ['key' => 'stock', 'label' => 'Low stock', 'count' => $lowStockCount, 'href' => route('admin.catalog.inventory.index'), 'icon' => 'warning', 'tone' => 'warning'],
        ];

        return array_values(array_filter($items, fn ($i) => $i['count'] > 0));
    }

    public function getAiInsightsEnabledProperty(): bool
    {
        return app(AddonRegistry::class)->installed('ai') && app(AiService::class)->featureEnabled('insights');
    }

    /**
     * AI "what needs attention" bullets from the numbers already on this page.
     * Button-triggered only (never on load); cached per day and provider/model,
     * so a regenerate the same day serves the cached copy — like the sales
     * summary on the Reports overview.
     */
    public function generateInsights(): void
    {
        if (! app(AddonRegistry::class)->installed('ai')) {
            abort(404);
        }

        $ai = app(AiService::class);
        if (! $ai->featureEnabled('insights')) {
            $this->dispatch('toast', message: 'Turn on dashboard insights in Settings → AI first', type: 'danger');

            return;
        }

        $system = 'You are an ecommerce analyst reviewing a small online store\'s dashboard. Reply with exactly 2 to 4 short bullet points, each on its own line starting with "- ". Weigh the figures the way a store owner actually should: conversion rate and average order value over raw traffic, unverified Cash-on-Delivery orders as a fulfillment/trust risk (not just a count), and low stock as lost-sale risk on specific products, not a generic warning. Cover: what changed versus the previous period, what needs attention today, and one concrete, specific suggestion tied to one of those figures. Plain language, no fluff, no markdown besides the leading dash. Only reference the figures provided — never invent or estimate numbers.';

        $cacheKey = sprintf('ai.dashboard-insights.%s.%s.%s', now()->toDateString(), $ai->provider(), $ai->model());

        try {
            $draft = Cache::remember(
                $cacheKey,
                now()->addDay(),
                fn () => $ai->complete($this->insightFacts(), $system, 400),
            );
        } catch (AiRequestException $e) {
            $this->dispatch('toast', message: 'AI generation failed: '.$e->getMessage(), type: 'danger');

            return;
        } catch (\Throwable) {
            $this->dispatch('toast', message: 'Could not generate insights — please try again.', type: 'danger');

            return;
        }

        $this->openAiReview([
            ['key' => 'insights', 'label' => 'Insights', 'before' => (string) $this->aiInsights, 'after' => $draft],
        ]);
    }

    /** Apply the AI-suggested insights the owner left checked in the review modal. */
    public function applyAiReview(): void
    {
        foreach ($this->aiReview as $item) {
            if ($item['selected'] && $item['key'] === 'insights') {
                $this->aiInsights = $item['value'];
            }
        }

        $this->discardAiReview();
    }

    /** The dashboard's own numbers, rendered as plain text for the prompt. */
    protected function insightFacts(): string
    {
        // Same 30-day window that drives the stat cards, so the bullets and the
        // cards always agree.
        $since = now()->subDays(29)->startOfDay();
        $prevFrom = $since->copy()->subDays(30);
        $prevTo = $since->copy()->subDay();

        $revenue = (int) Order::where('placed_at', '>=', $since)
            ->whereIn('status', Order::REVENUE_STATUSES)->sum(DB::raw('grand_total - refunded_total'));
        $orders = Order::where('placed_at', '>=', $since)
            ->whereIn('status', Order::REVENUE_STATUSES)->count();
        $prevRevenue = (int) Order::whereBetween('placed_at', [$prevFrom, $prevTo])
            ->whereIn('status', Order::REVENUE_STATUSES)->sum(DB::raw('grand_total - refunded_total'));
        $prevOrders = Order::whereBetween('placed_at', [$prevFrom, $prevTo])
            ->whereIn('status', Order::REVENUE_STATUSES)->count();

        $ordersToday = Order::where('placed_at', '>=', now()->startOfDay())->count();
        $visitors = (int) DB::table('storefront_visits')->where('date', '>=', $since->toDateString())->sum('count');
        $newCustomers = Customer::where('created_at', '>=', $since)->count();
        $pendingCod = Order::where('cod_verification_status', 'unverified')->count();

        // Same figures behind the Conversion and AOV stat cards elsewhere on this
        // page — reused here (not recomputed differently) so the AI's read of
        // "what needs attention" agrees with what the owner sees on screen.
        $conversion = $visitors > 0 ? round($orders / $visitors * 100, 1).'%' : '—';
        $aov = $orders > 0 ? format_money((int) round($revenue / $orders)) : '—';

        $lowStock = $this->lowStockProducts()
            ->map(fn ($p) => "{$p->name} ({$p->stock_qty} left)")
            ->implode('; ');

        return implode("\n", [
            'Window: last 30 days (vs the 30 days before)',
            'Revenue: '.format_money($revenue).' (previous: '.format_money($prevRevenue).')',
            "Orders: {$orders} (previous: {$prevOrders})",
            "Orders placed today so far: {$ordersToday}",
            'Storefront visitors: '.number_format($visitors),
            "Conversion rate (orders ÷ visitors): {$conversion}",
            "Average order value: {$aov}",
            "New customers: {$newCustomers}",
            "Orders awaiting COD verification: {$pendingCod}",
            'Low-stock products: '.($lowStock !== '' ? $lowStock : 'none'),
        ]);
    }

    public function render()
    {
        // One window drives the headline totals, their sparklines and the deltas
        // so all three agree (RPT / admin-audit #4).
        $to = now();
        $from = now()->subDays(29)->startOfDay();
        $since = $from;
        $prevFrom = $from->copy()->subDays(30);
        $prevTo = $from->copy()->subDay();

        $revenue = (int) Order::where('placed_at', '>=', $since)
            ->whereIn('status', Order::REVENUE_STATUSES)
            ->sum(DB::raw('grand_total - refunded_total'));

        // Same revenue-eligible basis for the order count and conversion numerator,
        // so the three figures on the card agree (RPT-02).
        $orderCount = Order::where('placed_at', '>=', $since)
            ->whereIn('status', Order::REVENUE_STATUSES)
            ->count();

        // Real storefront traffic drives a sessions-based conversion (orders ÷
        // visitors); falls back to nothing when there's no traffic yet.
        $visitors = (int) DB::table('storefront_visits')->where('date', '>=', $since->toDateString())->sum('count');
        $conversion = $visitors > 0 ? round($orderCount / $visitors * 100, 1).'%' : '—';

        $newCustomers = Customer::where('created_at', '>=', $since)->count();

        // Previous 30-day window, for period-over-period deltas.
        $prevRevenue = (int) Order::whereBetween('placed_at', [$prevFrom, $prevTo])
            ->whereIn('status', Order::REVENUE_STATUSES)->sum(DB::raw('grand_total - refunded_total'));
        $prevOrders = (int) Order::whereBetween('placed_at', [$prevFrom, $prevTo])
            ->whereIn('status', Order::REVENUE_STATUSES)->count();
        $prevVisitors = (int) DB::table('storefront_visits')
            ->whereBetween('date', [$prevFrom->toDateString(), $prevTo->toDateString()])->sum('count');
        $prevConv = $prevVisitors > 0 ? $prevOrders / $prevVisitors * 100 : 0;
        $curConv = $visitors > 0 ? $orderCount / $visitors * 100 : 0;
        $prevNewCustomers = Customer::whereBetween('created_at', [$prevFrom, $prevTo])->count();

        $revenueSeries = $this->series($from, $to, 'revenue');
        $ordersSeries = $this->series($from, $to, 'orders');

        $lowStock = $this->lowStockProducts();

        $pendingCod = Order::where('cod_verification_status', 'unverified')->count();

        $journey = app(JourneyService::class);

        return View::make('reports::livewire.dashboard', [
            'today' => $this->todayStats(),
            'attention' => $this->attentionItems($pendingCod, $this->lowStockQuery()->count()),
            'stats' => [
                ['label' => 'Revenue (30d)', 'value' => format_money($revenue), 'hint' => 'vs previous 30 days', 'tone' => 'primary', 'icon' => 'reports', 'delta' => $this->delta($revenue, $prevRevenue), 'series' => $revenueSeries],
                ['label' => 'Orders (30d)', 'value' => (string) $orderCount, 'hint' => 'vs previous 30 days', 'tone' => 'info', 'icon' => 'orders', 'delta' => $this->delta($orderCount, $prevOrders), 'series' => $ordersSeries],
                ['label' => 'Visitors (30d)', 'value' => number_format($visitors), 'hint' => 'vs previous 30 days', 'tone' => 'info', 'icon' => 'customers', 'delta' => $this->delta($visitors, $prevVisitors), 'series' => []],
                ['label' => 'Conversion', 'value' => $conversion, 'hint' => 'Orders ÷ visitors', 'tone' => 'success', 'icon' => 'products', 'delta' => $this->delta($curConv, $prevConv), 'series' => []],
                ['label' => 'New customers', 'value' => (string) $newCustomers, 'hint' => 'vs previous 30 days', 'tone' => 'warning', 'icon' => 'customers', 'delta' => $this->delta($newCustomers, $prevNewCustomers), 'series' => []],
            ],
            'revenueSeries' => $revenueSeries,
            'salesByCategory' => $this->salesByCategory($from, $to),
            'recentOrders' => Order::latest('placed_at')->limit(6)->get(),
            'lowStock' => $lowStock,
            'pendingCod' => $pendingCod,
            'journeySteps' => $journey->steps(),
            'journeyCompleted' => $journey->completed(),
            'journeyTotal' => $journey->total(),
            'journeyComplete' => $journey->isComplete(),
        ]);
    }
}
