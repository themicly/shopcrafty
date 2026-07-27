<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Install · {{ config('app.name', 'Shopcrafty') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="relative min-h-screen bg-surface text-content antialiased">
    <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full opacity-30 blur-3xl" style="background: var(--bz-primary)"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full opacity-30 blur-3xl" style="background: var(--bz-brand-2)"></div>

    <div class="relative mx-auto flex min-h-screen max-w-2xl flex-col justify-center px-4 py-12">
        <div class="mb-6 flex items-center justify-center gap-2.5">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg font-bold text-white shadow-sm" style="background: linear-gradient(135deg, var(--bz-primary), var(--bz-brand-2))">{{ strtoupper(substr(config('app.name', 'Shopcrafty'), 0, 1)) }}</span>
            <span class="text-lg font-semibold text-content">{{ config('app.name', 'Shopcrafty') }} installer</span>
        </div>

        <div class="rounded-2xl border border-line bg-surface-raised p-6 shadow-sm sm:p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-center text-xs text-content-muted">&copy; {{ date('Y') }} {{ config('app.name', 'Shopcrafty') }}</p>
    </div>
    @livewireScripts
</body>
</html>
