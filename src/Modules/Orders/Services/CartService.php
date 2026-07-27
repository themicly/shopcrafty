<?php

namespace Themicly\Shopcrafty\Modules\Orders\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Orders\Models\Cart;
use Themicly\Shopcrafty\Modules\Orders\Models\CartItem;

class CartService
{
    protected ?Cart $cart = null;

    public function current(): Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        $token = session('cart_token');

        if (! $token) {
            $token = (string) Str::uuid();
            session(['cart_token' => $token]);
        }

        return $this->cart = Cart::firstOrCreate(['token' => $token]);
    }

    public function add(int $productId, ?int $variantId = null, int $qty = 1): bool
    {
        // A product with variants can't be added without a choice — quick-add paths
        // that omit the variant must send the shopper to the product page (UI-04/06).
        if ($variantId === null && Product::whereKey($productId)->has('variants')->exists()) {
            return false;
        }

        $cart = $this->current();

        $item = $cart->items()
            ->where('product_id', $productId)
            ->when($variantId, fn ($q) => $q->where('variant_id', $variantId), fn ($q) => $q->whereNull('variant_id'))
            ->first();

        if ($item) {
            $item->increment('qty', max(1, $qty));
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'qty' => max(1, $qty),
            ]);
        }

        return true;
    }

    public function updateQty(int $itemId, int $qty): void
    {
        $item = $this->current()->items()->find($itemId);

        if (! $item) {
            return;
        }

        if ($qty <= 0) {
            $item->delete();
        } else {
            $item->update(['qty' => $qty]);
        }
    }

    public function remove(int $itemId): void
    {
        $this->current()->items()->whereKey($itemId)->delete();
    }

    /** @return Collection<int, CartItem> */
    public function items(): Collection
    {
        return $this->current()->items()->with(['product.media', 'variant'])->get();
    }

    public function subtotal(): int
    {
        return (int) $this->items()->sum(fn (CartItem $i) => $i->lineTotal());
    }

    public function count(): int
    {
        return (int) $this->items()->sum('qty');
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Does this cart contain anything that physically ships? A cart of only
     * digital goods skips the shipping address, rate and COD at checkout.
     */
    public function requiresShipping(): bool
    {
        return $this->items()->contains(fn (CartItem $i) => (bool) $i->product?->requires_shipping);
    }

    public function clear(): void
    {
        $this->current()->items()->delete();
    }
}
