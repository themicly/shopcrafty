{{-- Haven hero — a full-bleed room scene. The photograph drifts slowly
     (Ken Burns), an espresso scrim carries the entrance sequence: eyebrow fades
     up, each lowercase serif headline line rises out of its clip, then the
     subline and CTAs follow. Extra images (slide 2/3) become a slow ambient
     crossfade with a pause control (no page-wide key bindings). Without
     photography the hero holds a composed espresso field. --}}
@php
    $layout = $s['layout'] ?? 'text';
    $image = trim((string) ($s['image'] ?? ''));
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

    if ($layout === 'image' && $image === '') {
        $layout = 'text';
    }
    if ($layout === 'video' && ! $videoFile && ! $videoEmbed) {
        $layout = 'text';
    }

    $heading = trim((string) ($s['heading'] ?? 'Rooms worth coming home to'));
    // Split the headline into two balanced lines so each can rise from its own clip.
    $words = preg_split('/\s+/', $heading) ?: [];
    $mid = (int) ceil(count($words) / 2);
    $lines = count($words) > 2
        ? [implode(' ', array_slice($words, 0, $mid)), implode(' ', array_slice($words, $mid))]
        : [$heading];

    $primaryCta = ! empty($s['cta_label']);
    $secondaryCta = ! empty($s['cta2_label']);
    $ambient = $layout === 'image' && count($slides) > 1;
@endphp

<section class="stt-haven-hero {{ $layout === 'text' ? 'stt-haven-hero--plain' : '' }} stt-haven-invert"
    @if ($ambient)
        x-data="{
            i: 0,
            n: {{ count($slides) }},
            playing: true,
            timer: null,
            reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            init() { if (this.reduce) { this.playing = false; return; } this.start(); },
            destroy() { this.stop(); },
            start() { this.stop(); this.timer = setInterval(() => { this.i = (this.i + 1) % this.n; }, 7000); this.playing = true; },
            stop() { if (this.timer) { clearInterval(this.timer); this.timer = null; } this.playing = false; },
            toggle() { this.playing ? this.stop() : this.start(); },
        }"
        role="group" aria-roledescription="carousel" aria-label="{{ $heading }}"
    @endif>

    {{-- Backdrop --}}
    @if ($layout === 'image')
        <div class="stt-haven-hero-media" aria-hidden="true">
            @foreach ($slides as $k => $src)
                <img src="{{ $src }}" alt=""
                    loading="{{ $k === 0 ? 'eager' : 'lazy' }}"
                    @if ($k === 0) fetchpriority="high" @endif
                    @if ($ambient)
                        style="opacity: {{ $k === 0 ? '1' : '0' }}"
                        :style="{ opacity: i === {{ $k }} ? 1 : 0 }"
                    @endif>
            @endforeach
        </div>
    @elseif ($layout === 'video' && $videoFile)
        <div class="stt-haven-hero-media" aria-hidden="true">
            <video autoplay muted loop playsinline class="absolute inset-0 h-full w-full object-cover">
                <source src="{{ $videoFile }}">
            </video>
        </div>
    @elseif ($layout === 'video' && $videoEmbed)
        <div class="stt-haven-hero-media overflow-hidden" aria-hidden="true">
            <iframe src="{{ $videoEmbed }}" title="{{ $heading }}" loading="lazy" allow="autoplay; encrypted-media" allowfullscreen frameborder="0"
                class="pointer-events-none absolute left-1/2 top-1/2 min-h-full min-w-full -translate-x-1/2 -translate-y-1/2" style="height: 125%; width: 177.78%"></iframe>
        </div>
    @endif

    {{-- The statement --}}
    <div class="st-container relative w-full" style="padding-block: clamp(4rem, 9vw, 7rem)">
        <div class="max-w-3xl">
            @if (! empty($s['eyebrow']))
                <p class="stt-haven-eyebrow stt-haven-hero-in-1 mb-6" style="color: var(--stt-haven-brass-lt)">{{ $s['eyebrow'] }}</p>
            @endif

            <h1 class="stt-haven-display stt-haven-hero-title" style="color: var(--st-bg)">
                @foreach ($lines as $li => $line)
                    <span class="stt-haven-hero-line">
                        <span>
                            @if ($li === count($lines) - 1 && str_contains($line, ' '))
                                @php $lw = explode(' ', $line); $last = array_pop($lw); @endphp
                                {{ implode(' ', $lw) }} <em style="color: var(--stt-haven-brass-lt)">{{ $last }}</em>
                            @else
                                {{ $line }}
                            @endif
                        </span>
                    </span>
                @endforeach
            </h1>

            @if (! empty($s['subheading']))
                <p class="stt-haven-hero-in-2 mt-7 max-w-lg text-base leading-relaxed sm:text-lg" style="color: #e6dfd3">{{ $s['subheading'] }}</p>
            @endif

            @if ($primaryCta || $secondaryCta || ! empty($s['coupon']))
                <div class="stt-haven-hero-in-3 mt-9 flex flex-wrap items-center gap-4">
                    @if ($primaryCta)
                        <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-haven-btn stt-haven-btn--light">
                            {{ $s['cta_label'] }}
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    @endif
                    @if ($secondaryCta)
                        <a href="{{ url($s['cta2_url'] ?? '/shop') }}" class="stt-haven-btn stt-haven-btn--ghost-inv" style="color: var(--st-bg)">{{ $s['cta2_label'] }}</a>
                    @endif
                    @if (! empty($s['coupon']))
                        <span class="stt-haven-badge" style="background: transparent; color: var(--stt-haven-brass-lt); border-color: color-mix(in srgb, var(--stt-haven-brass-lt) 60%, transparent)">{{ __('storefront.code') }}: {{ $s['coupon'] }}</span>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if ($ambient)
        {{-- Pause/play for the ambient crossfade — scoped to this button, no page-wide keys. --}}
        <button type="button" @click="toggle()"
            class="absolute z-10 grid h-11 w-11 place-items-center"
            style="right: 1rem; bottom: 1rem; color: var(--st-bg); border: 1px solid rgba(255,255,255,.4); border-radius: var(--st-radius); background: rgba(24,17,11,.35)"
            :aria-pressed="(! playing).toString()"
            :aria-label="playing ? 'Pause background slideshow' : 'Play background slideshow'" aria-label="Pause background slideshow">
            <svg x-show="playing" fill="currentColor" viewBox="0 0 24 24" class="h-4 w-4" aria-hidden="true"><path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z"/></svg>
            <svg x-show="! playing" x-cloak fill="currentColor" viewBox="0 0 24 24" class="h-4 w-4" aria-hidden="true"><path d="M8 5.5v13l11-6.5z"/></svg>
        </button>
    @endif
</section>
