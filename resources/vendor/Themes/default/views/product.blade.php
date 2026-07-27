@extends('theme::layout')

@section('title', $product->seo_title ?: $product->name)
@section('meta_description', $product->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($product->description), 150))
@if ($product->featuredImage())@section('og_image', url($product->featuredImage()->path))@endif

@section('content')
    {{-- Self-contained gradient buy CTA (mirrors the chrome's signature button). Token-driven,
         so under third-party theme layouts it degrades to THEIR primary→accent blend.
         Disabled / sold-out stays clearly flat and non-gradient. --}}
    <style>
        .stx-pdp-buy{
            background-image:linear-gradient(120deg,
                var(--st-primary) 0%,
                color-mix(in srgb, var(--st-primary) 55%, var(--st-accent)) 100%);
            color:var(--st-primary-ink);
            box-shadow:0 10px 22px -10px color-mix(in srgb, var(--st-primary) 55%, transparent);
            transition:transform .2s ease, box-shadow .2s ease;
        }
        .stx-pdp-buy:hover:not(:disabled){
            transform:translateY(-2px);
            box-shadow:0 14px 26px -10px color-mix(in srgb, var(--st-primary) 60%, transparent);
        }
        .stx-pdp-buy:focus-visible{
            outline:2px solid var(--st-primary); outline-offset:3px;
        }
        .stx-pdp-buy:disabled{
            background-image:none;
            background:color-mix(in srgb, var(--st-ink) 14%, var(--st-bg));
            color:var(--st-ink-soft);
            box-shadow:none;
        }
        @media (prefers-reduced-motion: reduce){
            .stx-pdp-buy{ transition:none; }
            .stx-pdp-buy:hover:not(:disabled){ transform:none; }
        }
    </style>

    @php
        $images = $product->media->pluck('path')->all();
        $first = $images[0] ?? null;
        $whatsapp = settings('general.whatsapp');
        $waNum = preg_replace('/[^0-9]/', '', (string) $whatsapp);
        $waUrl = url('/product/'.$product->slug);
        $variants = $product->variants->map(fn ($v) => [
            'id' => $v->id,
            'options' => (object) $v->options,
            'price' => format_money($v->price),
            'stock' => $v->stock_qty,
        ])->values();
        // No pre-selection: a variable product forces the shopper to choose every
        // option before add-to-cart / buy-now will fire.
        $initialSelected = (object) [];
        $currency = app(\Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService::class);
    @endphp

    <script>
        window.bzTrack && window.bzTrack('view_item', @js([
            'currency' => $currency->baseCode(),
            'value' => $currency->toBaseMajor($product->price),
            'items' => [['id' => $product->id, 'name' => $product->name, 'price' => $currency->toBaseMajor($product->price), 'quantity' => 1]],
        ]));
    </script>

    {{-- Alpine state is hoisted to this wrapper so the sticky mobile buy bar (far
         below, outside the detail grid) shares the selected variant + quantity.
         `current` resolves the variant matching the per-attribute selection. --}}
    <div x-data="{
            active: @js($first),
            images: @js($images),
            lightbox: false,
            variants: @js($variants),
            selected: @js($initialSelected),
            qty: 1,
            waNum: @js($waNum),
            waUrl: @js($waUrl),
            pname: @js($product->name),
            /* Owner toggle: when off, out-of-stock options never block ordering. */
            block: @js((bool) $product->track_inventory),
            optionCount: @js(count($optionGroups)),
            get current() {
                if (! this.variants.length) return null;
                /* Every option group must be chosen before a variant resolves. */
                if (Object.keys(this.selected).length < this.optionCount) return null;
                return this.variants.find(v => Object.keys(this.selected).every(k => String(v.options[k]) === String(this.selected[k]))) || null;
            },
            get variantId() { return this.current ? this.current.id : null; },
            /* A variable product with no complete variant choice yet. */
            get needsSelection() { return this.variants.length && ! this.current; },
            /* A fully-chosen variant that's out of stock while blocking is on. */
            get soldOut() { return !! this.current && this.block && this.current.stock <= 0; },
            get unavailable() { return this.needsSelection || this.soldOut; },
            /* Add to cart (or straight to checkout when buyNow). Prompts to choose
               options first for a variable product, and no-ops on a sold-out variant. */
            buy(buyNow, flyTarget) {
                if (this.needsSelection) { window.toast(@js(__('storefront.select_options_first')), 'danger'); return; }
                if (this.soldOut) return;
                if (flyTarget) window.flyToCart(flyTarget);
                window.Livewire.dispatch(buyNow ? 'cart-buy-now' : 'cart-add', { productId: {{ $product->id }}, variantId: this.variantId, qty: this.qty });
            },
            /* Is there any selectable variant for this option value, holding the shopper's
               other selections fixed? When stock blocking is on, an option with no in-stock
               combination is disabled; when off, every existing option stays selectable. */
            optionAvailable(name, value) {
                return this.variants.some(v =>
                    String(v.options[name]) === String(value) &&
                    (! this.block || v.stock > 0) &&
                    Object.keys(this.selected).every(k => k === name || String(v.options[k]) === String(this.selected[k]))
                );
            },
            /* wa.me link that carries the shopper's chosen variant + quantity so the
               merchant receives an actionable order message, not just a product name. */
            waBuy() {
                const opts = Object.entries(this.selected).map(([k, v]) => `${k}: ${v}`).join(', ');
                const label = opts ? ` (${opts})` : '';
                const msg = `Hi, I want to order: ${this.pname}${label} ×${this.qty} — ${this.waUrl}`;
                return `https://wa.me/${this.waNum}?text=${encodeURIComponent(msg)}`;
            },
        }">
    <div class="st-container py-8 sm:py-12">
        <nav class="mb-6 text-sm" style="color: var(--st-ink-soft)">
            <a href="{{ url('/shop') }}" class="hover:opacity-70">{{ __('storefront.shop') }}</a>
            <span class="mx-1.5">/</span>
            <span style="color: var(--st-ink)">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-10 lg:grid-cols-2 lg:gap-16">
            {{-- Gallery --}}
            <div class="st-reveal">
                <div class="relative overflow-hidden" style="border-radius: var(--st-radius); background: var(--st-surface)" x-ref="gallery">
                    <div class="aspect-square" style="view-transition-name: product-image">
                        @if ($first)
                            <img :src="active" alt="{{ $product->name }}" @click="lightbox = true" class="h-full w-full cursor-zoom-in object-cover">
                            {{-- Zoom affordance --}}
                            <button type="button" @click="lightbox = true" aria-label="{{ __('storefront.zoom_image') }}"
                                class="absolute bottom-3 right-3 grid h-9 w-9 place-items-center backdrop-blur"
                                style="background: color-mix(in srgb, var(--st-bg) 80%, transparent); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" /></svg>
                            </button>
                        @else
                            <div class="grid h-full w-full place-items-center" style="color: var(--st-line)">{{ __('storefront.no_image') }}</div>
                        @endif
                    </div>
                </div>
                @if (count($images) > 1)
                    <div class="mt-4 flex gap-3 overflow-x-auto pb-1">
                        @foreach ($images as $img)
                            <button type="button" @click="active = @js($img)" class="h-20 w-20 shrink-0 overflow-hidden border-2 transition"
                                :class="active === @js($img) ? '' : 'opacity-60'"
                                :style="active === @js($img) ? 'border-color: var(--st-ink); border-radius: var(--st-radius-sm)' : 'border-color: transparent; border-radius: var(--st-radius-sm)'">
                                <img src="{{ $img }}" alt="" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div class="st-reveal">
                <h1 class="st-display text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">{{ $product->name }}</h1>

                @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('reviews') && settings('catalog.reviews_enabled', true) && $product->reviews_count > 0)
                    <a href="#reviews" class="mt-2 inline-flex">
                        <x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" />
                    </a>
                @endif

                <div class="mt-4">
                    @if ($optionGroups)
                        <span class="text-2xl font-semibold" style="color: var(--st-ink)" x-text="current ? current.price : @js(format_money($product->price))"></span>
                    @else
                        <x-st.price :price="$product->price" :compare-at="$product->compare_at_price" size="lg" />
                    @endif
                </div>

                {{-- Stock / urgency --}}
                @if ($optionGroups)
                    {{-- Reflects the selected variant's stock. --}}
                    <div class="mt-3 text-sm font-medium" x-show="current && block" x-cloak>
                        <template x-if="current && current.stock <= 0"><span style="color: var(--st-accent)">● {{ __('storefront.sold_out') }}</span></template>
                        <template x-if="current && current.stock > 0 && current.stock <= 10"><span style="color: var(--st-accent)">● {{ __('storefront.only') }} <span x-text="current.stock"></span> {{ __('storefront.left_order_soon') }}</span></template>
                        <template x-if="current && current.stock > 10"><span style="color: var(--st-success)">● {{ __('storefront.in_stock') }}</span></template>
                    </div>
                @elseif ($product->track_inventory)
                    <div class="mt-3 text-sm font-medium">
                        @if ($product->stock_qty <= 0)
                            <span style="color: var(--st-accent)">● {{ __('storefront.sold_out') }}</span>
                        @elseif ($product->stock_qty <= 10)
                            <span style="color: var(--st-accent)">● {{ __('storefront.only') }} {{ $product->stock_qty }} {{ __('storefront.left_order_soon') }}</span>
                        @else
                            <span style="color: var(--st-success)">● {{ __('storefront.in_stock') }}</span>
                        @endif
                    </div>
                @endif

                {{-- Per-attribute selectors (Size / Color …) with colour swatches. --}}
                @if ($optionGroups)
                    <div class="mt-6 space-y-4">
                        @foreach ($optionGroups as $group)
                            <div>
                                <p class="mb-2 text-sm font-medium" style="color: var(--st-ink)">
                                    {{ $group['name'] }}
                                    @if ($group['is_color'])<span class="font-normal" style="color: var(--st-ink-soft)"> · <span x-text="selected[@js($group['name'])]"></span></span>@endif
                                </p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($group['values'] as $val)
                                        @if ($group['is_color'] && $val['color'])
                                            <button type="button" @click="selected[@js($group['name'])] = @js($val['value'])"
                                                :disabled="! optionAvailable(@js($group['name']), @js($val['value']))"
                                                class="relative h-9 w-9 overflow-hidden rounded-full border-2 transition disabled:cursor-not-allowed"
                                                :class="! optionAvailable(@js($group['name']), @js($val['value'])) && 'opacity-40'"
                                                style="background: {{ $val['color'] }}"
                                                :style="'background: {{ $val['color'] }}; ' + (selected[@js($group['name'])] === @js($val['value']) ? 'border-color: var(--st-ink)' : 'border-color: var(--st-line)')"
                                                :aria-pressed="selected[@js($group['name'])] === @js($val['value'])"
                                                title="{{ $val['value'] }}" aria-label="{{ $group['name'] }}: {{ $val['value'] }}">
                                                {{-- Diagonal strike marks a colour with no in-stock size/combination. --}}
                                                <span x-show="! optionAvailable(@js($group['name']), @js($val['value']))" x-cloak aria-hidden="true"
                                                    class="pointer-events-none absolute inset-0" style="background: linear-gradient(to top right, transparent calc(50% - 1px), var(--st-ink) 50%, transparent calc(50% + 1px))"></span>
                                            </button>
                                        @else
                                            <button type="button" @click="selected[@js($group['name'])] = @js($val['value'])"
                                                :disabled="! optionAvailable(@js($group['name']), @js($val['value']))"
                                                class="border px-4 py-2 text-sm font-medium transition disabled:cursor-not-allowed"
                                                :class="! optionAvailable(@js($group['name']), @js($val['value'])) && 'line-through opacity-40'"
                                                :style="selected[@js($group['name'])] === @js($val['value'])
                                                    ? 'border-color: var(--st-ink); background: var(--st-ink); color: var(--st-bg); border-radius: var(--st-radius-sm)'
                                                    : 'border-color: var(--st-line); color: var(--st-ink); border-radius: var(--st-radius-sm)'">{{ $val['value'] }}</button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        <p x-show="needsSelection" x-cloak class="text-sm" style="color: var(--st-accent)">{{ __('storefront.select_options_first') }}</p>
                    </div>
                @endif

                {{-- Size chart (renders nothing when the category tree has none) --}}
                <x-st.size-chart :product="$product" class="mt-5" />

                {{-- Quantity + add to cart --}}
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <div class="inline-flex items-center border" style="border-color: var(--st-line); border-radius: var(--st-radius-sm)">
                        <button type="button" @click="qty = Math.max(1, qty - 1)" aria-label="{{ __('storefront.decrease_quantity') }}" class="grid h-12 w-12 place-items-center text-lg" style="color: var(--st-ink)">&minus;</button>
                        <span class="w-10 text-center text-sm font-medium" style="color: var(--st-ink)" x-text="qty"></span>
                        <button type="button" @click="qty++" aria-label="{{ __('storefront.increase_quantity') }}" class="grid h-12 w-12 place-items-center text-lg" style="color: var(--st-ink)">+</button>
                    </div>

                    <div class="flex flex-1 items-center gap-3">
                        <button type="button"
                            :disabled="soldOut"
                            :class="soldOut && 'opacity-50 cursor-not-allowed'"
                            @click="buy(false, $refs.gallery)"
                            class="stx-pdp-buy flex-1 px-8 py-3.5 text-sm font-semibold"
                            style="border-radius: var(--st-radius-sm)">
                            {{ __('storefront.add_to_cart') }}
                        </button>

                        <x-st.wishlist-heart :product-id="$product->id" class="h-12 w-12 shrink-0" />
                    </div>
                </div>

                {{-- Buy it now → adds and jumps straight to checkout. --}}
                <button type="button"
                    :disabled="soldOut"
                    :class="soldOut && 'opacity-50 cursor-not-allowed'"
                    @click="buy(true, $refs.gallery)"
                    class="mt-3 w-full border px-8 py-3.5 text-sm font-semibold transition hover:opacity-90"
                    style="border-color: var(--st-ink); background: var(--st-ink); color: var(--st-bg); border-radius: var(--st-radius-sm)">
                    {{ __('storefront.buy_it_now') }}
                </button>

                @if ($whatsapp)
                    @if (settings('general.whatsapp_buy_enabled', true))
                    <a :href="waBuy()" target="_blank" rel="noopener"
                        class="mt-3 flex items-center justify-center gap-2 border px-5 py-2.5 text-xs font-semibold"
                        style="border-color: var(--st-line); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" style="color: #25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c0-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        {{ __('storefront.buy_via_whatsapp') }}
                    </a>
                    @endif

                    <div class="mt-2 flex gap-2 text-sm">
                        @if (settings('general.whatsapp_inquiry', true))
                            <a href="https://wa.me/{{ $waNum }}?text={{ urlencode('Hi, I have a question about ' . $product->name . ' — ' . $waUrl) }}"
                                class="flex flex-1 items-center justify-center gap-1.5 border px-3 py-2.5 font-medium"
                                style="border-color: var(--st-line); color: var(--st-ink-soft); border-radius: var(--st-radius-sm)">{{ __('storefront.ask_question') }}</a>
                        @endif
                        @if (settings('general.whatsapp_share', true))
                            <button type="button" @click="stShare(@js($product->name), @js($waUrl), @js(__('storefront.link_copied')))"
                                class="flex flex-1 items-center justify-center gap-1.5 border px-3 py-2.5 font-medium"
                                style="border-color: var(--st-line); color: var(--st-ink-soft); border-radius: var(--st-radius-sm)">{{ __('storefront.share') }}</button>
                        @endif
                    </div>
                @endif

                {{-- Trust row --}}
                <div class="mt-8 grid grid-cols-3 gap-3 border-t pt-6 text-center" style="border-color: var(--st-line)">
                    @foreach ([__('storefront.secure_checkout'), __('storefront.fast_delivery'), __('storefront.easy_returns')] as $t)
                        <div class="text-xs font-medium" style="color: var(--st-ink-soft)">{{ $t }}</div>
                    @endforeach
                </div>

                {{-- Description / Shipping tabs --}}
                @php
                    $shippingInfo = settings('general.shipping_info');
                    $returnsInfo = settings('general.returns_info');
                    $shipText = trim(($shippingInfo ?: __('storefront.default_shipping_info'))."\n".($returnsInfo ?: __('storefront.default_returns_info')));
                @endphp
                <div class="mt-8 border-t pt-6" style="border-color: var(--st-line)" x-data="{ tab: 'desc' }">
                    <div class="flex gap-6 border-b" style="border-color: var(--st-line)">
                        <button type="button" @click="tab = 'desc'" class="pb-2 text-sm font-medium transition"
                            :style="tab === 'desc' ? 'color: var(--st-ink); border-bottom: 2px solid var(--st-ink)' : 'color: var(--st-ink-soft)'">{{ __('storefront.description') }}</button>
                        <button type="button" @click="tab = 'ship'" class="pb-2 text-sm font-medium transition"
                            :style="tab === 'ship' ? 'color: var(--st-ink); border-bottom: 2px solid var(--st-ink)' : 'color: var(--st-ink-soft)'">{{ __('storefront.shipping_returns_tab') }}</button>
                    </div>
                    <div class="pt-4 text-sm leading-relaxed" style="color: var(--st-ink)">
                        <div x-show="tab === 'desc'">
                            @if ($product->description){!! nl2br(e($product->description)) !!}@else<span style="color: var(--st-ink-soft)">{{ __('storefront.no_description') }}</span>@endif
                        </div>
                        <div x-show="tab === 'ship'" x-cloak>{!! nl2br(e($shipText)) !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Frequently bought together --}}
    @php $fbt = app(\Themicly\Shopcrafty\Modules\Marketing\Services\BoughtTogether::class)->forProduct($product->id, 4); @endphp
    @if ($fbt->isNotEmpty())
        <section class="st-container pb-16">
            <div class="mb-8">
                <x-st.section-heading :eyebrow="__('storefront.complete_the_set')" :title="__('storefront.frequently_bought_together')" />
                <span aria-hidden="true" style="display: block; width: 3rem; height: 3px; margin-top: 0.875rem; border-radius: 999px; background: linear-gradient(90deg, var(--st-primary), var(--st-accent))"></span>
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4">
                @foreach ($fbt as $p)
                    <x-st.product-card :product="$p" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Reviews --}}
    @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('reviews') && settings('catalog.reviews_enabled', true))
        <div id="reviews">
            <livewire:catalog.product-reviews :product-id="$product->id" />
        </div>
    @endif

    {{-- Recently viewed --}}
    @if (isset($recentlyViewed) && $recentlyViewed->isNotEmpty())
        <section class="st-container pb-16">
            <div class="mb-8">
                <x-st.section-heading :eyebrow="__('storefront.keep_exploring')" :title="__('storefront.recently_viewed')" />
                <span aria-hidden="true" style="display: block; width: 3rem; height: 3px; margin-top: 0.875rem; border-radius: 999px; background: linear-gradient(90deg, var(--st-primary), var(--st-accent))"></span>
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4">
                @foreach ($recentlyViewed as $p)
                    <x-st.product-card :product="$p" wire:key="rv-{{ $p->id }}" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Fullscreen image lightbox --}}
    @if ($first)
        <div x-show="lightbox" x-cloak x-transition.opacity @keydown.escape.window="lightbox = false"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4" @click.self="lightbox = false">
            <button type="button" @click="lightbox = false" class="absolute right-4 top-4 text-3xl text-white/80 hover:text-white" aria-label="{{ __('storefront.close') }}">&times;</button>
            <img :src="active" alt="{{ $product->name }}" class="max-h-[85vh] max-w-full object-contain">
            <template x-if="images.length > 1">
                <div class="absolute inset-x-0 bottom-4 flex justify-center gap-2 px-4">
                    <template x-for="(img, i) in images" :key="i">
                        <button type="button" @click="active = img" class="h-14 w-14 overflow-hidden rounded border-2 transition"
                            :style="active === img ? 'border-color: #fff' : 'border-color: transparent; opacity: .6'">
                            <img :src="img" alt="" class="h-full w-full object-cover">
                        </button>
                    </template>
                </div>
            </template>
        </div>
    @endif

    </div>
@endsection
