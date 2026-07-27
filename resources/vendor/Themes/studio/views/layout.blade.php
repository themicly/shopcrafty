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
            --st-surface: {!! $cssToken($theme['surface'] ?? null, '#f4f4f2') !!};
            --st-ink: {!! $cssToken($theme['ink'] ?? null, '#131313') !!};
            --st-ink-soft: {!! $cssToken($theme['ink_soft'] ?? null, '#6f6f6a') !!};
            --st-line: {!! $cssToken($theme['line'] ?? null, '#e4e4e0') !!};
            --st-primary: {!! $cssToken($theme['primary'] ?? null, '#131313') !!};
            --st-primary-ink: {!! $cssToken($theme['primary_ink'] ?? null, '#ffffff') !!};
            --st-accent: {!! $cssToken($theme['accent'] ?? null, '#44624a') !!};
            /* Studio signature: the pastel sage color band behind the hero,
               category plaques and newsletter panel. Background tone only —
               never a button fill. */
            --st-band: {!! $cssToken($theme['band'] ?? null, '#d3e4cd') !!};
            --st-radius: {!! $cssToken($theme['radius'] ?? null, '4px') !!};
            /* Small radius follows the theme so inner-page controls match its shape. */
            --st-radius-sm: calc(var(--st-radius) * 0.6);
            --st-font-display: {!! $cssToken($theme['display_font'] ?? null, "'Fraunces Variable', Georgia, serif") !!};
            --st-font-body: {!! $cssToken($theme['body_font'] ?? null, "'Inter Variable', ui-sans-serif, system-ui, sans-serif") !!};
        }
    </style>

    {{-- ================================================================
         STUDIO — classic fashion store. Pastel sage band + black & white,
         UPPERCASE Fraunces display headings closed by thin underline
         rules, white plaque labels over photography, solid black CTAs,
         squared 4px corners. Palette flows through the --st-* tokens so
         the customizer keeps working.
         ================================================================ --}}
    <style>
        /* --- Rhythm ---------------------------------------------------- */
        .stt-studio-section { padding-block: clamp(3.5rem, 8vw, 5.5rem); }

        /* --- Color bands: sage (signature) and light grey --------------- */
        .stt-studio-band { background: var(--st-band); }
        .stt-studio-band-grey { background: var(--st-surface); }

        /* --- Labels & display type ------------------------------------- */
        .stt-studio-eyebrow {
            font-family: var(--st-font-body);
            font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.18em;
            color: var(--st-ink-soft);
        }
        /* The Studio voice: uppercase serif with open tracking. */
        .stt-studio-display {
            font-family: var(--st-font-display);
            font-weight: 600; line-height: 1.14;
            text-transform: uppercase; letter-spacing: 0.045em;
            color: var(--st-ink);
        }
        .stt-studio-hero-title {
            font-family: var(--st-font-display);
            font-weight: 600; line-height: 1.1;
            text-transform: uppercase; letter-spacing: 0.03em;
            font-size: clamp(2rem, 4.6vw, 3.4rem);
            color: var(--st-ink);
        }
        /* Section title, usually paired with --rule below. */
        .stt-studio-title {
            font-family: var(--st-font-display);
            font-weight: 600; line-height: 1.15;
            text-transform: uppercase; letter-spacing: 0.06em;
            font-size: clamp(1.35rem, 2.7vw, 1.9rem);
            color: var(--st-ink);
        }
        /* Signature underline rule: a thin ink line the width of the words.
           Drawn as a ::after (not border-bottom) so it can scale in on reveal;
           at rest it renders identically to the old 1.5px border. */
        .stt-studio-title--rule {
            display: inline-block;
            position: relative;
            padding-bottom: 0.55rem;
        }
        .stt-studio-title--rule::after {
            content: ""; position: absolute; inset-inline: 0; bottom: 0;
            height: 1.5px; background: currentColor;
        }
        /* Reveal choreography 1/3 — the rule draws in from the center once its
           section head enters (JS-gated; stilled under reduced motion below). */
        .st-js .st-reveal:not(.is-in) .stt-studio-title--rule::after { transform: scaleX(0); }
        .st-js .st-reveal.is-in .stt-studio-title--rule::after {
            transform: scaleX(1);
            transform-origin: center;
            transition: transform .7s cubic-bezier(.2,.7,.2,1) .25s;
        }
        /* Centered section head. */
        .stt-studio-head {
            display: flex; flex-direction: column; align-items: center;
            gap: 0.8rem; text-align: center;
            margin-bottom: clamp(2rem, 5vw, 3rem);
        }

        /* --- Plaque: the white bordered label set over photography ------- */
        .stt-studio-plaque {
            display: inline-block;
            background: #fff; color: var(--st-ink);
            font-family: var(--st-font-display);
            font-weight: 600; font-size: 0.85rem;
            text-transform: uppercase; letter-spacing: 0.16em;
            padding: 0.85rem 1.6rem;
            /* Inner hairline frame, drawn inside the white field. */
            outline: 1px solid color-mix(in srgb, var(--st-ink) 35%, transparent);
            outline-offset: -6px;
        }
        /* Reveal choreography 2/3 — plaques rise and fade a beat after their
           parent reveals (JS-gated; stilled under reduced motion below). */
        .st-js .st-reveal:not(.is-in) .stt-studio-plaque { opacity: 0; transform: translateY(10px); }
        .st-js .st-reveal.is-in .stt-studio-plaque {
            opacity: 1; transform: none;
            transition: opacity .55s cubic-bezier(.2,.7,.2,1) .18s, transform .55s cubic-bezier(.2,.7,.2,1) .18s;
        }

        /* --- Buttons ----------------------------------------------------- */
        /* Primary: solid black, squared. */
        .stt-studio-btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.6rem;
            min-height: 3rem; padding: 0.85rem 2rem;
            font-family: var(--st-font-body);
            font-size: 0.9rem; font-weight: 600; letter-spacing: 0.03em;
            background: var(--st-primary); color: var(--st-primary-ink);
            border: 1px solid var(--st-primary);
            border-radius: var(--st-radius);
            transition: background .2s ease, border-color .2s ease, color .2s ease;
        }
        .stt-studio-btn:hover {
            background: color-mix(in srgb, var(--st-primary) 78%, var(--st-bg));
            border-color: color-mix(in srgb, var(--st-primary) 78%, var(--st-bg));
        }
        /* Light: white fill on the sage band / dark panels. */
        .stt-studio-btn--light {
            background: #fff; border-color: #fff; color: var(--st-ink);
        }
        .stt-studio-btn--light:hover {
            background: var(--st-ink); border-color: var(--st-ink); color: #fff;
        }
        /* Small outlined caps button (promo tiles). Inherits currentColor so it
           sits on both light and dark media. */
        .stt-studio-btn-outline {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            min-height: 2.5rem; padding: 0.55rem 1.4rem;
            font-family: var(--st-font-body);
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.16em;
            background: transparent; color: currentColor;
            border: 1px solid currentColor;
            border-radius: var(--st-radius);
            transition: background .2s ease, color .2s ease;
        }
        .stt-studio-btn-outline:hover { background: #fff; color: var(--st-ink); border-color: #fff; }

        /* --- Header ------------------------------------------------------ */
        .stt-studio-wordmark {
            font-family: var(--st-font-display); font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.1em;
            color: var(--st-ink);
        }
        .stt-studio-nav {
            display: flex; align-items: center;
            min-height: 2.75rem; padding: 0.5rem 0.75rem;
            font-family: var(--st-font-body);
            font-size: 0.9rem; font-weight: 600;
            color: var(--st-ink);
            text-decoration: none;
            transition: color .2s ease;
        }
        .stt-studio-nav:hover { color: var(--st-accent); text-decoration: underline; text-underline-offset: 6px; }
        /* Icon button: 44px hit-area with a quiet surface hover. */
        .stt-studio-iconbtn {
            position: relative; display: grid; place-items: center;
            width: 2.75rem; height: 2.75rem; color: var(--st-ink);
            border-radius: var(--st-radius);
            transition: background-color .2s ease;
        }
        .stt-studio-iconbtn:hover { background: var(--st-surface); }
        /* This unlayered display:grid outranks Tailwind's LAYERED md:hidden utility,
           so the hamburger would leak onto desktop — re-assert the breakpoint unlayered. */
        @media (min-width: 768px) {
            .stt-studio-iconbtn.md\:hidden { display: none; }
        }
        /* Field-look search trigger in the header (opens the predictive overlay). */
        .stt-studio-search {
            display: inline-flex; align-items: center; gap: 0.6rem;
            min-height: 2.75rem; min-width: 13rem;
            padding: 0.5rem 1rem;
            font-family: var(--st-font-body); font-size: 0.85rem;
            color: var(--st-ink-soft);
            background: var(--st-surface);
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
            transition: border-color .2s ease;
            cursor: text;
        }
        .stt-studio-search:hover { border-color: var(--st-ink-soft); }
        /* Unlayered display:inline-flex above outranks Tailwind's LAYERED
           `hidden md:inline-flex` on this element, so it never hid on mobile —
           re-assert the breakpoint unlayered (same fix as .stt-studio-iconbtn). */
        @media (max-width: 767.98px) { .stt-studio-search { display: none; } }

        /* --- Form controls ------------------------------------------------ */
        .stt-studio-input {
            width: 100%; min-height: 3rem;
            background: var(--st-bg); color: var(--st-ink);
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
            padding: 0.8rem 1rem; font-size: 0.95rem;
            transition: border-color .2s ease;
        }
        .stt-studio-input:focus { outline: none; border-color: var(--st-ink); }
        .stt-studio-input::placeholder { color: var(--st-ink-soft); }
        /* Squared variant/option chip — black fill when active. */
        .stt-studio-chip {
            padding: 0.65rem 1.15rem; font-size: 12px; font-weight: 600;
            letter-spacing: 0.04em;
            border: 1px solid var(--st-line); color: var(--st-ink);
            background: var(--st-bg); border-radius: var(--st-radius);
            transition: border-color .2s ease, background .2s ease, color .2s ease;
        }
        .stt-studio-chip:hover { border-color: var(--st-ink); }
        .stt-studio-chip--active {
            background: var(--st-ink); color: var(--st-bg); border-color: var(--st-ink);
        }

        /* --- Prices --------------------------------------------------------- */
        .stt-studio-price {
            font-family: var(--st-font-body); font-weight: 700;
            font-size: 1rem; color: var(--st-ink);
        }
        .stt-studio-price-was {
            font-family: var(--st-font-body); font-size: 0.85rem;
            color: var(--st-ink-soft); text-decoration: line-through;
        }

        /* --- Badge (Limited stock / sale chips) ------------------------------ */
        .stt-studio-badge {
            display: inline-block; padding: 0.35rem 0.7rem;
            font-size: 10px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.12em;
            background: var(--st-ink); color: #fff;
            border-radius: var(--st-radius); line-height: 1;
        }

        /* --- Carousel chrome: round arrows + dot indicators ------------------- */
        .stt-studio-arrow {
            display: grid; place-items: center;
            height: 2.75rem; width: 2.75rem;
            border-radius: 50%;
            background: #fff; color: var(--st-ink);
            border: 1px solid var(--st-line);
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            transition: background .2s ease, color .2s ease, border-color .2s ease;
        }
        .stt-studio-arrow:hover { background: var(--st-ink); border-color: var(--st-ink); color: #fff; }
        /* Edge-mounted variant (hero slider): centered on the band, hidden on
           small screens where it would sit over the copy. The unlayered
           display:grid above would beat a layered `hidden` utility, so the
           breakpoint is re-asserted here. */
        .stt-studio-arrow--edge { position: absolute; top: 50%; transform: translateY(-50%); z-index: 2; }
        @media (max-width: 639.98px) { .stt-studio-arrow--edge { display: none; } }
        .stt-studio-dotbtn {
            display: block; padding: 0.55rem; /* generous hit-area around the dot */
            background: transparent; border: 0; cursor: pointer;
        }
        .stt-studio-dotbtn::after {
            content: ""; display: block; height: 7px; width: 7px;
            border-radius: 50%;
            background: color-mix(in srgb, var(--st-ink) 28%, transparent);
            transition: background-color .3s ease, transform .3s ease;
        }
        .stt-studio-dotbtn[aria-current="true"]::after {
            background: var(--st-ink); transform: scale(1.25);
        }

        /* --- Hero: sage band, copy left / photo right, slider chrome ---------- */
        .stt-studio-hero-grid {
            display: grid; align-items: center;
            gap: 2.5rem;
            padding-block: clamp(2.5rem, 6vw, 4.5rem);
        }
        @media (min-width: 1024px) {
            .stt-studio-hero-grid { grid-template-columns: 1.05fr 0.95fr; gap: 4rem; }
        }
        .stt-studio-hero-media {
            position: relative; overflow: hidden;
            aspect-ratio: 4 / 5; max-height: 34rem; width: 100%;
            border-radius: var(--st-radius);
        }
        .stt-studio-hero-media img { width: 100%; height: 100%; object-fit: cover; }

        /* --- Category trio: photo card with a centered plaque ----------------- */
        .stt-studio-cat-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
        @media (min-width: 640px) { .stt-studio-cat-grid { grid-template-columns: repeat(3, 1fr); gap: 1.5rem; } }
        .stt-studio-catcard { position: relative; display: block; overflow: hidden; border-radius: var(--st-radius); }
        .stt-studio-catcard img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s cubic-bezier(.2,.7,.2,1); }
        .stt-studio-catcard:hover img, .stt-studio-catcard:focus-visible img { transform: scale(1.04); }
        .stt-studio-catcard .stt-studio-plaque {
            position: absolute; inset-inline: 0; bottom: 1.75rem;
            margin-inline: auto; width: max-content; max-width: calc(100% - 2rem);
        }
        @media (prefers-reduced-motion: reduce) { .stt-studio-catcard img { transition: none; } }

        /* --- Product grid: commerce density, 4-up on desktop ------------------- */
        .stt-studio-grid {
            display: grid; grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem 1rem;
        }
        @media (min-width: 768px) { .stt-studio-grid { grid-template-columns: repeat(3, 1fr); gap: 2rem 1.5rem; } }
        @media (min-width: 1024px) { .stt-studio-grid { grid-template-columns: repeat(4, 1fr); } }

        /* --- Stagger: children of a marked grid reveal in measured sequence ------ */
        /* Each child is its own .st-reveal (product cards ship the class); the
           parent only meters delays, capped at ~6 so deep grids don't lag.
           Reduced motion is covered by app.css's transition:none !important on
           .st-reveal (the shorthand zeroes these delays) and re-stated below. */
        .st-js .stt-studio-stagger > .st-reveal.is-in:nth-child(2) { transition-delay: 70ms; }
        .st-js .stt-studio-stagger > .st-reveal.is-in:nth-child(3) { transition-delay: 140ms; }
        .st-js .stt-studio-stagger > .st-reveal.is-in:nth-child(4) { transition-delay: 210ms; }
        .st-js .stt-studio-stagger > .st-reveal.is-in:nth-child(5) { transition-delay: 280ms; }
        .st-js .stt-studio-stagger > .st-reveal.is-in:nth-child(n+6) { transition-delay: 350ms; }

        /* --- Promo tiles (Big saving zone) -------------------------------------- */
        .stt-studio-promo-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        @media (min-width: 768px) { .stt-studio-promo-grid { grid-template-columns: repeat(2, 1fr); } }
        .stt-studio-promo {
            position: relative; overflow: hidden; isolation: isolate;
            display: flex; flex-direction: column; align-items: flex-start; justify-content: flex-end;
            min-height: clamp(18rem, 42vw, 24rem);
            padding: clamp(1.5rem, 4vw, 2.5rem);
            border-radius: var(--st-radius);
            background: var(--st-ink); color: #fff;
        }
        .stt-studio-promo > img {
            position: absolute; inset: 0; z-index: -2;
            width: 100%; height: 100%; object-fit: cover;
            transition: transform .6s cubic-bezier(.2,.7,.2,1);
        }
        /* Reveal choreography 3/3 — slow photographic zoom on hover/focus,
           matching the category tiles (stilled under reduced motion below). */
        .stt-studio-promo:hover > img,
        .stt-studio-promo:focus-within > img { transform: scale(1.04); }
        /* Scrim only over photography — pastel fallback tiles keep ink copy. */
        .stt-studio-promo--photo::after {
            content: ""; position: absolute; inset: 0; z-index: -1;
            background: linear-gradient(to top, rgba(10,10,10,.62) 0%, rgba(10,10,10,.28) 55%, rgba(10,10,10,.12) 100%);
        }
        .stt-studio-promo-title {
            font-family: var(--st-font-display);
            font-weight: 600; line-height: 1.12;
            text-transform: uppercase; letter-spacing: 0.05em;
            font-size: clamp(1.4rem, 2.6vw, 2rem);
            color: #fff;
        }

        /* --- Split banner: dark leafy panel + full-bleed photo ------------------- */
        .stt-studio-split { display: grid; grid-template-columns: 1fr; overflow: hidden; border-radius: var(--st-radius); }
        @media (min-width: 1024px) { .stt-studio-split { grid-template-columns: 1fr 1fr; } }
        .stt-studio-split-panel {
            display: flex; flex-direction: column; align-items: flex-start; justify-content: center;
            gap: 1.4rem;
            padding: clamp(2.5rem, 6vw, 4.5rem);
            /* Deep leafy green: the accent sunk toward ink. */
            background:
                radial-gradient(46rem 20rem at -8% 112%, color-mix(in srgb, var(--st-band) 22%, transparent), transparent 60%),
                color-mix(in srgb, var(--st-accent) 62%, var(--st-ink));
            color: #fff;
        }
        .stt-studio-split-media { position: relative; min-height: 20rem; background: var(--st-surface); }
        .stt-studio-split-media img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }

        /* --- Testimonials: overlapping-feel white cards on grey ------------------- */
        .stt-studio-testi-track {
            display: flex; gap: 1.5rem;
            overflow-x: auto; scroll-snap-type: x mandatory;
            padding: 1rem 0.25rem 1.5rem;
            scrollbar-width: none;
        }
        .stt-studio-testi-track::-webkit-scrollbar { display: none; }
        .stt-studio-testi-card {
            scroll-snap-align: center;
            flex: 0 0 min(100%, 24rem);
            display: flex; flex-direction: column;
            padding: 2rem;
            background: #fff; color: var(--st-ink);
            border: 1px solid var(--st-line);
            border-radius: var(--st-radius);
            box-shadow: 0 14px 30px -22px rgba(0,0,0,.35);
        }

        /* --- Newsletter panel ------------------------------------------------------ */
        .stt-studio-nl-grid { display: grid; grid-template-columns: 1fr; overflow: hidden; border-radius: var(--st-radius); }
        @media (min-width: 1024px) { .stt-studio-nl-grid { grid-template-columns: 0.9fr 1.1fr; } }
        .stt-studio-nl-heading {
            font-family: var(--st-font-display);
            font-weight: 600; line-height: 1.15;
            text-transform: uppercase; letter-spacing: 0.06em;
            font-size: clamp(1.35rem, 2.7vw, 1.9rem);
            color: var(--st-ink);
        }
        .stt-studio-nl-sub { color: var(--st-ink-soft); }
        .stt-studio-nl-error { color: #b3261e; }
        .stt-studio-nl-done { color: var(--st-accent); }

        /* --- Inverted (ink band) context: flips the kit for the black footer ------- */
        .stt-studio-invert, .stt-studio-invert .stt-studio-nav { color: #fff; }
        .stt-studio-invert .stt-studio-nl-heading { color: #fff; }
        .stt-studio-invert .stt-studio-nl-sub { color: rgba(255,255,255,.72); }
        .stt-studio-invert .stt-studio-nl-error { color: #f5b7ae; }
        .stt-studio-invert .stt-studio-nl-done { color: var(--st-band); }
        .stt-studio-invert .stt-studio-eyebrow { color: rgba(255,255,255,.65); }
        .stt-studio-invert .stt-studio-input {
            background: rgba(255,255,255,.08); color: #fff;
            border-color: rgba(255,255,255,.28);
        }
        .stt-studio-invert .stt-studio-input:focus { border-color: #fff; }
        .stt-studio-invert .stt-studio-input::placeholder { color: rgba(255,255,255,.5); }
        .stt-studio-invert .stt-studio-btn { background: #fff; border-color: #fff; color: var(--st-ink); }
        .stt-studio-invert .stt-studio-btn:hover { background: rgba(255,255,255,.85); border-color: rgba(255,255,255,.85); }
        /* Footer link + column label voices. */
        .stt-studio-footlabel {
            font-family: var(--st-font-display);
            font-size: 0.95rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.12em;
            color: #fff;
        }
        .stt-studio-footlink {
            font-size: 0.875rem; color: rgba(255,255,255,.7);
            text-decoration: none; transition: color .2s ease;
        }
        .stt-studio-footlink:hover { color: #fff; text-decoration: underline; text-underline-offset: 4px; }
        .stt-studio-social {
            display: grid; place-items: center;
            height: 2.5rem; width: 2.5rem;
            border: 1px solid rgba(255,255,255,.3); color: #fff;
            border-radius: var(--st-radius);
            transition: background .2s ease, color .2s ease;
        }
        .stt-studio-social:hover { background: #fff; color: var(--st-ink); }

        /* --- Quiet micro-caps (breadcrumbs, meta) ------------------------------------ */
        .stt-studio-crumb {
            font-size: 11px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.14em;
            color: var(--st-ink-soft);
        }
        .stt-studio-crumb a:hover { color: var(--st-ink); }

        /* --- Focus & anchor affordances ------------------------------------------------ */
        /* currentColor keeps the ring legible on the white canvas, the sage band
           and the black footer alike; inputs keep their designed border focus. */
        .storefront.storefront :is(a, button, select, summary, [tabindex]):focus-visible {
            outline: 2px solid currentColor; outline-offset: 3px;
        }
        /* In-page anchors (e.g. #reviews) land clear of the sticky header. */
        .storefront.storefront [id] { scroll-margin-top: 7rem; }

        /* --- Calm means still for those who ask for it --------------------------------- */
        @media (prefers-reduced-motion: reduce) {
            .stt-studio-btn, .stt-studio-btn-outline, .stt-studio-nav,
            .stt-studio-iconbtn, .stt-studio-arrow, .stt-studio-dotbtn::after,
            .stt-studio-search, .stt-studio-chip { transition: none; }
            /* Reveal choreography stands completely still: rule stays drawn,
               plaques stay put, stagger delays vanish, tiles don't zoom. */
            .stt-studio-title--rule::after { transform: none !important; transition: none !important; }
            .st-reveal .stt-studio-plaque { opacity: 1 !important; transform: none !important; transition: none !important; }
            .stt-studio-stagger > .st-reveal { transition-delay: 0s !important; }
            .stt-studio-promo > img { transition: none; }
            .stt-studio-promo:hover > img,
            .stt-studio-promo:focus-within > img { transform: none; }
        }

        /* --- Compat: responsive steps absent from the frozen compiled CSS --------------- */
        @media (min-width: 1024px) {
            .stt-studio-pdp-grid { column-gap: 4rem; }
            .stt-studio-pdp-sticky { position: sticky; top: 6.5rem; align-self: flex-start; }
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
            font-family: var(--st-font-display); font-size: .62rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .05em;
            text-decoration: none; background: none; border: none; cursor: pointer;
        }
        .stt-bottom-nav-icon-wrap{ position: relative; display: inline-flex; }
        .stt-bottom-nav-badge{
            position: absolute; top: -.3rem; right: -.5rem;
            display: grid; place-items: center;
            height: 1rem; min-width: 1rem; padding-inline: .2rem;
            border-radius: 4px; font-size: 9px; font-weight: 700;
            background: var(--st-ink); color: #fff;
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
            background: var(--st-ink); color: #fff; cursor: pointer;
            box-shadow: 0 6px 16px rgba(0,0,0,.3), 0 2px 6px rgba(0,0,0,.15);
        }
        .stt-bottom-nav-badge--cta{ top: -.15rem; right: -.15rem; background: var(--st-accent); }
        @media (min-width: 768px){ .stt-bottom-nav.md\:hidden{ display: none; } }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @include('theme::partials.tracking-scripts')
</head>
<body class="storefront min-h-screen pb-20 antialiased md:pb-0">
    @includeWhen($theme['show_announcement'] ?? true, 'theme::partials.announcement')
    @include('theme::partials.header')

    <main>
        @yield('content')
    </main>

    @include('theme::partials.footer')
    <x-storefront-addon-links />
    @include('theme::partials.mobile-bottom-nav', ['r' => 6])

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
