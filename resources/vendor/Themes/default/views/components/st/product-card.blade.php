@props(['product'])

{{--
    Shared product card. The visual variant is chosen at RUNTIME from the theme's
    `card_style` setting — NOT via a per-theme component file. Anonymous components
    are baked into their parent view's compiled cache, which is shared across themes,
    so a per-theme override would leak between themes (THM-07). One file, one switch.

    overlay (Aurora) · bordered (Marketplace) · basket (Fresh) · editorial (Boutique) · caption · classic (Studio) · gallery (Haven)
--}}
@php
    $cardStyle = app(\Themicly\Shopcrafty\Modules\Themes\Services\ThemeService::class)->setting('card_style', 'overlay');
    $media = $product->media ?? collect();
    $img = $media->first()?->path;
    $hoverImg = $media->skip(1)->first()?->path;
    $onSale = $product->compare_at_price && $product->compare_at_price > $product->price;
    // Discount percentage for badge variants that show it (guarded: only when it rounds to ≥1%).
    $salePct = $onSale ? (int) round((1 - $product->price / $product->compare_at_price) * 100) : 0;
    $soldOut = $product->track_inventory && $product->stock_qty <= 0;
    $lowStock = $product->track_inventory && $product->stock_qty > 0 && $product->stock_qty <= 10;
    // Does this product have selectable variants? Prefers the eager-loaded count
    // (withCount('variants') on listing queries) and never triggers an N+1.
    $hasVariants = ($product->variants_count ?? ($product->relationLoaded('variants') ? $product->variants->count() : $product->variants()->count())) > 0;
    $productUrl = url('/product/' . $product->slug);
    $showReviews = app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('reviews') && settings('catalog.reviews_enabled', true);
    $placeholder = 'm2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 15.75h.008M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z';
@endphp

{{-- Keyboard access: hover-revealed card actions must also appear when the card (or the
     control itself) holds focus. The touch fallback already lives in app.css, which
     forces .st-card-slide/.st-card-actions visible under @media (hover: none). --}}
@once
<style>
    .group:focus-within .st-card-slide,
    .st-card-slide:focus-visible { transform: none; opacity: 1; }
    .group:focus-within .st-card-actions { opacity: 1; }
</style>
@endonce

@switch($cardStyle)

{{-- ============================ BORDERED (Volt) ============================ --}}
@case('bordered')
<div x-data {{ $attributes->merge(['class' => 'group st-reveal relative flex flex-col overflow-hidden']) }}
    style="background: var(--st-bg); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
    <x-st.wishlist-heart :product-id="$product->id" floating />
    <x-st.compare-toggle :product-id="$product->id" floating />
    <a href="{{ url('/product/' . $product->slug) }}" class="block">
        <div class="relative aspect-square w-full overflow-hidden" style="background: var(--st-surface)">
            @if ($img)
                <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
            @else
                <div class="grid h-full w-full place-items-center" style="color: var(--st-line)"><svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="h-10 w-10"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $placeholder }}" /></svg></div>
            @endif
            @if ($onSale)<span class="absolute left-2.5 top-2.5 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide" style="background: var(--st-accent); color: #fff; border-radius: var(--st-radius)">{{ $salePct >= 1 ? '-'.$salePct.'%' : __('storefront.sale') }}</span>
            @elseif ($soldOut)<span class="absolute left-2.5 top-2.5 px-2 py-0.5 text-[11px] font-bold uppercase tracking-wide" style="background: var(--st-ink); color: var(--st-bg); border-radius: var(--st-radius)">{{ __('storefront.sold_out') }}</span>@endif
            @if ($hasVariants)<span class="absolute bottom-2.5 left-2.5 inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide backdrop-blur" style="background: color-mix(in srgb, var(--st-bg) 82%, transparent); color: var(--st-ink); border-radius: var(--st-radius)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-3 w-3" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg>{{ __('storefront.options_available') }}</span>@endif
        </div>
    </a>
    <div class="flex flex-1 flex-col p-3.5">
        <h3 class="line-clamp-2 text-sm font-semibold leading-snug" style="color: var(--st-ink)"><a href="{{ url('/product/' . $product->slug) }}">{{ $product->name }}</a></h3>
        @if ($showReviews && ($product->reviews_count ?? 0) > 0)<div class="mt-1.5"><x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" /></div>@endif
        <div class="mt-2"><x-st.price :price="$product->price" :compare-at="$product->compare_at_price" size="sm" /></div>
        @if ($lowStock)<p class="mt-1 text-xs font-semibold" style="color: var(--st-accent)">{{ __('storefront.low_stock', ['count' => $product->stock_qty]) }}</p>@endif
        <div class="mt-3 flex items-center gap-2 pt-1">
            @if ($hasVariants)
                <a href="{{ $productUrl }}" class="flex-1 py-2 text-center text-xs font-bold uppercase tracking-wide transition-opacity hover:opacity-90" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius)">{{ __('storefront.select_options') }}</a>
            @elseif (! $soldOut)
                <button type="button" @click="window.Livewire.dispatch('cart-add', { productId: {{ $product->id }}, qty: 1 })" class="flex-1 py-2 text-xs font-bold uppercase tracking-wide transition-opacity hover:opacity-90" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius)">{{ __('storefront.add_to_cart') }}</button>
            @else
                <span class="flex-1 py-2 text-center text-xs font-bold uppercase tracking-wide" style="background: var(--st-surface); color: var(--st-ink-soft); border-radius: var(--st-radius)">{{ __('storefront.sold_out') }}</span>
            @endif
            <button type="button" @click="window.Livewire.dispatch('quick-view', { productId: {{ $product->id }} })" class="hidden h-8 w-8 shrink-0 place-items-center sm:grid" style="border: 1px solid var(--st-line); color: var(--st-ink); border-radius: var(--st-radius)" aria-label="{{ __('storefront.quick_view') }}"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg></button>
        </div>
    </div>
