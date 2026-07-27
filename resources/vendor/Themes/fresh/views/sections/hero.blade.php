{{-- Fresh (Bloom) hero — a soft-green market FIELD, not an image overlay.
     Left copy (leaf eyebrow, big Fraunces headline, warm subhead, a chunky
     green pill CTA + a perforated coupon ticket) sits beside a produce shot
     framed by a dashed organic ring. Preserves the default hero's full data
     contract: text / image / video layouts, all resolved_settings keys and
     the same video-source normalisation. --}}
@php
    $layout = $s['layout'] ?? 'text';
    $image = trim((string) ($s['image'] ?? ''));
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

    // Reusable CTA + coupon flags, shared across every layout.
    $primaryCta = ! empty($s['cta_label']);
    $secondaryCta = ! empty($s['cta2_label']);
    $coupon = trim((string) ($s['coupon'] ?? ''));

    // Bloom's signature leaf glyph — the same outline path is reused wherever the leaf mark appears.
    $leaf = 'M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12';
@endphp

@if ($layout === 'video')
    {{-- Video hero: full-bleed background media with the Bloom copy overlaid. --}}
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
        <div class="absolute inset-0" style="background: color-mix(in srgb, var(--st-ink) 58%, transparent)"></div>

        <div class="st-container relative">
            <div class="st-reveal mx-auto flex max-w-3xl flex-col items-center py-28 text-center sm:py-36">
                @if (! empty($s['eyebrow']))
                    <span class="stt-fresh-eyebrow mb-5" style="color: #fff">{{ $s['eyebrow'] }}</span>
                @endif

                <h1 class="stt-fresh-heading text-5xl sm:text-6xl lg:text-7xl" style="color: #fff">
                    {{ $s['heading'] ?? 'Fresh, every day' }}
                </h1>

                @if (! empty($s['subheading']))
                    <p class="mt-6 max-w-xl text-base leading-relaxed sm:text-lg" style="color: #fff; opacity: .88">{{ $s['subheading'] }}</p>
                @endif

                @if ($primaryCta || $secondaryCta || $coupon !== '')
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-fresh-btn">{{ $s['cta_label'] }}</a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}" class="stt-fresh-btn stt-fresh-btn--soft" style="background: color-mix(in srgb, #fff 18%, transparent); color: #fff">{{ $s['cta2_label'] }}</a>
                        @endif
                        @if ($coupon !== '')
                            {{-- Copyable coupon ticket: tap to copy, label flips to "Copied!". --}}
                            <button type="button" class="stt-fresh-coupon" style="background: #fff"
                                x-data="{ copied: false }"
                                @click="if (navigator.clipboard) { navigator.clipboard.writeText(@js($coupon)); copied = true; setTimeout(() => copied = false, 1800) }"
                                aria-label="{{ __('storefront.copy_coupon_code', ['code' => $coupon]) }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0" style="color: var(--st-primary)" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $leaf }}" /></svg>
                                <span x-show="! copied">{{ __('storefront.use_code') }}</span>
                                <span x-show="copied" x-cloak style="color: var(--st-primary)">{{ __('storefront.copied') }}</span>
                                <span class="stt-fresh-coupon-code">{{ $coupon }}</span>
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" style="color: var(--st-ink-soft)" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75h-6a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@elseif ($layout === 'image')
    {{-- Split market banner: copy beside a produce shot framed by a dashed
         organic ring, all sitting on the soft-green Bloom field. --}}
    <section class="stt-fresh-hero">
        {{-- Blurred soft-green blobs + petal glyphs (decorative). --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="stt-fresh-blob" style="left: -6rem; top: -8rem; width: 26rem; height: 26rem"></div>
            <div class="stt-fresh-blob" style="right: 8%; bottom: -9rem; width: 30rem; height: 30rem"></div>
            <div class="stt-fresh-leaf" style="left: 1.5rem; top: 2.5rem; width: 3rem; height: 3rem; transform: rotate(45deg)"></div>
            <div class="stt-fresh-leaf" style="left: 33%; bottom: 2.5rem; width: 2rem; height: 2rem; transform: rotate(90deg)"></div>
        </div>

        <div class="st-container relative grid items-center gap-8 py-14 sm:py-16 lg:grid-cols-2 lg:gap-12 lg:min-h-[540px] lg:py-0">
            {{-- Copy --}}
            <div class="st-reveal">
                @if (! empty($s['eyebrow']))
                    <span class="stt-fresh-eyebrow mb-4">{{ $s['eyebrow'] }}</span>
                @endif

                <h1 class="stt-fresh-heading text-5xl sm:text-6xl" style="font-size: clamp(2.75rem, 6vw, 4.5rem)">
                    {{ $s['heading'] ?? 'Fresh, every day' }}
                </h1>

                @if (! empty($s['subheading']))
                    <p class="mt-6 max-w-md text-base leading-relaxed sm:text-lg" style="color: var(--st-ink-soft)">{{ $s['subheading'] }}</p>
                @endif

                @if ($primaryCta || $secondaryCta || $coupon !== '')
                    <div class="mt-9 flex flex-wrap items-center gap-4">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-fresh-btn">
                                {{ $s['cta_label'] }}
                                <span aria-hidden="true">→</span>
                            </a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}" class="stt-fresh-btn stt-fresh-btn--soft">{{ $s['cta2_label'] }}</a>
                        @endif
                        @if ($coupon !== '')
                            {{-- Copyable coupon ticket: tap to copy, label flips to "Copied!". --}}
                            <button type="button" class="stt-fresh-coupon"
                                x-data="{ copied: false }"
                                @click="if (navigator.clipboard) { navigator.clipboard.writeText(@js($coupon)); copied = true; setTimeout(() => copied = false, 1800) }"
                                aria-label="{{ __('storefront.copy_coupon_code', ['code' => $coupon]) }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0" style="color: var(--st-primary)" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $leaf }}" /></svg>
                                <span x-show="! copied">{{ __('storefront.use_code') }}</span>
                                <span x-show="copied" x-cloak style="color: var(--st-primary)">{{ __('storefront.copied') }}</span>
                                <span class="stt-fresh-coupon-code">{{ $coupon }}</span>
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" style="color: var(--st-ink-soft)" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75h-6a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                            </button>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Produce shot inside the dashed organic ring. --}}
            <div class="st-reveal relative flex items-center justify-center lg:justify-end">
                <div class="stt-fresh-ring w-full max-w-md">
                    <img src="{{ $image }}" alt="{{ $s['heading'] ?? '' }}" loading="eager" class="max-h-[380px] w-auto object-contain drop-shadow-2xl">
                </div>
            </div>
        </div>
    </section>
