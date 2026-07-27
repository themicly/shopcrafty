@php
    use Illuminate\Support\Facades\Route;

    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots();
    $storeName = settings('general.store_name', config('app.name'));
    $popularSearchTerms = array_slice((array) settings('search.popular_terms', []), 0, 10);

    // Header builder options (TASK #31).
    $headerLayout = $theme['header_layout'] ?? 'logo-left';
    $centered = $headerLayout === 'logo-center';
    $sticky = (bool) ($theme['header_sticky'] ?? true);
    // Haven's masthead stays a solid linen bar over its hairline — the
    // transparent-overlay move is Aurora's — but the flag is kept for builder parity.
    $transparent = (bool) ($theme['header_transparent_home'] ?? false) && request()->is('/');

    $headerMenu = \Themicly\Shopcrafty\Modules\CMS\Models\Menu::where('location', 'header')->first()?->items()->whereNull('parent_id')->with('children')->orderBy('position')->get() ?? collect();

    // Slug → image map for the mega-menu / fallback nav (TASK #33). One query, no N+1.
    $catImages = \Themicly\Shopcrafty\Modules\Catalog\Models\Category::whereNotNull('image_path')->pluck('image_path', 'slug');

    $headerPosition = $sticky ? 'sticky top-0' : 'relative';
@endphp

{{-- Haven masthead: warm linen bar closed by a hairline — lowercase serif
     wordmark, quiet nav, hairline search field, wishlist/compare/account/cart. --}}
