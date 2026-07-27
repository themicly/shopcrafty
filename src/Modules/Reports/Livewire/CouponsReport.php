<?php

namespace Themicly\Shopcrafty\Modules\Reports\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Reports\Concerns\HasDateRange;

class CouponsReport extends Component
{
    use HasDateRange;

    /** Financial/marketing reports are owner-only, like the rest of Reports (RPT-08). */
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);
    }

    /** Base query: redemptions in range, joined to the order they were used on — revenue-eligible orders only. */
    protected function redemptions(Carbon $from, Carbon $to)
    {
        return DB::table('marketing_coupon_redemptions as r')
            ->join('orders as o', 'o.id', '=', 'r.order_id')
            ->whereBetween('r.created_at', [$from, $to->copy()->endOfDay()])
            ->whereIn('o.status', Order::REVENUE_STATUSES);
    }

    protected function summary(Carbon $from, Carbon $to): array
    {
        $row = $this->redemptions($from, $to)
            ->selectRaw('
                COUNT(*) as uses,
                COALESCE(SUM(r.discount_amount), 0) as discount_given,
                COALESCE(SUM(o.grand_total - o.refunded_total), 0) as revenue
            ')
            ->first();

        $uses = (int) ($row->uses ?? 0);
        $discountGiven = (int) ($row->discount_given ?? 0);

        return [
            'uses' => $uses,
            'discountGiven' => $discountGiven,
            'revenueGenerated' => (int) ($row->revenue ?? 0),
            'avgDiscount' => $uses > 0 ? (int) round($discountGiven / $uses) : 0,
        ];
    }

    /** Every coupon used in range, ranked by how often it was redeemed. */
    protected function topCoupons(Carbon $from, Carbon $to, int $limit = 10)
    {
        return $this->redemptions($from, $to)
            ->join('marketing_coupons as c', 'c.id', '=', 'r.coupon_id')
            ->selectRaw('
                c.code, c.name, c.type,
                COUNT(*) as uses,
                COALESCE(SUM(r.discount_amount), 0) as discount_given,
                COALESCE(SUM(o.grand_total - o.refunded_total), 0) as revenue
            ')
            ->groupBy('c.id', 'c.code', 'c.name', 'c.type')
            ->orderByDesc('uses')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'code' => $r->code,
                'name' => $r->name,
                'type' => $r->type,
                'uses' => (int) $r->uses,
                'discountGiven' => (int) $r->discount_given,
                'revenue' => (int) $r->revenue,
            ]);
    }

    public function render()
    {
        [$from, $to] = $this->period();

        return View::make('reports::livewire.coupons-report', [
            'summary' => $this->summary($from, $to),
            'topCoupons' => $this->topCoupons($from, $to),
        ]);
    }
}
