<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Services;

use Illuminate\Support\Facades\DB;
use Themicly\Shopcrafty\Modules\Marketing\Contracts\CouponValidator;
use Themicly\Shopcrafty\Modules\Marketing\Models\Coupon;
use Themicly\Shopcrafty\Modules\Marketing\Models\CouponRedemption;

class CouponService implements CouponValidator
{
    public function validate(string $code, int $subtotal, ?int $customerId = null, array $items = [], bool $lock = false): array
    {
        $fail = fn (string $message) => ['ok' => false, 'discount' => 0, 'free_shipping' => false, 'code' => null, 'message' => $message];

        $coupon = Coupon::query()
            ->when($lock, fn ($q) => $q->lockForUpdate())
            ->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($code))])
            ->first();

        if (! $coupon) {
            return $fail('That coupon code is not valid.');
        }

        if (! $coupon->isLive()) {
            return $fail("This coupon is {$coupon->statusLabel()}.");
        }

        if ($coupon->min_purchase && $subtotal < $coupon->min_purchase) {
            return $fail('Add more to your cart to use this coupon.');
        }

        if ($coupon->per_customer_limit) {
            // No guest identity to check past use against — require an account.
            if (! $customerId) {
                return $fail('Log in to use this coupon.');
            }

            $used = CouponRedemption::where('coupon_id', $coupon->id)->where('customer_id', $customerId)->count();
            if ($used >= $coupon->per_customer_limit) {
                return $fail('You have already used this coupon.');
            }
        }

        [$discount, $freeShipping] = $this->compute($coupon, $subtotal, $items);

        if ($discount === 0 && ! $freeShipping && $coupon->scope_type !== 'all') {
            return $fail('Your cart has no items eligible for this coupon.');
        }

        return ['ok' => true, 'discount' => $discount, 'free_shipping' => $freeShipping, 'code' => $coupon->code, 'message' => null];
    }

    /**
     * @param  array<int, array{product_id?:int, category_id?:?int, price:int, qty:int}>  $items
     * @return array{0: int, 1: bool} [discount, freeShipping]
     */
    protected function compute(Coupon $coupon, int $subtotal, array $items): array
    {
        $eligible = $this->eligibleItems($coupon, $items);
        $base = $coupon->scope_type === 'all'
            ? $subtotal
            : array_sum(array_map(fn ($i) => $i['price'] * $i['qty'], $eligible));

        return match ($coupon->type) {
            'percentage' => [(int) round($base * min(100, $coupon->value) / 100), false],
            'fixed' => [min($coupon->value, $base), false],
            // Free shipping applies cart-wide for "all" scope, otherwise only when
            // the cart actually contains scoped items (MKT-05).
            'free_shipping' => [0, $coupon->scope_type === 'all' || ! empty($eligible)],
            'bogo' => [$this->bogoDiscount($coupon, $eligible), false],
            default => [0, false],
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function eligibleItems(Coupon $coupon, array $items): array
    {
        if ($coupon->scope_type === 'all' || empty($coupon->scope_ids)) {
            return $items;
        }

        $ids = array_map('intval', $coupon->scope_ids);
        $key = $coupon->scope_type === 'category' ? 'category_id' : 'product_id';

        return array_values(array_filter($items, fn ($i) => in_array((int) ($i[$key] ?? 0), $ids, true)));
    }

    /** @param array<int, array<string, mixed>> $eligible */
    protected function bogoDiscount(Coupon $coupon, array $eligible): int
    {
        $buy = max(1, (int) $coupon->buy_qty);
        $get = max(1, (int) $coupon->get_qty);

        // Expand to individual unit prices, cheapest first — the cheapest qualify as free.
        $units = [];
        foreach ($eligible as $item) {
            for ($n = 0; $n < (int) $item['qty']; $n++) {
                $units[] = (int) $item['price'];
            }
        }

        $group = $buy + $get;
        if (count($units) < $group) {
            return 0;
        }

        sort($units);
        $freeCount = intdiv(count($units), $group) * $get;

        return (int) array_sum(array_slice($units, 0, $freeCount));
    }

    /**
     * Record a redemption after an order is placed — idempotent (one per order,
     * MKT-03) with a limit-respecting atomic increment (MKT-01 / ORD-09).
     */
    public function redeem(Coupon $coupon, int $orderId, ?int $customerId, int $discount): void
    {
        DB::transaction(function () use ($coupon, $orderId, $customerId, $discount) {
            $redemption = CouponRedemption::firstOrCreate(
                ['coupon_id' => $coupon->id, 'order_id' => $orderId],
                ['customer_id' => $customerId, 'discount_amount' => $discount],
            );

            if (! $redemption->wasRecentlyCreated) {
                return; // already counted for this order
            }

            // Only bump the counter while the coupon is still within its limit.
            Coupon::whereKey($coupon->id)
                ->where(fn ($q) => $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit'))
                ->increment('used_count');
        });
    }
}
