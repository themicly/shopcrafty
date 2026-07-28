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

    {{-- Aurora chrome: the flagship's own design layer. Everything below is driven by
         the --st-* tokens above, so the customizer palette still flows through — these
         classes only give Aurora's header/footer/announcement their violet, softly
         rounded, gradient-friendly character. --}}
    <style>
        /* ===== Aurora — elegant violet flagship ===== */

        /* --- Focus convention: a confident violet ring on every keyboard stop --- */
        .storefront :is(a, button, select, summary, [tabindex]):focus-visible{
          outline:2px solid var(--st-primary); outline-offset:3px;
          border-radius:calc(var(--st-radius-sm) * .6);
        }

        /* --- Signature gradient (primary → primary/accent blend) --- */
        .stt-aurora-grad{
          background-image:linear-gradient(120deg,
            var(--st-primary) 0%,
            color-mix(in srgb, var(--st-primary) 55%, var(--st-accent)) 100%);
        }

        /* --- Announcement: slim gradient ribbon --- */
        .stt-aurora-announce{
          background-image:linear-gradient(100deg,
            var(--st-primary) 0%,
            color-mix(in srgb, var(--st-primary) 55%, var(--st-accent)) 100%);
          color:var(--st-primary-ink);
          font-size:12px; font-weight:500; letter-spacing:.01em;
        }

        /* --- Wordmark --- */
        .stt-aurora-wordmark{
          font-family:var(--st-font-display); font-weight:600;
          letter-spacing:-.01em; color:var(--st-ink);
        }

        /* --- Nav link: quiet, with a growing gradient underline --- */
        .stt-aurora-nav{
          position:relative; font-size:.875rem; font-weight:500;
          color:var(--st-ink); padding-block:.3rem;
          transition:color .2s ease;
        }
        .stt-aurora-nav::after{
          content:""; position:absolute; left:0; bottom:0; height:2px; width:100%;
          border-radius:999px; transform:scaleX(0); transform-origin:left;
          background-image:linear-gradient(90deg, var(--st-primary), var(--st-accent));
          transition:transform .25s ease;
        }
        .stt-aurora-nav:hover{ color:var(--st-primary); }
        .stt-aurora-nav:hover::after,
        .stt-aurora-nav:focus-visible::after{ transform:scaleX(1); }

        /* --- Icon button: 44px tap target with a soft violet halo on hover --- */
        .stt-aurora-iconbtn{
          position:relative; display:grid; place-items:center;
          width:2.75rem; height:2.75rem; border-radius:999px;
          color:var(--st-ink);
          transition:color .2s ease, background-color .2s ease;
        }
        /* display:grid above is unlayered so it beats Tailwind's layered md:hidden;
           re-assert the responsive hide for icon buttons marked with it. */
        @media (min-width: 768px){ .stt-aurora-iconbtn.md\:hidden{ display:none; } }
        .stt-aurora-iconbtn:hover{
          color:var(--st-primary);
          background:color-mix(in srgb, var(--st-primary) 9%, transparent);
        }

        /* --- Count bubble on wishlist / compare / cart --- */
        .stt-aurora-count{
          position:absolute; right:1px; top:1px;
          display:grid; place-items:center;
          height:1.05rem; min-width:1.05rem; padding-inline:.28rem;
          border-radius:999px; font-size:10px; font-weight:600;
          background:var(--st-accent); color:#fff;
          box-shadow:0 0 0 2px var(--st-bg);
        }

        /* --- Mobile bottom nav: floating pill with a center notch the cart
           CTA nests into (shared markup, themed via var(--st-*)). --- */
        .stt-bottom-nav{
          position:fixed; left:1rem; width:calc(100vw - 2rem); z-index:40;
          bottom:calc(1rem + env(safe-area-inset-bottom));
          display:flex; align-items:stretch; height:4rem;
        }
        .stt-bottom-nav-shape{
          position:absolute; inset:0; width:100%; height:100%; z-index:0;
          filter:drop-shadow(0 8px 20px rgba(0,0,0,.16));
        }
        .stt-bottom-nav-item{
          position:relative; z-index:1;
          flex:1 1 0; display:flex; flex-direction:column; align-items:center; justify-content:center;
          gap:.2rem; padding:.4rem .25rem; min-height:3.25rem;
          font-size:.64rem; font-weight:600; text-decoration:none;
          background:none; border:none; cursor:pointer; font-family:inherit;
        }
        .stt-bottom-nav-icon-wrap{ position:relative; display:inline-flex; }
        .stt-bottom-nav-badge{
          position:absolute; top:-.3rem; right:-.5rem;
          display:grid; place-items:center;
          height:1rem; min-width:1rem; padding-inline:.2rem;
          border-radius:999px; font-size:9px; font-weight:700;
          background:var(--st-accent); color:#fff;
        }
        .stt-bottom-nav-dot{
          position:absolute; bottom:.15rem; width:.3rem; height:.3rem;
          border-radius:999px; background:var(--st-primary);
        }
        /* Cart CTA: centered (3rd of 5 items), round, straddling the notch. */
        .stt-bottom-nav-cta{
          position:relative; z-index:2; flex:0 0 auto; align-self:flex-start;
          width:3.5rem; height:3.5rem; margin-top:-1.75rem;
          display:grid; place-items:center;
          border-radius:999px; border:3px solid var(--st-bg);
          background:var(--st-primary); color:#fff; cursor:pointer;
          box-shadow:0 6px 16px color-mix(in srgb, var(--st-primary) 45%, transparent), 0 2px 6px rgba(0,0,0,.15);
        }
        .stt-bottom-nav-badge--cta{ top:-.15rem; right:-.15rem; }
        @media (min-width: 768px){ .stt-bottom-nav.md\:hidden{ display:none; } }
        @media (min-width: 1024px){ .stt-bottom-nav.lg\:hidden{ display:none; } }

        /* --- Floating panel (dropdowns, search sheet, mobile drawer) --- */
        .stt-aurora-panel{
          background:var(--st-bg); border:1px solid var(--st-line);
          border-radius:var(--st-radius);
          box-shadow:0 24px 48px -16px color-mix(in srgb, var(--st-ink) 22%, transparent),
                     0 4px 12px -4px color-mix(in srgb, var(--st-ink) 8%, transparent);
        }

        /* --- Buttons: gradient primary + quiet ghost --- */
        .stt-aurora-btn{
          display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
          padding:.85rem 1.75rem; border-radius:var(--st-radius);
          font-size:.875rem; font-weight:600;
          background-image:linear-gradient(120deg,
            var(--st-primary) 0%,
            color-mix(in srgb, var(--st-primary) 55%, var(--st-accent)) 100%);
          color:var(--st-primary-ink);
          box-shadow:0 10px 22px -10px color-mix(in srgb, var(--st-primary) 55%, transparent);
          transition:transform .2s ease, box-shadow .2s ease;
        }
        .stt-aurora-btn:hover{
          transform:translateY(-2px);
          box-shadow:0 14px 26px -10px color-mix(in srgb, var(--st-primary) 60%, transparent);
        }
        .stt-aurora-btn-ghost{
          display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
          padding:.85rem 1.75rem; border-radius:var(--st-radius);
          font-size:.875rem; font-weight:600;
          background:transparent; color:var(--st-ink);
          border:1px solid var(--st-line);
          transition:border-color .2s ease, color .2s ease, background-color .2s ease;
        }
        .stt-aurora-btn-ghost:hover{
          color:var(--st-primary);
          border-color:color-mix(in srgb, var(--st-primary) 45%, var(--st-line));
          background:color-mix(in srgb, var(--st-primary) 5%, transparent);
        }

        /* --- Quiet text link (footer columns) --- */
        .stt-aurora-link{
          color:var(--st-ink-soft); transition:color .2s ease;
          text-underline-offset:4px;
        }
        .stt-aurora-link:hover{ color:var(--st-primary); text-decoration:underline; }

        /* --- Trust / payment chips --- */
        .stt-aurora-chip{
          display:inline-flex; align-items:center; gap:.4rem;
          padding:.3rem .8rem; border-radius:999px;
          font-size:12px; font-weight:500;
          background:var(--st-surface); border:1px solid var(--st-line);
          color:var(--st-ink-soft);
        }

        /* --- Frosted header glass. The blur lives on a ::before layer, NOT on the
               header itself: backdrop-filter on the header would make it the containing
               block for fixed descendants, trapping the cart drawer / search overlay /
               mobile drawer (all rendered inside <header>) in the header's box. --- */
        .stt-aurora-glass{ isolation:isolate; }
        .stt-aurora-glass::before{
          content:""; position:absolute; inset:0; z-index:-1;
          -webkit-backdrop-filter:blur(10px); backdrop-filter:blur(10px);
        }

        /* --- Gradient hairline used to cap the footer --- */
        .stt-aurora-hairline{
          height:2px; border:0;
          background-image:linear-gradient(90deg,
            var(--st-primary), var(--st-accent),
            color-mix(in srgb, var(--st-accent) 25%, var(--st-bg)));
        }

        /* --- Stagger: a .st-reveal group whose CHILDREN cascade in. The parent stays
               opaque (it out-specifies app.css's .st-js .st-reveal initial state) and
               each child inherits the reveal transition plus an incremental delay,
               capped at the 6th child so long grids don't crawl. JS-gated via .st-js
               exactly like .st-reveal, so no-JS visitors always see content. --- */
        .st-js .st-reveal.stt-aurora-stagger{ opacity:1; transform:none; }
        .st-js .st-reveal.stt-aurora-stagger > *{ opacity:0; transform:translateY(14px); }
        .st-js .st-reveal.stt-aurora-stagger.is-in > *{
          opacity:1; transform:none;
          transition:opacity .6s cubic-bezier(.2,.7,.2,1), transform .6s cubic-bezier(.2,.7,.2,1);
        }
        .st-js .st-reveal.stt-aurora-stagger.is-in > :nth-child(2){ transition-delay:.08s; }
        .st-js .st-reveal.stt-aurora-stagger.is-in > :nth-child(3){ transition-delay:.16s; }
        .st-js .st-reveal.stt-aurora-stagger.is-in > :nth-child(4){ transition-delay:.24s; }
        .st-js .st-reveal.stt-aurora-stagger.is-in > :nth-child(5){ transition-delay:.32s; }
        .st-js .st-reveal.stt-aurora-stagger.is-in > :nth-child(n+6){ transition-delay:.4s; }

        /* --- Micro-motion 1: gradient shimmer sweeping across the primary CTAs.
               A translated ::after overlay — transform only, no layout shift. The
               transition lives on the hover state so the sheen resets instantly. --- */
        .stt-aurora-btn, .stx-hero-cta{ position:relative; overflow:hidden; }
        .stt-aurora-btn::after, .stx-hero-cta::after{
          content:""; position:absolute; inset:0; pointer-events:none;
          background-image:linear-gradient(105deg,
            transparent 35%, rgba(255,255,255,.35) 50%, transparent 65%);
          transform:translateX(-130%);
        }
        .stt-aurora-btn:hover::after, .stt-aurora-btn:focus-visible::after,
        .stx-hero-cta:hover::after, .stx-hero-cta:focus-visible::after{
          transform:translateX(130%); transition:transform .65s ease;
        }

        /* --- Micro-motion 2: hover lift + violet-tinted shadow for section cards --- */
        .stt-aurora-lift{ transition:transform .25s ease, box-shadow .25s ease; }
        .stt-aurora-lift:hover{
          transform:translateY(-4px);
          box-shadow:0 18px 32px -14px color-mix(in srgb, var(--st-ink) 30%, transparent);
        }

        /* --- Micro-motion 3: growing gradient underline on section "view all" links --- */
        .stt-aurora-viewall{
          position:relative; padding-bottom:2px; color:var(--st-ink);
          transition:color .2s ease;
        }
        .stt-aurora-viewall::after{
          content:""; position:absolute; left:0; bottom:0; height:2px; width:100%;
          border-radius:999px; transform:scaleX(0); transform-origin:left;
          background-image:linear-gradient(90deg, var(--st-primary), var(--st-accent));
          transition:transform .25s ease;
        }
        .stt-aurora-viewall:hover{ color:var(--st-primary); }
        .stt-aurora-viewall:hover::after,
        .stt-aurora-viewall:focus-visible::after{ transform:scaleX(1); }

        /* --- Calm means still for those who ask for it --- */
        @media (prefers-reduced-motion: reduce){
          .stt-aurora-nav, .stt-aurora-nav::after,
          .stt-aurora-iconbtn, .stt-aurora-btn, .stt-aurora-btn-ghost,
          .stt-aurora-link{ transition:none; }
          .stt-aurora-btn:hover{ transform:none; }
          /* Stagger children land already-visible (mirrors app.css's .st-reveal guard). */
          .st-reveal.stt-aurora-stagger > *{
            opacity:1 !important; transform:none !important; transition:none !important;
          }
          /* Shimmer sweep off entirely. */
          .stt-aurora-btn::after, .stx-hero-cta::after{ content:none; }
          /* Cards keep the shadow cue but never move. */
          .stt-aurora-lift{ transition:none; }
          .stt-aurora-lift:hover{ transform:none; }
          /* View-all: static underline instead of the animated one. */
          .stt-aurora-viewall{ transition:none; }
          .stt-aurora-viewall::after{ content:none; }
          .stt-aurora-viewall:hover, .stt-aurora-viewall:focus-visible{
            text-decoration:underline; text-underline-offset:4px;
          }
        }
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
    @include('theme::partials.mobile-bottom-nav')

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
