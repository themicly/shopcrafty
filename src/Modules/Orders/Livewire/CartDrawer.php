<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\Variant;
use Themicly\Shopcrafty\Modules\Marketing\Contracts\CouponValidator;
use Themicly\Shopcrafty\Modules\Marketing\Services\BoughtTogether;
use Themicly\Shopcrafty\Modules\Orders\Models\ShippingZone;
use Themicly\Shopcrafty\Modules\Orders\Services\CartService;
use Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService;

class CartDrawer extends Component
{
    public bool $open = false;

    /** Coupon the shopper types into the drawer. */
    public string $couponCode = '';

    /** Validation feedback shown under the coupon field (error, or null when applied). */
    public ?string $couponMessage = null;

    /** Breakpoint below which the header trigger hides — the mobile bottom nav has its own cart icon. */
    public string $hideTriggerBelow = 'md';

    public function mount(string $hideTriggerBelow = 'md'): void
    {
        $this->hideTriggerBelow = $hideTriggerBelow;
        // Pre-fill the field from a previously applied code so it survives reloads.
        $this->couponCode = (string) session('cart_coupon', '');
    }

    #[On('cart-add')]
    public function addToCart(int $productId, ?int $variantId = null, int $qty = 1): void
    {
        // Variable products need a variant chosen — send the shopper to the product
        // page instead of silently doing nothing (UI-04).
        if (! app(CartService::class)->add($productId, $variantId, $qty)) {
            $product = Product::find($productId);

            if ($product) {
                $this->redirectRoute('storefront.product', $product->slug, navigate: true);
            }

            return;
        }

        $this->dispatch('cart-item-added', item: $this->trackedItem($productId, $variantId, $qty));
        $this->open = true;
    }

    /**
     * Add then go straight to checkout ("Buy it now"). Same variant guard as
     * addToCart — a variable product with no chosen variant bounces to its page.
     */
    #[On('cart-buy-now')]
    public function buyNow(int $productId, ?int $variantId = null, int $qty = 1): void
    {
        if (! app(CartService::class)->add($productId, $variantId, $qty)) {
            $product = Product::find($productId);

            if ($product) {
                $this->redirectRoute('storefront.product', $product->slug, navigate: true);
            }

            return;
        }

        $this->dispatch('cart-item-added', item: $this->trackedItem($productId, $variantId, $qty));
        $this->redirectRoute('storefront.checkout', navigate: true);
    }

    #[On('open-cart')]
    public function openCart(): void
    {
        $this->open = true;
    }

    public function increment(int $itemId): void
    {
        $cart = app(CartService::class);
        $item = $cart->current()->items()->find($itemId);
        if ($item) {
            $cart->updateQty($itemId, $item->qty + 1);
        }
    }

    public function decrement(int $itemId): void
    {
        $cart = app(CartService::class);
        $item = $cart->current()->items()->find($itemId);
        if ($item) {
            $cart->updateQty($itemId, $item->qty - 1);
        }
    }

    public function remove(int $itemId): void
    {
        app(CartService::class)->remove($itemId);
    }

    /**
     * Validate + apply the typed coupon and remember it on the session so it
     * carries into checkout. Mirrors Checkout::applyCoupon (same rate limiting).
     */
    public function applyCoupon(CartService $cart): void
    {
        $code = trim($this->couponCode);

        if ($code === '') {
            $this->removeCoupon();

            return;
        }

        // Stop coupon-code enumeration: cap attempts per visitor.
        $key = 'coupon-apply:'.(auth('customer')->id() ?? request()->ip());

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->couponMessage = 'Too many attempts. Please try again in a minute.';

            return;
        }

        RateLimiter::hit($key, 60);

        $result = $this->validateCoupon($cart, $code);

        if ($result['ok']) {
            session(['cart_coupon' => $result['code']]);
            $this->couponCode = (string) $result['code'];
            $this->couponMessage = null;
        } else {
            session()->forget('cart_coupon');
            $this->couponMessage = $result['message'];
        }
    }

    public function removeCoupon(): void
    {
        session()->forget('cart_coupon');
        $this->couponCode = '';
        $this->couponMessage = null;
    }

    /** Run the shared coupon validator against the current cart. */
    protected function validateCoupon(CartService $cart, string $code): array
    {
        $items = $cart->items()->map(fn ($item) => [
            'product_id' => (int) $item->product_id,
            'category_id' => $item->product?->category_id,
            'price' => $item->unitPrice(),
            'qty' => (int) $item->qty,
        ])->all();

        return app(CouponValidator::class)
            ->validate($code, $cart->subtotal(), auth('customer')->id(), $items);
    }

    public function render()
    {
        $cart = app(CartService::class);
        $items = $cart->items();
        $subtotal = $cart->subtotal();
        $count = $cart->count();

        // Keeps any out-of-component badge (e.g. the mobile bottom nav) in sync on every mutation.
        $this->dispatch('cart-count-updated', count: $count);

        // Re-validate any applied coupon every render so the discount stays
        // authoritative as the cart changes (never trust a stored amount). A
        // now-invalid code (expired, cart dropped below minimum) is dropped.
        $discount = 0;
        $freeShipping = false;
        $appliedCode = null;

        if ($code = session('cart_coupon')) {
            $result = $this->validateCoupon($cart, (string) $code);

            if ($result['ok']) {
                $discount = $result['discount'];
                $freeShipping = $result['free_shipping'];
                $appliedCode = $result['code'];
            } else {
                session()->forget('cart_coupon');
            }
        }

        // Free-shipping meter from the first zone that offers a threshold.
        $threshold = ShippingZone::where('is_active', true)->whereNotNull('free_above')->min('free_above');
        $remaining = $threshold ? max(0, $threshold - $subtotal) : null;
        $progress = $threshold ? min(100, (int) round($subtotal / $threshold * 100)) : null;

        return View::make('orders::livewire.cart-drawer', [
            'items' => $items,
            'count' => $count,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'freeShipping' => $freeShipping,
            'appliedCode' => $appliedCode,
            'freeShipThreshold' => $threshold,
            'freeShipRemaining' => $remaining,
            'freeShipProgress' => $progress,
            'suggestion' => $this->suggestion($items),
        ]);
    }

    /** Normalized add_to_cart payload for the bzTrack JS bridge (GA4/FB Pixel). */
    protected function trackedItem(int $productId, ?int $variantId, int $qty): ?array
    {
        $product = Product::find($productId);

        if (! $product) {
            return null;
        }

        $variant = $variantId ? Variant::find($variantId) : null;
        $currency = app(CurrencyService::class);
        $price = $currency->toBaseMajor($variant->price ?? $product->price);

        return [
            'currency' => $currency->baseCode(),
            'value' => round($price * $qty, 2),
            'items' => [[
                'id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'quantity' => $qty,
                'variant' => $variant ? collect((array) $variant->options)->implode(', ') : null,
            ]],
        ];
    }

    /** One "frequently bought together" pick for what's already in the cart. */
    protected function suggestion($items)
    {
        if ($items->isEmpty()) {
            return null;
        }

        $inCart = $items->pluck('product_id')->filter()->all();
        $bought = app(BoughtTogether::class);

        return collect($inCart)
            ->flatMap(fn ($id) => $bought->forProduct((int) $id, 3))
            ->reject(fn ($p) => in_array($p->id, $inCart, true))
            ->unique('id')
            ->first();
    }
}
