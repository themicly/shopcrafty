<div>
    @if ($products->isEmpty())
        <div class="border p-12 text-center" style="border-color: var(--st-line); border-radius: var(--st-radius)">
            <p class="st-display text-xl" style="color: var(--st-ink)">{{ __('storefront.wishlist_empty') }}</p>
            <p class="mt-2 text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.wishlist_empty_hint') }}</p>
            <a href="{{ url('/shop') }}" class="mt-4 inline-flex text-sm font-medium" style="color: var(--st-accent)">{{ __('storefront.browse_the_shop') }}</a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)
                <div class="flex gap-4 border p-4" style="border-color: var(--st-line); border-radius: var(--st-radius)" wire:key="wl-{{ $product->id }}">
                    <a href="{{ url('/product/'.$product->slug) }}" class="block h-24 w-20 shrink-0 overflow-hidden" style="border-radius: var(--st-radius-sm); background: var(--st-surface)">
                        @if ($product->media->first())
                            <img src="{{ $product->media->first()->path }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                        @endif
                    </a>
                    <div class="flex min-w-0 flex-1 flex-col">
                        <a href="{{ url('/product/'.$product->slug) }}" class="truncate text-sm font-medium" style="color: var(--st-ink)">{{ $product->name }}</a>
                        <div class="mt-1"><x-st.price :price="$product->price" :compare-at="$product->compare_at_price" size="sm" /></div>
                        <div class="mt-auto flex items-center gap-3 pt-2">
                            <button wire:click="moveToCart({{ $product->id }})" class="text-xs font-semibold" style="color: var(--st-accent)">{{ __('storefront.move_to_cart') }}</button>
                            <button wire:click="remove({{ $product->id }})" class="text-xs" style="color: var(--st-ink-soft)">{{ __('storefront.remove') }}</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
