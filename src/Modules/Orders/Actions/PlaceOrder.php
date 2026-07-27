<?php

namespace Themicly\Shopcrafty\Modules\Orders\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Themicly\Shopcrafty\Core\Support\DemoMode;
use Themicly\Shopcrafty\Core\Support\DemoModeException;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\Variant;
use Themicly\Shopcrafty\Modules\Marketing\Contracts\CouponValidator;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderPlaced;
use Themicly\Shopcrafty\Modules\Orders\Exceptions\InsufficientStockException;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Models\ShippingZone;
use Themicly\Shopcrafty\Modules\Orders\Services\CartService;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentRegistry;
use Themicly\Shopcrafty\Modules\Orders\Services\TaxService;

class PlaceOrder
{
    public function __construct(
        protected CartService $cart,
        protected PaymentRegistry $payments,
    ) {}

    /**
     * @param  array{name:string, phone:?string, email:?string, address:string, city:string, region:?string, shipping_zone_id:?int, payment_method:string, notes:?string}  $data
     */
    public function handle(array $data): Order
    {
        // Fail fast with a clear, order-specific message before any stock
        // locking / coupon validation runs. The global demo-mode write guard
        // (Themicly\Shopcrafty\Core\Support\DemoMode) would also catch the Order::create()
        // below, but this gives a nicer message and avoids the wasted work.
        if (DemoMode::blocksAction()) {
            throw new DemoModeException(__('checkout.order_placement_disabled_demo'));
        }

        // Only genuinely enabled gateways may be used (defence in depth — the
        // Livewire rules already allow-list, but manual/API callers land here too).
        $gateway = $this->payments->find($data['payment_method']);
        if (! $gateway || ! $gateway->isEnabled()) {
            throw new RuntimeException(__('checkout.payment_method_unavailable'));
        }

        return DB::transaction(function () use ($data) {
            $items = $this->cart->items();

            if ($items->isEmpty()) {
                throw new RuntimeException(__('checkout.cart_is_empty'));
            }

            $subtotal = 0;

            // Lock stock rows and validate availability before committing.
            foreach ($items as $item) {
                if ($item->variant_id) {
                    $variant = Variant::whereKey($item->variant_id)->lockForUpdate()->first();
                    if (! $variant || $variant->stock_qty < $item->qty) {
                        throw new InsufficientStockException(__('checkout.insufficient_stock', ['product' => $item->product?->name]));
                    }
                } elseif ($item->product?->track_inventory) {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                    if (! $product || $product->stock_qty < $item->qty) {
                        throw new InsufficientStockException(__('checkout.insufficient_stock', ['product' => $item->product?->name]));
                    }
                }

                $subtotal += $item->lineTotal();
            }

            // A cart of only digital goods never ships — no zone, no shipping cost,
            // regardless of what the client submitted.
            $requiresShipping = $items->contains(fn ($item) => (bool) $item->product?->requires_shipping);

            // Only active zones are billable (a deactivated zone must not grant a
            // stale/free rate — see SET-02).
            $zone = ($requiresShipping && ($data['shipping_zone_id'] ?? null))
                ? ShippingZone::where('is_active', true)->find($data['shipping_zone_id'])
                : null;

            // Never trust the client for money. Re-derive discount / free shipping
            // from the coupon code against the *live* cart (ORD-01 / ORD-04).
            [$discount, $freeShipping, $couponCode] = $this->resolveCoupon($data, $items, $subtotal);

            $shipping = $freeShipping ? 0 : ($zone ? $zone->costFor($subtotal) : 0);

            $taxBase = max(0, $subtotal - $discount);
            $tax = app(TaxService::class)->taxFor($taxBase);
            $grand = $taxBase + app(TaxService::class)->addedTaxFor($taxBase) + $shipping;

            $order = Order::create([
                'number' => $this->generateNumber(),
                'customer_id' => $data['customer_id'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'],
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'shipping_total' => $shipping,
                'tax_total' => $tax,
                'grand_total' => $grand,
                'currency' => settings('localization.currency_code', 'USD'),
                'coupon_code' => $couponCode,
                'notes' => $data['notes'] ?? null,
                'source' => 'web',
                'placed_at' => now(),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'name' => $item->product?->name ?? 'Item',
                    'variant_label' => $item->variant ? implode(' / ', array_values($item->variant->options)) : null,
                    'sku' => $item->variant?->sku ?? $item->product?->sku,
                    'price' => $item->unitPrice(),
                    'qty' => $item->qty,
                    'line_total' => $item->lineTotal(),
                ]);
            }

            $order->addresses()->create([
                'type' => 'shipping',
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'],
                'city' => $data['city'],
                'region' => $data['region'] ?? null,
                'location_id' => $data['location_id'] ?? null,
            ]);

            $order->history()->create(['to_status' => 'pending', 'note' => 'Order placed', 'actor' => 'customer']);

            // Prepaid orders reserve stock immediately; COD defers to confirmation
            // (see OrderStatusService) so unverified COD can't over-reserve inventory.
            if ($data['payment_method'] !== 'cod') {
                app(CommitStock::class)->handle($order);
            }

            $this->payments->find($data['payment_method'])?->process($order);

            event(new OrderPlaced($order));

            $this->cart->clear();

            return $order;
        });
    }

    /**
     * Re-validate the coupon server-side and return the authoritative
     * [discount, freeShipping, couponCode]. A cart that no longer qualifies
     * silently drops the coupon rather than trusting the client's numbers.
     *
     * @return array{0:int,1:bool,2:?string}
     */
    protected function resolveCoupon(array $data, $items, int $subtotal): array
    {
        $code = trim((string) ($data['coupon_code'] ?? ''));

        if ($code === '') {
            return [0, false, null];
        }

        $lines = $items->map(fn ($item) => [
            'product_id' => (int) $item->product_id,
            'category_id' => $item->product?->category_id,
            'price' => $item->unitPrice(),
            'qty' => (int) $item->qty,
        ])->all();

        // Locked — this is the authoritative check inside handle()'s transaction.
        $result = app(CouponValidator::class)->validate($code, $subtotal, $data['customer_id'] ?? null, $lines, lock: true);

        if (! ($result['ok'] ?? false)) {
            return [0, false, null];
        }

        return [
            min((int) $result['discount'], $subtotal),
            (bool) $result['free_shipping'],
            $result['code'],
        ];
    }

    /**
     * Order numbers double as the capability that unlocks the public thank-you
     * page, so they must be unguessable (not a sequential max(id)+1 — ORD-03/05).
     */
    protected function generateNumber(): string
    {
        $prefix = strtoupper((string) settings('general.order_number_prefix', 'SC'));

        do {
            $number = $prefix.'-'.strtoupper(Str::random(10));
        } while (Order::where('number', $number)->exists());

        return $number;
    }
}
