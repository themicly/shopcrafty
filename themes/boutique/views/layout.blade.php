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
            --st-bg: {!! $cssToken($theme['bg'] ?? null, '#ffffff') !!};
            --st-surface: {!! $cssToken($theme['surface'] ?? null, '#f6f5f2') !!};
            --st-ink: {!! $cssToken($theme['ink'] ?? null, '#1a1815') !!};
            --st-ink-soft: {!! $cssToken($theme['ink_soft'] ?? null, '#6f6a60') !!};
            --st-line: {!! $cssToken($theme['line'] ?? null, '#e8e5de') !!};
            --st-primary: {!! $cssToken($theme['primary'] ?? null, '#1a1815') !!};
            --st-primary-ink: {!! $cssToken($theme['primary_ink'] ?? null, '#ffffff') !!};
            --st-accent: {!! $cssToken($theme['accent'] ?? null, '#8a6d33') !!};
            --st-radius: {!! $cssToken($theme['radius'] ?? null, '3px') !!};
            /* Small radius follows the theme so inner-page controls match its shape. */
            --st-radius-sm: calc(var(--st-radius) * 0.6);
            --st-font-display: {!! $cssToken($theme['display_font'] ?? null, "'Inter Variable', ui-sans-serif, system-ui, sans-serif") !!};
            --st-font-body: {!! $cssToken($theme['body_font'] ?? null, "'Inter Variable', ui-sans-serif, system-ui, sans-serif") !!};
            /* Star ratings follow the gold accent (compiled default is amber). */
            --st-star: var(--st-accent);
        }
    </style>

    {{-- ================================================================
         BOUTIQUE (boutique-v2 mould) — commerce-forward fashion retail.
         White canvas, near-black ink, gold accent on prices/badges, bold
         UPPERCASE section titles, filled squared buttons, visible buy
         affordances. Palette is driven by --st-* tokens so the customizer
         keeps working; hex appears only for fixed-alpha overlays.
         ================================================================ --}}
    <style>
        /* --- Rhythm & containers ------------------------------------- */
        .stt-boutique-section { padding-block: 4rem; background: var(--st-bg); }
        @media (min-width: 640px) { .stt-boutique-section { padding-block: 6rem; } }
        .stt-boutique-narrow { max-width: 80rem; margin-inline: auto; }
        .stt-boutique-measure { max-width: 36rem; }

        /* --- Labels & marks ------------------------------------------- */
        .stt-boutique-eyebrow {
            font-family: var(--st-font-body);
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.24em;
            color: var(--st-accent);
        }
        .stt-boutique-label {
            font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.12em;
            color: var(--st-ink);
        }
        .stt-boutique-display {
            font-family: var(--st-font-display);
            font-weight: 700; letter-spacing: -0.01em; line-height: 1.15;
            color: var(--st-ink);
        }
        /* Signature short gold bar under headings */
        .stt-boutique-mark {
            display: block; height: 2px; width: 3rem;
            background: var(--st-accent); border: 0;
        }

        /* --- Hairline rules ------------------------------------------ */
        .stt-boutique-rule { height: 1px; width: 100%; background: var(--st-line); border: 0; }
        .stt-boutique-hair { border-color: var(--st-line) !important; }

        /* --- Section heads -------------------------------------------- */
        /* Bold UPPERCASE retail titles: "THE BOUTIQUE", "OUR BLOG". */
        .stt-boutique-section-head { display: flex; flex-direction: column; gap: 0.9rem; }
        .stt-boutique-title {
            font-family: var(--st-font-display);
            font-weight: 800; line-height: 1.1;
            text-transform: uppercase; letter-spacing: 0.08em;
            font-size: clamp(1.4rem, 2.8vw, 2.1rem);
            color: var(--st-ink);
        }
        /* Left title + right "View all", closed by a hairline. */
        .stt-boutique-headrow {
            display: flex; align-items: flex-end; justify-content: space-between;
            gap: 1.5rem; padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--st-line);
        }
        /* Centered variant with the gold bar beneath (reference showcase heads). */
        .stt-boutique-head-center {
            display: flex; flex-direction: column; align-items: center;
            gap: 0.9rem; text-align: center;
        }

        /* --- Uppercase text link with gold underline growth ----------- */
        .stt-boutique-link {
            display: inline-flex; flex-direction: column; align-items: flex-start; gap: 0.45rem;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.16em;
            color: var(--st-ink);
            text-decoration: none;
        }
        .stt-boutique-link--center { align-items: center; }
        .stt-boutique-link::after {
            content: ""; display: block; height: 2px; width: 2.5rem;
            background: var(--st-accent);
            transition: width .3s cubic-bezier(.2,.7,.2,1);
        }
        .stt-boutique-link:hover::after { width: 100%; }

        /* --- Buttons (filled, squared, uppercase) ---------------------- */
        .stt-boutique-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            min-height: 3rem; padding: 0.85rem 1.9rem;
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.14em;
            background: var(--st-primary); color: var(--st-primary-ink);
            border: 1px solid var(--st-primary);
            border-radius: var(--st-radius);
            transition: background .2s ease, border-color .2s ease, color .2s ease;
        }
        .stt-boutique-btn:hover { background: var(--st-accent); border-color: var(--st-accent); color: #fff; }
        /* Secondary: filled gold. */
        .stt-boutique-btn-ghost {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            min-height: 3rem; padding: 0.85rem 1.9rem;
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.14em;
            background: var(--st-accent); color: #fff;
            border: 1px solid var(--st-accent);
            border-radius: var(--st-radius);
            transition: background .2s ease, border-color .2s ease, color .2s ease;
        }
        .stt-boutique-btn-ghost:hover { background: var(--st-primary); border-color: var(--st-primary); color: var(--st-primary-ink); }
        /* On dark media (hero/banner scrims): white-filled primary. */
        .stt-boutique-btn--invert { background: #fff; border-color: #fff; color: var(--st-ink); }
        .stt-boutique-btn--invert:hover { background: var(--st-accent); border-color: var(--st-accent); color: #fff; }
        /* Outlined variant kept for quiet tertiary actions (WhatsApp etc.). */
        .stt-boutique-btn-outline {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            min-height: 3rem; padding: 0.85rem 1.9rem;
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.14em;
            background: transparent; color: var(--st-ink);
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
            transition: border-color .2s ease, color .2s ease;
        }
        .stt-boutique-btn-outline:hover { border-color: var(--st-ink); }
        .stt-boutique-btn-ghost--invert { background: transparent; color: #fff; border-color: rgba(255,255,255,.55); }
        .stt-boutique-btn-ghost--invert:hover { background: #fff; border-color: #fff; color: var(--st-ink); }

        /* --- Badges (Sale/percent gold, sold-out neutral) -------------- */
        .stt-boutique-badge {
            display: inline-block; padding: 0.3rem 0.6rem;
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em;
            border-radius: var(--st-radius); line-height: 1;
        }
        .stt-boutique-badge--sale { background: var(--st-accent); color: #fff; }
        .stt-boutique-badge--sold { background: var(--st-surface); color: var(--st-ink-soft); border: 1px solid var(--st-line); }

        /* --- Hero: split (reused by the feature/story section) --------- */
        .stt-boutique-hero { display: grid; grid-template-columns: 1fr; align-items: center; gap: 2.5rem; }
        @media (min-width: 900px) {
            .stt-boutique-hero { grid-template-columns: 1.05fr 0.95fr; gap: 4rem; }
            .stt-boutique-hero--reverse { grid-template-columns: 0.95fr 1.05fr; }
        }
        .stt-boutique-hero-copy { display: flex; flex-direction: column; align-items: flex-start; gap: 1.4rem; }
        .stt-boutique-hero-title {
            font-family: var(--st-font-display);
            font-weight: 800; letter-spacing: 0.02em; line-height: 1.08;
            text-transform: uppercase;
            font-size: clamp(2.1rem, 5vw, 3.9rem);
            color: var(--st-ink);
        }
        .stt-boutique-hero-media {
            position: relative; overflow: hidden;
            aspect-ratio: 4 / 5; background: var(--st-surface);
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
        }
        .stt-boutique-hero-media img { width: 100%; height: 100%; object-fit: cover; }

        /* --- Hero: quiet wash for the text layout ---------------------- */
        .stt-boutique-hero-wash {
            background: linear-gradient(165deg,
                color-mix(in srgb, var(--st-accent) 9%, var(--st-bg)) 0%,
                color-mix(in srgb, var(--st-accent) 4%, var(--st-bg)) 46%,
                var(--st-bg) 100%);
            border-bottom: 1px solid var(--st-line);
        }

        /* --- Hero: full-bleed lifestyle banner (image layout) ----------- */
        /* Fixed min-height (no aspect jump while the image loads) with the
           copy over a soft ink scrim. */
        .stt-boutique-hero-bleed {
            position: relative; overflow: hidden; isolation: isolate;
            display: flex; align-items: center;
            min-height: clamp(28rem, 78vh, 44rem);
            background: var(--st-ink);
        }
        .stt-boutique-hero-bleed > img {
            position: absolute; inset: 0; z-index: -2;
            width: 100%; height: 100%; object-fit: cover;
        }
        .stt-boutique-hero-bleed .stt-boutique-hero-scrim {
            position: absolute; inset: 0; z-index: -1;
            background: linear-gradient(to right,
                color-mix(in srgb, var(--st-ink) 62%, transparent) 0%,
                color-mix(in srgb, var(--st-ink) 34%, transparent) 55%,
                color-mix(in srgb, var(--st-ink) 14%, transparent) 100%);
        }

        /* --- Lookbook/banner slider ------------------------------------ */
        /* Full-width crossfading plates; height is fixed per builder setting
           so slide swaps never reflow the page. */
        .stt-boutique-slider { position: relative; overflow: hidden; background: var(--st-ink); }
        .stt-boutique-slider--compact { height: clamp(19rem, 46vh, 32rem); }
        .stt-boutique-slider--standard { height: clamp(25rem, 62vh, 42rem); }
        .stt-boutique-slider--tall { height: clamp(32rem, 82vh, 52rem); }
        .stt-boutique-slide {
            position: absolute; inset: 0;
            opacity: 0; pointer-events: none;
            transition: opacity 1s ease;
        }
        /* First plate shows before Alpine boots (and without JS); once running,
           Alpine stamps explicit --active/--idle classes, declared after this
           with equal specificity so they win either way. */
        .stt-boutique-slide:first-child { opacity: 1; pointer-events: auto; }
        .stt-boutique-slide.stt-boutique-slide--idle { opacity: 0; pointer-events: none; }
        .stt-boutique-slide.stt-boutique-slide--active { opacity: 1; pointer-events: auto; }
        @media (prefers-reduced-motion: reduce) { .stt-boutique-slide { transition: none; } }
        /* Chevron paddles on the media. */
        .stt-boutique-slider-arrow {
            position: absolute; top: 50%; transform: translateY(-50%); z-index: 2;
            display: grid; place-items: center;
            height: 2.75rem; width: 2.75rem;
            color: #fff; opacity: .8;
            background: color-mix(in srgb, var(--st-ink) 35%, transparent);
            border-radius: var(--st-radius);
            transition: opacity .25s ease, background-color .25s ease;
        }
        .stt-boutique-slider-arrow:hover { opacity: 1; background: color-mix(in srgb, var(--st-ink) 60%, transparent); }
        /* Progress dashes — the active take stretches and turns gold. */
        .stt-boutique-slider-dash {
            display: block; padding-block: 0.7rem; /* generous hit-area around the dash */
            background: transparent; border: 0; cursor: pointer;
        }
        .stt-boutique-slider-dash::after {
            content: ""; display: block; height: 2px; width: 2.25rem;
            background: rgba(255,255,255,.5);
            transition: width .45s cubic-bezier(.2,.7,.2,1), background-color .45s ease;
        }
        .stt-boutique-slider-dash[aria-current="true"]::after {
            width: 4rem;
            background: color-mix(in srgb, var(--st-accent) 70%, #fff);
        }

        /* --- Tile card (collections / instagram) ----------------------- */
        .stt-boutique-card { position: relative; }
        .stt-boutique-card-media {
            position: relative; overflow: hidden;
            aspect-ratio: 1 / 1; background: var(--st-surface);
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
        }
        .stt-boutique-card-media img {
            width: 100%; height: 100%; object-fit: cover;
            transition: transform .5s cubic-bezier(.2,.7,.2,1), opacity .5s ease;
        }
        .stt-boutique-card:hover .stt-boutique-card-media img { transform: scale(1.05); }
        /* Slide-up action bar */
        .stt-boutique-card-add {
            position: absolute; inset-inline: 0; bottom: 0;
            padding-block: 0.9rem;
            background: color-mix(in srgb, var(--st-ink) 88%, transparent); color: #fff;
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.14em; text-align: center;
            transform: translateY(100%); opacity: 0;
            transition: transform .3s cubic-bezier(.2,.7,.2,1), opacity .3s ease;
        }
        .stt-boutique-card:hover .stt-boutique-card-add,
        .stt-boutique-card:focus-within .stt-boutique-card-add { transform: translateY(0); opacity: 1; }
        @media (hover: none) { .stt-boutique-card-add { transform: none; opacity: 1; } }
        @media (prefers-reduced-motion: reduce) {
            .stt-boutique-card-media img, .stt-boutique-card-add { transition: none; }
        }
        .stt-boutique-card-body { margin-top: 0.9rem; text-align: center; }
        .stt-boutique-card-title {
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.1em;
            color: var(--st-ink);
        }
        .stt-boutique-card-price {
            margin-top: 0.4rem;
            font-size: 0.95rem; font-weight: 700;
            color: var(--st-accent);
            display: inline-flex; gap: 0.5rem; align-items: baseline; justify-content: center;
        }
        .stt-boutique-card-was {
            font-size: 0.8rem; font-weight: 400;
            text-decoration: line-through; color: var(--st-ink-soft);
        }

        /* --- Blog card --------------------------------------------------- */
        .stt-boutique-blogcard {
            display: flex; flex-direction: column; overflow: hidden;
            background: var(--st-bg);
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .stt-boutique-blogcard:hover { border-color: var(--st-ink-soft); box-shadow: 0 4px 16px rgba(0,0,0,.06); }

        /* --- Brand logo cell --------------------------------------------- */
        .stt-boutique-brandcell {
            display: grid; place-items: center;
            aspect-ratio: 3 / 2; padding: 1.5rem;
            background: var(--st-bg);
        }

        /* --- Grid (product/tile grids: commerce density) ----------------- */
        .stt-boutique-grid {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .stt-boutique-grid { grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
        }
        @media (min-width: 1024px) {
            .stt-boutique-grid { grid-template-columns: repeat(4, 1fr); }
        }

        /* --- Form controls (boxed, squared) ------------------------------- */
        .stt-boutique-input {
            width: 100%; background: var(--st-bg); color: var(--st-ink);
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
            padding: 0.8rem 1rem; font-size: 0.95rem; min-height: 3rem;
            transition: border-color .2s ease;
        }
        .stt-boutique-input:focus { outline: none; border-color: var(--st-ink); }
        .stt-boutique-input::placeholder { color: var(--st-ink-soft); }

        /* Squared variant/option chip */
        .stt-boutique-chip {
            padding: 0.7rem 1.2rem; font-size: 12px; font-weight: 600;
            letter-spacing: 0.04em;
            border: 1px solid var(--st-line); color: var(--st-ink);
            background: var(--st-bg); border-radius: var(--st-radius);
            transition: border-color .2s ease, background .2s ease, color .2s ease;
        }
        .stt-boutique-chip:hover { border-color: var(--st-ink); }
        .stt-boutique-chip[aria-pressed="true"], .stt-boutique-chip--active {
            background: var(--st-ink); color: var(--st-bg); border-color: var(--st-ink);
        }

        /* --- Focus & anchor affordances ------------------------------- */
        /* currentColor keeps the ring legible on both the white canvas and
           the ink bands; inputs keep their designed border focus. */
        .storefront.storefront :is(a, button):focus-visible {
            outline: 2px solid currentColor; outline-offset: 3px;
        }
        .storefront.storefront .stt-boutique-btn:focus-visible { outline-color: var(--st-ink); }
        /* In-page anchors (e.g. #reviews) land clear of the sticky header. */
        .storefront.storefront [id] { scroll-margin-top: 8rem; }

        /* --- Header ---------------------------------------------------- */
        /* Icon button (40px hit-area, matches cart trigger). */
        .stt-boutique-iconbtn {
            display: grid; place-items: center;
            height: 2.5rem; width: 2.5rem;
            color: var(--st-ink);
            transition: color .2s ease;
        }
        .stt-boutique-iconbtn:hover { color: var(--st-accent); }
        /* This unlayered display:grid outranks Tailwind's LAYERED lg:hidden/lg:grid
           utilities, so both directions need re-asserting unlayered. */
        @media (min-width: 1024px) { .stt-boutique-iconbtn.lg\:hidden { display: none; } }
        @media (max-width: 1023.98px) { .stt-boutique-iconbtn.lg\:grid { display: none; } }

        /* --- Mobile bottom nav: floating pill with a center notch the cart
           CTA nests into (shared markup, editorial voice via var(--st-*)). --- */
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
            font-size: .62rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
            text-decoration: none; background: none; border: none; cursor: pointer; font-family: inherit;
        }
        .stt-bottom-nav-icon-wrap{ position: relative; display: inline-flex; }
        .stt-bottom-nav-badge{
            position: absolute; top: -.3rem; right: -.5rem;
            display: grid; place-items: center;
            height: 1rem; min-width: 1rem; padding-inline: .2rem;
            border-radius: 999px; font-size: 9px; font-weight: 700;
            background: var(--st-accent); color: #fff;
        }
        .stt-bottom-nav-dot{
            position: absolute; bottom: .15rem; width: .3rem; height: .3rem;
            border-radius: 1px; background: var(--st-primary);
        }
        .stt-bottom-nav-cta{
            position: relative; z-index: 2; flex: 0 0 auto; align-self: flex-start;
            width: 3.5rem; height: 3.5rem; margin-top: -1.75rem;
            display: grid; place-items: center;
            border-radius: 999px; border: 3px solid var(--st-bg);
            background: var(--st-accent); color: #fff; cursor: pointer;
            box-shadow: 0 6px 16px color-mix(in srgb, var(--st-accent) 45%, transparent), 0 2px 6px rgba(0,0,0,.15);
        }
        .stt-bottom-nav-badge--cta{ top: -.15rem; right: -.15rem; }
        @media (min-width: 1024px){ .stt-bottom-nav.lg\:hidden{ display: none; } }

        /* Second header row: uppercase menu links + category entries. */
        .stt-boutique-navrow { border-top: 1px solid var(--st-line); }
        .stt-boutique-navlink {
            display: inline-flex; align-items: center;
            min-height: 2.75rem; padding: 0.5rem 0.9rem;
            font-size: 12px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.12em;
            color: var(--st-ink);
            transition: color .2s ease;
        }
        .stt-boutique-navlink:hover { color: var(--st-accent); }

        /* --- Inverted (ink band) context ------------------------------ */
        /* Wrap a block in .stt-boutique-invert to flip the kit for the
           near-black newsletter band. Gold is lifted toward white for
           legibility on ink. */
        .stt-boutique-invert .stt-boutique-eyebrow { color: color-mix(in srgb, var(--st-accent) 60%, #fff); }
        .stt-boutique-invert .stt-boutique-label,
        .stt-boutique-invert .stt-boutique-link { color: #fff; }
        .stt-boutique-invert .stt-boutique-nl-heading { color: #fff; }
        .stt-boutique-invert .stt-boutique-nl-sub { color: rgba(255,255,255,.72); }
        .stt-boutique-invert .stt-boutique-input {
            background: rgba(255,255,255,.08); color: #fff;
            border-color: rgba(255,255,255,.28);
        }
        .stt-boutique-invert .stt-boutique-input:focus { border-color: #fff; }
        .stt-boutique-invert .stt-boutique-input::placeholder { color: rgba(255,255,255,.5); }
        .stt-boutique-invert .stt-boutique-btn {
            background: var(--st-accent); border-color: var(--st-accent); color: #fff;
        }
        .stt-boutique-invert .stt-boutique-btn:hover { background: #fff; border-color: #fff; color: var(--st-ink); }

        /* --- Newsletter form (theme::livewire.newsletter-form) -------- */
        .stt-boutique-nl-heading {
            font-family: var(--st-font-display);
            font-weight: 800; letter-spacing: 0.1em; line-height: 1.15;
            text-transform: uppercase;
            font-size: clamp(1.5rem, 3vw, 2.25rem);
            color: var(--st-ink);
        }
        .stt-boutique-nl-sub { color: var(--st-ink-soft); }
        .stt-boutique-nl-error { color: #c0392b; }
        .stt-boutique-invert .stt-boutique-nl-error { color: #f5b7ae; }
        /* Unstyled button reset so text links work on <button>. */
        .stt-boutique-linkbtn { background: transparent; border: 0; padding: 0; cursor: pointer; }

        /* --- Breadcrumb / meta rows ---------------------------------- */
        .stt-boutique-crumb {
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.14em;
            color: var(--st-ink-soft);
        }
        .stt-boutique-crumb a:hover { color: var(--st-accent); }

        /* --- Trust / meta row split by hairlines --------------------- */
        .stt-boutique-meta {
            display: grid; grid-template-columns: repeat(3, 1fr);
            border: 1px solid var(--st-line); border-radius: var(--st-radius);
        }
        .stt-boutique-meta > * {
            padding: 1.1rem 0.5rem; text-align: center;
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.14em;
            color: var(--st-ink-soft);
            border-left: 1px solid var(--st-line);
        }
        .stt-boutique-meta > *:first-child { border-left: 0; }

        /* --- Compat: responsive steps absent from the frozen compiled CSS --- */
        @media (min-width: 640px) {
            .stt-boutique-mb-loose { margin-bottom: 4rem; }  /* section heads: sm:mb-16 */
            .stt-boutique-ig-grid { column-gap: 1.5rem; }    /* instagram grid: sm:gap-x-6 */
            .stt-boutique-actions { gap: 0.25rem; }          /* header icon row: sm:gap-1 */
        }
        @media (min-width: 1024px) {
            .stt-boutique-pdp-sticky { position: sticky; top: 8rem; align-self: flex-start; } /* clears the two-row header */
            .stt-boutique-pdp-info { padding-top: 0.5rem; }
        }

        /* ==============================================================
           Motion pass — editorial, transform/opacity only. Reveal itself
           lives in app.css (.st-js .st-reveal → .is-in); everything below
           only enriches it and is neutralised under reduced motion.
           ============================================================== */

        /* --- Stagger: grids/tiles whose children are .st-reveal -------- */
        /* Children share one IntersectionObserver frame when a row enters,
           so per-child delays cascade them in. Delays cycle every 6 children
           (6n+…) so deep grids restart the wave per row instead of queueing. */
        .st-js .stt-boutique-stagger > .st-reveal.is-in:nth-child(6n+2) { transition-delay: 90ms; }
        .st-js .stt-boutique-stagger > .st-reveal.is-in:nth-child(6n+3) { transition-delay: 180ms; }
        .st-js .stt-boutique-stagger > .st-reveal.is-in:nth-child(6n+4) { transition-delay: 270ms; }
        .st-js .stt-boutique-stagger > .st-reveal.is-in:nth-child(6n+5) { transition-delay: 360ms; }
        .st-js .stt-boutique-stagger > .st-reveal.is-in:nth-child(6n)   { transition-delay: 450ms; }

        /* --- Signature 1: gold mark draws in after the copy fades ------ */
        .st-js .st-reveal .stt-boutique-mark {
            transform: scaleX(0); transform-origin: left center;
            transition: transform .9s cubic-bezier(.2,.7,.2,1) .25s;
        }
        .st-js .st-reveal.is-in .stt-boutique-mark { transform: scaleX(1); }
        .st-js .stt-boutique-head-center .stt-boutique-mark { transform-origin: center; }

        /* --- Signature 2: gold hairline draws under nav links on hover -- */
        .stt-boutique-navlink { position: relative; }
        .stt-boutique-navlink::after {
            content: ""; position: absolute;
            left: 0.9rem; right: 0.9rem; bottom: 0.5rem; height: 1px;
            background: var(--st-accent);
            transform: scaleX(0); transform-origin: left center;
            transition: transform .35s cubic-bezier(.2,.7,.2,1);
        }
        .stt-boutique-navlink:hover::after,
        .stt-boutique-navlink:focus-visible::after { transform: scaleX(1); }

        /* --- Signature 3: hero / lookbook photography settles slowly ---- */
        /* Split-hero & feature plates ease from a gentle 1.06 crop to rest
           as they reveal (media boxes clip via overflow: hidden). */
        .st-js .st-reveal .stt-boutique-hero-media img,
        .st-js .stt-boutique-hero-media.st-reveal img {
            transform: scale(1.06);
            transition: transform 1.8s cubic-bezier(.2,.7,.2,1);
        }
        .st-js .st-reveal.is-in .stt-boutique-hero-media img,
        .st-js .stt-boutique-hero-media.st-reveal.is-in img { transform: scale(1); }
        /* Full-bleed hero: the eager first plate settles once on load.
           Later (lazy) slides are left alone so the crossfade stays pure. */
        @keyframes stt-boutique-settle { from { transform: scale(1.05); } to { transform: none; } }
        .st-js .stt-boutique-hero-bleed > img:first-of-type {
            animation: stt-boutique-settle 2.2s cubic-bezier(.2,.7,.2,1) both;
        }

        /* --- Reduced motion: everything above collapses to static ------- */
        @media (prefers-reduced-motion: reduce) {
            .stt-boutique-stagger > .st-reveal.is-in { transition-delay: 0s !important; }
            .st-js .st-reveal .stt-boutique-mark,
            .st-js .st-reveal .stt-boutique-hero-media img,
            .st-js .stt-boutique-hero-media.st-reveal img {
                transform: none !important; transition: none !important;
            }
            .st-js .stt-boutique-hero-bleed > img:first-of-type { animation: none !important; }
            .stt-boutique-navlink::after,
            .stt-boutique-link::after { transition: none !important; }
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @include('theme::partials.tracking-scripts')
</head>
<body class="storefront min-h-screen pb-20 antialiased lg:pb-0">
    @includeWhen($theme['show_announcement'] ?? true, 'theme::partials.announcement')
    @include('theme::partials.header')

    <main>
        @yield('content')
    </main>

    @include('theme::partials.footer')
    <x-storefront-addon-links />
    @include('theme::partials.mobile-bottom-nav', ['bp' => 'lg', 'r' => 6])

    <livewire:catalog.quick-view />

    @if (session('flash_toast'))
        <script>
            window.addEventListener('load', () => window.toast?.(@json(session('flash_toast')), @json(session('flash_toast_type', 'info'))));
        </script>
    @endif

    @includeWhen(app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->installed('cookie-consent'), 'cookieconsent::cookie-consent')

    @include('theme::partials.toasts')

    @livewireScripts
</body>
</html>
