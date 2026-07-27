@php
    $layout = $s['layout'] ?? 'text';
    $image = trim((string) ($s['image'] ?? ''));
    // Optional extra hero images — when present the image hero crossfades through them.
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

    // Fall back to the plain text hero if the chosen media is missing.
    if ($layout === 'image' && $image === '') {
        $layout = 'text';
    }
    if ($layout === 'video' && ! $videoFile && ! $videoEmbed) {
        $layout = 'text';
    }
@endphp

@php
    // Reusable CTA presence flags, shared across every layout.
    $primaryCta = ! empty($s['cta_label']);
    $secondaryCta = ! empty($s['cta2_label']);
@endphp

@if ($layout === 'image')
    {{-- Image hero: full-bleed lifestyle banner — confident uppercase tagline + filled
         CTAs over a soft ink scrim. With extra images uploaded (Image — slide 2/3) the
         plates crossfade slowly; copy stays put, only the photography turns. --}}
    <section class="stt-boutique-hero-bleed"
        @if (count($slides) > 1)
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
                go(k) { this.i = k; this.start(); },
            }"
            @mouseenter="stop()" @mouseleave="start()"
        @endif>
        @foreach ($slides as $k => $src)
            <img src="{{ $src }}" alt="{{ $k === 0 ? ($s['heading'] ?? '') : '' }}"
                loading="{{ $k === 0 ? 'eager' : 'lazy' }}"
                @if ($k === 0) fetchpriority="high" @endif
                @if (count($slides) > 1)
                    style="opacity: {{ $k === 0 ? '1' : '0' }}; transition: opacity 1.2s ease"
                    :style="{ opacity: i === {{ $k }} ? 1 : 0 }"
                @endif>
        @endforeach
        <div class="stt-boutique-hero-scrim" aria-hidden="true"></div>

        @if (count($slides) > 1)
            {{-- Slide progress dashes, shared voice with the banner slider. --}}
            <div class="absolute flex items-center" style="right: 1.5rem; bottom: 1rem; gap: 0.9rem; z-index: 1">
                @foreach ($slides as $k => $src)
                    <button type="button" class="stt-boutique-slider-dash"
                        aria-label="{{ __('storefront.show_slide', ['number' => $k + 1]) }}"
                        aria-current="{{ $k === 0 ? 'true' : 'false' }}"
                        :aria-current="(i === {{ $k }}).toString()"
                        @click="go({{ $k }})"></button>
                @endforeach
            </div>
        @endif

        <div class="st-container stt-boutique-narrow relative w-full">
            {{-- padding-block inline: the compiled utility set lacks these steps (CSS can't be rebuilt) --}}
            <div class="st-reveal flex max-w-2xl flex-col items-start gap-6" style="padding-block: clamp(5rem, 12vh, 8rem)">
                @if (! empty($s['eyebrow']))
                    <p class="stt-boutique-eyebrow" style="color: color-mix(in srgb, var(--st-accent) 55%, #fff)">{{ $s['eyebrow'] }}</p>
                @endif

                <h1 class="stt-boutique-hero-title" style="color: #fff">{{ $s['heading'] ?? 'Welcome' }}</h1>

                <span class="stt-boutique-mark" aria-hidden="true"></span>

                @if (! empty($s['subheading']))
                    <p class="stt-boutique-measure text-base leading-relaxed" style="color: #fff; opacity: .9">{{ $s['subheading'] }}</p>
                @endif

                @if ($primaryCta || $secondaryCta)
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-boutique-btn stt-boutique-btn--invert">{{ $s['cta_label'] }}</a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}" class="stt-boutique-btn-ghost stt-boutique-btn-ghost--invert">{{ $s['cta2_label'] }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@elseif ($layout === 'video')
    {{-- Video hero: full-bleed background media, bold uppercase copy left. --}}
    <section class="relative overflow-hidden" style="background: var(--st-ink)">
        <div class="absolute inset-0">
            @if ($videoFile)
                <video autoplay muted loop playsinline class="h-full w-full object-cover">
                    <source src="{{ $videoFile }}">
                </video>
            @else
                <iframe src="{{ $videoEmbed }}" title="{{ $s['heading'] ?? 'Hero video' }}" loading="lazy" allow="autoplay; encrypted-media" allowfullscreen frameborder="0"
                    class="pointer-events-none absolute left-1/2 top-1/2 h-[56.25vw] min-h-full w-[177.78vh] min-w-full -translate-x-1/2 -translate-y-1/2"></iframe>
            @endif
        </div>
        <div class="absolute inset-0" style="background: color-mix(in srgb, var(--st-ink) 55%, transparent)"></div>

        <div class="st-container relative">
            {{-- padding-block inline: the compiled utility set lacks these steps (CSS can't be rebuilt) --}}
            <div class="st-reveal flex max-w-2xl flex-col items-start gap-6" style="padding-block: clamp(7rem, 18vh, 10rem)">
                @if (! empty($s['eyebrow']))
                    <p class="stt-boutique-eyebrow" style="color: color-mix(in srgb, var(--st-accent) 55%, #fff)">{{ $s['eyebrow'] }}</p>
                @endif
                <h1 class="stt-boutique-hero-title" style="color: #fff">{{ $s['heading'] ?? 'Welcome' }}</h1>
                <span class="stt-boutique-mark" aria-hidden="true"></span>
                @if (! empty($s['subheading']))
                    <p class="max-w-md text-base leading-relaxed" style="color: #fff; opacity: .88">{{ $s['subheading'] }}</p>
                @endif
                @if ($primaryCta || $secondaryCta)
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-boutique-btn stt-boutique-btn--invert">{{ $s['cta_label'] }}</a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}" class="stt-boutique-btn-ghost stt-boutique-btn-ghost--invert">{{ $s['cta2_label'] }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@else
    {{-- Text hero (default): bold uppercase tagline over a quiet gold-tinted wash,
         with filled near-black + gold CTAs. --}}
    <section class="stt-boutique-section stt-boutique-hero-wash">
        <div class="st-container stt-boutique-narrow">
            <div class="st-reveal flex max-w-3xl flex-col items-start gap-6">
                @if (! empty($s['eyebrow']))
                    <p class="stt-boutique-eyebrow">{{ $s['eyebrow'] }}</p>
                @endif

                <h1 class="stt-boutique-hero-title">{{ $s['heading'] ?? 'Welcome' }}</h1>

                <span class="stt-boutique-mark" aria-hidden="true"></span>

                @if (! empty($s['subheading']))
                    <p class="stt-boutique-measure text-base leading-relaxed" style="color: var(--st-ink-soft)">
                        {{ $s['subheading'] }}
                    </p>
                @endif

                @if ($primaryCta || $secondaryCta)
                    <div class="mt-2 flex flex-wrap items-center gap-3">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-boutique-btn">{{ $s['cta_label'] }}</a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}" class="stt-boutique-btn-ghost">{{ $s['cta2_label'] }}</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
