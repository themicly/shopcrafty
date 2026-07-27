{{-- Marketplace banners (WoodMart mould): a fixed-width LEFT CATEGORY SIDEBAR beside
     the homepage banner slot. Each slide is a SQUARED SPLIT — a --st-surface text panel
     (eyebrow rule · bold heading · subheading · brand-blue CTA that deepens on hover)
     next to the product image. No overlay text, no gradient scrim: just honest boxes.
     Sidebar is desktop-only; on mobile it collapses and slides stack. --}}
@php
    $cats = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots()->take(10);
    $banners = \Themicly\Shopcrafty\Modules\Themes\Models\Banner::live()->placement('home_slider')
        ->where(fn ($q) => $q->whereNull('theme_id')->orWhere('theme_id', app(\Themicly\Shopcrafty\Modules\Themes\Services\ThemeService::class)->active()?->id))
        ->orderBy('sort')->get();
    $catIcon = 'M6 6.878V6a2.25 2.25 0 0 1 2.25-2.25h7.5A2.25 2.25 0 0 1 18 6v.878m-12 0c.235-.083.487-.128.75-.128h10.5c.263 0 .515.045.75.128m-12 0A2.25 2.25 0 0 0 4.5 9v.878m13.5-3A2.25 2.25 0 0 1 19.5 9v.878m0 0a2.246 2.246 0 0 0-.75-.128H5.25c-.263 0-.515.045-.75.128m15 0A2.25 2.25 0 0 1 21 12v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6c0-.98.626-1.813 1.5-2.122';

    $align = ($s['align'] ?? 'left') === 'center' ? 'center' : 'left';
    // Height presets live in the theme design system (layout.blade.php) — the arbitrary
    // min-h-[...] utilities they replaced are absent from the frozen compiled CSS.
    $heightClass = match ($s['height'] ?? 'standard') {
        'compact' => 'stt-market-slide stt-market-slide--compact',
        'tall' => 'stt-market-slide stt-market-slide--tall',
        default => 'stt-market-slide',
    };
    $textAlign = $align === 'center' ? 'items-center text-center' : 'items-start text-left';
@endphp

