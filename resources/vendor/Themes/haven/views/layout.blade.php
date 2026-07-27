@php
    $storeName = settings('general.store_name', config('app.name'));
    // CSS-token guard: reject any value carrying characters that could break out of
    // the declaration/<style> block, falling back to the default (THM-01).
    $cssToken = fn ($value, $fallback) => ($value !== null && $value !== '' && ! preg_match('/[;{}<>@()\\\\]/', (string) $value)) ? $value : $fallback;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Required by AJAX POSTs from the storefront (e.g. the wishlist heart) — without
         it those requests send an undefined CSRF token and 419 (B3). --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Escape everything user/admin-authored that lands in the head (CMS-01). --}}
    <title>{{ $__env->yieldContent('title', $storeName) }}</title>
    @if ($favicon = settings('general.favicon'))<link rel="icon" href="{{ $favicon }}">@endif

    @hasSection('meta_description')
        <meta name="description" content="{{ $__env->yieldContent('meta_description') }}">
        <meta property="og:description" content="{{ $__env->yieldContent('meta_description') }}">
    @endif

    <meta property="og:title" content="{{ $__env->yieldContent('title', $storeName) }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    @hasSection('og_image')<meta property="og:image" content="{{ $__env->yieldContent('og_image') }}">@endif

    {{-- Store-specific design tokens, driven by the customizer. The doubled class
         (.storefront.storefront) raises specificity above the unlayered defaults in
         app.css, which load after this block and would otherwise win the cascade and
         flatten every theme back to the default palette (THM-06). --}}
    <style>
        .storefront.storefront {
            --st-bg: {!! $cssToken($theme['bg'] ?? null, '#f7f3ec') !!};
            --st-surface: {!! $cssToken($theme['surface'] ?? null, '#efe8dc') !!};
            --st-ink: {!! $cssToken($theme['ink'] ?? null, '#2b2118') !!};
            --st-ink-soft: {!! $cssToken($theme['ink_soft'] ?? null, '#6b5d4c') !!};
            --st-line: {!! $cssToken($theme['line'] ?? null, '#ddd2c0') !!};
            --st-primary: {!! $cssToken($theme['primary'] ?? null, '#2b2118') !!};
            --st-primary-ink: {!! $cssToken($theme['primary_ink'] ?? null, '#f7f3ec') !!};
            --st-accent: {!! $cssToken($theme['accent'] ?? null, '#8a5a19') !!};
            --st-radius: {!! $cssToken($theme['radius'] ?? null, '0px') !!};
            /* Small radius follows the theme so inner-page controls match its shape. */
            --st-radius-sm: calc(var(--st-radius) * 0.6);
            --st-font-display: {!! $cssToken($theme['display_font'] ?? null, "'Fraunces Variable', Georgia, serif") !!};
            --st-font-body: {!! $cssToken($theme['body_font'] ?? null, "'Inter Variable', ui-sans-serif, system-ui, sans-serif") !!};
            /* Derived, not customizer-facing: legible brass for text on espresso panels
               (the raw accent is too dark against ink), and the panel tone itself. */
            --stt-haven-brass-lt: color-mix(in srgb, var(--st-accent) 52%, #f2ddb4);
            --stt-haven-panel: color-mix(in srgb, var(--st-ink) 96%, #000000);
        }
    </style>

    {{-- ================================================================
         HAVEN — premium interiors / furniture. Espresso ink on warm linen,
         aged-brass accent. Lowercase Fraunces display type, oversized ghost
         numerals, offset image + text compositions, thin hairline
         dividers — and a disciplined motion system: staggered reveals,
         a Ken-Burns hero and a values marquee.
         Palette flows through the --st-* tokens so the customizer works.
         ================================================================ --}}
    <style>
        /* --- Motion vocabulary (transform/opacity only — zero layout shift) ---- */
        @keyframes stt-haven-lineup { from { transform: translateY(110%); } to { transform: none; } }
        @keyframes stt-haven-fadeup { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: none; } }
        @keyframes stt-haven-drift  { from { transform: scale(1.09) translateY(-1%); } to { transform: scale(1) translateY(0); } }
        @keyframes stt-haven-marquee { to { transform: translateX(-50%); } }

        /* Staggered scroll-reveal: the parent keeps app.js's .st-reveal observer,
           but stays visible itself while its children fade + rise in sequence. */
        .st-js .stt-haven-stagger.st-reveal { opacity: 1; transform: none; }
        .st-js .stt-haven-stagger.st-reveal > * { opacity: 0; transform: translateY(22px); }
        .st-js .stt-haven-stagger.st-reveal.is-in > * {
            opacity: 1; transform: none;
            transition: opacity .75s cubic-bezier(.2,.7,.2,1), transform .75s cubic-bezier(.2,.7,.2,1);
        }
        .st-js .stt-haven-stagger.st-reveal.is-in > *:nth-child(2) { transition-delay: .09s; }
        .st-js .stt-haven-stagger.st-reveal.is-in > *:nth-child(3) { transition-delay: .18s; }
        .st-js .stt-haven-stagger.st-reveal.is-in > *:nth-child(4) { transition-delay: .27s; }
        .st-js .stt-haven-stagger.st-reveal.is-in > *:nth-child(5) { transition-delay: .36s; }
        .st-js .stt-haven-stagger.st-reveal.is-in > *:nth-child(n+6) { transition-delay: .45s; }

        /* --- Rhythm ------------------------------------------------------------ */
        .stt-haven-section { padding-block: clamp(4rem, 9vw, 6.5rem); }

        /* Signature divider: a single hairline rule. */
        .stt-haven-divider { height: 1px; background: var(--st-line); }
        /* A divider carrying .st-reveal draws itself outward from the centre
           instead of fading up (transform only — zero layout shift). Higher
           specificity than app.css's .st-js .st-reveal base, so this wins. */
        .st-js .stt-haven-divider.st-reveal { opacity: 1; transform: scaleX(0); transform-origin: center; }
        .st-js .stt-haven-divider.st-reveal.is-in {
            transform: scaleX(1);
            transition: transform 1.2s cubic-bezier(.2,.7,.2,1);
        }

        /* --- Type voice: lowercase serif, quiet tracking ------------------------ */
        .stt-haven-eyebrow {
            font-family: var(--st-font-body);
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.24em;
            color: var(--st-accent);
        }
        .stt-haven-display {
            font-family: var(--st-font-display);
            font-weight: 420; line-height: 1.04;
            text-transform: lowercase; letter-spacing: -0.015em;
            color: var(--st-ink);
        }
        .stt-haven-display em, .stt-haven-display i {
            font-style: italic; font-weight: 380; color: var(--st-accent);
        }
        .stt-haven-hero-title { font-size: clamp(2.5rem, 6vw, 4.5rem); }
        .stt-haven-title { font-size: clamp(1.7rem, 3.4vw, 2.5rem); }

        /* Oversized ghost numeral — the section index watermark. */
        .stt-haven-numeral {
            font-family: var(--st-font-display);
            font-weight: 380; font-style: italic; line-height: 1;
            font-size: clamp(4.5rem, 10vw, 7.5rem);
            color: color-mix(in srgb, var(--st-ink) 11%, transparent);
            user-select: none; pointer-events: none;
        }
        /* Left-aligned section head: numeral sits behind the eyebrow + title. */
        .stt-haven-head { position: relative; margin-bottom: clamp(2.25rem, 5vw, 3.5rem); }
        .stt-haven-head .stt-haven-numeral { position: absolute; top: -0.45em; left: -0.06em; }
        .stt-haven-head > p, .stt-haven-head > h1, .stt-haven-head > h2, .stt-haven-head > div { position: relative; }

        /* --- Links: underline draws itself in ---------------------------------- */
        .stt-haven-link {
            display: inline; padding-bottom: 2px;
            color: var(--st-ink); text-decoration: none;
            background-image: linear-gradient(currentColor, currentColor);
            background-repeat: no-repeat; background-position: left bottom;
            background-size: 0% 1px;
            transition: background-size .35s cubic-bezier(.2,.7,.2,1);
        }
        .stt-haven-link:hover, .stt-haven-link:focus-visible { background-size: 100% 1px; }
        /* Caps variant used for "view all" affordances. */
        .stt-haven-link--caps {
            font-family: var(--st-font-body);
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.2em;
        }

        /* --- Buttons: knife-edge espresso, arrow slides on hover ---------------- */
        .stt-haven-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.7rem;
            min-height: 3.1rem; padding: 0.9rem 2.2rem;
            font-family: var(--st-font-body);
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.18em;
            background: var(--st-primary); color: var(--st-primary-ink);
            border: 1px solid var(--st-primary);
            border-radius: var(--st-radius);
            transition: background .25s ease, border-color .25s ease, color .25s ease;
        }
        .stt-haven-btn:hover { background: color-mix(in srgb, var(--st-primary) 82%, var(--st-accent)); border-color: color-mix(in srgb, var(--st-primary) 82%, var(--st-accent)); }
        .stt-haven-btn svg { transition: transform .3s cubic-bezier(.2,.7,.2,1); }
        .stt-haven-btn:hover svg, .stt-haven-btn:focus-visible svg { transform: translateX(5px); }
        /* Ghost: hairline frame that fills on hover. */
        .stt-haven-btn--ghost { background: transparent; color: var(--st-ink); border-color: color-mix(in srgb, var(--st-ink) 45%, transparent); }
        .stt-haven-btn--ghost:hover { background: var(--st-ink); border-color: var(--st-ink); color: var(--st-bg); }
        /* Light: ivory fill for espresso panels. */
        .stt-haven-btn--light { background: var(--st-bg); border-color: var(--st-bg); color: var(--st-ink); }
        .stt-haven-btn--light:hover { background: var(--stt-haven-brass-lt); border-color: var(--stt-haven-brass-lt); color: var(--st-ink); }
        /* Ghost on dark media/panels: inherits currentColor. */
        .stt-haven-btn--ghost-inv { background: transparent; color: currentColor; border-color: currentColor; }
        .stt-haven-btn--ghost-inv:hover { background: var(--st-bg); border-color: var(--st-bg); color: var(--st-ink); }

        /* --- Header ------------------------------------------------------------- */
        .stt-haven-wordmark {
            font-family: var(--st-font-display);
            font-weight: 460; text-transform: lowercase; letter-spacing: -0.01em;
            color: var(--st-ink);
        }
        .stt-haven-nav {
            display: inline-flex; align-items: center;
            min-height: 2.75rem; padding: 0.5rem 0.25rem;
            font-family: var(--st-font-body);
            font-size: 0.85rem; font-weight: 500; letter-spacing: 0.02em;
            color: var(--st-ink); text-decoration: none;
        }
        .stt-haven-iconbtn {
            position: relative; display: grid; place-items: center;
            width: 2.75rem; height: 2.75rem; color: var(--st-ink);
            border-radius: var(--st-radius);
            transition: background-color .2s ease;
        }
        .stt-haven-iconbtn:hover { background: var(--st-surface); }
        /* This unlayered display:grid outranks Tailwind's LAYERED md:hidden utility,
           so the hamburger would leak onto desktop — re-assert the breakpoint unlayered. */
        @media (min-width: 768px) {
            .stt-haven-iconbtn.md\:hidden { display: none; }
        }
        .stt-haven-search {
            display: inline-flex; align-items: center; gap: 0.6rem;
            min-height: 2.75rem; min-width: 12rem;
            padding: 0.5rem 1rem;
            font-family: var(--st-font-body); font-size: 0.85rem;
            color: var(--st-ink-soft);
            background: transparent;
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
            transition: border-color .2s ease;
            cursor: text;
        }
        .stt-haven-search:hover { border-color: var(--st-ink-soft); }
        /* The utility `hidden md:inline-flex` on this field is out-specified by the
           rule above (THM-06), so on mobile the 12rem search bar showed and squeezed
           the min-w-0 logo to nothing ("logo gone"). Force it hidden below md. */
        @media (max-width: 767px) { .stt-haven-search { display: none !important; } }

        /* --- Form controls -------------------------------------------------------- */
        .stt-haven-input {
            width: 100%; min-height: 3.1rem;
            background: transparent; color: var(--st-ink);
            border: 1px solid color-mix(in srgb, var(--st-ink) 35%, transparent);
            border-radius: var(--st-radius);
            padding: 0.85rem 1.1rem; font-size: 0.95rem;
            transition: border-color .2s ease;
        }
        .stt-haven-input:focus { outline: none; border-color: var(--st-ink); }
        .stt-haven-input::placeholder { color: var(--st-ink-soft); }
        /* Variant/option chip — espresso fill when active. */
        .stt-haven-chip {
            padding: 0.65rem 1.15rem; font-size: 12px; font-weight: 600;
            letter-spacing: 0.06em;
            border: 1px solid var(--st-line); color: var(--st-ink);
            background: transparent; border-radius: var(--st-radius);
            transition: border-color .2s ease, background .2s ease, color .2s ease;
        }
        .stt-haven-chip:hover { border-color: var(--st-ink); }
        .stt-haven-chip--active { background: var(--st-ink); color: var(--st-bg); border-color: var(--st-ink); }

        /* --- Prices, badges, micro-caps --------------------------------------------- */
        .stt-haven-price {
            font-family: var(--st-font-display); font-weight: 500;
            font-size: 1.05rem; color: var(--st-ink); font-variant-numeric: lining-nums;
        }
        .stt-haven-price-was { font-family: var(--st-font-body); font-size: 0.85rem; color: var(--st-ink-soft); text-decoration: line-through; }
        .stt-haven-badge {
            display: inline-block; padding: 0.4rem 0.75rem;
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.16em; line-height: 1;
            color: var(--st-accent); border: 1px solid color-mix(in srgb, var(--st-accent) 55%, transparent);
            border-radius: var(--st-radius); background: var(--st-bg);
        }
        .stt-haven-crumb {
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.16em;
            color: var(--st-ink-soft);
        }
        .stt-haven-crumb a:hover { color: var(--st-ink); }

        /* --- Hero: full-bleed room scene with a slow drift ---------------------------- */
        .stt-haven-hero {
            position: relative; overflow: hidden; isolation: isolate;
            display: flex; align-items: flex-end;
            min-height: clamp(32rem, 78vh, 46rem);
            background: var(--stt-haven-panel); color: var(--st-bg);
        }
        .stt-haven-hero-media { position: absolute; inset: 0; z-index: -2; }
        .stt-haven-hero-media img {
            position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
            animation: stt-haven-drift 16s cubic-bezier(.25,.5,.4,1) both;
            transition: opacity 1.6s ease;
        }
        .stt-haven-hero::after {
            content: ""; position: absolute; inset: 0; z-index: -1;
            background: linear-gradient(98deg, rgba(24,17,11,.85) 0%, rgba(24,17,11,.55) 48%, rgba(24,17,11,.18) 100%);
        }
        /* Text-only layout keeps the espresso field, no scrim needed. */
        .stt-haven-hero--plain::after { background: none; }
        /* On phones a 32rem+ bottom-aligned band leaves a large empty void above
           the text (no photo to fill it) — let the plain hero size to its content. */
        @media (max-width: 767px) {
            .stt-haven-hero--plain { min-height: auto; }
        }
        /* Entrance sequence: each headline line rises out of an overflow-clip. */
        .stt-haven-hero-line { display: block; overflow: hidden; }
        .stt-haven-hero-line > span { display: block; animation: stt-haven-lineup 1s cubic-bezier(.2,.7,.2,1) both; animation-delay: .15s; }
        .stt-haven-hero-line + .stt-haven-hero-line > span { animation-delay: .3s; }
        .stt-haven-hero-in-1 { animation: stt-haven-fadeup .8s cubic-bezier(.2,.7,.2,1) both; }
        .stt-haven-hero-in-2 { animation: stt-haven-fadeup .8s cubic-bezier(.2,.7,.2,1) .55s both; }
        .stt-haven-hero-in-3 { animation: stt-haven-fadeup .8s cubic-bezier(.2,.7,.2,1) .75s both; }

        /* --- Banner slider: full-bleed espresso-scrim slides, à la hero -------------------- */
        .stt-haven-banner { position: relative; overflow: hidden; isolation: isolate; }
        .stt-haven-banner--compact { min-height: 20rem; }
        .stt-haven-banner--standard { min-height: 26rem; }
        .stt-haven-banner--tall { min-height: 32rem; }
        @media (min-width: 768px) {
            .stt-haven-banner--compact { min-height: 22rem; }
            .stt-haven-banner--standard { min-height: 30rem; }
            .stt-haven-banner--tall { min-height: 40rem; }
        }
        .stt-haven-banner-slide {
            position: absolute; inset: 0;
            transition: opacity 1.2s ease;
        }
        .stt-haven-banner-slide img {
            position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
        }
        .stt-haven-banner-slide::after {
            content: ""; position: absolute; inset: 0;
            background: linear-gradient(98deg, rgba(24,17,11,.78) 0%, rgba(24,17,11,.42) 48%, rgba(24,17,11,.08) 100%);
        }
        .stt-haven-banner-slide--center::after { background: linear-gradient(to top, rgba(24,17,11,.72), rgba(24,17,11,.2)); }
        .stt-haven-banner-arrow {
            position: absolute; top: 50%; z-index: 2; display: grid; place-items: center;
            width: 2.75rem; height: 2.75rem; color: var(--st-bg);
            border: 1px solid rgba(255,255,255,.4); background: rgba(24,17,11,.35);
            transform: translateY(-50%);
            transition: background-color .2s ease;
        }
        .stt-haven-banner-arrow:hover { background: rgba(24,17,11,.6); }
        .stt-haven-banner-dots {
            position: absolute; z-index: 2; left: 50%; bottom: 1.25rem;
            display: flex; align-items: center; gap: 0.6rem;
            transform: translateX(-50%);
        }
        .stt-haven-banner-dot {
            display: block; height: 2px; width: 1.5rem; background: rgba(255,255,255,.4);
            transition: background-color .3s ease, width .3s ease;
        }
        .stt-haven-banner-dot--active { width: 2.5rem; background: var(--stt-haven-brass-lt); }

        /* --- Values marquee --------------------------------------------------------------- */
        .stt-haven-marquee { overflow: hidden; padding-block: 1.1rem; }
        .stt-haven-marquee-track { display: inline-flex; width: max-content; align-items: center; animation: stt-haven-marquee 34s linear infinite; }
        .stt-haven-marquee:hover .stt-haven-marquee-track,
        .stt-haven-marquee:focus-within .stt-haven-marquee-track { animation-play-state: paused; }
        .stt-haven-marquee-item {
            display: inline-flex; align-items: center; gap: 2.5rem; padding-inline: 1.25rem;
            font-family: var(--st-font-display); font-weight: 420; font-style: italic;
            text-transform: lowercase; letter-spacing: 0;
            font-size: clamp(1.05rem, 2vw, 1.35rem); color: var(--st-ink);
            white-space: nowrap;
        }
        .stt-haven-marquee-item .stt-haven-diamond {
            display: inline-block; width: 7px; height: 7px; transform: rotate(45deg);
            background: var(--st-accent); flex: none;
        }

        /* --- Category collage: offset editorial tiles -------------------------------------- */
        .stt-haven-cat-grid { display: grid; grid-template-columns: 1fr; gap: 2rem 1.75rem; }
        @media (min-width: 640px) { .stt-haven-cat-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) {
            .stt-haven-cat-grid { grid-template-columns: repeat(3, 1fr); align-items: start; }
            /* Every middle-column tile drops a notch — the offset rhythm. Margin,
               not transform, so the stagger reveal (which animates transform)
               never flattens the offset. */
            .stt-haven-cat-grid > :nth-child(3n+2) { margin-top: 2.75rem; }
        }
        .stt-haven-catcard { display: block; color: var(--st-ink); text-decoration: none; }
        .stt-haven-catcard figure { position: relative; overflow: hidden; margin: 0; background: var(--st-surface); border-radius: var(--st-radius); }
        .stt-haven-catcard img { width: 100%; height: 100%; object-fit: cover; transition: transform 1.1s cubic-bezier(.2,.7,.2,1); }
        .stt-haven-catcard:hover img, .stt-haven-catcard:focus-visible img { transform: scale(1.05); }

        /* --- Product grid --------------------------------------------------------------------- */
        .stt-haven-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 2.25rem 1.25rem; }
        @media (min-width: 768px) { .stt-haven-grid { grid-template-columns: repeat(3, 1fr); gap: 2.75rem 1.75rem; } }
        @media (min-width: 1024px) { .stt-haven-grid { grid-template-columns: repeat(4, 1fr); } }

        /* --- Feature: the offset/overlap composition -------------------------------------------- */
        .stt-haven-feature { display: grid; grid-template-columns: 1fr; }
        .stt-haven-feature-media { position: relative; overflow: hidden; background: var(--st-surface); border-radius: var(--st-radius); }
        .stt-haven-feature-media img { display: block; width: 100%; height: 100%; object-fit: cover; }
        .stt-haven-feature-panel {
            position: relative; z-index: 1;
            background: var(--st-bg);
            border: 1px solid var(--st-line);
            padding: clamp(1.75rem, 4vw, 3.25rem);
            margin: -3rem 1rem 0;
        }
        @media (min-width: 1024px) {
            .stt-haven-feature { grid-template-columns: repeat(12, 1fr); align-items: center; }
            .stt-haven-feature-media { grid-column: 5 / -1; grid-row: 1; min-height: 34rem; }
            .stt-haven-feature-panel { grid-column: 1 / 7; grid-row: 1; margin: 0; }
        }
        /* --- Promo tiles: asymmetric pair ---------------------------------------------------------- */
        .stt-haven-promo-grid { display: grid; grid-template-columns: 1fr; gap: 1.75rem; }
        @media (min-width: 1024px) { .stt-haven-promo-grid { grid-template-columns: 3fr 2fr; } }
        .stt-haven-promo {
            position: relative; overflow: hidden; isolation: isolate;
            display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-end;
            min-height: clamp(19rem, 44vw, 27rem);
            padding: clamp(1.75rem, 4vw, 2.75rem);
            background: var(--stt-haven-panel); color: var(--st-bg);
            border-radius: var(--st-radius);
        }
        .stt-haven-promo > img {
            position: absolute; inset: 0; z-index: -2;
            width: 100%; height: 100%; object-fit: cover;
            transition: transform 1.2s cubic-bezier(.2,.7,.2,1);
        }
        .stt-haven-promo:hover > img { transform: scale(1.04); }
        .stt-haven-promo--photo::after {
            content: ""; position: absolute; inset: 0; z-index: -1;
            background: linear-gradient(to top, rgba(24,17,11,.78) 0%, rgba(24,17,11,.32) 55%, rgba(24,17,11,.08) 100%);
        }

        /* --- Testimonials: offset quote columns ------------------------------------------------------ */
        .stt-haven-quote-grid { display: grid; grid-template-columns: 1fr; gap: 1.75rem; }
        @media (min-width: 768px) {
            .stt-haven-quote-grid { grid-template-columns: repeat(3, 1fr); align-items: start; }
            .stt-haven-quote-grid > :nth-child(3n+2) { margin-top: 2.25rem; }
        }
        .stt-haven-quote {
            display: flex; flex-direction: column;
            background: var(--st-bg); border: 1px solid var(--st-line);
            padding: 2rem; border-radius: var(--st-radius);
        }
        .stt-haven-quote-mark {
            font-family: var(--st-font-display); font-weight: 380; font-style: italic;
            font-size: 3.25rem; line-height: 0.6; color: var(--st-accent);
            user-select: none;
        }

        /* --- Newsletter form states: light-canvas defaults, flipped by .stt-haven-invert. */
        .stt-haven-nl-sub { color: var(--st-ink-soft); }
        .stt-haven-nl-done, .stt-haven-nl-error { color: var(--st-accent); }

        /* --- Inverted (espresso panel) context --------------------------------------------------------- */
        .stt-haven-invert { color: var(--st-bg); }
        .stt-haven-invert .stt-haven-display { color: var(--st-bg); }
        .stt-haven-invert .stt-haven-eyebrow { color: var(--stt-haven-brass-lt); }
        .stt-haven-invert .stt-haven-nl-sub { color: #d8cfc2; }
        .stt-haven-invert .stt-haven-nl-error { color: color-mix(in srgb, var(--st-accent) 22%, #ffffff); }
        .stt-haven-invert .stt-haven-nl-done { color: var(--stt-haven-brass-lt); }
        .stt-haven-invert .stt-haven-input {
            background: rgba(255,255,255,.05); color: #ffffff;
            border-color: rgba(255,255,255,.35);
        }
        .stt-haven-invert .stt-haven-input:focus { border-color: #ffffff; }
        .stt-haven-invert .stt-haven-input::placeholder { color: rgba(255,255,255,.55); }
        .stt-haven-invert .stt-haven-link { color: var(--st-bg); }
        .stt-haven-footlabel {
            font-family: var(--st-font-body); font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.22em;
            color: var(--stt-haven-brass-lt);
        }
        .stt-haven-footlink { font-size: 0.875rem; color: #d8cfc2; text-decoration: none; transition: color .2s ease; }
        .stt-haven-footlink:hover { color: #ffffff; }
        .stt-haven-social {
            display: grid; place-items: center;
            height: 2.6rem; width: 2.6rem;
            border: 1px solid rgba(255,255,255,.32); color: var(--st-bg);
            border-radius: var(--st-radius);
            transition: background .25s ease, color .25s ease;
        }
        .stt-haven-social:hover { background: var(--st-bg); color: var(--st-ink); }

        /* --- Skip link: hidden until keyboard focus ------------------------------------------------------ */
        .stt-haven-skip {
            position: fixed; top: 0.75rem; left: 0.75rem; z-index: 100;
            padding: 0.8rem 1.4rem;
            font-family: var(--st-font-body); font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.14em;
            background: var(--st-ink); color: var(--st-bg);
            transform: translateY(-250%); opacity: 0;
            transition: transform .2s ease, opacity .2s ease;
        }
        .stt-haven-skip:focus-visible { transform: none; opacity: 1; outline-offset: 3px; }

        /* --- Focus & anchor affordances -------------------------------------------------------------------- */
        /* currentColor keeps the ring legible on linen and espresso alike. */
        .storefront.storefront :is(a, button, select, summary, [tabindex]):focus-visible {
            outline: 2px solid currentColor; outline-offset: 3px;
        }
        .storefront.storefront [id] { scroll-margin-top: 7rem; }

        /* --- PDP compat: responsive steps absent from the frozen compiled CSS -------------------------------- */
        @media (min-width: 1024px) {
            .stt-haven-pdp-grid { column-gap: 4.5rem; }
            .stt-haven-pdp-sticky { position: sticky; top: 6.5rem; align-self: flex-start; }
        }

        /* --- Calm means still: one master gate for the whole motion system ------------------------------------ */
        @media (prefers-reduced-motion: reduce) {
            .storefront.storefront [class*="stt-haven"],
            .storefront.storefront [class*="stt-haven"]::before,
            .storefront.storefront [class*="stt-haven"]::after,
            .storefront.storefront .stt-haven-hero-media img,
            .storefront.storefront .stt-haven-hero-line > span,
            .storefront.storefront .stt-haven-marquee-track {
                animation: none !important; transition: none !important;
            }
            /* Staggered children must stay readable when the reveal is disabled.
               (Dividers' scaleX draw is already stilled by app.css's !important
               reduced-motion override on .st-reveal.) */
            .st-js .stt-haven-stagger.st-reveal > * { opacity: 1 !important; transform: none !important; }
            /* Bare <img>/<svg> descendants carry zoom/slide transitions but no
               stt-haven-* class of their own, so the attribute blanket above
               misses them — still them here, hover states included. */
            .storefront.storefront .stt-haven-catcard img,
            .storefront.storefront .stt-haven-promo > img,
            .storefront.storefront .stt-haven-btn svg {
                animation: none !important; transition: none !important; transform: none !important;
            }
        }

        /* --- Mobile bottom nav: floating pill with a center notch the cart
           CTA nests into (shared markup, themed via var(--st-*)). --- */
        .stt-bottom-nav{
            position: fixed; left: 1rem; width: calc(100vw - 2rem); z-index: 40;
            bottom: calc(1rem + env(safe-area-inset-bottom));
            display: flex; align-items: stretch; height: 4rem;
        }
        .stt-bottom-nav-shape{
            position: absolute; inset: 0; width: 100%; height: 100%; z-index: 0;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,.16));
        }
        .stt-bottom-nav-item{
            position: relative; z-index: 1;
            flex: 1 1 0; display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: .2rem; padding: .4rem .25rem; min-height: 3.25rem;
            font-family: var(--st-font-display); font-size: .64rem; font-weight: 500;
            letter-spacing: .02em;
            text-decoration: none; background: none; border: none; cursor: pointer;
        }
        .stt-bottom-nav-icon-wrap{ position: relative; display: inline-flex; }
        .stt-bottom-nav-badge{
            position: absolute; top: -.3rem; right: -.5rem;
            display: grid; place-items: center;
            height: 1rem; min-width: 1rem; padding-inline: .2rem;
            border-radius: 0; font-size: 9px; font-weight: 700;
            background: var(--st-accent); color: #fff;
        }
        .stt-bottom-nav-dot{
            position: absolute; bottom: .15rem; width: .3rem; height: .3rem;
            border-radius: 0; background: var(--st-primary);
        }
        /* Round on purpose, unlike Haven's usual 0px corners — a distinct affordance. */
        .stt-bottom-nav-cta{
            position: relative; z-index: 2; flex: 0 0 auto; align-self: flex-start;
            width: 3.5rem; height: 3.5rem; margin-top: -1.75rem;
            display: grid; place-items: center;
            border-radius: 999px; border: 3px solid var(--st-bg);
            background: var(--st-accent); color: #fff; cursor: pointer;
            box-shadow: 0 6px 16px color-mix(in srgb, var(--st-accent) 45%, transparent), 0 2px 6px rgba(0,0,0,.15);
        }
        .stt-bottom-nav-badge--cta{ top: -.15rem; right: -.15rem; background: var(--st-ink); }
        @media (min-width: 768px){ .stt-bottom-nav.md\:hidden{ display: none; } }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @include('theme::partials.tracking-scripts')
</head>
<body class="storefront min-h-screen pb-20 antialiased md:pb-0">
    {{-- First focusable element on every page: jump straight past the chrome. --}}
    <a href="#main" class="stt-haven-skip">{{ __('storefront.skip_to_content') }}</a>

    @includeWhen($theme['show_announcement'] ?? true, 'theme::partials.announcement')
    @include('theme::partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('theme::partials.footer')
    <x-storefront-addon-links />
    @include('theme::partials.mobile-bottom-nav', ['r' => 0])

    @if (session('flash_toast'))
        <script>
            window.addEventListener('load', () => window.toast?.(@json(session('flash_toast')), @json(session('flash_toast_type', 'info'))));
        </script>
    @endif

    @include('theme::partials.cookie-consent')

    @include('theme::partials.toasts')

    @livewireScripts
</body>
</html>
