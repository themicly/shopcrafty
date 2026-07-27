@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title . ' · ' : '' }}{{ config('app.name', 'Shopcrafty') }}</title>
    @if ($favicon = settings('general.favicon'))<link rel="icon" href="{{ $favicon }}">@endif

    <script>
        (function () {
            try {
                var t = localStorage.getItem('bz-theme');
                if (t !== 'dark' && t !== 'light') t = 'light';
                document.documentElement.setAttribute('data-theme', t);
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="relative min-h-screen overflow-hidden bg-surface text-content antialiased">
    {{-- Decorative color blobs --}}
    <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full opacity-30 blur-3xl" style="background: var(--bz-primary)"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full opacity-30 blur-3xl" style="background: var(--bz-brand-2)"></div>

    <div class="relative flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <div class="mb-6 flex items-center justify-center gap-2.5">
                @if ($logo = settings('general.logo'))
                    <img src="{{ $logo }}" alt="{{ settings('general.store_name', config('app.name', 'Shopcrafty')) }}" class="h-9 w-9 rounded-lg object-contain shadow-sm">
                @else
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg font-bold text-white shadow-sm" style="background: linear-gradient(135deg, var(--bz-primary), var(--bz-brand-2))">{{ strtoupper(substr(settings('general.store_name', config('app.name', 'Shopcrafty')), 0, 1)) }}</span>
                @endif
                <span class="text-lg font-semibold text-content">{{ settings('general.store_name', config('app.name', 'Shopcrafty')) }}</span>
            </div>

            <div class="rounded-xl border border-line bg-surface-raised p-6 shadow-sm">
                {{ $slot }}
            </div>

            <p class="mt-6 text-center text-xs text-content-muted">
                &copy; {{ date('Y') }} {{ config('app.name', 'Shopcrafty') }}
            </p>
        </div>
    </div>
</body>
</html>
