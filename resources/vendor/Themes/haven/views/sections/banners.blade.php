@php
    $banners = \Themicly\Shopcrafty\Modules\Themes\Models\Banner::live()->placement('home_slider')
        ->where(fn ($q) => $q->whereNull('theme_id')->orWhere('theme_id', app(\Themicly\Shopcrafty\Modules\Themes\Services\ThemeService::class)->active()?->id))
        ->orderBy('sort')->get();

    $align = ($s['align'] ?? 'left') === 'center' ? 'center' : 'left';
    $heightClass = match ($s['height'] ?? 'standard') {
        'compact' => 'stt-haven-banner--compact',
        'tall' => 'stt-haven-banner--tall',
        default => 'stt-haven-banner--standard',
    };
    $contentAlign = $align === 'center' ? 'items-center text-center' : 'items-start text-left';
@endphp

@if ($banners->isNotEmpty())
    {{-- Haven banner slider — full-bleed espresso-scrim slides, the same crossfade
         and gradient language as the hero, but self-contained (own Alpine scope)
         so it can sit anywhere in the section order. Lowercase serif headline,
         brass eyebrow rule, squared (radius: 0) arrows and dash dots to match
         the theme's no-radius, hairline vocabulary. --}}
    <section class="stt-haven-banner {{ $heightClass }} st-reveal"
        role="region" aria-roledescription="carousel" aria-label="{{ __('storefront.promotions') }}"
        x-data="{
            i: 0,
            n: {{ $banners->count() }},
            autoplay: {{ ($s['autoplay'] ?? true) ? 'true' : 'false' }},
            timer: null,
            reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            init() { this.start(); },
            destroy() { this.stop(); },
            go(k) { this.i = (k + this.n) % this.n; this.start(); },
            next() { this.go(this.i + 1); },
            prev() { this.go(this.i - 1); },
            start() {
                this.stop();
                if (!this.autoplay || this.n < 2 || this.reduce) return;
                this.timer = setInterval(() => { this.i = (this.i + 1) % this.n; }, 6500);
            },
            stop() { if (this.timer) clearInterval(this.timer); },
        }"
        @mouseenter="stop()" @mouseleave="start()"
        @keydown.window.arrow-left="prev()" @keydown.window.arrow-right="next()">

        @foreach ($banners as $idx => $banner)
            @php $bannerLink = preg_match('#^(https?://|/|\#|mailto:|tel:)#i', (string) $banner->link_url) ? $banner->link_url : '#'; @endphp
            <div class="stt-haven-banner-slide {{ $align === 'center' ? 'stt-haven-banner-slide--center' : '' }}"
                style="opacity: {{ $idx === 0 ? '1' : '0' }}" :style="{ opacity: i === {{ $idx }} ? 1 : 0, 'z-index': i === {{ $idx }} ? 1 : 0 }"
                role="group" aria-roledescription="slide" aria-label="{{ __('storefront.slide_x_of_y', ['current' => $idx + 1, 'total' => $banners->count()]) }}"
                aria-hidden="{{ $idx === 0 ? 'false' : 'true' }}" :aria-hidden="i === {{ $idx }} ? 'false' : 'true'">
                <picture>
                    <source media="(max-width: 640px)" srcset="{{ $banner->mobileImage() }}">
                    <img src="{{ $banner->image_large }}" alt="" loading="{{ $idx === 0 ? 'eager' : 'lazy' }}">
                </picture>

                @if ($banner->title || $banner->subtitle || $banner->link_label)
                    <div class="relative z-10 flex h-full flex-col justify-end {{ $contentAlign }} st-container" style="padding-block: 2.5rem 4rem">
                        <div class="flex max-w-lg flex-col gap-4 {{ $contentAlign }}">
                            @if ($banner->title)
                                <h2 class="stt-haven-display" style="color: var(--st-bg); font-size: clamp(1.9rem, 4.2vw, 3rem)">{{ $banner->title }}</h2>
                            @endif
                            @if ($banner->subtitle)
                                <p class="max-w-md text-sm leading-relaxed sm:text-base" style="color: #e6dfd3">{{ $banner->subtitle }}</p>
                            @endif
                            @if ($banner->link_label)
                                <a href="{{ $bannerLink }}" @if ($bannerLink === '#') @click.prevent @endif
                                    tabindex="{{ $idx === 0 ? '0' : '-1' }}" :tabindex="i === {{ $idx }} ? 0 : -1"
                                    class="stt-haven-btn stt-haven-btn--light mt-1">
                                    {{ $banner->link_label }}
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <a href="{{ $bannerLink }}" class="absolute inset-0 z-10 block" @if ($bannerLink === '#') @click.prevent @endif
                        tabindex="{{ $idx === 0 ? '0' : '-1' }}" :tabindex="i === {{ $idx }} ? 0 : -1"
                        aria-label="{{ __('storefront.slide_number', ['number' => $idx + 1]) }}"></a>
                @endif
            </div>
        @endforeach

        @if ($banners->count() > 1)
            <button type="button" @click="prev()" aria-label="{{ __('storefront.previous_slide') }}" class="stt-haven-banner-arrow" style="inset-inline-start: 1rem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            </button>
            <button type="button" @click="next()" aria-label="{{ __('storefront.next_slide') }}" class="stt-haven-banner-arrow" style="inset-inline-end: 1rem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
            </button>

            <div class="stt-haven-banner-dots">
                @foreach ($banners as $idx => $banner)
                    <button type="button" @click="go({{ $idx }})" aria-label="{{ __('storefront.go_to_slide', ['number' => $idx + 1]) }}"
                        :aria-current="i === {{ $idx }}" class="p-1">
                        <span class="stt-haven-banner-dot" :class="i === {{ $idx }} ? 'stt-haven-banner-dot--active' : ''" aria-hidden="true"></span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>
@endif
