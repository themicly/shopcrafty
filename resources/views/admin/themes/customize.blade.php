<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Customize · {{ config('app.name') }}</title>
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
    @livewireStyles
</head>
<body class="bg-surface text-content antialiased">
    <livewire:themes.customizer />
    <x-ui.toasts />
    @livewireScripts
</body>
</html>
