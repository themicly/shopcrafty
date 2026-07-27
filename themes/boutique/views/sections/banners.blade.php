@php
    $banners = \Themicly\Shopcrafty\Modules\Themes\Models\Banner::live()->placement('home_slider')
        ->where(fn ($q) => $q->whereNull('theme_id')->orWhere('theme_id', app(\Themicly\Shopcrafty\Modules\Themes\Services\ThemeService::class)->active()?->id))
        ->orderBy('sort')->get();

    $align = ($s['align'] ?? 'left') === 'center' ? 'center' : 'left';
    $heightClass = match ($s['height'] ?? 'standard') {
        'compact' => 'stt-boutique-slider--compact',
        'tall' => 'stt-boutique-slider--tall',
        default => 'stt-boutique-slider--standard',
    };
    // Alignment-aware ink scrim + copy placement (fixed-alpha ink, per convention).
    $scrim = $align === 'center'
        ? 'linear-gradient(to top, color-mix(in srgb, var(--st-ink) 55%, transparent), color-mix(in srgb, var(--st-ink) 30%, transparent))'
        : 'linear-gradient(to right, color-mix(in srgb, var(--st-ink) 68%, transparent) 0%, color-mix(in srgb, var(--st-ink) 30%, transparent) 55%, transparent 100%)';
    $contentAlign = $align === 'center' ? 'items-center text-center' : 'items-start text-left';
@endphp

@if ($banners->isNotEmpty())
    {{-- Boutique: banner slider — full-width plates on a slow crossfade, bold uppercase
         headlines, filled CTA buttons, progress dashes, squared chevrons. --}}
    <section class="st-reveal" role="region" aria-roledescription="carousel" aria-label="{{ __('storefront.lookbook') }}"
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

        <div class="stt-boutique-slider {{ $heightClass }}">
            {{-- Plates — stacked, slow opacity crossfade; only the active take is interactive --}}
            @foreach ($banners as $idx => $banner)
                @php $bannerLink = preg_match('#^(https?://|/|\#|mailto:|tel:)#i', (string) $banner->link_url) ? $banner->link_url : '#'; @endphp
                <div class="stt-boutique-slide" :class="i === {{ $idx }} ? 'stt-boutique-slide--active' : 'stt-boutique-slide--idle'"
                    role="group" aria-roledescription="slide" aria-label="{{ __('storefront.slide_x_of_y', ['current' => $idx + 1, 'total' => $banners->count()]) }}"
                    aria-hidden="{{ $idx === 0 ? 'false' : 'true' }}" :aria-hidden="i === {{ $idx }} ? 'false' : 'true'">
                    <a href="{{ $bannerLink }}" class="absolute inset-0 block" @if ($bannerLink === '#') @click.prevent @endif
                        tabindex="{{ $idx === 0 ? '0' : '-1' }}" :tabindex="i === {{ $idx }} ? 0 : -1" aria-label="{{ $banner->title ?: __('storefront.slide_number', ['number' => $idx + 1]) }}">
                        <picture>
                            <source media="(max-width: 640px)" srcset="{{ $banner->mobileImage() }}">
                            <img src="{{ $banner->image_large }}" alt="{{ $banner->title }}" class="h-full w-full object-cover" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                        </picture>
                    </a>

                    <div class="pointer-events-none absolute inset-0" style="background: {{ $scrim }}"></div>

                    @if ($banner->title || $banner->subtitle || $banner->link_label)
                        {{-- padding-block inline: the compiled utility set lacks these steps (CSS can't be rebuilt);
                             the extra bottom clearance keeps copy clear of the progress dashes. --}}
                        <div class="pointer-events-none absolute inset-0 flex flex-col justify-end {{ $contentAlign }} px-6 sm:px-12 lg:px-16" style="padding-block: 2.5rem 5.5rem">
                            <div class="flex max-w-xl flex-col gap-5 {{ $contentAlign }} {{ $align === 'center' ? 'mx-auto' : '' }}">
                                <span class="stt-boutique-mark" aria-hidden="true"></span>
                                @if ($banner->title)
                                    <h2 class="text-3xl font-bold uppercase sm:text-5xl" style="color: #fff; letter-spacing: 0.04em; line-height: 1.1">{{ $banner->title }}</h2>
                                @endif
                                @if ($banner->subtitle)
                                    <p class="max-w-md text-sm leading-relaxed sm:text-base" style="color: #fff; opacity: .9">{{ $banner->subtitle }}</p>
                                @endif
                                @if ($banner->link_label)
                                    {{-- Button-styled label; pointer-events stay off so clicks land on the slide link beneath. --}}
                                    <span class="stt-boutique-btn stt-boutique-btn--invert mt-1">{{ $banner->link_label }}</span>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            @if ($banners->count() > 1)
                {{-- Chevron paddles --}}
                <button type="button" @click="prev()" aria-label="{{ __('storefront.previous_slide') }}" class="stt-boutique-slider-arrow start-3 sm:start-5">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </button>
                <button type="button" @click="next()" aria-label="{{ __('storefront.next_slide') }}" class="stt-boutique-slider-arrow end-3 sm:end-5">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-6 w-6 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>

                {{-- Progress dashes — the active take stretches and turns gold --}}
                <div class="absolute bottom-6 left-1/2 z-10 flex -translate-x-1/2 items-center gap-3">
                    @foreach ($banners as $idx => $banner)
                        <button type="button" @click="go({{ $idx }})" aria-label="{{ __('storefront.go_to_slide', ['number' => $idx + 1]) }}"
                            class="stt-boutique-slider-dash" aria-current="{{ $idx === 0 ? 'true' : 'false' }}" :aria-current="i === {{ $idx }} ? 'true' : 'false'"></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
