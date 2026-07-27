<div
    x-data="{ open: @entangle('open') }"
    x-show="open"
    x-cloak
    @keydown.escape.window="open = false"
    class="fixed inset-0 z-50"
    style="display: none;"
>
    <div x-show="open" x-transition.opacity class="fixed inset-0 bg-black/50" @click="open = false"></div>

    <div class="fixed inset-x-0 top-[8vh] mx-auto w-full max-w-2xl px-4">
        <div x-show="open" x-transition @click.outside="open = false"
            class="overflow-hidden shadow-xl" style="background: var(--st-bg); border-radius: var(--st-radius)">
            @if ($product)
                <div class="grid gap-0 sm:grid-cols-2" x-data="{ qty: 1 }" wire:key="qv-{{ $product->id }}">
                    <div class="aspect-square" style="background: var(--st-surface)">
                        @if ($product->media->first())
                            <img src="{{ $product->media->first()->path }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="flex flex-col p-6">
                        <button @click="open = false" class="mb-2 self-end text-lg" style="color: var(--st-ink-soft)" aria-label="{{ __('storefront.close') }}">&times;</button>
                        <h2 class="st-display text-xl font-semibold" style="color: var(--st-ink)">{{ $product->name }}</h2>
                        @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('reviews') && settings('catalog.reviews_enabled', true) && $product->reviews_count > 0)
                            <div class="mt-1"><x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" /></div>
                        @endif
                        <div class="mt-3"><x-st.price :price="$product->price" :compare-at="$product->compare_at_price" size="lg" /></div>

                        @if ($product->track_inventory && $product->stock_qty <= 0)
                            <p class="mt-3 text-sm font-medium" style="color: var(--st-accent)">{{ __('storefront.sold_out') }}</p>
                        @elseif ($product->track_inventory && $product->stock_qty <= 10)
                            <p class="mt-3 text-sm font-medium" style="color: var(--st-accent)">{{ __('storefront.low_stock', ['count' => $product->stock_qty]) }}</p>
                        @endif

                        @if ($product->description)
                            <p class="mt-3 line-clamp-4 text-sm leading-relaxed" style="color: var(--st-ink-soft)">{{ \Illuminate\Support\Str::limit(strip_tags($product->description), 220) }}</p>
                        @endif

                        <div class="mt-auto pt-5">
                            @if ($product->variants->isNotEmpty())
                                {{-- Variable products need option selection — send them to the full
                                     PDP rather than silently adding the base item (B2). --}}
                                <a href="{{ url('/product/'.$product->slug) }}"
                                    class="flex w-full items-center justify-center px-6 py-3 text-sm font-semibold"
                                    style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">
                                    {{ __('storefront.select_options') }}
                                </a>
                            @elseif (! ($product->track_inventory && $product->stock_qty <= 0))
                                <div class="flex items-center gap-3">
                                    <div class="inline-flex items-center border" style="border-color: var(--st-line); border-radius: var(--st-radius-sm)">
                                        <button type="button" @click="qty = Math.max(1, qty - 1)" aria-label="{{ __('storefront.decrease_quantity') }}" class="grid h-11 w-11 place-items-center" style="color: var(--st-ink)">&minus;</button>
                                        <span class="w-8 text-center text-sm" style="color: var(--st-ink)" x-text="qty"></span>
                                        <button type="button" @click="qty++" aria-label="{{ __('storefront.increase_quantity') }}" class="grid h-11 w-11 place-items-center" style="color: var(--st-ink)">+</button>
                                    </div>
                                    <button type="button"
                                        @click="window.Livewire.dispatch('cart-add', { productId: {{ $product->id }}, qty: qty }); open = false"
                                        class="flex-1 px-6 py-3 text-sm font-semibold"
                                        style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">
                                        {{ __('storefront.add_to_cart') }}
                                    </button>
                                </div>
                            @endif
                        </div>
                        <a href="{{ url('/product/'.$product->slug) }}" class="mt-3 text-center text-xs font-medium" style="color: var(--st-accent)">{{ __('storefront.view_details') }}</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