</div>
@break

{{-- ============================ BASKET (Bloom) ============================ --}}
@case('basket')
<div x-data {{ $attributes->merge(['class' => 'group st-reveal relative flex flex-col p-3']) }}
    style="background: var(--st-bg); border: 1px solid var(--st-line); border-radius: var(--st-radius); box-shadow: 0 1px 2px rgba(0,0,0,.03)">
    <x-st.wishlist-heart :product-id="$product->id" floating />
    <x-st.compare-toggle :product-id="$product->id" floating />
    <a href="{{ url('/product/' . $product->slug) }}" class="block">
        <div class="relative aspect-square w-full overflow-hidden" style="background: var(--st-surface); border-radius: var(--st-radius)">
            @if ($img)
                <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
            @else
                <div class="grid h-full w-full place-items-center" style="color: var(--st-line)"><svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="h-10 w-10"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $placeholder }}" /></svg></div>
            @endif
            @if ($onSale)<span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold" style="background: var(--st-accent); color: #fff">{{ __('storefront.save') }}</span>
            @elseif ($soldOut)<span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-bold" style="background: var(--st-ink); color: var(--st-bg)">{{ __('storefront.sold_out') }}</span>@endif
            @if ($hasVariants)<span class="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold backdrop-blur" style="background: color-mix(in srgb, var(--st-bg) 82%, transparent); color: var(--st-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-3 w-3" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg>{{ __('storefront.options_available') }}</span>@endif
        </div>
    </a>
    <div class="flex flex-1 flex-col px-1 pt-3">
        <h3 class="line-clamp-2 text-sm font-semibold leading-snug" style="color: var(--st-ink)"><a href="{{ url('/product/' . $product->slug) }}">{{ $product->name }}</a></h3>
        @if ($showReviews && ($product->reviews_count ?? 0) > 0)<div class="mt-1.5"><x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" /></div>@endif
        <div class="mt-2"><x-st.price :price="$product->price" :compare-at="$product->compare_at_price" size="sm" /></div>
        @if ($lowStock)<p class="mt-1 text-xs font-semibold" style="color: var(--st-accent)">{{ __('storefront.low_stock', ['count' => $product->stock_qty]) }}</p>@endif
        {{-- Full-width basket button below the price so it never clips in tight grids. --}}
        <div class="mt-auto pt-3">
            @if ($hasVariants)
                <a href="{{ $productUrl }}" class="flex w-full items-center justify-center gap-1.5 rounded-full py-2.5 text-xs font-bold shadow-sm transition-transform hover:-translate-y-0.5" style="background: var(--st-primary); color: var(--st-primary-ink)">{{ __('storefront.select_options') }}</a>
            @elseif (! $soldOut)
                <button type="button" @click="window.Livewire.dispatch('cart-add', { productId: {{ $product->id }}, qty: 1 }); window.toast(@js(__('storefront.added_to_basket')), 'success')" class="flex w-full items-center justify-center gap-1.5 rounded-full py-2.5 text-xs font-bold shadow-sm transition-transform hover:-translate-y-0.5" style="background: var(--st-primary); color: var(--st-primary-ink)" aria-label="{{ __('storefront.add_to_basket') }}"><svg fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>{{ __('storefront.add_to_basket') }}</button>
            @else
                <span class="flex w-full items-center justify-center rounded-full py-2.5 text-xs font-bold" style="background: var(--st-surface); color: var(--st-ink-soft)">{{ __('storefront.sold_out') }}</span>
            @endif
        </div>
    </div>