<section class="stt-market-section--bg st-reveal" style="background: var(--st-bg)">
    <div class="st-container py-6">
        <div class="flex gap-6">
            {{-- Left: category sidebar (the WoodMart signature) --}}
            <aside class="hidden shrink-0 lg:block" style="width: 15rem">
                <div class="stt-market-sidebar">
                    <div class="stt-market-sidebar-head flex items-center gap-2.5">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        {{ __('storefront.all_departments') }}
                    </div>
                    @if ($cats->isNotEmpty())
                        @foreach ($cats as $cat)
                            <a href="{{ url('/category/'.$cat->slug) }}" class="stt-market-sidebar-link group">
                                <span class="flex min-w-0 items-center gap-3">
                                    @if (! empty($cat->icon))
                                        <span class="w-5 shrink-0 text-center leading-none">{{ $cat->icon }}</span>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5 shrink-0" style="color: var(--st-ink-soft)"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $catIcon }}"/></svg>
                                    @endif
                                    <span class="truncate">{{ $cat->name }}</span>
                                </span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4 shrink-0 opacity-0 transition group-hover:opacity-100" style="color: var(--st-accent)"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                            </a>
                        @endforeach
                    @endif
                </div>
            </aside>

            {{-- Right: banner slider --}}
            <div class="min-w-0 flex-1">
                @if ($banners->isNotEmpty())
                    <div class="stt-market-box group relative overflow-hidden"
                        role="region" aria-roledescription="carousel" aria-label="{{ __('storefront.promotions') }}"
                        x-data="{
                            i: 0,
                            n: {{ $banners->count() }},
                            autoplay: {{ ($s['autoplay'] ?? true) ? 'true' : 'false' }},
                            timer: null,
                            reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
                            init() { this.start(); },
                            go(k) { this.i = (k + this.n) % this.n; this.start(); },
                            next() { this.go(this.i + 1); },
                            prev() { this.go(this.i - 1); },
                            start() {
                                this.stop();
                                if (!this.autoplay || this.n < 2 || this.reduce) return;
                                this.timer = setInterval(() => { this.i = (this.i + 1) % this.n; }, 6000);
                            },
                            stop() { if (this.timer) clearInterval(this.timer); },
                        }"
                        @mouseenter="stop()" @mouseleave="start()"
                        @keydown.window.arrow-left="prev()" @keydown.window.arrow-right="next()">

                        {{-- Track --}}
                        <div class="flex items-stretch transition-transform duration-700 ease-[cubic-bezier(.4,0,.2,1)]" :style="`transform: translateX(-${i * 100}%)`">
                            @foreach ($banners as $idx => $banner)
                                @php $bannerLink = preg_match('#^(https?://|/|\#|mailto:|tel:)#i', (string) $banner->link_url) ? $banner->link_url : '#'; @endphp
                                <div class="w-full shrink-0 {{ $heightClass }}">
                                    @if ($banner->title || $banner->subtitle || $banner->link_label)
                                        {{-- Squared split: text panel (surface) + image. No overlay. --}}
                                        <div class="grid h-full grid-cols-1 sm:grid-cols-2">
                                            <div class="stt-market-slide-panel order-2 flex flex-col justify-center gap-3 sm:order-1 {{ $textAlign }}" style="background: var(--st-surface)">
                                                @if ($banner->title)
                                                    <div class="{{ $align === 'center' ? 'flex flex-col items-center' : '' }}">
                                                        <h2 class="st-display text-2xl font-bold leading-tight tracking-tight sm:text-4xl" style="color: var(--st-ink)">{{ $banner->title }}</h2>
                                                        <span class="stt-market-rule"></span>
                                                    </div>
                                                @endif
                                                @if ($banner->subtitle)
                                                    <p class="max-w-sm text-sm leading-relaxed sm:text-base" style="color: var(--st-ink-soft)">{{ $banner->subtitle }}</p>
                                                @endif
                                                @if ($banner->link_label)
                                                    <a href="{{ $bannerLink }}" @if ($bannerLink === '#') @click.prevent @endif class="stt-market-btn stt-market-btn--lg mt-2">
                                                        {{ $banner->link_label }}
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0-6-6m6 6-6 6"/></svg>
                                                    </a>
                                                @endif
                                            </div>
                                            <a href="{{ $bannerLink }}" class="stt-market-slide-media order-1 block overflow-hidden sm:order-2" @if ($bannerLink === '#') @click.prevent @endif aria-label="{{ $banner->title ?: 'Slide '.($idx + 1) }}">
                                                <picture>
                                                    <source media="(max-width: 640px)" srcset="{{ $banner->mobileImage() }}">
                                                    <img src="{{ $banner->image_large }}" alt="{{ $banner->title }}" class="h-full w-full object-cover" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                                                </picture>
                                            </a>
                                        </div>
                                    @else
                                        {{-- Image-only slide: full-bleed within the boxed frame. --}}
                                        <a href="{{ $bannerLink }}" class="block h-full" @if ($bannerLink === '#') @click.prevent @endif aria-label="{{ $banner->title ?: 'Slide '.($idx + 1) }}">
                                            <picture>
                                                <source media="(max-width: 640px)" srcset="{{ $banner->mobileImage() }}">
                                                <img src="{{ $banner->image_large }}" alt="{{ $banner->title }}" class="h-full w-full object-cover" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                                            </picture>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($banners->count() > 1)
                            {{-- Squared prev / next controls — boxed, not floating/translucent. --}}
                            <button type="button" @click="prev()" aria-label="{{ __('storefront.previous_slide') }}"
                                class="stt-market-box stt-market-nav-btn absolute start-3 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center transition focus-visible:opacity-100 sm:opacity-0 sm:group-hover:opacity-100"
                                style="color: var(--st-ink)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                            </button>
                            <button type="button" @click="next()" aria-label="{{ __('storefront.next_slide') }}"
                                class="stt-market-box stt-market-nav-btn absolute end-3 top-1/2 grid h-10 w-10 -translate-y-1/2 place-items-center transition focus-visible:opacity-100 sm:opacity-0 sm:group-hover:opacity-100"
                                style="color: var(--st-ink)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                            </button>

                            {{-- Dots: active elongates to a brand-blue bar. --}}
                            <div class="absolute left-1/2 flex -translate-x-1/2 items-center" style="bottom: 0.625rem">
                                @foreach ($banners as $idx => $banner)
                                    <button type="button" @click="go({{ $idx }})" aria-label="{{ __('storefront.go_to_slide', ['number' => $idx + 1]) }}"
                                        :aria-current="i === {{ $idx }}" class="grid place-items-center p-1.5">
                                        <span class="stt-market-dot block h-1.5 transition-all duration-300"
                                            :class="i === {{ $idx }} ? 'w-8' : 'w-2.5'"
                                            :style="`border-radius: var(--st-radius); background: ${i === {{ $idx }} ? 'var(--st-primary)' : 'var(--st-line)'}`"></span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    {{-- No slides configured yet — keep the row balanced with a squared panel. --}}
                    <div class="stt-market-box flex {{ $heightClass }} flex-col items-start justify-center gap-4 p-10" style="background: var(--st-surface)">
                        <h2 class="st-display text-3xl font-bold sm:text-4xl" style="color: var(--st-ink)">{{ settings('general.store_name', 'Shop everything') }}</h2>
                        <span class="stt-market-rule"></span>
                        <a href="{{ url('/shop') }}" class="stt-market-btn stt-market-btn--lg">{{ __('storefront.shop_now') }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
