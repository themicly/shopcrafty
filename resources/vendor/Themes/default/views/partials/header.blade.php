@php
    use Illuminate\Support\Facades\Route;

    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots();
    $storeName = settings('general.store_name', config('app.name'));
    $popularSearchTerms = array_slice((array) settings('search.popular_terms', []), 0, 10);

    // Header builder options (TASK #31).
    $headerLayout = $theme['header_layout'] ?? 'logo-left';
    $centered = $headerLayout === 'logo-center';
    $sticky = (bool) ($theme['header_sticky'] ?? true);
    // Transparent overlay only makes sense on the homepage; solid everywhere else.
    $transparent = (bool) ($theme['header_transparent_home'] ?? false) && request()->is('/');

    $headerMenu = \Themicly\Shopcrafty\Modules\CMS\Models\Menu::where('location', 'header')->first()?->items()->whereNull('parent_id')->with('children')->orderBy('position')->get() ?? collect();

    // Slug → image map for the mega-menu / fallback nav (TASK #33). One query, no N+1.
    $catImages = \Themicly\Shopcrafty\Modules\Catalog\Models\Category::whereNotNull('image_path')->pluck('image_path', 'slug');

    // Positioning: transparent overlays the hero (fixed); otherwise honour the sticky toggle.
    $headerPosition = $transparent ? 'fixed inset-x-0 top-0' : ($sticky ? 'sticky top-0' : 'relative');
@endphp

