@php
    $storeName = settings('general.store_name', config('app.name'));
    $cssToken = fn ($value, $fallback) => ($value !== null && $value !== '' && ! preg_match('/[;{}<>@()\\\\]/', (string) $value)) ? $value : $fallback;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ text_direction() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('checkout.checkout')) · {{ $storeName }}</title>
    @if ($favicon = settings('general.favicon'))<link rel="icon" href="{{ $favicon }}">@endif

    {{-- Doubled class wins over app.css's unlayered defaults (see layout.blade.php / THM-06). --}}
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
            --st-radius-sm: calc(var(--st-radius) * 0.6);
            --st-font-display: {!! $cssToken($theme['display_font'] ?? null, "'Fraunces Variable', Georgia, serif") !!};
            --st-font-body: {!! $cssToken($theme['body_font'] ?? null, "'Inter Variable', ui-sans-serif, system-ui, sans-serif") !!};
        }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @include('theme::partials.tracking-scripts')
</head>
<body class="storefront min-h-screen antialiased">
    <header class="border-b" style="border-color: var(--st-line)">
        <div class="st-container flex h-16 items-center justify-between">
            @php $logo = settings('general.logo'); @endphp
            <a href="{{ url('/') }}" class="flex items-center" style="color: var(--st-ink)">
                @if ($logo)
                    <img src="{{ $logo }}" alt="{{ $storeName }}" class="h-8 w-auto object-contain">
                @else
                    <span class="st-display text-xl font-semibold">{{ $storeName }}</span>
                @endif
            </a>
            <span class="flex items-center gap-1.5 text-xs font-medium" style="color: var(--st-ink-soft)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                {{ __('storefront.secure_checkout') }}
            </span>
        </div>
    </header>

    {{-- overflow-x-clip guards against any wide child forcing a horizontal
         scroll on phones; `clip` (not `hidden`) so it doesn't turn the page into
         a scroll container and break the summary rail's sticky positioning. --}}
    <main class="st-container overflow-x-clip py-8 sm:py-12">
        @yield('content')
    </main>

    @include('theme::partials.toasts')

    {{-- On a failed submit, Checkout::placeOrder dispatches this event; bring
         the first invalid field into view (the submit button lives in the
         summary rail, far from the form on small screens). Delay a beat so
         Livewire has morphed the error states into the DOM. --}}
    <script>
        document.addEventListener('livewire:init', () => {
            window.Livewire.on('checkout-scroll-to-error', () => {
                setTimeout(() => {
                    document.querySelector('[aria-invalid="true"]')
                        ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 150);
            });
        });
    </script>

    @livewireScripts
</body>
</html>
