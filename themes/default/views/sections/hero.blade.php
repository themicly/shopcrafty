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
@endphp

@php
    // Reusable CTA button markup, shared across every layout.
    $primaryCta = ! empty($s['cta_label']);
    $secondaryCta = ! empty($s['cta2_label']);
@endphp

{{-- Self-contained gradient CTA (mirrors the chrome's signature button). Token-driven,
     so under third-party theme layouts it degrades to THEIR primary→accent blend. --}}
<style>
    .stx-hero-cta{
        background-image:linear-gradient(120deg,
            var(--st-primary) 0%,
            color-mix(in srgb, var(--st-primary) 55%, var(--st-accent)) 100%);
        color:var(--st-primary-ink);
        box-shadow:0 10px 22px -10px color-mix(in srgb, var(--st-primary) 55%, transparent);
        transition:transform .2s ease, box-shadow .2s ease;
    }
    .stx-hero-cta:hover{
        transform:translateY(-2px);
        box-shadow:0 14px 26px -10px color-mix(in srgb, var(--st-primary) 60%, transparent);
    }
    .stx-hero-cta:focus-visible{
        outline:2px solid var(--st-primary); outline-offset:3px;
    }
    @media (prefers-reduced-motion: reduce){
        .stx-hero-cta{ transition:none; }
        .stx-hero-cta:hover{ transform:none; }
    }
</style>

@if ($layout === 'image')
    {{-- Split hero: image beside the copy on desktop, stacked on mobile. --}}
    <section class="relative overflow-hidden" style="background: var(--st-bg)">
        <div class="st-container grid items-center gap-10 py-16 sm:py-20 lg:grid-cols-2 lg:gap-16">
            <div class="st-reveal order-2 lg:order-1">
                @if (! empty($s['eyebrow']))
                    <p class="mb-5 text-xs font-semibold uppercase tracking-[0.2em]"
                        style="background-image: linear-gradient(90deg, var(--st-primary), var(--st-accent)); -webkit-background-clip: text; background-clip: text; color: transparent">{{ $s['eyebrow'] }}</p>
                @endif
                <h1 class="st-display text-4xl font-semibold leading-[1.05] sm:text-5xl lg:text-6xl" style="color: var(--st-ink)">
                    {{ $s['heading'] ?? 'Welcome' }}
                </h1>
                @if (! empty($s['subheading']))
                    <p class="mt-6 max-w-xl text-base leading-relaxed sm:text-lg" style="color: var(--st-ink-soft)">{{ $s['subheading'] }}</p>
                @endif
                @if ($primaryCta || $secondaryCta)
                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}"
                                class="stx-hero-cta inline-flex items-center px-8 py-4 text-sm font-semibold"
                                style="border-radius: var(--st-radius)">
                                {{ $s['cta_label'] }}
                            </a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}"
                                class="inline-flex items-center px-8 py-4 text-sm font-semibold transition-transform duration-200 hover:-translate-y-0.5"
                                style="background: transparent; color: var(--st-ink); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                                {{ $s['cta2_label'] }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
            <div class="st-reveal order-1 overflow-hidden lg:order-2" style="border-radius: var(--st-radius)">
                <div class="aspect-[4/3] w-full">
                    <img src="{{ $image }}" alt="{{ $s['heading'] ?? '' }}" loading="eager" class="h-full w-full object-cover">
                </div>
            </div>
        </div>
    </section>
@elseif ($layout === 'video')
    {{-- Video hero: full-bleed background media with the copy overlaid. --}}
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
            <div class="st-reveal mx-auto flex max-w-3xl flex-col items-center py-28 text-center sm:py-36">
                @if (! empty($s['eyebrow']))
                    <p class="mb-5 text-xs font-semibold uppercase tracking-[0.2em]" style="color: #fff; opacity: .8">{{ $s['eyebrow'] }}</p>
                @endif
                <h1 class="st-display text-4xl font-semibold leading-[1.05] sm:text-6xl" style="color: #fff">
                    {{ $s['heading'] ?? 'Welcome' }}
                </h1>
                @if (! empty($s['subheading']))
                    <p class="mt-6 max-w-xl text-base leading-relaxed sm:text-lg" style="color: #fff; opacity: .85">{{ $s['subheading'] }}</p>
                @endif
                @if ($primaryCta || $secondaryCta)
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}"
                                class="stx-hero-cta inline-flex items-center px-8 py-4 text-sm font-semibold"
                                style="border-radius: var(--st-radius)">
                                {{ $s['cta_label'] }}
                            </a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}"
                                class="inline-flex items-center px-8 py-4 text-sm font-semibold transition-transform duration-200 hover:-translate-y-0.5"
                                style="background: transparent; color: #fff; border: 1px solid rgba(255,255,255,.5); border-radius: var(--st-radius)">
                                {{ $s['cta2_label'] }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@else
    {{-- Text hero (default): centered copy on a soft gradient. --}}
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, color-mix(in srgb, var(--st-primary) 14%, var(--st-bg)) 0%, color-mix(in srgb, var(--st-accent) 12%, var(--st-bg)) 100%)">
        <div class="st-container">
            <div class="st-reveal mx-auto flex max-w-3xl flex-col items-center py-24 text-center sm:py-32">
                @if (! empty($s['eyebrow']))
                    <p class="mb-5 text-xs font-semibold uppercase tracking-[0.2em]"
                        style="background-image: linear-gradient(90deg, var(--st-primary), var(--st-accent)); -webkit-background-clip: text; background-clip: text; color: transparent">{{ $s['eyebrow'] }}</p>
                @endif

                <h1 class="st-display text-4xl font-semibold leading-[1.05] sm:text-6xl" style="color: var(--st-ink)">
                    {{ $s['heading'] ?? 'Welcome' }}
                </h1>

                @if (! empty($s['subheading']))
                    <p class="mt-6 max-w-xl text-base leading-relaxed sm:text-lg" style="color: var(--st-ink-soft)">
                        {{ $s['subheading'] }}
                    </p>
                @endif

                @if ($primaryCta || $secondaryCta)
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                        @if ($primaryCta)
                            <a href="{{ url($s['cta_url'] ?? '/shop') }}"
                                class="stx-hero-cta inline-flex items-center px-8 py-4 text-sm font-semibold"
                                style="border-radius: var(--st-radius)">
                                {{ $s['cta_label'] }}
                            </a>
                        @endif
                        @if ($secondaryCta)
                            <a href="{{ url($s['cta2_url'] ?? '/shop') }}"
                                class="inline-flex items-center px-8 py-4 text-sm font-semibold transition-transform duration-200 hover:-translate-y-0.5"
                                style="background: transparent; color: var(--st-ink); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                                {{ $s['cta2_label'] }}
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