<header class="{{ $headerPosition }} z-30 border-b{{ $transparent ? '' : ' stt-aurora-glass' }}"
    style="border-color: var(--st-line); background: color-mix(in srgb, var(--st-bg) 92%, transparent)"
    x-data="{ mobileNav: false, search: false, scrolled: false, transparent: {{ $transparent ? 'true' : 'false' }} }"
    x-init="scrolled = window.scrollY > 20" @scroll.window="scrolled = window.scrollY > 20"
    {{-- When transparent and at the top, blank the background and flip tokens to light so
         children reading var(--st-ink)/var(--st-line) render legibly over the hero.
         The frosted blur is a class (::before layer), never inline backdrop-filter —
         see .stt-aurora-glass in the layout for why. --}}
    :class="(transparent && !scrolled) ? '' : 'stt-aurora-glass'"
    :style="(transparent && !scrolled)
        ? 'background: transparent; border-color: transparent; --st-ink: #ffffff; --st-ink-soft: rgba(255,255,255,0.85); --st-line: rgba(255,255,255,0.25)'
        : 'background: color-mix(in srgb, var(--st-bg) 92%, transparent); border-color: var(--st-line)'">
    <div class="st-container flex h-16 items-center gap-4">
        {{-- Mobile menu --}}
        <button class="stt-aurora-iconbtn md:hidden" style="margin-left: -0.5rem" @click="mobileNav = true" aria-label="{{ __('storefront.menu') }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
        </button>

        @php $logo = settings('general.logo'); @endphp
        <a href="{{ url('/') }}" class="flex items-center {{ $centered ? 'md:absolute md:left-1/2 md:-translate-x-1/2' : '' }}" style="color: var(--st-ink)">
            @if ($logo)
                <img src="{{ $logo }}" alt="{{ $storeName }}" class="h-8 w-auto object-contain">
            @else
                <span class="stt-aurora-wordmark text-xl">{{ $storeName }}</span>
            @endif
        </a>

        {{-- Desktop nav: configured header menu (with dropdown / mega submenus), else the
             category mega-menu. In the logo-center layout the nav drops to a second row. --}}
        @unless ($centered)
            @include('theme::partials.header-nav')
        @endunless

        <div class="ml-auto flex items-center gap-1">
            @php $currency = app(\Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService::class); @endphp
            @if ($currency->hasMultiple())
                <select onchange="if(this.value){window.location='{{ url('/currency') }}/'+this.value}" aria-label="Currency"
                    class="mr-1 h-9 cursor-pointer rounded-md border-0 bg-transparent px-2 text-sm font-medium focus:outline-none" style="color: var(--st-ink)">
                    @foreach ($currency->currencies() as $c)
                        <option value="{{ $c['code'] }}" @selected($c['code'] === $currency->activeCode())>{{ $c['code'] }}</option>
                    @endforeach
                </select>
            @endif
            <button @click="search = true" class="stt-aurora-iconbtn" aria-label="{{ __('storefront.search') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
            </button>
            {{-- Secondary actions collapse into the mobile drawer so the phone
                 masthead keeps only the logo + cart (and search). --}}
            <div class="hidden items-center gap-1 md:flex">
            @php $wishlistCount = app(\Themicly\Shopcrafty\Modules\Customers\Services\WishlistService::class)->count(); @endphp
            @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))
            <a href="{{ route('storefront.wishlist') }}"
                x-data="{ n: {{ $wishlistCount }} }"
                x-on:wishlist-changed.window="n = $event.detail.count"
                class="stt-aurora-iconbtn" aria-label="{{ __('storefront.wishlist') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="stt-aurora-count"></span>
            </a>
            @endif
            @php $compareCount = app(\Themicly\Shopcrafty\Modules\Catalog\Services\CompareService::class)->count(); @endphp
            @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))
            <a href="{{ route('storefront.compare') }}"
                x-data="{ n: {{ $compareCount }} }"
                x-on:compare-changed.window="n = $event.detail.count"
                class="stt-aurora-iconbtn hidden sm:grid" aria-label="{{ __('storefront.compare') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>
                <span x-show="n > 0" x-text="n" x-cloak class="stt-aurora-count"></span>
            </a>
            @endif
            <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="stt-aurora-iconbtn" aria-label="{{ __('storefront.account') }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
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
            <div class="fixed inset-0 bg-black/40 backdrop-blur" @click="search = false"></div>
            <div class="fixed inset-x-0 top-0 p-3 sm:p-6">
                <div class="stt-aurora-panel mx-auto max-w-2xl p-4"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <form action="{{ route('storefront.search') }}" method="GET" class="flex items-center gap-3 border-b pb-1" style="border-color: var(--st-line)">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5 shrink-0" style="color: var(--st-primary)"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                        <input name="q" x-model="q" @input="run()" x-ref="q" x-init="$watch('search', v => v && $nextTick(() => $refs.q.focus()))" type="search" placeholder="{{ $theme['header_search_placeholder'] ?? 'Search products…' }}" autocomplete="off" class="h-11 min-w-0 flex-1 bg-transparent text-base focus:outline-none" style="color: var(--st-ink)">
                        <button type="button" @click="search = false" class="shrink-0 rounded-md border px-2 py-1 text-xs font-medium" style="border-color: var(--st-line); color: var(--st-ink-soft)">Esc</button>
                    </form>

                    {{-- Suggestions --}}
                    <div x-show="results.length" class="mt-2 grid grid-cols-1 gap-1 overflow-y-auto" style="max-height: 60vh">
                        <template x-for="item in results" :key="item.url">
                            <a :href="item.url" class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-black/5">
                                <span class="grid h-12 w-11 shrink-0 place-items-center overflow-hidden" style="border-radius: var(--st-radius-sm); background: var(--st-surface)">
                                    <template x-if="item.image"><img :src="item.image" alt="" class="h-full w-full object-cover"></template>
                                </span>
                                <span class="min-w-0 flex-1 truncate text-sm font-medium" style="color: var(--st-ink)" x-text="item.name"></span>
                                <span class="shrink-0 text-sm font-semibold" style="color: var(--st-primary)" x-text="item.price"></span>
                            </a>
                        </template>
                    </div>
                    <p x-show="q.trim().length >= 3 && ! results.length" x-cloak class="mt-3 px-1 pb-1 text-sm" style="color: var(--st-ink-soft)">
                        {{ $theme['header_search_empty'] ?? 'No matches yet — press Enter to search everything.' }}
                    </p>
                    @if (count($popularSearchTerms))
                        <div x-show="!q.trim().length" class="mt-3 flex flex-wrap items-center gap-2 px-1 pb-1">
                            <span class="text-xs font-medium uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.popular_searches') }}</span>
                            @foreach ($popularSearchTerms as $term)
                                <a href="{{ route('storefront.search', ['q' => $term]) }}" class="rounded-full px-3 py-1 text-xs transition-colors hover:bg-black/5" style="border: 1px solid var(--st-line); color: var(--st-ink)">{{ $term }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Mobile nav drawer --}}
        <div x-show="mobileNav" x-cloak class="fixed inset-0 z-50 md:hidden" style="display:none">
            <div class="fixed inset-0 bg-black/40 backdrop-blur" @click="mobileNav = false"></div>
            <div class="fixed inset-y-0 start-0 flex w-72 max-w-[85vw] flex-col overflow-y-auto p-5" style="background: var(--st-bg); border-start-end-radius: var(--st-radius); border-end-end-radius: var(--st-radius)"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full" x-transition:enter-end="translate-x-0">
                <div class="mb-4 flex items-center justify-between">
                    <span class="stt-aurora-wordmark text-lg">{{ $storeName }}</span>
                    <button @click="mobileNav = false" class="stt-aurora-iconbtn text-2xl leading-none" style="margin-inline-end: -0.5rem" aria-label="{{ __('storefront.close') }}">&times;</button>
                </div>
                <nav class="space-y-1">
                    <a href="{{ url('/shop') }}" class="block rounded-lg px-3 py-2.5 text-sm font-semibold transition-colors hover:bg-black/5" style="color: var(--st-primary)">{{ __('storefront.shop') }}</a>
                    @foreach ($tree as $category)
                        <a href="{{ url('/category/' . $category->slug) }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors hover:bg-black/5" style="color: var(--st-ink)">{{ $category->name }}</a>
                    @endforeach
                </nav>
                <div class="mt-auto space-y-1 border-t pt-4" style="border-color: var(--st-line)">
                    <a href="{{ auth('customer')->check() ? route('storefront.account.index') : route('storefront.login') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors hover:bg-black/5" style="color: var(--st-ink)">{{ __('storefront.account') }}</a>
                    @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))
                    <a href="{{ route('storefront.wishlist') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors hover:bg-black/5" style="color: var(--st-ink)">{{ __('storefront.wishlist') }}</a>
                    @endif
                    @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))
                    <a href="{{ route('storefront.compare') }}" class="block rounded-lg px-3 py-2.5 text-sm font-medium transition-colors hover:bg-black/5" style="color: var(--st-ink)">{{ $theme['header_compare_label'] ?? 'Compare' }}</a>
                    @endif
                </div>
                {{-- Currency: desktop-only in the masthead, so mobile needs its own way in. --}}
                @if ($currency->hasMultiple())
                    <div class="mt-4 flex items-center gap-3 border-t pt-4" style="border-color: var(--st-line)">
                        <select onchange="if(this.value){window.location='{{ url('/currency') }}/'+this.value}" aria-label="{{ __('storefront.currency') }}"
                            class="h-9 cursor-pointer rounded-md border-0 bg-transparent px-2 text-sm font-medium focus:outline-none" style="color: var(--st-ink)">
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
        <div class="hidden border-t md:block" style="border-color: var(--st-line)">
            @include('theme::partials.header-nav', ['navClass' => 'st-container flex h-12 items-center justify-center gap-6'])
        </div>
    @endif
</header>
