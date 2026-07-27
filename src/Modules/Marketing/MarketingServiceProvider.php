<?php

namespace Themicly\Shopcrafty\Modules\Marketing;

use Illuminate\Support\Facades\Event;
use Themicly\Shopcrafty\Core\Module\ModuleServiceProvider;
use Themicly\Shopcrafty\Modules\Marketing\Contracts\CouponValidator;
use Themicly\Shopcrafty\Modules\Marketing\Models\Coupon;
use Themicly\Shopcrafty\Modules\Marketing\Services\BoughtTogether;
use Themicly\Shopcrafty\Modules\Marketing\Services\CouponService;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderPlaced;

class MarketingServiceProvider extends ModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Marketing';
    }

    protected function modulePath(): string
    {
        return __DIR__;
    }

    protected function registerModule(): void
    {
        $this->app->bind(CouponValidator::class, CouponService::class);
    }

    protected function bootModule(): void
    {
        Event::listen(OrderPlaced::class, function (OrderPlaced $event) {
            $order = $event->order;

            // Record coupon redemption for every applied coupon — including
            // free-shipping coupons, which produce discount_total = 0.
            if ($order->coupon_code) {
                $coupon = Coupon::whereRaw('LOWER(code) = ?', [mb_strtolower($order->coupon_code)])->first();
                if ($coupon) {
                    app(CouponService::class)->redeem($coupon, $order->id, $order->customer_id, $order->discount_total);
                }
            }

            // Update frequently-bought-together weights.
            app(BoughtTogether::class)->recordOrder($order->loadMissing('items'));
        });
    }
}
