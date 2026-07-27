@php
    $links = [
        ['route' => 'storefront.account.index', 'label' => __('storefront.orders')],
        ['route' => 'storefront.account.downloads', 'label' => __('storefront.downloads')],
        ['route' => 'storefront.wishlist', 'label' => __('storefront.wishlist')],
        ['route' => 'storefront.account.addresses', 'label' => __('storefront.addresses')],
        ['route' => 'storefront.account.profile', 'label' => __('storefront.profile')],
        ['route' => 'storefront.support', 'label' => __('storefront.help_support')],
    ];
@endphp

<aside class="lg:w-56 lg:shrink-0">
    <p class="st-display mb-4 text-lg font-semibold" style="color: var(--st-ink)">{{ __('storefront.my_account') }}</p>
    <nav class="flex gap-1 overflow-x-auto lg:flex-col">
        @foreach ($links as $link)
            @php $active = request()->routeIs($link['route']); @endphp
            <a href="{{ route($link['route']) }}" class="whitespace-nowrap px-3 py-2 text-sm"
                style="border-radius: var(--st-radius-sm); {{ $active ? 'background: var(--st-surface); color: var(--st-ink); font-weight: 600' : 'color: var(--st-ink-soft)' }}">
                {{ $link['label'] }}
            </a>
        @endforeach
        <form method="POST" action="{{ route('storefront.logout') }}">
            @csrf
            <button type="submit" class="w-full px-3 py-2 text-left text-sm" style="border-radius: var(--st-radius-sm); color: var(--st-ink-soft)">{{ __('storefront.sign_out') }}</button>
        </form>
    </nav>
</aside>