</div>
@break

{{-- ============================ EDITORIAL (Atelier) ============================ --}}
@case('editorial')
<div x-data {{ $attributes->merge(['class' => 'group st-reveal relative']) }}>
    <x-st.wishlist-heart :product-id="$product->id" floating />
    <x-st.compare-toggle :product-id="$product->id" floating />
    <a href="{{ url('/product/' . $product->slug) }}" class="block">
        <div class="relative overflow-hidden" style="background: var(--st-surface)">
            <div class="aspect-[3/4] w-full">
                @if ($img)
                    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-all duration-700 ease-out {{ $hoverImg ? 'group-hover:opacity-0' : 'group-hover:scale-[1.04]' }}">
                    @if ($hoverImg)<img src="{{ $hoverImg }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-700 group-hover:opacity-100">@endif
                @else
                    <div class="grid h-full w-full place-items-center" style="color: var(--st-line)"><svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="h-10 w-10"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $placeholder }}" /></svg></div>
                @endif
                @if ($onSale)<span class="absolute left-4 top-4 text-[10px] font-medium uppercase tracking-[0.2em]" style="color: var(--st-accent)">{{ __('storefront.sale') }}</span>
                @elseif ($soldOut)<span class="absolute left-4 top-4 text-[10px] font-medium uppercase tracking-[0.2em]" style="color: var(--st-ink-soft)">{{ __('storefront.sold_out') }}</span>@endif
                @if ($hasVariants)<span class="absolute bottom-4 left-4 text-[10px] font-medium uppercase tracking-[0.2em]" style="color: var(--st-ink-soft)">{{ __('storefront.options_available') }}</span>@endif
            </div>
            @if ($hasVariants)
                {{-- Parent anchor already routes to the PDP; a plain label avoids nested <a>. --}}
                <span class="st-card-slide absolute inset-x-0 bottom-0 translate-y-full py-3.5 text-center text-[11px] font-semibold uppercase tracking-[0.22em] opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100" style="background: var(--st-ink); color: var(--st-bg)">{{ __('storefront.select_options') }}</span>
            @elseif (! $soldOut)
                <button type="button" @click.prevent="window.Livewire.dispatch('cart-add', { productId: {{ $product->id }}, qty: 1 }); window.toast(@js(__('storefront.added_to_bag')), 'success')" class="st-card-slide absolute inset-x-0 bottom-0 translate-y-full py-3.5 text-[11px] font-semibold uppercase tracking-[0.22em] opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100" style="background: var(--st-ink); color: var(--st-bg)">{{ __('storefront.add_to_bag') }}</button>
            @endif
        </div>
    </a>
    <div class="mt-4 text-center">
        <h3 class="text-xs font-medium uppercase tracking-[0.12em]" style="color: var(--st-ink)"><a href="{{ url('/product/' . $product->slug) }}">{{ $product->name }}</a></h3>
        <div class="mt-2 flex items-center justify-center gap-2 text-sm" style="color: var(--st-ink-soft)">
            <span style="color: var(--st-ink)">{{ format_money($product->price) }}</span>
            @if ($onSale)<span class="text-xs line-through" style="color: var(--st-ink-soft)">{{ format_money($product->compare_at_price) }}</span>@endif
        </div>
    </div>