@else
    {{-- Text hero (default): centered copy on the soft-green Bloom field. --}}
    <section class="stt-fresh-hero">
        {{-- Blurred soft-green blobs + petal glyphs (decorative). --}}
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="stt-fresh-blob" style="left: 50%; top: -9rem; width: 28rem; height: 28rem; transform: translateX(-50%)"></div>
            <div class="stt-fresh-blob" style="left: -5rem; bottom: -8rem; width: 24rem; height: 24rem"></div>
            <div class="stt-fresh-blob" style="right: -5rem; bottom: -6rem; width: 22rem; height: 22rem"></div>
            <div class="stt-fresh-leaf" style="left: 8%; top: 3rem; width: 3rem; height: 3rem; transform: rotate(45deg)"></div>
            <div class="stt-fresh-leaf" style="right: 10%; top: 4rem; width: 2rem; height: 2rem; transform: rotate(120deg)"></div>
        </div>

        <div class="st-container relative">
            <div class="st-reveal mx-auto flex max-w-3xl flex-col items-center py-24 text-center sm:py-32">
                @if (! empty($s['eyebrow']))
                    <span class="stt-fresh-eyebrow mb-5">{{ $s['eyebrow'] }}</span>
                @endif

                <h1 class="stt-fresh-heading" style="font-size: clamp(2.75rem, 6vw, 4.5rem)">
                    {{ $s['heading'] ?? 'Fresh, every day' }}
                </h1>

                @if (! empty($s['subheading']))
                    <p class="mt-6 max-w-xl text-base leading-relaxed sm:text-lg" style="color: var(--st-ink-soft)">
                        {{ $s['subheading'] }}
                    </p>
                @endif

                @if ($primaryCta || $secondaryCta || $coupon !== '')
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-fresh-btn">
                                {{ $s['cta_label'] }}
                                <span aria-hidden="true">→</span>
                            </a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}" class="stt-fresh-btn stt-fresh-btn--soft">{{ $s['cta2_label'] }}</a>
                        @endif
                        @if ($coupon !== '')
                            {{-- Copyable coupon ticket: tap to copy, label flips to "Copied!". --}}
                            <button type="button" class="stt-fresh-coupon"
                                x-data="{ copied: false }"
                                @click="if (navigator.clipboard) { navigator.clipboard.writeText(@js($coupon)); copied = true; setTimeout(() => copied = false, 1800) }"
                                aria-label="{{ __('storefront.copy_coupon_code', ['code' => $coupon]) }}">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0" style="color: var(--st-primary)" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $leaf }}" /></svg>
                                <span x-show="! copied">{{ __('storefront.use_code') }}</span>
                                <span x-show="copied" x-cloak style="color: var(--st-primary)">{{ __('storefront.copied') }}</span>
                                <span class="stt-fresh-coupon-code">{{ $coupon }}</span>
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" style="color: var(--st-ink-soft)" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75h-6a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
