<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Contracts;

/**
 * Validates a coupon code against a cart. Consumed by Orders (checkout) without
 * coupling to the Marketing internals.
 */
interface CouponValidator
{
    /**
     * @param  array<int, array{product_id?:int, category_id?:?int, price:int, qty:int}>  $items  cart line items (for BOGO / scoped coupons)
     * @param  bool  $lock  row-lock the coupon; pass true only when placing an order, inside its transaction
     * @return array{ok: bool, discount: int, free_shipping: bool, code: ?string, message: ?string}
     */
    public function validate(string $code, int $subtotal, ?int $customerId = null, array $items = [], bool $lock = false): array;
}