<header class="{{ $headerPosition }} z-30"
    style="background: var(--st-bg); border-bottom: 1px solid var(--st-line)"
    x-data="{ mobileNav: false, search: false }"
    @keydown.escape.window="mobileNav = false">
    <div class="st-container flex h-20 items-center gap-3 md:gap-6">
        {{-- Mobile menu --}}
        <button class="stt-haven-iconbtn md:hidden" style="margin-left: -0.5rem" @click="mobileNav = true"
            aria-label="{{ __('storefront.menu') }}" aria-controls="haven-mobile-nav" :aria-expanded="mobileNav.toString()" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.4" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7h16.5M3.75 12h16.5m-16.5 5h16.5" /></svg>
        </button>

        @php $logo = settings('general.logo'); @endphp
        <a href="{{ url('/') }}" class="flex min-w-0 items-center {{ $centered ? 'md:absolute md:left-1/2 md:-translate-x-1/2' : '' }}">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $storeName }}" class="h-9 w-auto object-contain">
            @else
                <span class="stt-haven-wordmark truncate text-2xl">{{ $storeName }}</span>
            @endif
        </a>

        {{-- Desktop nav: configured header menu (with dropdown / mega submenus), else the
             category mega-menu. In the logo-center layout the nav drops to a second row. --}}
        @unless ($centered)
            @include('theme::partials.header-nav', ['navClass' => 'mx-auto hidden items-center gap-7 md:flex'])
        @endunless

        <div class="{{ $centered ? 'ml-auto' : '' }} flex items-center gap-0.5">
            @php $currency = app(\Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService::class); @endphp
            @if ($currency->hasMultiple())
                <select onchange="if(this.value){window.location='{{ url('/currency') }}/'+this.value}" aria-label="{{ __('storefront.currency') }}"
                    class="stt-haven-crumb mr-1 h-9 cursor-pointer border-0 bg-transparent px-1 focus:outline-none">
                    @foreach ($currency->currencies() as $c)
                        <option value="{{ $c['code'] }}" @selected($c['code'] === $currency->activeCode())>{{ $c['code'] }}</option>
                    @endforeach
                </select>
            @endif

            {{-- Hairline search trigger (desktop) + icon trigger (mobile); both open
                 the predictive overlay below. --}}
            <button type="button" @click="search = true" class="stt-haven-search mr-1 hidden md:inline-flex">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                <span>{{ __('storefront.search') }}</span>
            </button>
            <button @click="search = true" class="stt-haven-iconbtn md:hidden" aria-label="{{ __('storefront.search') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </button>

            {{-- Secondary actions collapse into the mobile drawer so the phone
                 masthead keeps only the logo + cart (and search). --}}
            <div class="hidden items-center gap-0.5 md:flex">
            @php $wishlistCount = app(\Themicly\Shopcrafty\Modules\Customers\Services\WishlistService::class)->count(); @endphp
            @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))
            <a href="{{ route('storefront.wishlist') }}"
                x-data="{ n: {{ $wishlistCount }} }"
                x-on:wishlist-changed.window="n = $event.detail.count"
                class="stt-haven-iconbtn" aria-label="{{ __('storefront.wishlist') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="absolute top-0.5 grid h-4 min-w-4 place-items-center rounded-full px-1 text-[10px] font-semibold" style="right: 0.125rem; background: var(--st-ink); color: var(--st-bg)"></span>
            </a>
            @endif
            @php $compareCount = app(\Themicly\Shopcrafty\Modules\Catalog\Services\CompareService::class)->count(); @endphp
            @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))
            <a href="{{ route('storefront.compare') }}"
                x-data="{ n: {{ $compareCount }} }"
                x-on:compare-changed.window="n = $event.detail.count"
                class="stt-haven-iconbtn" aria-label="{{ __('storefront.compare') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="absolute top-0.5 grid h-4 min-w-4 place-items-center rounded-full px-1 text-[10px] font-semibold" style="right: 0.125rem; background: var(--st-ink); color: var(--st-bg)"></span>
            </a>
            @endif
            <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="stt-haven-iconbtn" aria-label="{{ __('storefront.account') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
            </a>
            </div>
            <livewire:orders.cart-drawer />
        </div>

        {{-- Predictive search overlay --}}
        <div x-show="search" x-cloak class="fixed inset-0 z-50" style="display:none" @keydown.escape.window="search = false"
            x-data="{
                q: '', results: [], timer: null,
                run() {
                    clearTimeout(this.timer);
                    if (this.q.trim().length < 3) { this.results = []; return; }
                    this.timer = setTimeout(() => {
                        fetch('{{ route('storefront.search.suggest') }}?q=' + encodeURIComponent(this.q.trim()))
                            .then(r => r.ok ? r.json() : [])
                            .then(d => this.results = Array.isArray(d) ? d : [])
                            .catch(() => this.results = []);
                    }, 180);
                }
            }">
            <div class="fixed inset-0 bg-black/40" @click="search = false"></div>
            <div class="fixed inset-x-0 top-0 p-4" style="padding-bottom: 1.5rem; background: var(--st-bg); border-bottom: 1px solid var(--st-line)">
                <div class="st-container">
                    <form action="{{ route('storefront.search') }}" method="GET" class="flex items-center gap-3 px-5 py-2" style="border: 1px solid color-mix(in srgb, var(--st-ink) 40%, transparent); border-radius: var(--st-radius)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5" style="color: var(--st-ink-soft)"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        <input name="q" x-model="q" @input="run()" x-ref="q" x-init="$watch('search', v => v && $nextTick(() => $refs.q.focus()))" type="search" placeholder="{{ $theme['header_search_placeholder'] ?? 'Search the collection…' }}" autocomplete="off" class="h-11 flex-1 bg-transparent focus:outline-none" style="font-family: var(--st-font-display); font-size: 1.15rem; color: var(--st-ink)">
                        <button type="button" @click="search = false" class="stt-haven-crumb px-3 py-3 transition hover:opacity-70" style="margin-block: -0.5rem" aria-label="{{ __('storefront.close_search') }}">Esc</button>
                    </form>

                    {{-- Suggestions --}}
                    <div x-show="results.length" class="mt-4 grid grid-cols-1 gap-1">
                        <template x-for="item in results" :key="item.url">
                            <a :href="item.url" class="flex items-center gap-3 p-2 hover:opacity-70">
                                <span class="grid h-14 w-14 shrink-0 place-items-center overflow-hidden" style="background: var(--st-surface); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                                    <template x-if="item.image"><img :src="item.image" alt="" class="h-full w-full object-cover"></template>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-sm font-medium" style="color: var(--st-ink)" x-text="item.name"></span>
                                <span class="stt-haven-price" style="font-size: 0.95rem" x-text="item.price"></span>
                            </a>
                        </template>
                    </div>
                    @if (count($popularSearchTerms))
                        <div x-show="!q.trim().length" class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="stt-haven-crumb" style="color: var(--st-ink-soft)">{{ __('storefront.popular_searches') }}</span>
                            @foreach ($popularSearchTerms as $term)
                                <a href="{{ route('storefront.search', ['q' => $term]) }}" class="rounded-full px-3 py-1 text-xs transition hover:opacity-70" style="border: 1px solid var(--st-line); color: var(--st-ink)">{{ $term }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mobile nav drawer: esc closes (window listener on the header root). --}}
        <div x-show="mobileNav" x-cloak id="haven-mobile-nav" class="fixed inset-0 z-50 md:hidden" style="display:none">
            <div class="fixed inset-0 bg-black/40" @click="mobileNav = false"></div>
            <div class="fixed inset-y-0 start-0 flex w-72 flex-col overflow-y-auto p-6" style="background: var(--st-bg)"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full" x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="ltr:-translate-x-full rtl:translate-x-full">
                <div class="mb-4 flex items-center justify-between">
                    <span class="stt-haven-wordmark text-xl">{{ $storeName }}</span>
                    <button @click="mobileNav = false" class="stt-haven-iconbtn text-2xl" style="margin-inline-end: -0.5rem" aria-label="{{ __('storefront.close') }}">&times;</button>
                </div>
                <div class="stt-haven-divider mb-4" aria-hidden="true"></div>
                <nav class="space-y-1">
                    <a href="{{ url('/shop') }}" class="stt-haven-nav block py-2.5">{{ __('storefront.shop') }}</a>
                    @foreach ($tree as $category)
                        <a href="{{ url('/category/' . $category->slug) }}" class="stt-haven-nav block py-2.5">{{ $category->name }}</a>
                    @endforeach
                </nav>
                {{-- Account & shopping tools that live in the desktop masthead move here on mobile. --}}
                <div class="stt-haven-divider my-4" aria-hidden="true"></div>
                <nav class="space-y-1">
                    <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="stt-haven-nav block py-2.5">{{ auth('customer')->check() ? __('storefront.account') : __('storefront.sign_in') }}</a>
                    @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))
                        <a href="{{ route('storefront.wishlist') }}" class="stt-haven-nav block py-2.5">{{ __('storefront.wishlist') }}</a>
                    @endif
                    @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))
                        <a href="{{ route('storefront.compare') }}" class="stt-haven-nav block py-2.5">{{ __('storefront.compare') }}</a>
                    @endif
                </nav>
                {{-- Currency: desktop-only in the masthead, so mobile needs its own way in. --}}
                @if ($currency->hasMultiple())
                    <div class="stt-haven-divider my-4" aria-hidden="true"></div>
                    <div class="flex items-center gap-4">
                        <select onchange="if(this.value){window.location='{{ url('/currency') }}/'+this.value}" aria-label="{{ __('storefront.currency') }}"
                            class="stt-haven-crumb h-9 cursor-pointer border-0 bg-transparent px-0 focus:outline-none">
                            @foreach ($currency->currencies() as $c)
                                <option value="{{ $c['code'] }}" @selected($c['code'] === $currency->activeCode())>{{ $c['code'] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($centered)
        {{-- Logo-center layout: the primary nav lives on a centered second row. --}}
        <div class="hidden md:block" style="border-top: 1px solid var(--st-line)">
            @include('theme::partials.header-nav', ['navClass' => 'st-container flex h-12 items-center justify-center gap-7'])
        </div>
    @endif
</header>
