@php
    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots();
    $storeName = settings('general.store_name', config('app.name'));
    $logo = settings('general.logo');
    $headerMenu = \Themicly\Shopcrafty\Modules\CMS\Models\Menu::where('location', 'header')->first()?->items()->get() ?? collect();
    $addons = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class);
    $wishlistOn = $addons->installed('wishlist') && settings('catalog.wishlist_enabled', true);
    $wishlistService = $wishlistOn ? ($addons->all()['wishlist']['service'] ?? null) : null;
    $wishlistCount = $wishlistService ? app($wishlistService)->count() : 0;
    $compareService = $addons->all()['compare']['service'] ?? null;
    $compareCount = $compareService ? app($compareService)->count() : 0;
    $popularSearchTerms = array_slice((array) settings('search.popular_terms', []), 0, 10);
@endphp

{{-- Marketplace header: electronics-megastore pattern — brand-blue utility bar + persistent search + departments rail. --}}
<header class="{{ ($theme['header_sticky'] ?? true) ? 'sticky top-0' : 'relative' }} z-30" style="background: var(--st-bg); border-bottom: 1px solid var(--st-line)"
    @keydown.escape.window="mobileNav = false; deptOpen = false; results = []; searchFocused = false"
    x-data="{ mobileNav: false, deptOpen: false, searchFocused: false, q: '', results: [], timer: null,
        run() {
            clearTimeout(this.timer);
            if (this.q.trim().length < 3) { this.results = []; return; }
            this.timer = setTimeout(() => {
                fetch('{{ route('storefront.search.suggest') }}?q=' + encodeURIComponent(this.q.trim()))
                    .then(r => r.ok ? r.json() : []).then(d => this.results = Array.isArray(d) ? d : []).catch(() => this.results = []);
            }, 180);
        }
    }">
    {{-- (1) Slim brand-blue utility bar — delivery promise · track/deals/help. Hidden on mobile. --}}
    <div class="stt-market-utilbar hidden md:block">
        <div class="st-container flex h-9 items-center justify-between">
            <span class="font-semibold">{{ $theme['header_promo'] ?? 'Free next-day delivery on 10,000+ products' }}</span>
            <nav class="flex items-center gap-5 font-medium">
                <a href="{{ url('/track') }}" class="hover:opacity-70">{{ $theme['header_track'] ?? 'Track order' }}</a>
                <a href="{{ url('/shop') }}" class="hover:opacity-70">{{ $theme['header_deals'] ?? 'Deals' }}</a>
                <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="hover:opacity-70">{{ $theme['header_help'] ?? 'Help' }}</a>
                @php $currency = app(\Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService::class); @endphp
                @if ($currency->hasMultiple())
                    <select onchange="if(this.value){window.location='{{ url('/currency') }}/'+this.value}" aria-label="{{ __('storefront.currency') }}"
                        class="cursor-pointer border-0 bg-transparent py-0 pl-0 pr-5 font-medium focus:outline-none" style="color: inherit">
                        @foreach ($currency->currencies() as $c)
                            <option value="{{ $c['code'] }}" @selected($c['code'] === $currency->activeCode())>{{ $c['code'] }}</option>
                        @endforeach
                    </select>
                @endif
            </nav>
        </div>
    </div>

    {{-- (2) Main row: logo · persistent search · actions --}}
    <div class="st-container flex h-16 items-center gap-4">
        <button class="grid h-11 w-11 place-items-center rounded-full hover:bg-black/5 lg:hidden" style="margin-left: -0.5rem" @click="mobileNav = true" aria-label="{{ __('storefront.menu') }}" :aria-expanded="mobileNav">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6" style="color: var(--st-ink)"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
        </button>

        <a href="{{ url('/') }}" class="flex shrink-0 items-center" style="color: var(--st-ink)">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $storeName }}" class="h-8 w-auto object-contain">
            @else
                <span class="st-display text-2xl font-extrabold tracking-tight">{{ $storeName }}</span>
            @endif
        </a>

        {{-- Persistent, front-and-centre search with live suggestions dropdown --}}
        <div class="relative hidden flex-1 md:block" @click.outside="results = []; searchFocused = false">
            <form action="{{ route('storefront.search') }}" method="GET" class="stt-market-field">
                <input name="q" x-model="q" @input="run()" @focus="searchFocused = true" type="search" autocomplete="off" placeholder="{{ $theme['header_search_placeholder'] ?? 'Search products, brands and categories…' }}"
                    class="h-11" style="color: var(--st-ink)">
                <button type="submit" aria-label="{{ __('storefront.search') }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </button>
            </form>
            <div x-show="results.length" x-cloak class="stt-market-box absolute inset-x-0 top-full z-40 mt-1 overflow-hidden shadow-lg">
                <template x-for="item in results" :key="item.url">
                    <a :href="item.url" class="flex items-center gap-3 px-3 py-2 hover:bg-black/5">
                        <span class="grid h-10 w-10 shrink-0 place-items-center overflow-hidden" style="background: var(--st-surface); border-radius: var(--st-radius)">
                            <template x-if="item.image"><img :src="item.image" alt="" class="h-full w-full object-cover"></template>
                        </span>
                        <span class="min-w-0 flex-1 truncate text-sm" style="color: var(--st-ink)" x-text="item.name"></span>
                        <span class="stt-market-price text-sm" x-text="item.price"></span>
                    </a>
                </template>
            </div>
            @if (count($popularSearchTerms))
                <div x-show="searchFocused && !q.trim().length" x-cloak class="stt-market-box absolute inset-x-0 top-full z-40 mt-1 flex flex-wrap items-center gap-2 p-3">
                    <span class="text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.popular_searches') }}</span>
                    @foreach ($popularSearchTerms as $term)
                        <a href="{{ route('storefront.search', ['q' => $term]) }}" class="rounded-full px-3 py-1 text-xs transition-colors hover:bg-black/5" style="border: 1px solid var(--st-line); color: var(--st-ink)">{{ $term }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="ml-auto flex items-center gap-1">
            {{-- Secondary actions collapse into the mobile drawer so the phone
                 masthead keeps only the logo + cart (search has its own row). --}}
            <div class="hidden items-center gap-1 md:flex">
            @if ($wishlistOn)
            <a href="{{ route('storefront.wishlist') }}" x-data="{ n: {{ $wishlistCount }} }" x-on:wishlist-changed.window="n = $event.detail.count"
                class="relative hidden h-10 w-10 place-items-center rounded-full hover:bg-black/5 sm:grid" aria-label="{{ __('storefront.wishlist') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5" style="color: var(--st-ink)"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="absolute right-0 top-0 grid h-4 min-w-4 place-items-center rounded-full px-1 text-[10px] font-bold text-white" style="background: var(--st-accent)"></span>
            </a>
            @endif
            @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))
            <a href="{{ route('storefront.compare') }}" x-data="{ n: {{ $compareCount }} }" x-on:compare-changed.window="n = $event.detail.count"
                class="relative hidden h-10 w-10 place-items-center rounded-full hover:bg-black/5 sm:grid" aria-label="{{ __('storefront.compare') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5" style="color: var(--st-ink)"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="absolute right-0 top-0 grid h-4 min-w-4 place-items-center rounded-full px-1 text-[10px] font-bold text-white" style="background: var(--st-accent)"></span>
            </a>
            @endif
            <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" aria-label="{{ __('storefront.account') }}" class="flex items-center gap-2 rounded-full px-2 py-1.5 hover:bg-black/5" style="color: var(--st-ink)">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                <span class="hidden text-xs font-semibold uppercase leading-tight tracking-wide lg:block">{{ $theme['header_account'] ?? 'Account' }}</span>
            </a>
            </div>
            <livewire:orders.cart-drawer :hide-trigger-below="'lg'" />
        </div>
    </div>

    {{-- (3) Departments rail — solid brand-blue mega-menu button · category links · red Today's Deals --}}
    <div class="hidden border-t lg:block" style="border-color: var(--st-line); background: var(--st-surface)">
        <div class="st-container flex h-11 items-center gap-1">
            <div class="relative" @mouseleave="deptOpen = false" @click.outside="deptOpen = false">
                <button @mouseenter="deptOpen = true" @click="deptOpen = ! deptOpen" :aria-expanded="deptOpen" aria-haspopup="true"
                    class="flex items-center gap-2 px-4 py-2 text-xs font-bold uppercase tracking-wide" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius)">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    {{ $theme['header_departments'] ?? 'All Departments' }}
                </button>
                <div x-show="deptOpen" x-cloak class="stt-market-box absolute left-0 top-full z-40 min-w-56 py-2 shadow-lg">
                    @foreach ($tree as $category)
                        <a href="{{ url('/category/' . $category->slug) }}" class="block px-4 py-2 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ $category->name }}</a>
                    @endforeach
                </div>
            </div>
            <nav class="ml-2 flex items-center gap-1 overflow-x-auto">
                @if ($headerMenu->isNotEmpty())
                    @foreach ($headerMenu as $item)
                        <a href="{{ $item->url }}" class="whitespace-nowrap px-3 py-1.5 text-sm font-medium hover:opacity-70" style="color: var(--st-ink)">{{ $item->label }}</a>
                    @endforeach
                @else
                    @foreach ($tree->take(6) as $category)
                        <a href="{{ url('/category/' . $category->slug) }}" class="whitespace-nowrap px-3 py-1.5 text-sm font-medium hover:opacity-70" style="color: var(--st-ink)">{{ $category->name }}</a>
                    @endforeach
                @endif
                <a href="{{ url('/shop') }}" class="whitespace-nowrap px-3 py-1.5 text-xs font-bold uppercase tracking-wide" style="color: var(--st-accent)">{{ $theme['header_todays_deals'] ?? "Today's Deals" }}</a>
            </nav>
        </div>
    </div>

    {{-- Mobile search --}}
    <div class="border-t p-3 md:hidden" style="border-color: var(--st-line)">
        <form action="{{ route('storefront.search') }}" method="GET" class="stt-market-field">
            <input name="q" type="search" placeholder="{{ $theme['header_search_placeholder_mobile'] ?? 'Search products…' }}" class="h-10" style="color: var(--st-ink)">
            <button type="submit" aria-label="{{ __('storefront.search') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </button>
        </form>
    </div>

    {{-- Mobile nav drawer --}}
    <div x-show="mobileNav" x-cloak class="fixed inset-0 z-50 lg:hidden" style="display:none">
        <div class="fixed inset-0 bg-black/40" @click="mobileNav = false"></div>
        <div class="fixed inset-y-0 start-0 w-72 overflow-y-auto p-5" style="background: var(--st-bg)"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full" x-transition:enter-end="translate-x-0">
            <div class="mb-6 flex items-center justify-between">
                <span class="st-display text-lg font-extrabold" style="color: var(--st-ink)">{{ $storeName }}</span>
                <button @click="mobileNav = false" aria-label="{{ __('storefront.close') }}" class="grid h-11 w-11 place-items-center rounded-full text-2xl leading-none hover:bg-black/5" style="color: var(--st-ink); margin-inline-end: -0.5rem">&times;</button>
            </div>
            <p class="mb-2 text-xs font-bold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ $theme['header_mobile_departments'] ?? 'Departments' }}</p>
            <nav class="space-y-1">
                <a href="{{ url('/shop') }}" class="block px-3 py-2.5 text-sm font-semibold hover:bg-black/5" style="color: var(--st-primary)">{{ $theme['header_all_products'] ?? 'All products' }}</a>
                @foreach ($tree as $category)
                    <a href="{{ url('/category/' . $category->slug) }}" class="block px-3 py-2.5 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ $category->name }}</a>
                @endforeach
            </nav>
            {{-- Account & shopping tools that live in the desktop masthead move here on mobile. --}}
            <div class="my-4 border-t" style="border-color: var(--st-line)"></div>
            <nav class="space-y-1">
                <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="block px-3 py-2.5 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ auth('customer')->check() ? __('storefront.account') : __('storefront.sign_in') }}</a>
                @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))<a href="{{ route('storefront.wishlist') }}" class="block px-3 py-2.5 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ __('storefront.wishlist') }}</a>@endif
                @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))<a href="{{ route('storefront.compare') }}" class="block px-3 py-2.5 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ __('storefront.compare') }}</a>@endif
            </nav>
            {{-- Currency: desktop-only in the masthead, so mobile needs its own way in. --}}
            @if ($currency->hasMultiple())
                <div class="my-4 border-t" style="border-color: var(--st-line)"></div>
                <div class="flex items-center gap-3 px-3">
                    <select onchange="if(this.value){window.location='{{ url('/currency') }}/'+this.value}" aria-label="{{ __('storefront.currency') }}"
                        class="h-9 cursor-pointer border-0 bg-transparent px-0 text-sm focus:outline-none" style="color: var(--st-ink)">
                        @foreach ($currency->currencies() as $c)
                            <option value="{{ $c['code'] }}" @selected($c['code'] === $currency->activeCode())>{{ $c['code'] }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>
</header>
