@php
    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots();
    $storeName = settings('general.store_name', config('app.name'));
    $logo = settings('general.logo');
    $wishlistCount = app(\Themicly\Shopcrafty\Modules\Customers\Services\WishlistService::class)->count();
    $compareCount = app(\Themicly\Shopcrafty\Modules\Catalog\Services\CompareService::class)->count();
    $currency = app(\Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService::class);
    // Bloom's signature leaf glyph — the same outline path is reused wherever the wordmark leaf appears.
    $freshLeaf = 'M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12';
    $popularSearchTerms = array_slice((array) settings('search.popular_terms', []), 0, 10);
@endphp

{{-- Bloom header: friendly three-tier grocery pattern — green delivery bar, pill search
     + round icon buttons, then a scrolling row of round category chips. Sticky. --}}
<header class="stt-fresh-glass sticky top-0 z-30" style="background: color-mix(in srgb, var(--st-bg) 92%, transparent); border-bottom: 1px solid var(--st-line)"
    x-data="{ mobileNav: false, searchFocused: false, q: '', results: [], timer: null,
        run() {
            clearTimeout(this.timer);
            if (this.q.trim().length < 3) { this.results = []; return; }
            this.timer = setTimeout(() => {
                fetch('{{ route('storefront.search.suggest') }}?q=' + encodeURIComponent(this.q.trim()))
                    .then(r => r.ok ? r.json() : []).then(d => this.results = Array.isArray(d) ? d : []).catch(() => this.results = []);
            }, 180);
        }
    }">
    {{-- (1) Green delivery bar --}}
    <div class="text-center text-xs font-medium" style="background: var(--st-primary); color: var(--st-primary-ink)">
        <div class="st-container flex h-9 items-center justify-center gap-2">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4 shrink-0" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" /></svg>
            <span>{{ $theme['header_delivery_text'] ?? 'Free delivery over $35 · Same-day slots available' }}</span>
        </div>
    </div>

    {{-- (2) Main row: wordmark · pill search · round action buttons --}}
    <div class="st-container flex items-center gap-4" style="height: 4.5rem">
        {{-- 44px round tap target (mobile-heavy grocery shoppers). --}}
        <button class="stt-fresh-iconbtn shrink-0 lg:hidden" style="margin-left: -0.5rem" @click="mobileNav = true" aria-label="{{ __('storefront.menu') }}" :aria-expanded="mobileNav">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6" style="color: var(--st-ink)"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
        </button>

        <a href="{{ url('/') }}" class="flex shrink-0 items-center gap-2" style="color: var(--st-ink)">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $storeName }}" class="h-9 w-auto object-contain">
            @else
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6 shrink-0" style="color: var(--st-primary)" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $freshLeaf }}" /></svg>
                <span class="stt-fresh-heading text-2xl">{{ $storeName }}</span>
            @endif
        </a>

        {{-- Rounded pill search with live suggest dropdown --}}
        <div class="relative hidden flex-1 md:block" @click.outside="results = []; searchFocused = false">
            <form action="{{ route('storefront.search') }}" method="GET" class="stt-fresh-input">
                <span class="grid w-11 place-items-center" style="color: var(--st-ink-soft)">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                </span>
                <input name="q" x-model="q" @input="run()" @focus="searchFocused = true" type="search" autocomplete="off" placeholder="{{ $theme['header_search_placeholder'] ?? 'Search for fruit, veg, bakery…' }}" class="h-11 flex-1 bg-transparent pr-3 text-sm focus:outline-none" style="color: var(--st-ink)">
                <button type="submit" class="rounded-full px-6 text-sm font-bold" style="background: var(--st-primary); color: var(--st-primary-ink)">{{ $theme['header_search_button'] ?? 'Search' }}</button>
            </form>
            <div x-show="results.length" x-cloak class="absolute inset-x-0 top-full z-40 mt-2 overflow-hidden shadow-lg" style="background: var(--st-bg); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                <template x-for="item in results" :key="item.url">
                    <a :href="item.url" class="flex items-center gap-3 px-3 py-2 hover:bg-black/5">
                        <span class="grid h-10 w-10 shrink-0 place-items-center overflow-hidden rounded-full" style="background: var(--st-surface)">
                            <template x-if="item.image"><img :src="item.image" alt="" class="h-full w-full object-cover"></template>
                        </span>
                        <span class="min-w-0 flex-1 truncate text-sm" style="color: var(--st-ink)" x-text="item.name"></span>
                        <span class="text-sm font-bold" style="color: var(--st-primary)" x-text="item.price"></span>
                    </a>
                </template>
            </div>
            @if (count($popularSearchTerms))
                <div x-show="searchFocused && !q.trim().length" x-cloak class="absolute inset-x-0 top-full z-40 mt-2 flex flex-wrap items-center gap-2 p-3" style="background: var(--st-bg); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
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
            @if ($currency->hasMultiple())
                <select onchange="if(this.value){window.location='{{ url('/currency') }}/'+this.value}" aria-label="{{ __('storefront.currency') }}"
                    class="mr-1 hidden h-9 cursor-pointer rounded-full border-0 bg-transparent px-2 text-sm font-semibold focus:outline-none sm:block" style="color: var(--st-ink)">
                    @foreach ($currency->currencies() as $c)
                        <option value="{{ $c['code'] }}" @selected($c['code'] === $currency->activeCode())>{{ $c['code'] }}</option>
                    @endforeach
                </select>
            @endif
            @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))
            <a href="{{ route('storefront.wishlist') }}" x-data="{ n: {{ $wishlistCount }} }" x-on:wishlist-changed.window="n = $event.detail.count"
                class="stt-fresh-iconbtn relative hidden sm:grid" aria-label="{{ __('storefront.wishlist') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5" style="color: var(--st-ink)"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="absolute right-1 top-1 grid h-4 min-w-4 place-items-center rounded-full px-1 text-[10px] font-bold text-white" style="background: var(--st-accent)"></span>
            </a>
            @endif
            @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))
            <a href="{{ route('storefront.compare') }}" x-data="{ n: {{ $compareCount }} }" x-on:compare-changed.window="n = $event.detail.count"
                class="stt-fresh-iconbtn relative hidden sm:grid" aria-label="{{ __('storefront.compare') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5" style="color: var(--st-ink)"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="absolute right-1 top-1 grid h-4 min-w-4 place-items-center rounded-full px-1 text-[10px] font-bold text-white" style="background: var(--st-accent)"></span>
            </a>
            @endif
            <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="stt-fresh-iconbtn" aria-label="{{ __('storefront.account') }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6" style="color: var(--st-ink)"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
            </a>
            </div>
            <livewire:orders.cart-drawer :hide-trigger-below="'lg'" />
        </div>
    </div>

    {{-- Mobile pill search (collapses to its own row) --}}
    <div class="px-4 pb-3 md:hidden">
        <form action="{{ route('storefront.search') }}" method="GET" class="stt-fresh-input">
            <input name="q" type="search" placeholder="{{ $theme['header_search_placeholder_mobile'] ?? 'Search groceries…' }}" class="h-10 flex-1 bg-transparent px-4 text-sm focus:outline-none" style="color: var(--st-ink)">
            <button type="submit" class="rounded-full px-5 text-sm font-bold" style="background: var(--st-primary); color: var(--st-primary-ink)">{{ $theme['header_search_button_mobile'] ?? 'Go' }}</button>
        </form>
    </div>

    {{-- (3) Scrolling row of round category chips --}}
    <div class="border-t" style="border-color: var(--st-line)">
        <div class="st-container flex items-center gap-2 overflow-x-auto py-2.5">
            <a href="{{ url('/shop') }}" class="stt-fresh-chip stt-fresh-chip--active">{{ $theme['header_all_chip'] ?? 'All' }}</a>
            {{-- Grocery pattern: chips are always categories, so shoppers browse aisles fast. --}}
            @foreach ($tree->take(10) as $category)
                <a href="{{ url('/category/' . $category->slug) }}" class="stt-fresh-chip">{{ $category->name }}</a>
            @endforeach
        </div>
    </div>

    {{-- Mobile nav drawer (rounded slide-in) --}}
    <div x-show="mobileNav" x-cloak class="fixed inset-0 z-50 lg:hidden" style="display:none">
        <div class="fixed inset-0 bg-black/40" @click="mobileNav = false"></div>
        <div class="fixed inset-y-0 start-0 w-72 overflow-y-auto p-5" style="background: var(--st-bg); border-start-end-radius: 28px; border-end-end-radius: 28px"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full" x-transition:enter-end="translate-x-0">
            <div class="mb-6 flex items-center justify-between">
                <span class="stt-fresh-heading flex items-center gap-2 text-lg"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5 shrink-0" style="color: var(--st-primary)" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $freshLeaf }}" /></svg>{{ $storeName }}</span>
                <button @click="mobileNav = false" aria-label="{{ __('storefront.close') }}" class="stt-fresh-iconbtn shrink-0 text-2xl leading-none" style="color: var(--st-ink); margin-inline-end: -0.5rem">&times;</button>
            </div>
            <nav class="space-y-1">
                <a href="{{ url('/shop') }}" class="block rounded-full px-4 py-2.5 text-sm font-semibold hover:bg-black/5" style="color: var(--st-primary)">{{ __('storefront.shop') }}</a>
                @foreach ($tree as $category)
                    <a href="{{ url('/category/' . $category->slug) }}" class="block rounded-full px-4 py-2.5 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ $category->name }}</a>
                @endforeach
            </nav>
            {{-- Account & shopping tools that live in the desktop masthead move here on mobile. --}}
            <div class="my-4 border-t" style="border-color: var(--st-line)"></div>
            <nav class="space-y-1">
                <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="block rounded-full px-4 py-2.5 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ auth('customer')->check() ? __('storefront.account') : __('storefront.sign_in') }}</a>
                @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))<a href="{{ route('storefront.wishlist') }}" class="block rounded-full px-4 py-2.5 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ __('storefront.wishlist') }}</a>@endif
                @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))<a href="{{ route('storefront.compare') }}" class="block rounded-full px-4 py-2.5 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ __('storefront.compare') }}</a>@endif
            </nav>
            {{-- Currency: desktop-only in the masthead, so mobile needs its own way in. --}}
            @if ($currency->hasMultiple())
                <div class="my-4 border-t" style="border-color: var(--st-line)"></div>
                <div class="flex items-center gap-3 px-4">
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
