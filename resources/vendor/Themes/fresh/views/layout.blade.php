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
            --st-ink: {!! $cssToken($theme['ink'] ?? null, '#14140f') !!};
            --st-ink-soft: {!! $cssToken($theme['ink_soft'] ?? null, '#6b6b63') !!};
            --st-line: {!! $cssToken($theme['line'] ?? null, '#e9e9e4') !!};
            --st-primary: {!! $cssToken($theme['primary'] ?? null, '#14140f') !!};
            --st-primary-ink: {!! $cssToken($theme['primary_ink'] ?? null, '#ffffff') !!};
            --st-accent: {!! $cssToken($theme['accent'] ?? null, '#e8503a') !!};
            --st-radius: {!! $cssToken($theme['radius'] ?? null, '14px') !!};
            /* Small radius follows the theme so inner-page controls match its shape. */
            --st-radius-sm: calc(var(--st-radius) * 0.6);
            --st-font-display: {!! $cssToken($theme['display_font'] ?? null, "'Fraunces Variable', Georgia, serif") !!};
            --st-font-body: {!! $cssToken($theme['body_font'] ?? null, "'Inter Variable', ui-sans-serif, system-ui, sans-serif") !!};
        }
    </style>

    {{-- ============================================================
         Fresh (Bloom) — themed "chrome" backbone. Made available on
         EVERY page (including default-body fallbacks) so the whole
         storefront reads as one warm, rounded farmers-market shop.
         All classes are prefixed .stt-fresh- and driven by --st-*
         tokens so the customizer keeps working.
         ============================================================ --}}
    <style>
        /* --- Frosted header glass. Blur lives on a ::before layer, NOT on the
               header element: backdrop-filter on the header would make it the
               containing block for fixed descendants, trapping the cart drawer
               and mobile drawer (rendered inside <header>) in the header's box. --- */
        .stt-fresh-glass { isolation: isolate; }
        .stt-fresh-glass::before {
            content: ""; position: absolute; inset: 0; z-index: -1;
            -webkit-backdrop-filter: blur(10px); backdrop-filter: blur(10px);
        }

        /* --- Rhythm & crate panels ------------------------------------ */
        .stt-fresh-section { padding-block: clamp(3rem, 7vw, 5.5rem); }
        .stt-fresh-section--tight { padding-block: clamp(2.5rem, 5vw, 4rem); }
        .stt-fresh-band-surface { background: var(--st-surface); }
        .stt-fresh-band-bg { background: var(--st-bg); }
        .stt-fresh-panel {
            background: var(--st-surface);
            border: 1px solid var(--st-line);
            border-radius: 28px;
            padding: clamp(1.5rem, 4vw, 3rem);
        }

        /* --- Typography ---------------------------------------------- */
        .stt-fresh-heading {
            font-family: var(--st-font-display);
            font-weight: 600;
            line-height: 1.05;
            letter-spacing: -0.01em;
            color: var(--st-ink);
            font-optical-sizing: auto;
        }
        .stt-fresh-heading em {
            font-style: italic;
            color: var(--st-accent);
        }
        .stt-fresh-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-family: var(--st-font-body);
            font-weight: 700;
            font-size: 0.72rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--st-accent);
        }
        /* Signature petal/leaf glyph before every eyebrow */
        .stt-fresh-eyebrow::before {
            content: "";
            width: 0.72em;
            height: 0.72em;
            background: currentColor;
            border-radius: 0 100% 0 100%;
            transform: rotate(45deg);
            flex: none;
        }

        /* --- Buttons (always fully-rounded pills) --------------------- */
        .stt-fresh-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: var(--st-font-body);
            font-weight: 700;
            font-size: 0.9rem;
            line-height: 1;
            padding: 0.95rem 2rem;
            border-radius: 999px;
            background: var(--st-primary);
            color: var(--st-primary-ink);
            box-shadow: 0 8px 20px -8px color-mix(in srgb, var(--st-primary) 55%, transparent);
            transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
            cursor: pointer;
        }
        /* Basket-button pop: a friendly lift-and-swell on hover… */
        .stt-fresh-btn:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 14px 26px -8px color-mix(in srgb, var(--st-primary) 65%, transparent);
        }
        /* …and a quick pill "press" so taps feel springy (fast in, eased out). */
        .stt-fresh-btn:active {
            transform: translateY(0) scale(.96);
            box-shadow: 0 4px 10px -6px color-mix(in srgb, var(--st-primary) 55%, transparent);
            transition-duration: .08s;
        }
        .stt-fresh-btn--soft {
            background: color-mix(in srgb, var(--st-primary) 12%, var(--st-bg));
            color: var(--st-primary);
            box-shadow: none;
        }
        .stt-fresh-btn--soft:hover {
            background: color-mix(in srgb, var(--st-primary) 18%, var(--st-bg));
            box-shadow: none;
        }
        .stt-fresh-btn--accent {
            background: var(--st-accent);
            color: #fff;
            box-shadow: 0 8px 20px -8px color-mix(in srgb, var(--st-accent) 55%, transparent);
        }
        .stt-fresh-btn--block { width: 100%; }
        .stt-fresh-btn--sm { padding: 0.6rem 1.25rem; font-size: 0.8rem; }

        /* "View all →" pill */
        .stt-fresh-viewall {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-family: var(--st-font-body);
            font-weight: 600;
            font-size: 0.82rem;
            padding: 0.5rem 1.1rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--st-primary) 10%, var(--st-bg));
            color: var(--st-primary);
            transition: gap .2s ease, background .2s ease;
        }
        .stt-fresh-viewall:hover { gap: 0.7rem; background: color-mix(in srgb, var(--st-primary) 16%, var(--st-bg)); }

        /* --- Category chips & round tiles ----------------------------- */
        .stt-fresh-chip {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            font-family: var(--st-font-body);
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.5rem 1.15rem;
            border-radius: 999px;
            background: var(--st-surface);
            color: var(--st-ink);
            border: 1.5px solid var(--st-line);
            transition: color .18s ease, border-color .18s ease, background .18s ease, transform .18s ease;
        }
        .stt-fresh-chip:active { transform: scale(.94); transition-duration: .08s; }
        .stt-fresh-chip:hover {
            color: var(--st-primary);
            border-color: color-mix(in srgb, var(--st-primary) 45%, transparent);
            background: color-mix(in srgb, var(--st-primary) 8%, var(--st-bg));
        }
        .stt-fresh-chip--active {
            color: var(--st-primary);
            background: color-mix(in srgb, var(--st-primary) 12%, var(--st-bg));
            border-color: color-mix(in srgb, var(--st-primary) 45%, transparent);
        }

        /* Round category circle */
        .stt-fresh-circle {
            display: grid;
            place-items: center;
            aspect-ratio: 1;
            width: 100%;
            border-radius: 999px;
            overflow: hidden;
            background: var(--st-surface);
            border: 1px solid var(--st-line);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        }
        .stt-fresh-circle img { width: 100%; height: 100%; object-fit: cover; }
        .stt-fresh-circle-link:hover .stt-fresh-circle {
            transform: translateY(-4px);
            border-color: color-mix(in srgb, var(--st-primary) 40%, transparent);
            box-shadow: 0 12px 24px -12px color-mix(in srgb, var(--st-primary) 55%, transparent);
        }
        .stt-fresh-circle-label {
            font-family: var(--st-font-body);
            font-weight: 600;
            font-size: 0.78rem;
            color: var(--st-ink);
            text-align: center;
        }

        /* Price */
        .stt-fresh-price { font-family: var(--st-font-body); font-weight: 700; color: var(--st-ink); }
        .stt-fresh-price-was { font-size: 0.85em; text-decoration: line-through; color: var(--st-ink-soft); margin-left: 0.4rem; font-weight: 500; }
        .stt-fresh-price--lg { font-family: var(--st-font-display); font-weight: 600; font-size: clamp(1.6rem, 3vw, 2rem); }

        /* --- Badges --------------------------------------------------- */
        .stt-fresh-badge {
            display: inline-flex;
            align-items: center;
            font-family: var(--st-font-body);
            font-weight: 700;
            font-size: 0.68rem;
            letter-spacing: 0.02em;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            background: var(--st-accent);
            color: #fff;
        }
        .stt-fresh-badge--out { background: var(--st-ink); color: var(--st-bg); }
        .stt-fresh-badge--soft { background: color-mix(in srgb, var(--st-primary) 14%, var(--st-bg)); color: var(--st-primary); }

        /* --- Natural-food seal (dashed stamp) ------------------------- */
        .stt-fresh-seal {
            display: grid;
            place-items: center;
            aspect-ratio: 1;
            border-radius: 999px;
            border: 2px dashed color-mix(in srgb, var(--st-primary) 45%, transparent);
            color: var(--st-primary);
            font-family: var(--st-font-display);
            font-weight: 600;
            text-align: center;
            line-height: 1.05;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            transform: rotate(-6deg);
            background: color-mix(in srgb, var(--st-primary) 5%, var(--st-bg));
        }

        /* --- Coupon ticket (perforated) ------------------------------- */
        .stt-fresh-coupon {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.85rem 1.4rem;
            border-radius: 14px;
            background: var(--st-bg);
            border: 2px dashed color-mix(in srgb, var(--st-primary) 40%, transparent);
            font-family: var(--st-font-body);
            font-weight: 600;
            color: var(--st-ink);
            /* notch cut-outs on left & right edges */
            -webkit-mask:
                radial-gradient(circle 9px at 0 50%, transparent 98%, #000) left,
                radial-gradient(circle 9px at 100% 50%, transparent 98%, #000) right;
            -webkit-mask-size: 51% 100%;
            -webkit-mask-repeat: no-repeat;
            mask:
                radial-gradient(circle 9px at 0 50%, transparent 98%, #000) left,
                radial-gradient(circle 9px at 100% 50%, transparent 98%, #000) right;
            mask-size: 51% 100%;
            mask-repeat: no-repeat;
        }
        .stt-fresh-coupon-code {
            font-family: var(--st-font-display);
            font-weight: 600;
            font-size: 1.05em;
            color: var(--st-accent);
            letter-spacing: 0.02em;
        }

        /* --- USP / reassurance tile ----------------------------------- */
        .stt-fresh-usp {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.6rem;
            text-align: center;
        }
        .stt-fresh-usp-icon {
            display: grid;
            place-items: center;
            width: 3.25rem;
            height: 3.25rem;
            border-radius: 999px;
            background: color-mix(in srgb, var(--st-primary) 12%, var(--st-bg));
            color: var(--st-primary);
        }
        .stt-fresh-usp-label { font-family: var(--st-font-body); font-weight: 600; font-size: 0.85rem; color: var(--st-ink); }

        /* --- Hero field, blobs, rings, leaves ------------------------- */
        .stt-fresh-hero {
            position: relative;
            overflow: hidden;
            background:
                radial-gradient(120% 120% at 80% 0%, color-mix(in srgb, var(--st-primary) 10%, var(--st-bg)) 0%, transparent 60%),
                color-mix(in srgb, var(--st-primary) 8%, var(--st-bg));
            color: var(--st-ink);
        }
        .stt-fresh-blob {
            position: absolute;
            border-radius: 999px;
            filter: blur(2px);
            pointer-events: none;
            background: radial-gradient(closest-side, color-mix(in srgb, var(--st-primary) 22%, var(--st-bg)), transparent 72%);
            /* Signature motion: a very slow, barely-there drift so the hero feels
               alive, like light through foliage. Uses the standalone translate/scale
               properties (NOT transform) so blobs positioned with an inline
               transform (e.g. translateX(-50%)) keep their placement. */
            animation: stt-fresh-drift 18s ease-in-out infinite;
        }
        .stt-fresh-blob:nth-child(even) { animation-duration: 24s; animation-delay: -12s; }
        @keyframes stt-fresh-drift {
            0%, 100% { translate: 0 0; scale: 1; }
            50% { translate: 1.25rem -1rem; scale: 1.06; }
        }
        .stt-fresh-ring {
            position: relative;
            display: grid;
            place-items: center;
            aspect-ratio: 1;
            border-radius: 999px;
        }
        .stt-fresh-ring::before {
            content: "";
            position: absolute;
            inset: 6%;
            border-radius: 999px;
            background: radial-gradient(closest-side, var(--st-bg), color-mix(in srgb, var(--st-primary) 6%, var(--st-bg)));
        }
        .stt-fresh-ring::after {
            content: "";
            position: absolute;
            inset: 6%;
            border-radius: 999px;
            border: 2px dashed color-mix(in srgb, var(--st-primary) 30%, transparent);
        }
        .stt-fresh-ring > * { position: relative; z-index: 1; }
        /* Decorative petal/leaf glyph */
        .stt-fresh-leaf {
            position: absolute;
            background: color-mix(in srgb, var(--st-primary) 22%, transparent);
            border-radius: 0 100% 0 100%;
            pointer-events: none;
            /* Signature motion: a gentle breeze-sway. The standalone rotate
               property composes with each leaf's inline transform rotation,
               so every glyph sways around its own designed angle. */
            animation: stt-fresh-sway 7s ease-in-out infinite;
        }
        .stt-fresh-leaf:nth-child(odd) { animation-duration: 9s; animation-delay: -3s; }
        @keyframes stt-fresh-sway {
            0%, 100% { rotate: 0deg; }
            50% { rotate: 9deg; }
        }

        /* --- Organic divider ----------------------------------------- */
        .stt-fresh-divider {
            height: 1px;
            border: 0;
            background: repeating-linear-gradient(
                to right,
                color-mix(in srgb, var(--st-primary) 35%, transparent) 0 8px,
                transparent 8px 16px
            );
        }

        /* --- Rounded pill input (search / fields) --------------------- */
        .stt-fresh-input {
            display: flex;
            align-items: stretch;
            overflow: hidden;
            border-radius: 999px;
            background: var(--st-surface);
            border: 1.5px solid var(--st-line);
        }
        .stt-fresh-input:focus-within { border-color: color-mix(in srgb, var(--st-primary) 45%, transparent); }

        /* --- Round icon button (header actions) ----------------------- */
        .stt-fresh-iconbtn {
            display: grid;
            place-items: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 999px;
            color: var(--st-ink);
            transition: background .15s ease, transform .15s ease;
        }
        .stt-fresh-iconbtn:hover { background: color-mix(in srgb, var(--st-primary) 8%, transparent); }
        .stt-fresh-iconbtn:active { transform: scale(.92); transition-duration: .08s; }
        /* display:grid above is unlayered so it beats Tailwind's layered lg:hidden;
           re-assert the responsive hide for icon buttons marked with it. */
        @media (min-width: 1024px) { .stt-fresh-iconbtn.lg\:hidden { display: none; } }

        /* --- Rounded quantity stepper (product page) ------------------ */
        .stt-fresh-stepper {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1.5px solid var(--st-line);
            overflow: hidden;
        }
        .stt-fresh-stepper button {
            display: grid;
            place-items: center;
            width: 3rem;
            height: 3rem;
            font-size: 1.1rem;
            color: var(--st-ink);
            transition: background .15s ease;
        }
        .stt-fresh-stepper button:hover { background: color-mix(in srgb, var(--st-primary) 8%, transparent); }

        /* --- Copyable coupon ticket (hero) ----------------------------- */
        button.stt-fresh-coupon { cursor: pointer; transition: border-color .2s ease, transform .2s ease; }
        button.stt-fresh-coupon:hover {
            border-color: color-mix(in srgb, var(--st-primary) 70%, transparent);
            transform: translateY(-1px);
        }
        /* The ticket's notch mask clips outlines, so focus reads via the border. */
        button.stt-fresh-coupon:focus-visible {
            outline: none;
            border-color: var(--st-primary);
        }

        /* --- Keyboard focus (mirrors each hover affordance) ------------ */
        .stt-fresh-btn:focus-visible,
        .stt-fresh-chip:focus-visible,
        .stt-fresh-viewall:focus-visible,
        .stt-fresh-iconbtn:focus-visible,
        .stt-fresh-circle-link:focus-visible {
            outline: 2px solid var(--st-primary);
            outline-offset: 2px;
        }
        /* The stepper & pill input clip overflow, so inner focus draws inset. */
        .stt-fresh-stepper button:focus-visible {
            outline: 2px solid var(--st-primary);
            outline-offset: -4px;
        }
        .stt-fresh-input button:focus-visible {
            outline: 2px solid var(--st-primary-ink);
            outline-offset: -4px;
        }

        /* --- Organic stagger: grid children ripen in one-by-one -------- */
        /* Add .stt-fresh-stagger to an .st-reveal grid: the container stops
           animating itself and its children pop in with a soft spring, each a
           beat after the last (delays cap after the 6th so long grids never lag). */
        .st-js .stt-fresh-stagger.st-reveal { opacity: 1; transform: none; }
        .st-js .stt-fresh-stagger.st-reveal > * { opacity: 0; transform: translateY(18px) scale(.96); }
        .st-js .stt-fresh-stagger.st-reveal.is-in > * {
            opacity: 1; transform: none;
            transition: opacity .55s cubic-bezier(.2,.7,.2,1), transform .55s cubic-bezier(.34,1.4,.4,1);
        }
        .st-js .stt-fresh-stagger.st-reveal.is-in > *:nth-child(2) { transition-delay: .07s; }
        .st-js .stt-fresh-stagger.st-reveal.is-in > *:nth-child(3) { transition-delay: .14s; }
        .st-js .stt-fresh-stagger.st-reveal.is-in > *:nth-child(4) { transition-delay: .21s; }
        .st-js .stt-fresh-stagger.st-reveal.is-in > *:nth-child(5) { transition-delay: .28s; }
        .st-js .stt-fresh-stagger.st-reveal.is-in > *:nth-child(6) { transition-delay: .35s; }
        .st-js .stt-fresh-stagger.st-reveal.is-in > *:nth-child(n+7) { transition-delay: .42s; }

        /* --- Reduced motion: stop ALL Bloom animation & transitions -----
           The global block in app.css only neutralises .st-reveal; Bloom's own
           blob drift, leaf sway, hover lifts/pops and press feedback (the audit
           flagged the hover transforms) are silenced here. Static decorative
           transforms (seal tilt, leaf angles, centering translates) are inline
           styles, untouched by these rules, so the layout keeps its shape. */
        @media (prefers-reduced-motion: reduce) {
            .storefront.storefront [class*="stt-fresh"],
            .storefront.storefront [class*="stt-fresh"]::before,
            .storefront.storefront [class*="stt-fresh"]::after {
                animation: none !important;
                transition: none !important;
            }
            /* Hover/press transforms: no lift, swell or squash. */
            .stt-fresh-btn:hover, .stt-fresh-btn:active,
            .stt-fresh-chip:active, .stt-fresh-iconbtn:active,
            .stt-fresh-circle-link:hover .stt-fresh-circle,
            button.stt-fresh-coupon:hover {
                transform: none !important;
            }
            /* Staggered children must stay readable when the reveal is disabled. */
            .st-js .stt-fresh-stagger.st-reveal > * { opacity: 1 !important; transform: none !important; }
        }

        /* --- Compat: responsive steps absent from the frozen compiled CSS --- */
        .stt-fresh-seal-pos { bottom: 0.5rem; font-size: 0.6rem; }   /* bottom-2 text-[0.6rem] */
        @media (min-width: 640px) {
            /* sm:bottom-6 sm:right-4 sm:h-28 sm:w-28 sm:text-xs */
            .stt-fresh-seal-pos { bottom: 1.5rem; right: 1rem; height: 7rem; width: 7rem; font-size: 0.75rem; line-height: 1rem; }
            .stt-fresh-sm-pad { padding: 1.25rem; }                  /* sm:p-5 */
        }
        @media (min-width: 768px) {
            .stt-fresh-usp { padding-inline: 1.25rem; }              /* md:px-5 */
            .stt-fresh-usp--sep { border-left: 1px dashed; } /* md:border-l md:border-dashed; color set inline */
            .stt-fresh-usp-grid { column-gap: 1rem; }                /* md:gap-x-4 */
        }
        @media (min-width: 1024px) {
            .stt-fresh-title-lg { font-size: 3rem; line-height: 1; } /* lg:text-5xl */
            .stt-fresh-feature-grid { gap: 3.5rem; }                 /* lg:gap-14 */
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
            font-size: .66rem; font-weight: 700;
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
    @include('theme::partials.mobile-bottom-nav', ['bp' => 'lg', 'r' => 30])

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
