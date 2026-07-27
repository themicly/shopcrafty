@extends('theme::layout')

@section('title', $product->seo_title ?: $product->name)
@section('meta_description', $product->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($product->description), 150))
@if ($product->featuredImage())@section('og_image', url($product->featuredImage()->path))@endif

@section('content')
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
        // No pre-selection: a variable product forces the shopper to choose every option.
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
                if (Object.keys(this.selected).length < this.optionCount) return null;
                return this.variants.find(v => Object.keys(this.selected).every(k => String(v.options[k]) === String(this.selected[k]))) || null;
            },
            get variantId() { return this.current ? this.current.id : null; },
            get needsSelection() { return this.variants.length && ! this.current; },
            get soldOut() { return !! this.current && this.block && this.current.stock <= 0; },
            buy(buyNow, flyTarget) {
                if (this.needsSelection) { window.toast(@js(__('storefront.select_options_first')), 'danger'); return; }
                if (this.soldOut) return;
                if (flyTarget) window.flyToCart(flyTarget);
                window.Livewire.dispatch(buyNow ? 'cart-buy-now' : 'cart-add', { productId: {{ $product->id }}, variantId: this.variantId, qty: this.qty });
            },
            get unavailable() { return this.needsSelection || this.soldOut; },
            /* Is there any in-stock variant with this option value, holding the shopper's
               other selections fixed? Drives the disabling of out-of-stock swatches/sizes
               so a combination with no unit available can't be selected. */
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
    <div class="stt-boutique-section" style="padding-block: 3.5rem;">
    <div class="st-container stt-boutique-narrow">
        {{-- Editorial breadcrumb --}}
        <nav class="stt-boutique-crumb" style="margin-bottom: 3rem">
            <a href="{{ url('/shop') }}">{{ __('storefront.shop') }}</a>
            <span style="margin-inline: 0.5rem; color: var(--st-line)">/</span>
            <span style="color: var(--st-ink)">{{ $product->name }}</span>
        </nav>

        <div class="grid gap-12 lg:grid-cols-2 lg:gap-20">
            {{-- Gallery — frameless image on a sharp surface block; pins below the
                 sticky header on desktop while the long details column scrolls. --}}
            <div class="st-reveal stt-boutique-pdp-sticky">
                <div class="stt-boutique-hero-media" style="aspect-ratio: 3 / 4;" x-ref="gallery">
                    <div class="h-full w-full" style="view-transition-name: product-image">
                        @if ($first)
                            <img :src="active" alt="{{ $product->name }}" @click="lightbox = true" class="h-full w-full cursor-zoom-in object-cover">
                            {{-- Zoom affordance --}}
                            <button type="button" @click="lightbox = true" aria-label="{{ __('storefront.zoom_image') }}"
                                class="absolute bottom-4 right-4 grid h-9 w-9 place-items-center"
                                style="background: color-mix(in srgb, var(--st-bg) 78%, transparent); color: var(--st-ink); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607ZM10.5 7.5v6m3-3h-6" /></svg>
                            </button>
                        @else
                            <div class="grid h-full w-full place-items-center stt-boutique-label" style="color: var(--st-ink-soft)">{{ __('storefront.no_image') }}</div>
                        @endif
                    </div>
                </div>
                @if (count($images) > 1)
                    {{-- Square hairline-bordered thumbnails; active = solid ink hairline --}}
                    <div class="mt-4 flex gap-3 overflow-x-auto pb-1">
                        @foreach ($images as $img)
                            <button type="button" @click="active = @js($img)" class="h-20 w-20 shrink-0 overflow-hidden transition"
                                :style="active === @js($img)
                                    ? 'border: 1px solid var(--st-ink); border-radius: var(--st-radius)'
                                    : 'border: 1px solid var(--st-line); border-radius: var(--st-radius); opacity: .7'">
                                <img src="{{ $img }}" alt="" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Details — editorial info column with generous top offset --}}
            <div class="st-reveal stt-boutique-pdp-info">
                {{-- Category / eyebrow --}}
                <p class="stt-boutique-eyebrow mb-5">{{ $product->category?->name ?: 'The Atelier' }}</p>

                <h1 class="stt-boutique-display" style="font-size: clamp(2.25rem, 4vw, 3rem)">{{ $product->name }}</h1>

                @if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('reviews') && settings('catalog.reviews_enabled', true) && $product->reviews_count > 0)
                    <a href="#reviews" class="mt-4 inline-flex">
                        <x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" />
                    </a>
                @endif

                <hr class="stt-boutique-rule mt-7">

                {{-- Price, set in the serif --}}
                <div class="mt-7">
                    @if ($optionGroups)
                        <span style="font-family: var(--st-font-display); font-size: 1.5rem; font-weight: 400; color: var(--st-ink)" x-text="current ? current.price : @js(format_money($product->price))"></span>
                    @else
                        <x-st.price :price="$product->price" :compare-at="$product->compare_at_price" size="lg" />
                    @endif
                </div>

                {{-- Stock — text-only, no urgency pills --}}
                @if ($optionGroups)
                    {{-- Reflects the selected variant's stock. --}}
                    <div class="mt-4" x-show="current && block" x-cloak>
                        <template x-if="current && current.stock <= 0"><span class="stt-boutique-badge stt-boutique-badge--sold">{{ __('storefront.sold_out') }}</span></template>
                        <template x-if="current && current.stock > 0 && current.stock <= 10"><span class="stt-boutique-badge stt-boutique-badge--sale">Only <span x-text="current.stock"></span> remaining</span></template>
                        <template x-if="current && current.stock > 10"><span class="stt-boutique-badge" style="color: var(--st-ink-soft)">{{ __('storefront.in_stock') }}</span></template>
                    </div>
                @elseif ($product->track_inventory)
                    <div class="mt-4">
                        @if ($product->stock_qty <= 0)
                            <span class="stt-boutique-badge stt-boutique-badge--sold">{{ __('storefront.sold_out') }}</span>
                        @elseif ($product->stock_qty <= 10)
                            <span class="stt-boutique-badge stt-boutique-badge--sale">{{ __('storefront.only_count_remaining', ['count' => $product->stock_qty]) }}</span>
                        @else
                            <span class="stt-boutique-badge" style="color: var(--st-ink-soft)">{{ __('storefront.in_stock') }}</span>
                        @endif
                    </div>
                @endif

                {{-- Per-attribute selectors (Size / Color …) — sharp hairline chips. --}}
                @if ($optionGroups)
                    <div class="mt-9 space-y-6">
                        @foreach ($optionGroups as $group)
                            <div>
                                <p class="stt-boutique-label mb-3">
                                    {{ $group['name'] }}
                                    @if ($group['is_color'])<span style="color: var(--st-ink-soft)"> — <span x-text="selected[@js($group['name'])]"></span></span>@endif
                                </p>
                                <div class="flex flex-wrap gap-2.5">
                                    @foreach ($group['values'] as $val)
                                        @if ($group['is_color'] && $val['color'])
                                            <button type="button" @click="selected[@js($group['name'])] = @js($val['value'])"
                                                :disabled="! optionAvailable(@js($group['name']), @js($val['value']))"
                                                class="h-10 w-10 transition disabled:cursor-not-allowed"
                                                :class="! optionAvailable(@js($group['name']), @js($val['value'])) && 'opacity-40'"
                                                style="background: {{ $val['color'] }}; border-radius: var(--st-radius)"
                                                :style="'background: {{ $val['color'] }}; border-radius: var(--st-radius); ' + (selected[@js($group['name'])] === @js($val['value']) ? 'box-shadow: 0 0 0 1px var(--st-bg), 0 0 0 2px var(--st-ink)' : 'box-shadow: 0 0 0 1px var(--st-line)')"
                                                :aria-pressed="selected[@js($group['name'])] === @js($val['value'])"
                                                title="{{ $val['value'] }}" aria-label="{{ $group['name'] }}: {{ $val['value'] }}"></button>
                                        @else
                                            <button type="button" @click="selected[@js($group['name'])] = @js($val['value'])"
                                                :disabled="! optionAvailable(@js($group['name']), @js($val['value']))"
                                                class="stt-boutique-chip disabled:cursor-not-allowed"
                                                :class="[selected[@js($group['name'])] === @js($val['value']) ? 'stt-boutique-chip--active' : '', ! optionAvailable(@js($group['name']), @js($val['value'])) && 'line-through opacity-40']">{{ $val['value'] }}</button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        <p x-show="needsSelection" x-cloak class="stt-boutique-badge stt-boutique-badge--sale">{{ __('storefront.select_options_first') }}</p>
                    </div>
                @endif

                {{-- Size chart (renders nothing when the category tree has none) --}}
                <x-st.size-chart :product="$product" class="mt-6" />

                {{-- Quantity + add to bag --}}
                <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                    <div class="inline-flex items-center" style="border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                        <button type="button" @click="qty = Math.max(1, qty - 1)" aria-label="{{ __('storefront.decrease_quantity') }}" class="grid h-14 w-12 place-items-center text-lg" style="color: var(--st-ink)">&minus;</button>
                        <span class="w-10 text-center stt-boutique-label" style="letter-spacing: 0.1em" x-text="qty"></span>
                        <button type="button" @click="qty++" aria-label="{{ __('storefront.increase_quantity') }}" class="grid h-14 w-12 place-items-center text-lg" style="color: var(--st-ink)">+</button>
                    </div>

                    <div class="flex flex-1 items-center gap-3">
                        <button type="button"
                            :disabled="soldOut"
                            :class="soldOut && 'opacity-50 cursor-not-allowed'"
                            @click="buy(false, $refs.gallery)"
                            class="stt-boutique-btn flex-1">
                            {{ __('storefront.add_to_bag') }}
                        </button>

                        <x-st.wishlist-heart :product-id="$product->id" class="h-14 w-14 shrink-0" />
                    </div>
                </div>

                {{-- Buy it now → adds and jumps straight to checkout. --}}
                <button type="button"
                    :disabled="soldOut"
                    :class="soldOut && 'opacity-50 cursor-not-allowed'"
                    @click="buy(true, $refs.gallery)"
                    class="stt-boutique-btn mt-3 w-full"
                    style="background: var(--st-accent); border-color: var(--st-accent); color: #fff">
                    {{ __('storefront.buy_it_now') }}
                </button>

                @if ($whatsapp)
                    @if (settings('general.whatsapp_buy_enabled', true))
                        <a :href="waBuy()" target="_blank" rel="noopener"
                            class="stt-boutique-btn-ghost mt-3 w-full gap-2"
                            style="min-height: 2.5rem; padding-top: 0.5rem; padding-bottom: 0.5rem; font-size: 11px">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4" style="color: #25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c0-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            {{ __('storefront.buy_via_whatsapp') }}
                        </a>
                    @endif

                    <div class="mt-3 flex gap-3">
                        @if (settings('general.whatsapp_inquiry', true))
                            <a href="https://wa.me/{{ $waNum }}?text={{ urlencode('Hi, I have a question about ' . $product->name . ' — ' . $waUrl) }}"
                                class="stt-boutique-link stt-boutique-link--center flex-1 items-center">{{ __('storefront.ask_a_question') }}</a>
                        @endif
                        @if (settings('general.whatsapp_share', true))
                            <button type="button" @click="stShare(@js($product->name), @js($waUrl), @js(__('storefront.link_copied')))"
                                class="stt-boutique-link stt-boutique-link--center flex-1 items-center">{{ __('storefront.share') }}</button>
                        @endif
                    </div>
                @endif

                {{-- Trust row — three uppercase tracked labels split by hairlines --}}
                <div class="stt-boutique-meta mt-10">
                    @foreach ([__('storefront.secure_checkout'), __('storefront.fast_delivery'), __('storefront.easy_returns')] as $t)
                        <div>{{ $t }}</div>
                    @endforeach
                </div>

                {{-- Description / Shipping — underlined editorial tabs --}}
                @php
                    $shippingInfo = settings('general.shipping_info');
                    $returnsInfo = settings('general.returns_info');
                    $shipText = trim(($shippingInfo ?: 'We ship within 1–3 business days.')."\n".($returnsInfo ?: 'Returns accepted within 14 days of delivery.'));
                @endphp
                <div class="mt-10 pt-2" x-data="{ tab: 'desc' }">
                    <div class="flex gap-8" style="border-bottom: 1px solid var(--st-line)">
                        <button type="button" @click="tab = 'desc'" class="stt-boutique-label pb-3 transition"
                            :style="tab === 'desc' ? 'color: var(--st-ink); border-bottom: 1px solid var(--st-accent)' : 'color: var(--st-ink-soft)'">{{ __('storefront.description') }}</button>
                        <button type="button" @click="tab = 'ship'" class="stt-boutique-label pb-3 transition"
                            :style="tab === 'ship' ? 'color: var(--st-ink); border-bottom: 1px solid var(--st-accent)' : 'color: var(--st-ink-soft)'">{{ __('storefront.shipping_and_returns') }}</button>
                    </div>
                    <div class="pt-6 text-sm leading-relaxed" style="color: var(--st-ink-soft); font-weight: 300">
                        <div x-show="tab === 'desc'">
                            @if ($product->description){!! nl2br(e($product->description)) !!}@else<span style="color: var(--st-ink-soft)">{{ __('storefront.no_description') }}</span>@endif
                        </div>
                        <div x-show="tab === 'ship'" x-cloak>{!! nl2br(e($shipText)) !!}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    {{-- Frequently bought together --}}
    @php $fbt = app(\Themicly\Shopcrafty\Modules\Marketing\Services\BoughtTogether::class)->forProduct($product->id, 4); @endphp
    @if ($fbt->isNotEmpty())
        <section class="stt-boutique-section" style="padding-block: 4rem;">
            <div class="st-container stt-boutique-narrow">
                <div class="st-reveal stt-boutique-section-head mb-16">
                    <div class="stt-boutique-headrow">
                        <div class="flex flex-col gap-4">
                            <p class="stt-boutique-eyebrow">{{ __('storefront.complete_the_set') }}</p>
                            <h2 class="stt-boutique-title">{{ __('storefront.frequently_bought_together') }}</h2>
                        </div>
                    </div>
                </div>
                <div class="stt-boutique-grid">
                    @foreach ($fbt as $p)
                        <x-st.product-card :product="$p" />
                    @endforeach
                </div>
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
        <section class="stt-boutique-section" style="padding-block: 4rem;">
            <div class="st-container stt-boutique-narrow">
                <div class="st-reveal stt-boutique-section-head mb-16">
                    <div class="stt-boutique-headrow">
                        <div class="flex flex-col gap-4">
                            <p class="stt-boutique-eyebrow">{{ __('storefront.keep_exploring') }}</p>
                            <h2 class="stt-boutique-title">{{ __('storefront.recently_viewed') }}</h2>
                        </div>
                    </div>
                </div>
                <div class="stt-boutique-grid">
                    @foreach ($recentlyViewed as $p)
                        <x-st.product-card :product="$p" wire:key="rv-{{ $p->id }}" />
                    @endforeach
                </div>
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
                        <button type="button" @click="active = img" class="h-14 w-14 overflow-hidden border transition"
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
