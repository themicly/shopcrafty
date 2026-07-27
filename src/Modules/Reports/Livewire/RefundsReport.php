<?php

namespace Themicly\Shopcrafty\Modules\Reports\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Reports\Concerns\HasDateRange;

class RefundsReport extends Component
{
    use HasDateRange;

    /** Financial reports are owner-only, like the rest of Reports (RPT-08). */
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);
    }

    /**
     * Refunded amount is scoped to when the refund happened; revenue is scoped
     * to orders placed in the same window. An order refunded weeks after it was
     * placed will pull the % slightly out of true, but that's an acceptable
     * approximation for a headline ratio rather than an accounting reconciliation.
     */
    protected function summary(Carbon $from, Carbon $to): array
    {
        $refundedAmount = (int) DB::table('order_refunds')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->sum('amount');

        $revenue = (int) Order::whereBetween('placed_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('status', Order::REVENUE_STATUSES)
            ->sum('grand_total');

        return [
            'refundedAmount' => $refundedAmount,
            'refundPct' => $revenue > 0 ? round($refundedAmount / $revenue * 100, 1) : null,
        ];
    }

    /** Reasons are free text, not a fixed enum — grouped case/whitespace-insensitively. */
    protected function topReasons(Carbon $from, Carbon $to, int $limit = 6)
    {
        return DB::table('order_refunds')
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->whereNotNull('reason')
            ->where('reason', '!=', '')
            ->selectRaw('LOWER(TRIM(reason)) as reason, COUNT(*) as uses, SUM(amount) as amount')
            ->groupBy(DB::raw('LOWER(TRIM(reason))'))
            ->orderByDesc('uses')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => ['reason' => $r->reason, 'uses' => (int) $r->uses, 'amount' => (int) $r->amount]);
    }

    /**
     * Units refunded per product, via structured return line items — only
     * refunds tied to a return with itemized lines (a manual, return-less
     * refund has no line-item detail to attribute to a product).
     */
    protected function mostRefundedProducts(Carbon $from, Carbon $to, int $limit = 8)
    {
        $rows = DB::table('order_refunds as f')
            ->join('order_returns as r', 'r.id', '=', 'f.return_id')
            ->join('order_return_items as ri', 'ri.return_id', '=', 'r.id')
            ->join('order_items as oi', 'oi.id', '=', 'ri.order_item_id')
            ->whereBetween('f.created_at', [$from, $to->copy()->endOfDay()])
            ->whereNotNull('oi.product_id')
            ->selectRaw('oi.product_id, SUM(ri.qty) as qty')
            ->groupBy('oi.product_id')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get();

        $products = Product::with('media')->whereIn('id', $rows->pluck('product_id'))->get()->keyBy('id');

        return $rows->map(fn ($r) => ['product' => $products->get($r->product_id), 'qty' => (int) $r->qty])
            ->filter(fn ($r) => $r['product'] !== null)
            ->values();
    }

    /** Same line-item trail as mostRefundedProducts(), rolled up to category. */
    protected function mostRefundedCategories(Carbon $from, Carbon $to, int $limit = 6)
    {
        return DB::table('order_refunds as f')
            ->join('order_returns as r', 'r.id', '=', 'f.return_id')
            ->join('order_return_items as ri', 'ri.return_id', '=', 'r.id')
            ->join('order_items as oi', 'oi.id', '=', 'ri.order_item_id')
            ->leftJoin('catalog_products as p', 'p.id', '=', 'oi.product_id')
            ->leftJoin('catalog_categories as cat', 'cat.id', '=', 'p.category_id')
            ->whereBetween('f.created_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw("COALESCE(cat.name, 'Uncategorized') as category, SUM(ri.qty) as qty")
            ->groupBy('cat.name')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => ['category' => $r->category, 'qty' => (int) $r->qty]);
    }

    public function render()
    {
        [$from, $to] = $this->period();

        return View::make('reports::livewire.refunds-report', [
            'summary' => $this->summary($from, $to),
            'topReasons' => $this->topReasons($from, $to),
            'mostRefundedProducts' => $this->mostRefundedProducts($from, $to),
            'mostRefundedCategories' => $this->mostRefundedCategories($from, $to),
        ]);
    }
}