</div>
@break

{{-- ============================ CAPTION (Studio) ============================ --}}
{{-- Fine-framed 4:5 media with a caption-style body below: serif display name,
     accent-colored price, and a quiet underlined "Add" that reveals on hover or
     focus. Touch devices get it always-visible via the compiled
     `@media (hover:none) .st-card-actions` rule shared with the other variants. --}}
@case('caption')
<div x-data {{ $attributes->merge(['class' => 'group st-reveal relative']) }}>
    <x-st.wishlist-heart :product-id="$product->id" floating />
    <x-st.compare-toggle :product-id="$product->id" floating />
    <a href="{{ url('/product/' . $product->slug) }}" class="block">
        <div class="relative overflow-hidden" style="background: var(--st-surface); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
            <div class="aspect-[4/5] w-full">
                @if ($img)
                    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-all duration-700 ease-out {{ $hoverImg ? 'group-hover:opacity-0' : 'group-hover:scale-[1.04]' }}">
                    @if ($hoverImg)<img src="{{ $hoverImg }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-700 group-hover:opacity-100">@endif
                @else
                    <div class="grid h-full w-full place-items-center" style="color: var(--st-line)"><svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="h-10 w-10"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $placeholder }}" /></svg></div>
                @endif
                @if ($onSale)<span class="absolute left-3 top-3 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--st-accent); background: var(--st-surface); border: 1px solid var(--st-line); border-radius: var(--st-radius)">{{ __('storefront.sale') }}</span>
                @elseif ($soldOut)<span class="absolute left-3 top-3 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--st-ink-soft); background: var(--st-surface); border: 1px solid var(--st-line); border-radius: var(--st-radius)">{{ __('storefront.sold_out') }}</span>@endif
                @if ($hasVariants)<span class="absolute bottom-3 left-3 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-[0.18em]" style="color: var(--st-ink); background: var(--st-surface); border: 1px solid var(--st-line); border-radius: var(--st-radius)">{{ __('storefront.options_available') }}</span>@endif
            </div>
        </div>
    </a>
    <div class="mt-3">
        <h3 class="line-clamp-2" style="font-family: var(--st-font-display); font-weight: 400; font-size: 1.05rem; line-height: 1.3; color: var(--st-ink)"><a href="{{ url('/product/' . $product->slug) }}">{{ $product->name }}</a></h3>
        <div class="mt-1 flex items-baseline justify-between gap-3">
            <div class="flex items-baseline gap-2">
                <span style="font-family: var(--st-font-body); font-size: 0.9rem; letter-spacing: 0.02em; color: var(--st-accent)">{{ format_money($product->price) }}</span>
                @if ($onSale)<span class="line-through" style="font-family: var(--st-font-body); font-size: 0.8rem; color: var(--st-ink-soft)">{{ format_money($product->compare_at_price) }}</span>@endif
            </div>
            @if ($hasVariants)
                <a href="{{ $productUrl }}" class="st-card-actions shrink-0 text-[10px] font-semibold uppercase tracking-[0.18em] underline underline-offset-4 opacity-0 transition-opacity duration-200 group-hover:opacity-100" style="color: var(--st-ink)">{{ __('storefront.select_options') }}</a>
            @elseif (! $soldOut)
                <button type="button" @click="window.Livewire.dispatch('cart-add', { productId: {{ $product->id }}, qty: 1 })" class="st-card-actions shrink-0 text-[10px] font-semibold uppercase tracking-[0.18em] underline underline-offset-4 opacity-0 transition-opacity duration-200 group-hover:opacity-100" style="color: var(--st-ink)">{{ __('storefront.add') }}</button>
            @endif
        </div>
    </div>
</div>
@break

{{-- ============================ CLASSIC (Studio) ============================ --}}
{{-- Classic retail card: plain 3:4 media with a floating wishlist heart, then
     title, star row, bold ink price + struck compare, and a full-width filled
     Add-to-cart button (theme primary — black under Studio). Always-visible
     buy affordance; no hover reveals to fail on touch. --}}
