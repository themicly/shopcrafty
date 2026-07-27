@php
    // Match whichever breakpoint the including theme's own header uses for its hamburger.
    $bp = $bp ?? 'md';
    // Outer pill corner radius, in the shared SVG's 0-64 coordinate space — each
    // theme passes its own value so the nav's silhouette echoes its --st-radius
    // identity (e.g. Haven's square plates vs Fresh's soft organic pill) even
    // though this markup itself is shared across all themes.
    $r = $r ?? 20;
    $rightX = 400 - $r;
    $bottomY = 64 - $r;
    $wishlistOn = app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true);
    $wishlistCount = $wishlistOn ? app(\Themicly\Shopcrafty\Modules\Customers\Services\WishlistService::class)->count() : 0;
    $cartCount = app(\Themicly\Shopcrafty\Modules\Orders\Services\CartService::class)->count();
    $isHome = request()->is('/');
    $isShop = request()->is('shop', 'shop/*', 'category/*', 'product/*');
    $isWishlist = request()->routeIs('storefront.wishlist');
    $isAccount = request()->routeIs('storefront.account.*') || request()->routeIs('storefront.login');
@endphp
<nav class="stt-bottom-nav {{ $bp }}:hidden" role="navigation" aria-label="{{ __('storefront.menu') }}">
    {{-- Pill shape + center notch, one continuous cutout path so the page
         behind shows through the notch regardless of what's there. --}}
    <svg class="stt-bottom-nav-shape" viewBox="0 0 400 64" preserveAspectRatio="none" aria-hidden="true">
        <path d="M{{ $r }},0 L145,0 C168,0 172,37 200,37 C228,37 232,0 255,0 L{{ $rightX }},0 A{{ $r }},{{ $r }} 0 0 1 400,{{ $r }} L400,{{ $bottomY }} A{{ $r }},{{ $r }} 0 0 1 {{ $rightX }},64 L{{ $r }},64 A{{ $r }},{{ $r }} 0 0 1 0,{{ $bottomY }} L0,{{ $r }} A{{ $r }},{{ $r }} 0 0 1 {{ $r }},0 Z" fill="var(--st-bg)" />
    </svg>

    <a href="{{ url('/') }}" class="stt-bottom-nav-item" style="color: {{ $isHome ? 'var(--st-primary)' : 'var(--st-ink-soft)' }}" aria-current="{{ $isHome ? 'page' : 'false' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
        <span>{{ __('storefront.home') }}</span>
        @if ($isHome)<i class="stt-bottom-nav-dot"></i>@endif
    </a>
    <a href="{{ url('/shop') }}" class="stt-bottom-nav-item" style="color: {{ $isShop ? 'var(--st-primary)' : 'var(--st-ink-soft)' }}" aria-current="{{ $isShop ? 'page' : 'false' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z" /></svg>
        <span>{{ __('storefront.shop') }}</span>
        @if ($isShop)<i class="stt-bottom-nav-dot"></i>@endif
    </a>

    {{-- Cart sits centered, raised into the notch — the primary action gets top billing. --}}
    <button type="button" data-cart-target @click="$dispatch('open-cart')" class="stt-bottom-nav-cta" aria-label="{{ __('storefront.cart') }}"
        x-data="{ n: {{ $cartCount }} }" x-on:cart-count-updated.window="n = $event.detail.count">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
        <span x-show="n > 0" x-text="n" x-cloak class="stt-bottom-nav-badge stt-bottom-nav-badge--cta"></span>
    </button>

    @if ($wishlistOn)
    <a href="{{ route('storefront.wishlist') }}" class="stt-bottom-nav-item" style="color: {{ $isWishlist ? 'var(--st-primary)' : 'var(--st-ink-soft)' }}" aria-current="{{ $isWishlist ? 'page' : 'false' }}"
        x-data="{ n: {{ $wishlistCount }} }" x-on:wishlist-changed.window="n = $event.detail.count">
        <span class="stt-bottom-nav-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
            <span x-show="n > 0" x-text="n" x-cloak class="stt-bottom-nav-badge"></span>
        </span>
        <span>{{ __('storefront.wishlist') }}</span>
        @if ($isWishlist)<i class="stt-bottom-nav-dot"></i>@endif
    </a>
    @endif
    <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="stt-bottom-nav-item" style="color: {{ $isAccount ? 'var(--st-primary)' : 'var(--st-ink-soft)' }}" aria-current="{{ $isAccount ? 'page' : 'false' }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
        <span>{{ __('storefront.account') }}</span>
        @if ($isAccount)<i class="stt-bottom-nav-dot"></i>@endif
    </a>
</nav>
