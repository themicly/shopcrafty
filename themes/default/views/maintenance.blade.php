<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ settings('general.store_name', config('app.name')) }} — {{ __('storefront.back_soon') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="st-body">
    <div class="flex min-h-screen items-center justify-center px-6 text-center" style="background: var(--st-bg)">
        <div class="max-w-md">
            <h1 class="st-display text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">
                {{ settings('general.store_name', config('app.name')) }}
            </h1>
            <p class="mt-4 text-base" style="color: var(--st-ink-soft)">{{ $message }}</p>
        </div>
    </div>
</body>
</html>