@case('classic')
<div x-data {{ $attributes->merge(['class' => 'group st-reveal relative flex flex-col']) }}>
    <x-st.wishlist-heart :product-id="$product->id" floating />
    <x-st.compare-toggle :product-id="$product->id" floating />
    <a href="{{ url('/product/' . $product->slug) }}" class="block">
        <div class="relative overflow-hidden" style="background: var(--st-surface); border-radius: var(--st-radius)">
            <div class="aspect-[3/4] w-full">
                @if ($img)
                    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-all duration-500 ease-out {{ $hoverImg ? 'group-hover:opacity-0' : 'group-hover:scale-[1.04]' }}">
                    @if ($hoverImg)<img src="{{ $hoverImg }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100">@endif
                @else
                    <div class="grid h-full w-full place-items-center" style="color: var(--st-line)"><svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="h-10 w-10"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $placeholder }}" /></svg></div>
                @endif
                @if ($onSale)<span class="absolute left-3 top-3 px-2 py-1 text-[10px] font-bold uppercase tracking-wide" style="background: var(--st-accent); color: #fff; border-radius: var(--st-radius)">{{ $salePct >= 1 ? '-'.$salePct.'%' : __('storefront.sale') }}</span>
                @elseif ($soldOut)<span class="absolute left-3 top-3 px-2 py-1 text-[10px] font-bold uppercase tracking-wide" style="background: var(--st-ink); color: var(--st-bg); border-radius: var(--st-radius)">{{ __('storefront.sold_out') }}</span>@endif
                @if ($hasVariants)<span class="absolute bottom-3 left-3 inline-flex items-center gap-1 px-2 py-1 text-[10px] font-bold uppercase tracking-wide backdrop-blur" style="background: color-mix(in srgb, var(--st-bg) 82%, transparent); color: var(--st-ink); border-radius: var(--st-radius)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-3 w-3" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg>{{ __('storefront.options_available') }}</span>@endif
            </div>
        </div>
    </a>
    <div class="flex flex-1 flex-col pt-3">
        <h3 class="line-clamp-2 text-sm font-medium leading-snug" style="color: var(--st-ink)"><a href="{{ url('/product/' . $product->slug) }}">{{ $product->name }}</a></h3>
        @if ($showReviews && ($product->reviews_count ?? 0) > 0)<div class="mt-1.5"><x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" /></div>@endif
        <div class="mt-2 flex items-baseline gap-2">
            <span class="font-bold" style="color: var(--st-ink)">{{ format_money($product->price) }}</span>
            @if ($onSale)<span class="text-sm line-through" style="color: var(--st-ink-soft)">{{ format_money($product->compare_at_price) }}</span>@endif
        </div>
        @if ($lowStock)<p class="mt-1 text-xs font-semibold" style="color: var(--st-accent)">{{ __('storefront.low_stock', ['count' => $product->stock_qty]) }}</p>@endif
        <div class="mt-auto pt-3">
            @if ($hasVariants)
                <a href="{{ $productUrl }}"
                    class="flex w-full items-center justify-center gap-2 py-2.5 text-xs font-semibold transition-opacity hover:opacity-90"
                    style="background: var(--st-primary); color: var(--st-primary-ink); border: 1px solid var(--st-primary); border-radius: var(--st-radius); letter-spacing: 0.04em">
                    {{ __('storefront.select_options') }}
                </a>
            @elseif (! $soldOut)
                <button type="button" @click="window.Livewire.dispatch('cart-add', { productId: {{ $product->id }}, qty: 1 })"
                    class="flex w-full items-center justify-center gap-2 py-2.5 text-xs font-semibold transition-opacity hover:opacity-90"
                    style="background: var(--st-primary); color: var(--st-primary-ink); border: 1px solid var(--st-primary); border-radius: var(--st-radius); letter-spacing: 0.04em">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                    {{ __('storefront.add_to_cart') }}
                </button>
            @else
                <span class="flex w-full items-center justify-center py-2.5 text-xs font-semibold" style="background: var(--st-surface); color: var(--st-ink-soft); border: 1px solid var(--st-line); border-radius: var(--st-radius); letter-spacing: 0.04em">{{ __('storefront.sold_out') }}</span>
            @endif
        </div>
    </div>
