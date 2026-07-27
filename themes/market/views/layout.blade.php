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
            --st-surface: {!! $cssToken($theme['surface'] ?? null, '#f7f7f5') !!};
            --st-ink: {!! $cssToken($theme['ink'] ?? null, '#2b2b2b') !!};
            --st-ink-soft: {!! $cssToken($theme['ink_soft'] ?? null, '#6b6b63') !!};
            --st-line: {!! $cssToken($theme['line'] ?? null, '#e9e9e4') !!};
            --st-primary: {!! $cssToken($theme['primary'] ?? null, '#1d4ed8') !!};
            --st-primary-ink: {!! $cssToken($theme['primary_ink'] ?? null, '#ffffff') !!};
            --st-accent: {!! $cssToken($theme['accent'] ?? null, '#e8503a') !!};
            --st-radius: {!! $cssToken($theme['radius'] ?? null, '14px') !!};
            /* Small radius follows the theme so inner-page controls match its shape. */
            --st-radius-sm: calc(var(--st-radius) * 0.6);
            --st-font-display: {!! $cssToken($theme['display_font'] ?? null, "'Inter Variable', ui-sans-serif, system-ui, sans-serif") !!};
            --st-font-body: {!! $cssToken($theme['body_font'] ?? null, "'Inter Variable', ui-sans-serif, system-ui, sans-serif") !!};
        }
    </style>

    {{-- ============================================================
         Marketplace (WoodMart mould) — cross-page design backbone.
         All classes prefixed .stt-market-. Colors come from --st-* tokens
         so the customizer keeps working; hex only where the brand demands
         a fixed red on a fixed white (badges).
         ============================================================ --}}
    <style>
        /* --- Section rhythm & alternating fields --- */
        .stt-market-section { padding-block: 3.5rem; }
        @media (min-width: 640px) { .stt-market-section { padding-block: 5rem; } }
        .stt-market-section--surface { background: var(--st-surface); }
        .stt-market-section--bg { background: var(--st-bg); }

        /* --- Boxed module: the universal hairline frame --- */
        .stt-market-box {
            background: var(--st-bg);
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
        }
        /* lift variant: hover/focus shadow for boxed links (blog cards etc.) */
        .stt-market-box--lift { transition: box-shadow .2s, border-color .2s; }
        .stt-market-box--lift:hover, .stt-market-box--lift:focus-visible {
            box-shadow: 0 10px 30px -12px rgba(0,0,0,0.25);
            border-color: color-mix(in srgb, var(--st-primary) 35%, var(--st-line));
        }

        /* Focus ring follows the theme's brand blue, not the admin brand token. */
        .storefront :focus-visible { outline-color: var(--st-primary); }

        /* --- Section header row: eyebrow · title · rule · view-all --- */
        .stt-market-shead {
            display: flex; align-items: flex-end; justify-content: space-between;
            gap: 1rem; margin-bottom: 2rem;
        }
        @media (min-width: 640px) { .stt-market-shead { margin-bottom: 2.5rem; } }
        .stt-market-eyebrow {
            font-size: 0.6875rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.16em; color: var(--st-accent);
            margin-bottom: 0.5rem;
        }
        .stt-market-title {
            font-family: var(--st-font-display);
            font-size: 1.375rem; font-weight: 700; letter-spacing: -0.01em;
            line-height: 1.15; color: var(--st-ink);
        }
        @media (min-width: 640px) { .stt-market-title { font-size: 1.75rem; } }
        /* short red underline rule under a title */
        .stt-market-rule {
            display: block; height: 2px; width: 3rem;
            margin-top: 0.75rem; background: var(--st-accent);
        }
        /* alternative: red left accent bar beside a title */
        .stt-market-title--bar { padding-left: 0.75rem; border-left: 3px solid var(--st-accent); }
        .stt-market-viewall {
            display: inline-flex; align-items: center; gap: 0.375rem;
            flex-shrink: 0; font-size: 0.8125rem; font-weight: 600;
            color: var(--st-primary); transition: opacity .2s;
        }
        .stt-market-viewall:hover { opacity: 0.6; }

        /* --- Buttons: squared, brand blue, uppercase --- */
        .stt-market-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.625rem 1rem; border-radius: var(--st-radius);
            font-size: 0.75rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.06em; line-height: 1;
            background: var(--st-primary); color: var(--st-primary-ink);
            border: 1px solid var(--st-primary);
            transition: background .2s, opacity .2s;
        }
        /* hover deepens the blue — red stays reserved for sale/deal signals */
        .stt-market-btn:hover {
            background: color-mix(in srgb, var(--st-primary) 82%, #000);
            border-color: color-mix(in srgb, var(--st-primary) 82%, #000);
        }
        .stt-market-btn--lg { padding: 0.875rem 2rem; font-size: 0.8125rem; }
        .stt-market-btn--block { width: 100%; }
        .stt-market-btn--outline {
            background: transparent; color: var(--st-ink);
            border-color: var(--st-line);
        }
        .stt-market-btn--outline:hover { background: transparent; border-color: var(--st-primary); color: var(--st-primary); }
        .stt-market-btn--ghostlink {
            background: transparent; border: none; color: var(--st-primary);
            border-bottom: 2px solid var(--st-primary); border-radius: 0;
            padding: 0 0 0.125rem;
        }
        .stt-market-btn--ghostlink:hover { background: transparent; opacity: 0.6; }

        /* --- Badges --- */
        .stt-market-badge {
            position: absolute; top: 0.625rem; left: 0.625rem;
            padding: 0.125rem 0.5rem; border-radius: var(--st-radius);
            font-size: 0.6875rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.04em; line-height: 1.4;
        }
        .stt-market-badge--sale { background: var(--st-accent); color: #fff; }
        .stt-market-badge--out  { background: var(--st-surface); color: var(--st-ink); border: 1px solid var(--st-line); }
        /* small red discount chip, e.g. -20% */
        .stt-market-chip-off {
            display: inline-block; padding: 0.0625rem 0.375rem;
            border-radius: var(--st-radius);
            font-size: 0.6875rem; font-weight: 700;
            background: color-mix(in srgb, var(--st-accent) 12%, var(--st-bg));
            color: var(--st-accent);
        }

        /* --- Prices --- */
        .stt-market-price { font-size: 1rem; font-weight: 700; color: var(--st-ink); }
        .stt-market-price--lg { font-size: 1.5rem; }
        .stt-market-price-was { font-size: 0.8125rem; text-decoration: line-through; color: var(--st-ink-soft); margin-left: 0.375rem; }
        .stt-market-price-save { font-size: 0.75rem; font-weight: 700; color: var(--st-accent); }
        .stt-market-lowstock { font-size: 0.75rem; font-weight: 700; color: var(--st-accent); }

        /* --- Category tile (always shows a product count) --- */
        .stt-market-tile {
            display: block; overflow: hidden; text-align: center;
            background: var(--st-bg); border: 1px solid var(--st-line);
            border-radius: var(--st-radius); transition: box-shadow .2s;
        }
        .stt-market-tile:hover { box-shadow: 0 10px 30px -12px rgba(0,0,0,0.25); }
        .stt-market-tile-media { aspect-ratio: 1/1; background: var(--st-surface); overflow: hidden; }
        .stt-market-tile-media img { height: 100%; width: 100%; object-fit: cover; transition: transform .5s; }
        .stt-market-tile:hover .stt-market-tile-media img { transform: scale(1.05); }
        .stt-market-tile-name { font-size: 0.875rem; font-weight: 600; color: var(--st-ink); }
        .stt-market-tile:hover .stt-market-tile-name { color: var(--st-primary); }
        .stt-market-tile-count { margin-top: 0.25rem; font-size: 0.75rem; color: var(--st-ink-soft); }

        /* --- Left category sidebar (homepage banner + shop) --- */
        .stt-market-sidebar {
            background: var(--st-bg); border: 1px solid var(--st-line);
            border-radius: var(--st-radius); overflow: hidden;
        }
        .stt-market-sidebar-head {
            padding: 0.75rem 1rem; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: var(--st-primary-ink); background: var(--st-primary);
        }
        .stt-market-sidebar-link {
            display: flex; align-items: center; justify-content: space-between;
            padding: 0.625rem 1rem; font-size: 0.875rem; color: var(--st-ink);
            border-top: 1px solid var(--st-line); transition: background .15s, color .15s;
        }
        .stt-market-sidebar-link:first-child { border-top: none; }
        .stt-market-sidebar-link:hover { background: var(--st-surface); color: var(--st-primary); }

        /* --- Homepage banner slides: squared-split height presets + responsive media/panel --- */
        .stt-market-slide { min-height: 320px; }
        .stt-market-slide--compact { min-height: 280px; }
        .stt-market-slide--tall { min-height: 400px; }
        .stt-market-slide-media { height: 11rem; }
        .stt-market-slide-panel { padding: 1.5rem; }
        @media (min-width: 640px) {
            .stt-market-slide { min-height: 440px; }
            .stt-market-slide--compact { min-height: 340px; }
            .stt-market-slide--tall { min-height: 520px; }
            .stt-market-slide-media { height: 100%; }
            .stt-market-slide-panel { padding: 2.5rem; }
        }

        /* --- USP / trust strip --- */
        .stt-market-usp {
            padding-block: 2.5rem; background: var(--st-surface);
            border-block: 1px solid var(--st-line);
        }
        .stt-market-usp-item { display: flex; align-items: center; gap: 0.75rem; }
        /* blue-tinted squared tile; holds either the built-in SVG or an uploaded icon image */
        .stt-market-usp-ico {
            display: grid; place-items: center; height: 2.75rem; width: 2.75rem; flex-shrink: 0;
            border-radius: var(--st-radius);
            background: color-mix(in srgb, var(--st-primary) 10%, transparent);
            color: var(--st-primary);
        }
        .stt-market-usp-ico img { height: 1.75rem; width: 1.75rem; object-fit: contain; }
        .stt-market-usp-label { font-size: 0.875rem; font-weight: 600; line-height: 1.25; color: var(--st-ink); }
        /* ≥768px the four cells butt together spec-sheet style, split by hairlines */
        @media (min-width: 768px) {
            .stt-market-usp-grid { gap: 0; }
            .stt-market-usp-item { padding-inline: 1.5rem; }
            .stt-market-usp-item:not(:first-child) { border-left: 1px solid var(--st-line); }
        }

        /* --- Tabbed section headers --- */
        .stt-market-tabs { display: flex; gap: 1.5rem; border-bottom: 1px solid var(--st-line); }
        .stt-market-tab {
            padding-bottom: 0.5rem; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.08em;
            color: var(--st-ink-soft); border-bottom: 2px solid transparent; margin-bottom: -1px;
            transition: color .15s;
        }
        .stt-market-tab[aria-selected="true"], .stt-market-tab.is-active {
            color: var(--st-ink); border-bottom-color: var(--st-primary);
        }

        /* --- Squared form controls (search, inputs, steppers, swatches) --- */
        .stt-market-field {
            display: flex; align-items: stretch; overflow: hidden;
            border: 2px solid var(--st-primary); border-radius: var(--st-radius);
        }
        .stt-market-field input { flex: 1; background: transparent; padding: 0 1rem; font-size: 0.875rem; color: var(--st-ink); }
        .stt-market-field input:focus { outline: none; }
        .stt-market-field button { padding: 0 1.25rem; font-weight: 700; background: var(--st-primary); color: var(--st-primary-ink); }
        .stt-market-stepper {
            display: inline-flex; align-items: center;
            border: 1px solid var(--st-line); border-radius: var(--st-radius);
        }
        .stt-market-swatch {
            padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500;
            border: 1px solid var(--st-line); border-radius: var(--st-radius); color: var(--st-ink);
        }
        .stt-market-swatch.is-active { background: var(--st-primary); color: var(--st-primary-ink); border-color: var(--st-primary); }

        /* --- Breadcrumb --- */
        .stt-market-crumbs { font-size: 0.8125rem; color: var(--st-ink-soft); }
        .stt-market-crumbs a:hover { opacity: 0.7; }
        .stt-market-crumbs .sep { margin-inline: 0.375rem; }

        /* --- Utility bar (brand blue) used by header/announcement promos --- */
        .stt-market-utilbar { background: var(--st-primary); color: var(--st-primary-ink); font-size: 0.75rem; }

        /* --- Carousel arrow buttons: hairline boxes whose border flips blue on hover --- */
        .stt-market-nav-btn:hover { border-color: var(--st-primary); color: var(--st-primary); }

        /* --- Hairline vertical dividers between grid columns (PDP trust row) --- */
        .stt-market-divide-x > :not(:first-child) { border-left: 1px solid var(--st-line); }

        /* --- Responsive padding helpers (compiled Tailwind lacks these steps) --- */
        @media (min-width: 640px) {
            .stt-market-nl-box { padding-inline: 2.5rem; }   /* newsletter panel: sm:px-10 */
            .stt-market-page-pad { padding-block: 2.5rem; }  /* shop page bands: sm:py-10 */
        }

        /* ============================================================
           Motion — crisp retail: fast, precise, transform/opacity/
           border-color only. Everything is switched off wholesale in
           the prefers-reduced-motion block at the end.
           ============================================================ */

        /* Reveal tuning: the global .st-reveal ride (app.css) is a floaty .6s —
           Marketplace snaps in at .24s from a shorter 10px offset. Higher
           specificity than app.css's .st-js .st-reveal so it wins the cascade. */
        .st-js .storefront .st-reveal { transform: translateY(10px); }
        .st-js .storefront .st-reveal.is-in {
            transition-duration: .24s;
            transition-timing-function: cubic-bezier(.3,.7,.4,1);
        }

        /* Stagger: children of a .stt-market-stagger grid reveal with incremental
           delays — 50ms ticks, capped at the 6th cell so deep grids don't lag. */
        .st-js .stt-market-stagger > .st-reveal.is-in:nth-child(2) { transition-delay: .05s; }
        .st-js .stt-market-stagger > .st-reveal.is-in:nth-child(3) { transition-delay: .10s; }
        .st-js .stt-market-stagger > .st-reveal.is-in:nth-child(4) { transition-delay: .15s; }
        .st-js .stt-market-stagger > .st-reveal.is-in:nth-child(5) { transition-delay: .20s; }
        .st-js .stt-market-stagger > .st-reveal.is-in:nth-child(n+6) { transition-delay: .25s; }

        /* (1) Card snap: bordered product cards flip their hairline to brand blue
           with a quick blue-tinted shadow — 150ms, no lift, no scale. !important
           beats the card's inline border declaration on hover only. */
        .stt-market-stagger > .group { transition: border-color .15s ease, box-shadow .15s ease; }
        .stt-market-stagger > .group:hover, .stt-market-stagger > .group:focus-within {
            border-color: var(--st-primary) !important;
            box-shadow: 0 6px 16px -10px color-mix(in srgb, var(--st-primary) 45%, transparent);
        }
        /* Category tiles get the same border snap on top of their existing shadow. */
        .stt-market-tile { transition: box-shadow .15s ease, border-color .15s ease; }
        .stt-market-tile:hover { border-color: color-mix(in srgb, var(--st-primary) 55%, var(--st-line)); }

        /* (2) Banner dot tick: the freshly-active dot snaps in like a counter
           digit — one quick 250ms overshoot, then settles. */
        @keyframes stt-market-dot-tick {
            0% { transform: scale(.55, 1.35); }
            70% { transform: scale(1.12, 1); }
            100% { transform: scale(1, 1); }
        }
        button[aria-current="true"] .stt-market-dot { animation: stt-market-dot-tick .25s cubic-bezier(.3,.7,.4,1); }

        /* (3) Deal-badge attention pulse: once the card has revealed, the red
           sale badge scales up twice and STOPS — never infinite on sale signals. */
        @keyframes stt-market-badge-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.09); }
        }
        .st-js .stt-market-stagger .st-reveal.is-in span[style*="background: var(--st-accent)"] {
            animation: stt-market-badge-pulse .4s ease-in-out .7s 2;
        }

        /* (4) View-all arrow nudge: 3px slide on hover, 200ms. */
        .stt-market-viewall svg { transition: transform .2s cubic-bezier(.3,.7,.4,1); }
        .stt-market-viewall:hover svg, .stt-market-viewall:focus-visible svg { transform: translateX(3px); }

        /* ============================================================
           Reduced motion — covers BOTH the new motion above and every
           pre-existing stt-market transition/animation (audit: this
           block previously had no prefers-reduced-motion coverage).
           ============================================================ */
        @media (prefers-reduced-motion: reduce) {
            /* Reveal + stagger: show content instantly, no delays. */
            .st-js .storefront .st-reveal,
            .st-js .storefront .st-reveal.is-in { transition: none !important; transform: none !important; opacity: 1 !important; }
            .st-js .stt-market-stagger > .st-reveal.is-in { transition-delay: 0s !important; }

            /* All stt-market transitions (new and pre-existing). */
            .stt-market-box--lift, .stt-market-tile, .stt-market-tile-media img,
            .stt-market-btn, .stt-market-viewall, .stt-market-viewall svg,
            .stt-market-sidebar-link, .stt-market-tab, .stt-market-nav-btn,
            .stt-market-stagger > .group, .stt-market-dot { transition: none !important; }

            /* Hover transforms: no image zoom, no arrow nudge. */
            .stt-market-tile:hover .stt-market-tile-media img,
            .stt-market-viewall:hover svg, .stt-market-viewall:focus-visible svg { transform: none !important; }

            /* Keyframe animations: dot tick and badge pulse never run. */
            button[aria-current="true"] .stt-market-dot,
            .st-js .stt-market-stagger .st-reveal.is-in span[style*="background: var(--st-accent)"] { animation: none !important; }

            /* Banner track: slides cut instead of gliding (autoplay is already
               disabled by the carousel's own reduced-motion check). */
            .storefront [aria-roledescription="carousel"] .transition-transform { transition: none !important; }
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
            font-size: .64rem; font-weight: 600;
            text-decoration: none; background: none; border: none; cursor: pointer; font-family: inherit;
        }
        .stt-bottom-nav-icon-wrap{ position: relative; display: inline-flex; }
        .stt-bottom-nav-badge{
            position: absolute; top: -.3rem; right: -.5rem;
            display: grid; place-items: center;
            height: 1rem; min-width: 1rem; padding-inline: .2rem;
            border-radius: 2px; font-size: 9px; font-weight: 700;
            background: var(--st-accent); color: #fff;
        }
        .stt-bottom-nav-dot{
            position: absolute; bottom: .15rem; width: .3rem; height: .3rem;
            border-radius: 999px; background: var(--st-primary);
        }
        .stt-bottom-nav-cta{
            position: relative; z-index: 2; flex: 0 0 auto; align-self: flex-start;
            width: 3.5rem; height: 3.5rem; margin-top: -1.75rem;
            display: grid; place-items: center;
            border-radius: 999px; border: 3px solid var(--st-bg);
            background: var(--st-primary); color: #fff; cursor: pointer;
            box-shadow: 0 6px 16px color-mix(in srgb, var(--st-primary) 45%, transparent), 0 2px 6px rgba(0,0,0,.15);
        }
        .stt-bottom-nav-badge--cta{ top: -.15rem; right: -.15rem; }
        @media (min-width: 1024px){ .stt-bottom-nav.lg\:hidden{ display: none; } }
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
    @include('theme::partials.mobile-bottom-nav', ['bp' => 'lg', 'r' => 14])

    <livewire:catalog.quick-view />

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
