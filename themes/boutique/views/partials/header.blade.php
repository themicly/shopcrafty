@php
    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots();
    $storeName = settings('general.store_name', config('app.name'));
    $logo = settings('general.logo');
    $popularSearchTerms = array_slice((array) settings('search.popular_terms', []), 0, 10);
    $headerMenu = \Themicly\Shopcrafty\Modules\CMS\Models\Menu::where('location', 'header')->first()?->items()->get() ?? collect();
    $addons = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class);
    $wishlistCount = $addons->installed('wishlist') ? app(\Themicly\Shopcrafty\Modules\Customers\Services\WishlistService::class)->count() : 0;
    // Second-row nav: admin menu when present, otherwise the top categories.
    $navItems = $headerMenu->isNotEmpty()
        ? $headerMenu->map(fn ($i) => ['label' => $i->label, 'url' => $i->url])
        : $tree->take(6)->map(fn ($c) => ['label' => $c->name, 'url' => url('/category/' . $c->slug)]);
@endphp

{{-- Boutique header (boutique-v2 pattern): two rows — main row with logo left and
     search/wishlist/account/cart icons right, then a desktop nav row of bold
     uppercase menu + category links. No backdrop-filter on <header>: it would trap
     the fixed cart drawer rendered inside it. --}}
<header class="{{ ($theme['header_sticky'] ?? true) ? 'sticky top-0' : 'relative' }} z-30" style="background: var(--st-bg); border-bottom: 1px solid var(--st-line)"
    x-data="{ mobileNav: false, search: false, q: '', results: [], timer: null,
        run() {
            clearTimeout(this.timer);
            if (this.q.trim().length < 3) { this.results = []; return; }
            this.timer = setTimeout(() => {
                fetch('{{ route('storefront.search.suggest') }}?q=' + encodeURIComponent(this.q.trim()))
                    .then(r => r.ok ? r.json() : []).then(d => this.results = Array.isArray(d) ? d : []).catch(() => this.results = []);
            }, 180);
        }
    }">
    {{-- Main row: hamburger (mobile) + logo left, icon set right --}}
    <div class="st-container flex h-20 items-center justify-between gap-4">
        <div class="flex min-w-0 items-center gap-1">
            <button class="stt-boutique-iconbtn lg:hidden" style="margin-left: -0.5rem" @click="mobileNav = true" aria-label="{{ __('storefront.menu') }}" :aria-expanded="mobileNav">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
            </button>
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3" style="color: var(--st-ink)">
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $storeName }}" class="h-9 w-auto object-contain">
                @else
                    <span class="truncate text-xl font-bold uppercase" style="letter-spacing: 0.14em">{{ $storeName }}</span>
                @endif
            </a>
        </div>

        {{-- Right: commerce icon set — search, wishlist, compare, account, cart. --}}
        {{-- 40px hit-areas (stt-boutique-iconbtn) matching the shared cart trigger.
             Wishlist/compare/account collapse into the mobile bottom nav below
             1024px (search + cart stay on the masthead everywhere). --}}
        <div class="stt-boutique-actions flex items-center justify-end gap-0.5" style="margin-right: -0.5rem">
            @php $currency = app(\Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService::class); @endphp
            @if ($currency->hasMultiple())
                <select onchange="if(this.value){window.location='{{ url('/currency') }}/'+this.value}" aria-label="{{ __('storefront.currency') }}"
                    class="hidden h-9 cursor-pointer border-0 bg-transparent px-1 text-sm focus:outline-none lg:block" style="color: var(--st-ink)">
                    @foreach ($currency->currencies() as $c)
                        <option value="{{ $c['code'] }}" @selected($c['code'] === $currency->activeCode())>{{ $c['code'] }}</option>
                    @endforeach
                </select>
            @endif
            <button @click="search = true" class="stt-boutique-iconbtn" aria-label="{{ __('storefront.search') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </button>
            @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))
            <a href="{{ route('storefront.wishlist') }}" x-data="{ n: {{ $wishlistCount }} }" x-on:wishlist-changed.window="n = $event.detail.count"
                class="stt-boutique-iconbtn relative hidden lg:grid" aria-label="{{ __('storefront.wishlist') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="absolute top-0.5 grid h-4 min-w-4 place-items-center px-1 text-[10px] font-bold" style="right: 0; background: var(--st-accent); color: #fff; border-radius: 999px"></span>
            </a>
            @endif
            @php $compareCount = $addons->installed('compare') ? app(\Themicly\Shopcrafty\Modules\Catalog\Services\CompareService::class)->count() : 0; @endphp
            @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))
            <a href="{{ route('storefront.compare') }}" x-data="{ n: {{ $compareCount }} }" x-on:compare-changed.window="n = $event.detail.count"
                class="stt-boutique-iconbtn relative hidden lg:grid" aria-label="{{ __('storefront.compare') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="absolute top-0.5 grid h-4 min-w-4 place-items-center px-1 text-[10px] font-bold" style="right: 0; background: var(--st-accent); color: #fff; border-radius: 999px"></span>
            </a>
            @endif
            <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="stt-boutique-iconbtn hidden lg:grid" aria-label="{{ __('storefront.account') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
            </a>
            <livewire:orders.cart-drawer :hide-trigger-below="'lg'" />
        </div>
    </div>

    {{-- Nav row (desktop): bold uppercase menu + category entries --}}
    <div class="stt-boutique-navrow hidden lg:block">
        <nav class="st-container flex flex-wrap items-center justify-center" aria-label="{{ __('storefront.main_navigation') }}">
            <a href="{{ url('/') }}" class="stt-boutique-navlink">{{ $theme['header_nav_home'] ?? 'Home' }}</a>
            <a href="{{ url('/shop') }}" class="stt-boutique-navlink">{{ $theme['header_nav_shop'] ?? 'Shop' }}</a>
            @foreach ($navItems as $item)
                <a href="{{ $item['url'] }}" class="stt-boutique-navlink">{{ $item['label'] }}</a>
            @endforeach
            @if ($addons->installed('blog'))<a href="{{ url('/blog') }}" class="stt-boutique-navlink">{{ $theme['header_nav_blog'] ?? 'Blog' }}</a>@endif
        </nav>
    </div>

    {{-- Search: full-width top drawer --}}
    <div x-show="search" x-cloak class="fixed inset-0 z-50" style="display:none" @keydown.escape.window="search = false">
        <div class="fixed inset-0" style="background: color-mix(in srgb, var(--st-ink) 55%, transparent)" @click="search = false"></div>
        <div class="fixed inset-x-0 top-0 px-6" style="padding-bottom: 2rem; padding-top: 2.5rem; background: var(--st-bg); border-bottom: 1px solid var(--st-line)" @click.outside="search = false">
            <div class="st-container mx-auto max-w-2xl">
                <p class="stt-boutique-eyebrow mb-4">{{ $theme['header_search_prompt'] ?? 'Search the store' }}</p>
                {{-- On phones the controls stack so the input keeps full width; on
                     ≥sm they sit inline again (input · Search · Close). --}}
                <form action="{{ route('storefront.search') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <input name="q" x-model="q" @input="run()" x-ref="q" x-init="$watch('search', v => v && $nextTick(() => $refs.q.focus()))" type="search" autocomplete="off" placeholder="{{ $theme['header_search_placeholder'] ?? 'What are you looking for?' }}" class="stt-boutique-input w-full sm:flex-1">
                    <div class="flex items-center gap-3">
                        <button type="submit" class="stt-boutique-btn flex-1 sm:flex-none sm:shrink-0">{{ $theme['header_search_button'] ?? 'Search' }}</button>
                        <button type="button" @click="search = false" class="stt-boutique-label shrink-0 px-1 py-3" style="color: var(--st-ink-soft)">{{ $theme['header_search_close'] ?? 'Close' }}</button>
                    </div>
                </form>
                <div x-show="results.length" class="mt-6 grid grid-cols-1 gap-1">
                    <template x-for="item in results" :key="item.url">
                        <a :href="item.url" class="flex items-center gap-4 py-2 hover:opacity-70">
                            <span class="grid h-14 w-12 shrink-0 place-items-center overflow-hidden" style="background: var(--st-surface); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                                <template x-if="item.image"><img :src="item.image" alt="" class="h-full w-full object-cover"></template>
                            </span>
                            <span class="min-w-0 flex-1 truncate text-sm font-semibold" style="color: var(--st-ink)" x-text="item.name"></span>
                            <span class="text-sm font-bold" style="color: var(--st-accent)" x-text="item.price"></span>
                        </a>
                    </template>
                </div>
                @if (count($popularSearchTerms))
                    <div x-show="!q.trim().length" class="mt-6 flex flex-wrap items-center gap-2">
                        <span class="stt-boutique-label" style="color: var(--st-ink-soft)">{{ __('storefront.popular_searches') }}</span>
                        @foreach ($popularSearchTerms as $term)
                            <a href="{{ route('storefront.search', ['q' => $term]) }}" class="rounded-full px-3 py-1 text-xs transition hover:opacity-70" style="border: 1px solid var(--st-line); color: var(--st-ink)">{{ $term }}</a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Mobile nav drawer --}}
    <div x-show="mobileNav" x-cloak class="fixed inset-0 z-50 lg:hidden" style="display:none" @keydown.escape.window="mobileNav = false">
        <div x-show="mobileNav" x-transition.opacity class="fixed inset-0" style="background: color-mix(in srgb, var(--st-ink) 55%, transparent)" @click="mobileNav = false"></div>
        <div x-show="mobileNav" class="fixed inset-y-0 start-0 flex w-72 flex-col overflow-y-auto p-6" style="background: var(--st-bg); border-inline-end: 1px solid var(--st-line)"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="ltr:-translate-x-full rtl:translate-x-full">
            <div class="mb-6 flex items-center justify-between border-b pb-5" style="border-color: var(--st-line)">
                <span class="text-sm font-bold uppercase" style="letter-spacing: 0.14em; color: var(--st-ink)">{{ $storeName }}</span>
                <button @click="mobileNav = false" aria-label="{{ __('storefront.close') }}" class="stt-boutique-iconbtn text-xl leading-none" style="margin-inline-end: -0.5rem">&times;</button>
            </div>
            <nav class="space-y-1">
                {{-- Roomy tap targets (~44px rows) inside the drawer. --}}
                <a href="{{ url('/') }}" class="stt-boutique-label block py-2.5">{{ $theme['header_nav_home'] ?? 'Home' }}</a>
                <a href="{{ url('/shop') }}" class="stt-boutique-label block py-2.5">{{ $theme['header_nav_shop'] ?? 'Shop' }}</a>
                @foreach ($tree as $category)
                    <a href="{{ url('/category/' . $category->slug) }}" class="stt-boutique-label block py-2.5">{{ $category->name }}</a>
                @endforeach
                @if ($addons->installed('blog'))<a href="{{ url('/blog') }}" class="stt-boutique-label block py-2.5">{{ $theme['header_nav_blog'] ?? 'Blog' }}</a>@endif
                <div class="border-t pt-3" style="border-color: var(--st-line)">
                    @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))
                    <a href="{{ route('storefront.wishlist') }}" class="stt-boutique-label block py-2.5" style="color: var(--st-ink-soft)">{{ $theme['header_nav_wishlist'] ?? 'Wishlist' }}</a>
                    @endif
                    @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))
                    <a href="{{ route('storefront.compare') }}" class="stt-boutique-label block py-2.5" style="color: var(--st-ink-soft)">{{ $theme['header_nav_compare'] ?? 'Compare' }}</a>
                    @endif
                    <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="stt-boutique-label block py-2.5" style="color: var(--st-ink-soft)">{{ $theme['header_nav_account'] ?? 'Account' }}</a>
                </div>
            </nav>
            {{-- Currency: desktop-only in the masthead, so mobile needs its own way in. --}}
            @if ($currency->hasMultiple())
                <div class="mt-3 flex items-center gap-3 border-t pt-3" style="border-color: var(--st-line)">
                    <select onchange="if(this.value){window.location='{{ url('/currency') }}/'+this.value}" aria-label="{{ __('storefront.currency') }}"
                        class="h-9 cursor-pointer border-0 bg-transparent px-0 text-sm focus:outline-none" style="color: var(--st-ink)">
                        @foreach ($currency->currencies() as $c)
                            <option value="{{ $c['code'] }}" @selected($c['code'] === $currency->activeCode())>{{ $c['code'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <span class="stt-boutique-mark" style="margin-top: auto" aria-hidden="true"></span>
        </div>
    </div>
</header>
