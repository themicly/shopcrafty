<div x-data="{ open: @entangle('open') }">
    {{-- Cart trigger (lives in the header) — hidden below the theme's mobile
         breakpoint, since the bottom nav carries its own cart icon there. --}}
    <button @click="open = true" data-cart-target class="relative hidden {{ $hideTriggerBelow }}:grid h-10 w-10 place-items-center rounded-full hover:bg-black/5" aria-label="{{ __('storefront.cart') }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5" style="color: var(--st-ink)"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
        @if ($count > 0)
            <span class="absolute -end-0.5 -top-0.5 grid h-5 min-w-5 place-items-center rounded-full px-1 text-[11px] font-bold" style="background: var(--st-accent); color: #fff">{{ $count }}</span>
        @endif
    </button>

    {{-- Drawer — teleported to <body> so it always stacks above everything on
         the page (the header's own sticky z-index would otherwise trap it in
         a local stacking context below body-level siblings like the mobile
         bottom nav, regardless of this z-50). --}}
    <template x-teleport="body">
    <div x-show="open" x-cloak class="fixed inset-0 z-50" style="display:none" @keydown.escape.window="open = false">
        <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/40" @click="open = false"></div>

        <div x-show="open" class="fixed inset-y-0 end-0 flex w-full max-w-md flex-col" style="background: var(--st-bg)"
            x-transition:enter="transition ease-out duration-250" x-transition:enter-start="ltr:translate-x-full rtl:-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="ltr:translate-x-full rtl:-translate-x-full">

            <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--st-line)">
                <h2 class="st-display text-lg font-semibold" style="color: var(--st-ink)">{{ __('storefront.your_cart') }} ({{ $count }})</h2>
                <button @click="open = false" class="text-2xl leading-none" style="color: var(--st-ink-soft)" aria-label="{{ __('storefront.close') }}">&times;</button>
            </div>

            {{-- Free-shipping meter --}}
            @if ($freeShipThreshold)
                <div class="border-b px-5 py-3" style="border-color: var(--st-line)">
                    @if ($freeShipRemaining > 0)
                        <p class="text-xs" style="color: var(--st-ink-soft)">{!! __('storefront.free_shipping_progress', ['amount' => '<strong style="color: var(--st-ink)">'.format_money($freeShipRemaining).'</strong>']) !!}</p>
                    @else
                        <p class="text-xs font-medium" style="color: var(--st-ink)">🎉 {{ __('storefront.free_shipping_unlocked') }}</p>
                    @endif
                    <div class="mt-2 h-1.5 overflow-hidden rounded-full" style="background: var(--st-surface)">
                        <div class="h-full rounded-full transition-all duration-500" style="width: {{ $freeShipProgress }}%; background: var(--st-accent)"></div>
                    </div>
                </div>
            @endif

            {{-- Items --}}
            <div class="flex-1 overflow-y-auto px-5">
                @forelse ($items as $item)
                    <div class="flex gap-3 border-b py-4" style="border-color: var(--st-line)" wire:key="ci-{{ $item->id }}">
                        <div class="h-20 w-16 shrink-0 overflow-hidden" style="border-radius: var(--st-radius-sm); background: var(--st-surface)">
                            @if ($item->product?->featuredImage())
                                <img src="{{ $item->product->featuredImage()->path }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium" style="color: var(--st-ink)">{{ $item->product?->name }}</p>
                            @if ($item->variant)
                                <p class="text-xs" style="color: var(--st-ink-soft)">{{ implode(' / ', array_values($item->variant->options)) }}</p>
                            @endif
                            <div class="mt-2 flex items-center justify-between">
                                <div class="inline-flex items-center border" style="border-color: var(--st-line); border-radius: var(--st-radius-sm)">
                                    <button wire:click="decrement({{ $item->id }})" aria-label="{{ __('storefront.decrease_quantity') }}" class="grid h-7 w-7 place-items-center" style="color: var(--st-ink)">&minus;</button>
                                    <span class="w-7 text-center text-sm" style="color: var(--st-ink)">{{ $item->qty }}</span>
                                    <button wire:click="increment({{ $item->id }})" aria-label="{{ __('storefront.increase_quantity') }}" class="grid h-7 w-7 place-items-center" style="color: var(--st-ink)">+</button>
                                </div>
                                <span class="text-sm font-semibold" style="color: var(--st-ink)">{{ format_money($item->lineTotal()) }}</span>
                            </div>
                        </div>
                        <button wire:click="remove({{ $item->id }})" class="self-start text-xs" style="color: var(--st-ink-soft)" aria-label="{{ __('storefront.remove') }}">{{ __('storefront.remove') }}</button>
                    </div>
                @empty
                    <div class="py-20 text-center">
                        <p class="st-display text-xl" style="color: var(--st-ink)">{{ __('storefront.cart_empty') }}</p>
                        <button @click="open = false" class="mt-3 text-sm font-medium" style="color: var(--st-accent)">{{ __('storefront.continue_shopping') }}</button>
                    </div>
                @endforelse
            </div>

            {{-- Frequently bought together --}}
            @if ($count > 0 && $suggestion)
                <div class="border-t px-5 py-4" style="border-color: var(--st-line)">
                    <p class="mb-2 text-xs font-medium uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.frequently_bought_together') }}</p>
                    <div class="flex items-center gap-3">
                        <a href="{{ url('/product/'.$suggestion->slug) }}" class="h-14 w-12 shrink-0 overflow-hidden" style="border-radius: var(--st-radius-sm); background: var(--st-surface)">
                            @if ($suggestion->media->first())
                                <img src="{{ $suggestion->media->first()->path }}" alt="{{ $suggestion->name }}" class="h-full w-full object-cover">
                            @endif
                        </a>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium" style="color: var(--st-ink)">{{ $suggestion->name }}</p>
                            <p class="text-sm" style="color: var(--st-ink-soft)">{{ format_money($suggestion->price) }}</p>
                        </div>
                        <button type="button" wire:click="addToCart({{ $suggestion->id }})"
                            class="shrink-0 px-3 py-2 text-xs font-semibold" style="background: var(--st-surface); color: var(--st-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.add') }}</button>
                    </div>
                </div>
            @endif

            {{-- Footer --}}
            @if ($count > 0)
                <div class="border-t px-5 py-4" style="border-color: var(--st-line)">
                    {{-- Coupon --}}
                    <form wire:submit="applyCoupon" class="mb-3">
                        <label for="cart-coupon" class="sr-only">{{ __('storefront.coupon_code') }}</label>
                        <div class="flex gap-2">
                            <input id="cart-coupon" type="text" wire:model="couponCode" placeholder="{{ __('storefront.coupon_code') }}"
                                class="min-w-0 flex-1 border px-3 py-2 text-sm outline-none"
                                style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                            <button type="submit" class="shrink-0 px-3 py-2 text-xs font-semibold" style="background: var(--st-surface); color: var(--st-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.apply') }}</button>
                        </div>
                        @if ($appliedCode)
                            <div class="mt-2 flex items-center justify-between text-xs" style="color: var(--st-ink)">
                                <span>✓ {{ __('storefront.coupon_applied') }}: <strong>{{ $appliedCode }}</strong>{{ $freeShipping ? ' — '.__('storefront.free_shipping_unlocked') : '' }}</span>
                                <button type="button" wire:click="removeCoupon" style="color: var(--st-ink-soft)">{{ __('storefront.remove') }}</button>
                            </div>
                        @elseif ($couponMessage)
                            <p class="mt-2 text-xs" style="color: var(--st-accent)">{{ $couponMessage }}</p>
                        @endif
                    </form>

                    <div class="mb-1 flex items-center justify-between">
                        <span class="text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.subtotal') }}</span>
                        <span class="text-sm" style="color: var(--st-ink)">{{ format_money($subtotal) }}</span>
                    </div>
                    @if ($discount > 0)
                        <div class="mb-1 flex items-center justify-between">
                            <span class="text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.discount') }}</span>
                            <span class="text-sm" style="color: var(--st-ink)">&minus;{{ format_money($discount) }}</span>
                        </div>
                    @endif
                    <div class="mb-3 flex items-center justify-between border-t pt-2" style="border-color: var(--st-line)">
                        <span class="text-sm font-medium" style="color: var(--st-ink)">{{ __('storefront.total') }}</span>
                        <span class="text-lg font-semibold" style="color: var(--st-ink)">{{ format_money(max(0, $subtotal - $discount)) }}</span>
                    </div>
                    <a href="{{ url('/checkout') }}" class="block w-full py-3.5 text-center text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">
                        {{ __('storefront.proceed_to_checkout') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
    </template>
</div>
