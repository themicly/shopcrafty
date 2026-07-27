@php
    $ctrl = 'w-full border px-3 py-2 text-sm outline-none';
    $ctrlStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
    $countStyle = 'color: var(--st-ink-soft)';
@endphp

<div x-data="{ filtersOpen: false }">
    @once
        <style>
            .st-range-track{position:absolute;top:50%;left:0;right:0;height:4px;transform:translateY(-50%);border-radius:999px;background:var(--st-line);}
            .st-range-fill{position:absolute;top:50%;height:4px;transform:translateY(-50%);border-radius:999px;background:var(--st-ink);}
            .st-range-input{position:absolute;top:0;left:0;width:100%;height:100%;margin:0;background:transparent;-webkit-appearance:none;appearance:none;pointer-events:none;}
            .st-range-input::-webkit-slider-thumb{-webkit-appearance:none;appearance:none;pointer-events:auto;height:16px;width:16px;border-radius:50%;background:var(--st-bg);border:2px solid var(--st-ink);cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,.2);}
            .st-range-input::-moz-range-thumb{pointer-events:auto;height:16px;width:16px;border-radius:50%;background:var(--st-bg);border:2px solid var(--st-ink);cursor:pointer;}
            .st-range-input:focus-visible::-webkit-slider-thumb{outline:2px solid var(--st-ink);outline-offset:2px;}
        </style>
    @endonce
    {{-- Toolbar: result count + mobile filter trigger + sort --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.results_count', ['count' => $products->total()]) }}</p>
        <div class="flex items-center gap-2">
            <button type="button" @click="filtersOpen = true"
                class="inline-flex items-center gap-1.5 border px-4 py-2.5 text-sm font-medium lg:hidden"
                style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius)">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z" /></svg>
                {{ __('storefront.filters') }}
            </button>
            <select wire:model.live="sort" aria-label="{{ __('storefront.sort') }}" class="border px-4 py-2.5 text-sm" style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius)">
                <option value="latest">{{ __('storefront.sort_newest') }}</option>
                <option value="best_selling">{{ __('storefront.sort_best_selling') }}</option>
                @if ($reviewsEnabled)<option value="top_rated">{{ __('storefront.sort_rating') }}</option>@endif
                <option value="price_asc">{{ __('storefront.sort_price_low') }}</option>
                <option value="price_desc">{{ __('storefront.sort_price_high') }}</option>
            </select>
        </div>
    </div>

    {{-- Active filter chips --}}
    @if (! empty($activeChips))
        <div class="mb-5 flex flex-wrap items-center gap-2">
            @foreach ($activeChips as $chip)
                <button type="button"
                    @if ($chip['type'] === 'category') wire:click="removeCategory({{ $chip['id'] }})"
                    @elseif ($chip['type'] === 'brand') wire:click="removeBrand({{ $chip['id'] }})"
                    @else wire:click="clearPrice" @endif
                    class="inline-flex items-center gap-1.5 border px-3 py-1 text-xs font-medium"
                    style="border-color: var(--st-line); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                    {{ $chip['label'] }} <span aria-hidden="true">&times;</span>
                </button>
            @endforeach
            <button type="button" wire:click="clearFilters" class="text-xs font-medium underline" style="color: var(--st-ink-soft)">{{ __('storefront.clear_all') }}</button>
        </div>
    @endif

    <div class="flex flex-col gap-8 lg:flex-row">
        {{-- Filters --}}
        <aside class="lg:w-60 lg:shrink-0">
            <div x-show="filtersOpen" x-cloak x-transition.opacity @click="filtersOpen = false" class="fixed inset-0 z-40 bg-black/40 lg:hidden"></div>

            <div :class="filtersOpen ? 'fixed inset-y-0 left-0 z-50 w-80 max-w-[85vw] overflow-y-auto p-5 shadow-xl' : 'hidden lg:block'"
                style="background: var(--st-bg)" @keydown.escape.window="filtersOpen = false">
                <div class="mb-4 flex items-center justify-between lg:hidden">
                    <span class="st-display text-lg font-semibold" style="color: var(--st-ink)">{{ __('storefront.filters') }}</span>
                    <button type="button" @click="filtersOpen = false" class="text-2xl leading-none" style="color: var(--st-ink-soft)" aria-label="{{ __('storefront.close_filters') }}">&times;</button>
                </div>

                <div class="space-y-6">
                    @if ($categories->isNotEmpty())
                        <div>
                            <h3 class="mb-2 text-sm font-semibold" style="color: var(--st-ink)">{{ __('storefront.category') }}</h3>
                            <div class="space-y-1.5">
                                @foreach ($categories as $cat)
                                    <label class="flex items-center justify-between gap-2 text-sm" style="color: var(--st-ink-soft)">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" wire:model.live="categoryFilter" value="{{ $cat->id }}">
                                            {{ $cat->name }}
                                        </span>
                                        <span class="text-xs" style="{{ $countStyle }}">{{ $categoryCounts[$cat->id] ?? 0 }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($brands->isNotEmpty())
                        <div>
                            <h3 class="mb-2 text-sm font-semibold" style="color: var(--st-ink)">{{ __('storefront.brand') }}</h3>
                            <div class="space-y-1.5">
                                @foreach ($brands as $brand)
                                    <label class="flex items-center justify-between gap-2 text-sm" style="color: var(--st-ink-soft)">
                                        <span class="flex items-center gap-2">
                                            <input type="checkbox" wire:model.live="brandFilter" value="{{ $brand->id }}">
                                            {{ $brand->name }}
                                        </span>
                                        <span class="text-xs" style="{{ $countStyle }}">{{ $brandCounts[$brand->id] ?? 0 }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Attribute facets (colour swatches + pills) --}}
                    @foreach ($optionFacets as $group)
                        <div wire:key="facet-{{ $group['name'] }}">
                            <h3 class="mb-2 text-sm font-semibold" style="color: var(--st-ink)">{{ $group['name'] }}</h3>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($group['values'] as $val)
                                    @php $on = $this->optionChecked($group['name'], $val['value']); @endphp
                                    @if ($group['is_color'] && $val['color'])
                                        <button type="button" wire:click="toggleOption(@js($group['name']), @js($val['value']))"
                                            title="{{ $val['value'] }}" aria-pressed="{{ $on ? 'true' : 'false' }}"
                                            class="block h-8 w-8 rounded-full border-2 transition"
                                            style="background: {{ $val['color'] }}; border-color: {{ $on ? 'var(--st-ink)' : 'var(--st-line)' }};{{ $on ? ' box-shadow: 0 0 0 2px var(--st-ink);' : '' }}">
                                            <span class="sr-only">{{ $val['value'] }}</span>
                                        </button>
                                    @else
                                        <button type="button" wire:click="toggleOption(@js($group['name']), @js($val['value']))"
                                            aria-pressed="{{ $on ? 'true' : 'false' }}"
                                            class="inline-block border px-3 py-1.5 text-sm transition"
                                            style="border-radius: var(--st-radius-sm); {{ $on ? 'background: var(--st-ink); color: var(--st-bg); border-color: var(--st-ink)' : 'border-color: var(--st-line); color: var(--st-ink)' }}">{{ $val['value'] }}</button>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    {{-- Price: dual-thumb range slider. Alpine drives the thumbs live;
                         Livewire's min/max only commit on release, so results refetch once. --}}
                    <div wire:key="price-{{ $priceFloor }}-{{ $priceCeil }}"
                        x-data="{
                            floor: {{ $priceFloor }}, ceil: {{ $priceCeil }}, sym: @js(settings('localization.currency_symbol', '$')),
                            lo: {{ (int) ($min !== null && $min !== '' ? max($priceFloor, (int) $min) : $priceFloor) }},
                            hi: {{ (int) ($max !== null && $max !== '' ? min($priceCeil, (int) $max) : $priceCeil) }},
                            fmt(v){ return this.sym + Number(v).toLocaleString(); },
                            pct(v){ return (v - this.floor) / (this.ceil - this.floor) * 100; },
                            commit(){
                                $wire.set('min', this.lo <= this.floor ? '' : String(this.lo));
                                $wire.set('max', this.hi >= this.ceil ? '' : String(this.hi));
                            }
                        }">
                        <div class="mb-3 flex items-center justify-between text-sm font-semibold" style="color: var(--st-ink)">
                            <span>{{ __('storefront.price') }}</span>
                            <span class="text-xs font-medium" style="color: var(--st-ink-soft)"><span x-text="fmt(lo)"></span> – <span x-text="fmt(hi)"></span></span>
                        </div>
                        <div class="st-range relative h-5">
                            <div class="st-range-track"></div>
                            <div class="st-range-fill" :style="`left:${pct(lo)}%; right:${100 - pct(hi)}%`"></div>
                            <input type="range" class="st-range-input" :min="floor" :max="ceil" x-model.number="lo"
                                @input="if (lo > hi) lo = hi" @change="commit()" aria-label="{{ __('storefront.min_price') }}">
                            <input type="range" class="st-range-input" :min="floor" :max="ceil" x-model.number="hi"
                                @input="if (hi < lo) hi = lo" @change="commit()" aria-label="{{ __('storefront.max_price') }}">
                        </div>
                    </div>

                    {{-- Minimum rating --}}
                    @if ($reviewsEnabled)
                        <div>
                            <h3 class="mb-2 text-sm font-semibold" style="color: var(--st-ink)">{{ __('storefront.rating') }}</h3>
                            <div class="space-y-1.5">
                                @foreach (['4' => __('storefront.rating_and_up', ['stars' => 4]), '3' => __('storefront.rating_and_up', ['stars' => 3]), '2' => __('storefront.rating_and_up', ['stars' => 2])] as $value => $label)
                                    <label class="flex items-center gap-2 text-sm" style="color: var(--st-ink-soft)">
                                        <input type="radio" wire:model.live="minRating" value="{{ $value }}">
                                        {{ $label }}
                                    </label>
                                @endforeach
                                <label class="flex items-center gap-2 text-sm" style="color: var(--st-ink-soft)">
                                    <input type="radio" wire:model.live="minRating" value="">
                                    {{ __('storefront.any_rating') }}
                                </label>
                            </div>
                        </div>
                    @endif

                    <label class="flex items-center gap-2 text-sm" style="color: var(--st-ink-soft)">
                        <input type="checkbox" wire:model.live="inStock">
                        {{ __('storefront.in_stock_only') }}
                    </label>

                    <button type="button" wire:click="clearFilters" class="text-sm font-medium" style="color: var(--st-ink-soft)">{{ __('storefront.clear_filters') }}</button>
                </div>
            </div>
        </aside>

        {{-- Results --}}
        <div class="min-w-0 flex-1">
            <div wire:loading.class="opacity-50" class="transition-opacity">
                @if ($products->isEmpty())
                    <div class="py-12">
                        <div class="text-center">
                            <p class="st-display text-2xl" style="color: var(--st-ink)">{{ __('storefront.no_exact_matches') }}</p>
                            <p class="mt-2 text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.try_widening_filters') }}</p>
                            @if ($hasFilters)
                                <button type="button" wire:click="clearFilters"
                                    class="mt-4 inline-flex items-center gap-1.5 border px-4 py-2.5 text-sm font-medium"
                                    style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius)">
                                    {{ __('storefront.clear_all_filters') }}
                                </button>
                            @endif
                        </div>

                        @if ($suggestions)
                            @if ($suggestions['relaxed']->isNotEmpty())
                                <div class="mt-6 flex flex-wrap items-center justify-center gap-2">
                                    <span class="text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.did_you_mean') }}</span>
                                    @foreach ($suggestions['relaxed'] as $s)
                                        <a href="{{ url('/product/'.$s->slug) }}" class="border px-3 py-1 text-xs font-medium" style="border-color: var(--st-line); color: var(--st-ink); border-radius: var(--st-radius-sm)">{{ $s->name }}</a>
                                    @endforeach
                                </div>
                            @endif

                            @php $picks = $suggestions['relaxed']->isNotEmpty() ? $suggestions['relaxed'] : $suggestions['popular']; @endphp
                            @if ($picks->isNotEmpty())
                                <div class="mt-10">
                                    <h3 class="mb-6 text-center text-sm font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">
                                        {{ $suggestions['relaxed']->isNotEmpty() ? __('storefront.you_might_like') : __('storefront.popular_right_now') }}
                                    </h3>
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4">
                                        @foreach ($picks as $p)
                                            <x-st.product-card :product="$p" wire:key="sugg-{{ $p->id }}" />
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3">
                        @foreach ($products as $product)
                            <x-st.product-card :product="$product" wire:key="p-{{ $product->id }}" />
                        @endforeach
                    </div>
                    <div class="mt-12">{{ $products->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@script
<script>
    // app.js re-runs its scroll-reveal on a `livewire:updated` event that Livewire
    // v3/v4 never dispatches, so product cards morphed in by filtering, sorting or
    // pagination kept .st-reveal's opacity:0 and the grid looked empty. Reveal
    // morphed-in elements immediately — in-place grid swaps should feel instant,
    // and the .6s opacity transition still gives them a soft entrance.
    if (! window.__stRevealMorphHook) {
        window.__stRevealMorphHook = true;
        const show = (el) => {
            if (! el || el.nodeType !== 1) return;
            if (el.classList.contains('st-reveal')) el.classList.add('is-in');
            el.querySelectorAll('.st-reveal:not(.is-in)').forEach((n) => n.classList.add('is-in'));
        };
        Livewire.hook('morph.added', ({ el }) => show(el));
        Livewire.hook('morph.updated', ({ el }) => show(el));
    }
</script>
@endscript