</div>
@break

{{-- ============================ GALLERY (Haven) ============================ --}}
{{-- Matted plate: the image sits inside a padded, hairline-framed surface like a
     framed print. Below, a hairline-ruled caption row — display-serif name, ink
     price with struck compare — and a quiet underlined "Add to cart" that reveals
     on hover/focus (always visible on touch via the shared .st-card-actions rule).
     Theme-neutral: --st-* tokens only. --}}
@case('gallery')
<div x-data {{ $attributes->merge(['class' => 'group st-reveal relative flex flex-col']) }}>
    <x-st.wishlist-heart :product-id="$product->id" floating />
    <x-st.compare-toggle :product-id="$product->id" floating />
    <a href="{{ url('/product/' . $product->slug) }}" class="block">
        <div style="background: var(--st-surface); border: 1px solid var(--st-line); border-radius: var(--st-radius); padding: 0.75rem">
            <div class="relative overflow-hidden" style="background: var(--st-bg); border-radius: var(--st-radius)">
                <div class="aspect-[4/5] w-full">
                    @if ($img)
                        <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-all duration-700 ease-out {{ $hoverImg ? 'group-hover:opacity-0' : 'group-hover:scale-[1.05]' }}">
                        @if ($hoverImg)<img src="{{ $hoverImg }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-700 group-hover:opacity-100">@endif
                    @else
                        <div class="grid h-full w-full place-items-center" style="color: var(--st-line)"><svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="h-10 w-10"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $placeholder }}" /></svg></div>
                    @endif
                    @if ($onSale)<span class="absolute left-3 top-3 px-2 py-0.5 text-[10px] font-medium uppercase tracking-[0.2em]" style="color: var(--st-accent); background: var(--st-surface); border: 1px solid var(--st-line)">{{ __('storefront.sale') }}</span>
                    @elseif ($soldOut)<span class="absolute left-3 top-3 px-2 py-0.5 text-[10px] font-medium uppercase tracking-[0.2em]" style="color: var(--st-ink-soft); background: var(--st-surface); border: 1px solid var(--st-line)">{{ __('storefront.sold_out') }}</span>@endif
                    @if ($hasVariants)<span class="absolute bottom-3 left-3 px-2 py-0.5 text-[10px] font-medium uppercase tracking-[0.2em]" style="color: var(--st-ink); background: var(--st-surface); border: 1px solid var(--st-line)">{{ __('storefront.options_available') }}</span>@endif
                </div>
            </div>
        </div>
    </a>
    <div class="mt-3 flex flex-1 flex-col pt-2.5" style="border-top: 1px solid var(--st-line)">
        <h3 class="line-clamp-2" style="font-family: var(--st-font-display); font-weight: 400; font-size: 1.02rem; line-height: 1.35; color: var(--st-ink)"><a href="{{ url('/product/' . $product->slug) }}">{{ $product->name }}</a></h3>
        @if ($showReviews && ($product->reviews_count ?? 0) > 0)<div class="mt-1.5"><x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" /></div>@endif
        <div class="mt-1.5 flex items-baseline justify-between gap-3">
            <div class="flex items-baseline gap-2">
                <span style="font-size: 0.9rem; letter-spacing: 0.02em; color: var(--st-ink)">{{ format_money($product->price) }}</span>
                @if ($onSale)<span class="line-through" style="font-size: 0.8rem; color: var(--st-ink-soft)">{{ format_money($product->compare_at_price) }}</span>@endif
            </div>
            @if ($hasVariants)
                <a href="{{ $productUrl }}" class="st-card-actions shrink-0 text-[10px] font-semibold uppercase tracking-[0.18em] underline underline-offset-4 opacity-0 transition-opacity duration-200 group-hover:opacity-100" style="color: var(--st-accent)">{{ __('storefront.select_options') }}</a>
            @elseif (! $soldOut)
                <button type="button" @click="window.Livewire.dispatch('cart-add', { productId: {{ $product->id }}, qty: 1 })" class="st-card-actions shrink-0 text-[10px] font-semibold uppercase tracking-[0.18em] underline underline-offset-4 opacity-0 transition-opacity duration-200 group-hover:opacity-100" style="color: var(--st-accent)">{{ __('storefront.add_to_cart') }}</button>
            @endif
        </div>
        @if ($lowStock)<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ __('storefront.low_stock', ['count' => $product->stock_qty]) }}</p>@endif
    </div>
