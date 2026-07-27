<div>
    @php $showReviews = app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('reviews') && settings('catalog.reviews_enabled', true); @endphp

    @if ($products->isEmpty())
        <div class="border p-12 text-center" style="border-color: var(--st-line); border-radius: var(--st-radius)">
            <p class="st-display text-xl" style="color: var(--st-ink)">{{ __('storefront.no_products_compare') }}</p>
            <p class="mt-2 text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.compare_hint') }}</p>
            <a href="{{ url('/shop') }}" class="mt-4 inline-flex text-sm font-medium" style="color: var(--st-accent)">{{ __('storefront.browse_the_shop') }}</a>
        </div>
    @else
        <div class="mb-4 flex items-center justify-between">
            <p class="text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.comparing_count', ['count' => $products->count(), 'max' => \Themicly\Shopcrafty\Modules\Catalog\Services\CompareService::MAX]) }}</p>
            <button wire:click="clear" class="text-xs font-semibold" style="color: var(--st-ink-soft)">{{ __('storefront.clear_all') }}</button>
        </div>

        <div class="overflow-x-auto" style="scrollbar-width: thin">
            <table class="w-full border-collapse text-left align-top" style="min-width: {{ 12 + $products->count() * 15 }}rem">
                <colgroup>
                    <col style="width: 10rem">
                    @foreach ($products as $product)<col style="width: 15rem">@endforeach
                </colgroup>
                <tbody>
                    {{-- Image + remove --}}
                    <tr>
                        <th scope="row" class="p-3"></th>
                        @foreach ($products as $product)
                            <td class="p-3" wire:key="img-{{ $product->id }}">
                                <div class="relative">
                                    <button wire:click="remove({{ $product->id }})" class="absolute -right-1 -top-1 z-10 grid h-7 w-7 place-items-center rounded-full shadow-sm backdrop-blur" style="background: color-mix(in srgb, var(--st-bg) 85%, transparent); color: var(--st-ink)" aria-label="{{ __('storefront.remove_from_comparison') }}" title="{{ __('storefront.remove') }}">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                    <a href="{{ url('/product/'.$product->slug) }}" class="block aspect-square w-full overflow-hidden" style="border-radius: var(--st-radius); background: var(--st-surface)">
                                        @if ($product->media->first())
                                            <img src="{{ $product->media->first()->path }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                        @endif
                                    </a>
                                </div>
                            </td>
                        @endforeach
                    </tr>

                    {{-- Name --}}
                    <tr style="border-top: 1px solid var(--st-line)">
                        <th scope="row" class="p-3 text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.product') }}</th>
                        @foreach ($products as $product)
                            <td class="p-3" wire:key="name-{{ $product->id }}">
                                <a href="{{ url('/product/'.$product->slug) }}" class="text-sm font-semibold leading-snug" style="color: var(--st-ink)">{{ $product->name }}</a>
                            </td>
                        @endforeach
                    </tr>

                    {{-- Price --}}
                    <tr style="border-top: 1px solid var(--st-line)">
                        <th scope="row" class="p-3 text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.price') }}</th>
                        @foreach ($products as $product)
                            <td class="p-3" wire:key="price-{{ $product->id }}">
                                <x-st.price :price="$product->price" :compare-at="$product->compare_at_price" size="sm" />
                            </td>
                        @endforeach
                    </tr>

                    {{-- Rating (gated) --}}
                    @if ($showReviews)
                        <tr style="border-top: 1px solid var(--st-line)">
                            <th scope="row" class="p-3 text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.rating') }}</th>
                            @foreach ($products as $product)
                                <td class="p-3 text-sm" style="color: var(--st-ink)" wire:key="rating-{{ $product->id }}">
                                    @if (($product->reviews_count ?? 0) > 0)
                                        <x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" />
                                    @else
                                        <span style="color: var(--st-ink-soft)">{{ __('storefront.no_reviews_short') }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endif

                    {{-- Brand --}}
                    <tr style="border-top: 1px solid var(--st-line)">
                        <th scope="row" class="p-3 text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.brand') }}</th>
                        @foreach ($products as $product)
                            <td class="p-3 text-sm" style="color: var(--st-ink)" wire:key="brand-{{ $product->id }}">{{ $product->brand?->name ?? '—' }}</td>
                        @endforeach
                    </tr>

                    {{-- Category --}}
                    <tr style="border-top: 1px solid var(--st-line)">
                        <th scope="row" class="p-3 text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.category') }}</th>
                        @foreach ($products as $product)
                            <td class="p-3 text-sm" style="color: var(--st-ink)" wire:key="cat-{{ $product->id }}">{{ $product->category?->name ?? '—' }}</td>
                        @endforeach
                    </tr>

                    {{-- SKU --}}
                    <tr style="border-top: 1px solid var(--st-line)">
                        <th scope="row" class="p-3 text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.sku') }}</th>
                        @foreach ($products as $product)
                            <td class="p-3 text-sm" style="color: var(--st-ink)" wire:key="sku-{{ $product->id }}">{{ $product->sku ?: '—' }}</td>
                        @endforeach
                    </tr>

                    {{-- Attribute rows (Size, Color, …) --}}
                    @foreach ($attributeRows as $attr)
                        <tr style="border-top: 1px solid var(--st-line)" wire:key="attr-row-{{ $loop->index }}">
                            <th scope="row" class="p-3 text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ $attr }}</th>
                            @foreach ($products as $product)
                                @php $vals = $this->attributeValues($product, $attr); @endphp
                                <td class="p-3 text-sm" style="color: var(--st-ink)" wire:key="attr-{{ $loop->index }}-{{ $product->id }}">{{ $vals ? implode(', ', $vals) : '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach

                    {{-- Availability --}}
                    <tr style="border-top: 1px solid var(--st-line)">
                        <th scope="row" class="p-3 text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.availability') }}</th>
                        @foreach ($products as $product)
                            @php $soldOut = $product->track_inventory && $product->stock_qty <= 0; @endphp
                            <td class="p-3 text-sm" wire:key="stock-{{ $product->id }}">
                                @if ($soldOut)
                                    <span style="color: var(--st-ink-soft)">{{ __('storefront.out_of_stock') }}</span>
                                @elseif ($product->track_inventory)
                                    <span style="color: var(--st-ink)">{{ __('storefront.in_stock') }} ({{ $product->stock_qty }})</span>
                                @else
                                    <span style="color: var(--st-ink)">{{ __('storefront.in_stock') }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>

                    {{-- Add to cart --}}
                    <tr style="border-top: 1px solid var(--st-line)">
                        <th scope="row" class="p-3"></th>
                        @foreach ($products as $product)
                            @php $soldOut = $product->track_inventory && $product->stock_qty <= 0; @endphp
                            <td class="p-3" wire:key="cart-{{ $product->id }}">
                                @unless ($soldOut)
                                    <button wire:click="addToCart({{ $product->id }})" class="w-full py-2 text-xs font-bold uppercase tracking-wide transition-opacity hover:opacity-90" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius)">{{ __('storefront.add_to_cart') }}</button>
                                @else
                                    <span class="block w-full py-2 text-center text-xs font-bold uppercase tracking-wide" style="background: var(--st-surface); color: var(--st-ink-soft); border-radius: var(--st-radius)">{{ __('storefront.sold_out') }}</span>
                                @endunless
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>
