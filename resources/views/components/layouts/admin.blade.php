@props(['title' => null])

<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    data-theme="light"
>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' · ' : '' }}{{ config('app.name', 'Shopcrafty') }}</title>
    @if ($favicon = settings('general.favicon'))<link rel="icon" href="{{ $favicon }}">@endif

    {{-- Set theme + sidebar state before paint to avoid layout flash. --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('bz-theme');
                if (t !== 'dark' && t !== 'light') t = 'light';
                document.documentElement.setAttribute('data-theme', t);
                document.documentElement.setAttribute('data-sidebar',
                    localStorage.getItem('bz-sidebar-collapsed') === '1' ? 'collapsed' : 'expanded');
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Admin panel color customizer (Settings → Appearance) — overrides the
         --bz-* tokens app.css defines, so this must load after the app.css
         link above to win the cascade. Only emitted once the owner has
         actually customized something; a fresh install renders nothing here
         and keeps app.css's own defaults. --}}
    @php
        $adminCssToken = fn ($value) => (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) ? $value : null;
        $adminPrimary = $adminCssToken(settings('admin_appearance.primary'));
        $adminPrimaryFg = $adminCssToken(settings('admin_appearance.primary_fg'));
        $adminBrand2 = $adminCssToken(settings('admin_appearance.brand_2'));
    @endphp
    @if ($adminPrimary || $adminPrimaryFg || $adminBrand2)
        <style>
            :root {
                @if ($adminPrimary)
                    --bz-primary: {{ $adminPrimary }};
                    --bz-primary-hover: color-mix(in srgb, {{ $adminPrimary }} 85%, black);
                    --bz-primary-soft: color-mix(in srgb, {{ $adminPrimary }} 12%, white);
                @endif
                @if ($adminPrimaryFg)
                    --bz-primary-fg: {{ $adminPrimaryFg }};
                @endif
                @if ($adminBrand2)
                    --bz-brand-2: {{ $adminBrand2 }};
                @endif
            }
            [data-theme='dark'] {
                @if ($adminPrimary)
                    --bz-primary: {{ $adminPrimary }};
                    --bz-primary-hover: color-mix(in srgb, {{ $adminPrimary }} 80%, white);
                    --bz-primary-soft: color-mix(in srgb, {{ $adminPrimary }} 18%, black);
                @endif
                @if ($adminPrimaryFg)
                    --bz-primary-fg: {{ $adminPrimaryFg }};
                @endif
                @if ($adminBrand2)
                    --bz-brand-2: {{ $adminBrand2 }};
                @endif
            }
        </style>
    @endif

    @livewireStyles
</head>
<body class="min-h-screen bg-surface text-content antialiased">
    {{-- Global loading bar — activated by the Livewire commit hook in app.js. --}}
    <div id="bz-progress" aria-hidden="true"></div>

    <div
        class="flex min-h-screen"
        x-data="{
            sidebarOpen: false,
            collapsed: localStorage.getItem('bz-sidebar-collapsed') === '1',
            toggleCollapsed() {
                this.collapsed = !this.collapsed;
                document.documentElement.setAttribute('data-sidebar', this.collapsed ? 'collapsed' : 'expanded');
                try { localStorage.setItem('bz-sidebar-collapsed', this.collapsed ? '1' : '0'); } catch (e) {}
            },
        }"
    >
        <x-admin.sidebar />

        {{-- Mobile sidebar backdrop --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-black/40 lg:hidden"
            style="display: none;"
        ></div>

        <div class="flex min-w-0 flex-1 flex-col">
            <x-admin.topbar :title="$title" />

            {{-- Upgrade nudge: shown to owners when the uploaded files ship a newer
                 version than the one stamped in the DB. Dismissal is remembered
                 per target version so it stops nagging once acknowledged. --}}
            @php($upgrader = app(\Themicly\Shopcrafty\Modules\Settings\Services\InstallerService::class))
            @if (auth()->user()?->isOwner() && \Themicly\Shopcrafty\Modules\Settings\Services\InstallerService::installed() && $upgrader->upgradeAvailable())
                <div
                    x-data="{ show: (localStorage.getItem('bz-upgrade-dismissed') !== '{{ $upgrader->appVersion() }}') }"
                    x-show="show"
                    x-cloak
                    class="flex items-center justify-between gap-4 border-b border-line bg-primary-soft px-4 py-2.5 text-sm text-content sm:px-6 lg:px-8"
                >
                    <span>
                        A new version ({{ $upgrader->appVersion() }}) is ready. Re-uploaded your files? Run the database upgrade to finish.
                    </span>
                    <span class="flex shrink-0 items-center gap-3">
                        <a href="{{ route('install.upgrade') }}" class="font-medium text-primary hover:underline">Run upgrade →</a>
                        <button type="button" @click="localStorage.setItem('bz-upgrade-dismissed', '{{ $upgrader->appVersion() }}'); show = false" class="text-content-muted hover:text-content" aria-label="Dismiss">✕</button>
                    </span>
                </div>
            @endif

            <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto w-full max-w-[1280px]">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <x-admin.command-palette />
    <x-admin.confirm />
    <x-ui.toasts />

    @livewireScripts
</body>
</html>