</div>
@break

{{-- ============================ OVERLAY (Aurora / default) ============================ --}}
@default
<div x-data {{ $attributes->merge(['class' => 'group st-reveal relative']) }}>
    <x-st.wishlist-heart :product-id="$product->id" floating />
    <x-st.compare-toggle :product-id="$product->id" floating />
    <a href="{{ url('/product/' . $product->slug) }}" class="block">
        <div class="relative overflow-hidden" style="border-radius: var(--st-radius); background: var(--st-surface)">
            <div class="aspect-[4/5] w-full">
                @if ($img)
                    <img src="{{ $img }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-opacity duration-500 {{ $hoverImg ? 'group-hover:opacity-0' : 'group-hover:scale-105' }}">
                    @if ($hoverImg)<img src="{{ $hoverImg }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover opacity-0 transition-opacity duration-500 group-hover:opacity-100">@endif
                @else
                    <div class="grid h-full w-full place-items-center" style="color: var(--st-line)"><svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="h-10 w-10"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $placeholder }}" /></svg></div>
                @endif
                @if ($onSale)<span class="absolute left-3 top-3 rounded-full px-2 py-0.5 text-[11px] font-semibold" style="background: var(--st-accent); color: #fff">{{ __('storefront.sale') }}</span>
                @elseif ($soldOut)<span class="absolute left-3 top-3 rounded-full px-2 py-0.5 text-[11px] font-semibold" style="background: var(--st-ink); color: var(--st-bg)">{{ __('storefront.sold_out') }}</span>@endif
                @if ($hasVariants)<span class="absolute bottom-3 left-3 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold backdrop-blur" style="background: color-mix(in srgb, var(--st-bg) 82%, transparent); color: var(--st-ink)"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-3 w-3" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg>{{ __('storefront.options_available') }}</span>@endif
            </div>
        </div>
    </a>
    <div class="st-card-actions pointer-events-none absolute inset-x-3 bottom-[6.5rem] flex gap-2 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
        <button type="button" @click="window.Livewire.dispatch('quick-view', { productId: {{ $product->id }} })" class="pointer-events-auto hidden flex-1 py-2 text-xs font-semibold shadow-sm backdrop-blur sm:block" style="background: color-mix(in srgb, var(--st-bg) 88%, transparent); color: var(--st-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.quick_view') }}</button>
        @if ($hasVariants)
            <a href="{{ $productUrl }}" class="pointer-events-auto grid place-items-center py-2 px-3 text-xs font-semibold shadow-sm" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)" aria-label="{{ __('storefront.select_options') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-4 w-4" aria-hidden="true"><path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/></svg></a>
        @elseif (! $soldOut)
            <button type="button" @click="window.Livewire.dispatch('cart-add', { productId: {{ $product->id }}, qty: 1 })" class="pointer-events-auto grid place-items-center px-3 py-2 shadow-sm" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)" aria-label="{{ __('storefront.add_to_cart') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" /></svg>
            </button>
        @endif
    </div>
    <div class="mt-3 flex items-start justify-between gap-2">
        <div class="min-w-0">
            <h3 class="truncate text-sm font-medium" style="color: var(--st-ink)"><a href="{{ url('/product/' . $product->slug) }}">{{ $product->name }}</a></h3>
            <div class="mt-1"><x-st.price :price="$product->price" :compare-at="$product->compare_at_price" size="sm" /></div>
            @if ($showReviews && ($product->reviews_count ?? 0) > 0)<div class="mt-1"><x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" /></div>@endif
            @if ($lowStock)<p class="mt-1 text-xs font-medium" style="color: var(--st-accent)">{{ __('storefront.low_stock', ['count' => $product->stock_qty]) }}</p>@endif
        </div>
    </div>
</div>
@endswitch
