{{-- Studio hero — the full-width sage band: eyebrow, big UPPERCASE serif
     statement and a white "Shop now" button on the left, photography on the
     right. Extra images (slide 2/3) turn the photo into a crossfading slider
     with edge arrows and dot indicators. --}}
@php
    $layout = $s['layout'] ?? 'text';
    $image = trim((string) ($s['image'] ?? ''));
    // Optional extra hero images — when present the photo column crossfades through them.
    $slides = array_values(array_filter([
        $image,
        trim((string) ($s['image2'] ?? '')),
        trim((string) ($s['image3'] ?? '')),
    ]));
    $videoUrl = trim((string) ($s['video_url'] ?? ''));

    // Resolve the video source: raw mp4/webm files play as a muted background loop,
    // YouTube/Vimeo links are normalised to their embeddable iframe form.
    $videoFile = null;
    $videoEmbed = null;
    if ($videoUrl !== '') {
        if (preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $videoUrl)) {
            $videoFile = $videoUrl;
        } elseif (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/)|youtu\.be/)([\w-]+)~i', $videoUrl, $m)) {
            $videoEmbed = "https://www.youtube.com/embed/{$m[1]}?autoplay=1&mute=1&loop=1&controls=0&playlist={$m[1]}&playsinline=1&showinfo=0&rel=0";
        } elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~i', $videoUrl, $m)) {
            $videoEmbed = "https://player.vimeo.com/video/{$m[1]}?autoplay=1&muted=1&loop=1&background=1";
        } elseif (str_contains($videoUrl, '/embed') || str_contains($videoUrl, 'player.')) {
            $videoEmbed = $videoUrl; // already an embed URL
        }
    }

    // Studio keeps copy on the band beside the media — never a full-bleed overlay.
    // Fall back to the plain band if the chosen media is missing.
    if ($layout === 'image' && $image === '') {
        $layout = 'text';
    }
    if ($layout === 'video' && ! $videoFile && ! $videoEmbed) {
        $layout = 'text';
    }

    $primaryCta = ! empty($s['cta_label']);
    $secondaryCta = ! empty($s['cta2_label']);
    $sliding = $layout === 'image' && count($slides) > 1;
@endphp

<section class="stt-studio-band relative"
    @if ($sliding)
        role="region" aria-roledescription="carousel" aria-label="{{ $s['heading'] ?? 'Hero' }}"
        x-data="{
            i: 0,
            n: {{ count($slides) }},
            timer: null,
            reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            init() { this.start(); },
            destroy() { this.stop(); },
            start() { this.stop(); if (this.reduce || this.n < 2) return; this.timer = setInterval(() => { this.i = (this.i + 1) % this.n; }, 6000); },
            stop() { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
            go(k) { this.i = ((k % this.n) + this.n) % this.n; this.start(); },
        }"
        @mouseenter="stop()" @mouseleave="start()"
    @endif>
    <div class="st-container">
        <div class="stt-studio-hero-grid">
            {{-- Column A — the statement --}}
            <div class="st-reveal">
                @if (! empty($s['eyebrow']))
                    <p class="stt-studio-eyebrow mb-5" style="color: var(--st-ink)">{{ $s['eyebrow'] }}</p>
                @endif

                <h1 class="stt-studio-hero-title">{{ $s['heading'] ?? 'The best fashion style for you' }}</h1>

                @if (! empty($s['subheading']))
                    <p class="mt-6 max-w-md text-sm leading-relaxed sm:text-base" style="color: color-mix(in srgb, var(--st-ink) 72%, var(--st-band))">
                        {{ $s['subheading'] }}
                    </p>
                @endif

                @if ($primaryCta || $secondaryCta || ! empty($s['coupon']))
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-studio-btn stt-studio-btn--light">{{ $s['cta_label'] }}</a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}" class="stt-studio-btn">{{ $s['cta2_label'] }}</a>
                        @endif
                        @if (! empty($s['coupon']))
                            <span class="stt-studio-badge">{{ __('storefront.code') }}: {{ $s['coupon'] }}</span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Column B — photography / video --}}
            <div class="st-reveal">
                <div class="stt-studio-hero-media mx-auto">
                    @if ($layout === 'video' && $videoFile)
                        <video autoplay muted loop playsinline class="h-full w-full object-cover">
                            <source src="{{ $videoFile }}">
                        </video>
                    @elseif ($layout === 'video' && $videoEmbed)
                        <iframe src="{{ $videoEmbed }}" title="{{ $s['heading'] ?? 'Hero video' }}" loading="lazy" allow="autoplay; encrypted-media" allowfullscreen frameborder="0"
                            class="pointer-events-none absolute left-1/2 top-1/2 min-h-full min-w-full -translate-x-1/2 -translate-y-1/2" style="height: 125%; width: 177.78%"></iframe>
                    @elseif ($layout === 'image')
                        @foreach ($slides as $k => $src)
                            <img src="{{ $src }}" alt="{{ $k === 0 ? ($s['heading'] ?? '') : '' }}"
                                loading="{{ $k === 0 ? 'eager' : 'lazy' }}"
                                @if ($k === 0) fetchpriority="high" @endif
                                @if ($sliding)
                                    class="absolute inset-0"
                                    style="opacity: {{ $k === 0 ? '1' : '0' }}; transition: opacity 1.2s ease"
                                    :style="{ opacity: i === {{ $k }} ? 1 : 0 }"
                                @endif>
                        @endforeach
                    @else
                        {{-- Text layout: a white plaque holds the wordmark over the sage field. --}}
                        <div class="grid h-full w-full place-items-center" style="background: color-mix(in srgb, #ffffff 42%, var(--st-band))">
                            <span class="stt-studio-plaque">{{ settings('general.store_name', 'Studio') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($sliding)
        {{-- Edge arrows --}}
        <button type="button" class="stt-studio-arrow stt-studio-arrow--edge" style="left: 1rem"
            @click="go(i - 1)" aria-label="{{ __('storefront.previous_slide') }}">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        </button>
        <button type="button" class="stt-studio-arrow stt-studio-arrow--edge" style="right: 1rem"
            @click="go(i + 1)" aria-label="{{ __('storefront.next_slide') }}">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
        </button>

        {{-- Dot indicators --}}
        <div class="absolute inset-x-0 flex items-center justify-center" style="bottom: 0.75rem">
            @foreach ($slides as $k => $src)
                <button type="button" class="stt-studio-dotbtn"
                    aria-label="{{ __('storefront.show_slide', ['number' => $k + 1]) }}"
                    aria-current="{{ $k === 0 ? 'true' : 'false' }}"
                    :aria-current="(i === {{ $k }}).toString()"
                    @click="go({{ $k }})"></button>
            @endforeach
        </div>
    @endif
</section>
