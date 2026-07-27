@props(['title' => null])

@php
    use Illuminate\Support\Facades\Route;
    $navigation = app(\Themicly\Shopcrafty\Core\Navigation\NavigationRegistry::class);
@endphp

<header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-line bg-surface-raised/95 px-4 backdrop-blur sm:px-6">
    {{-- Mobile: open sidebar --}}
    <button
        type="button"
        class="grid h-9 w-9 place-items-center rounded-md text-content-secondary hover:bg-surface-sunken lg:hidden"
        @click="sidebarOpen = true"
        aria-label="Open navigation"
    >
        <x-ui.icon name="menu" />
    </button>

    {{-- Desktop: collapse sidebar --}}
    <button
        type="button"
        class="hidden h-9 w-9 place-items-center rounded-md text-content-secondary hover:bg-surface-sunken lg:grid"
        @click="toggleCollapsed()"
        aria-label="Collapse sidebar"
    >
        <x-ui.icon name="chevron-left" class="h-5 w-5 transition-transform" ::class="collapsed && 'rotate-180'" />
    </button>

    <x-admin.breadcrumbs :title="$title" />

    {{-- Command palette trigger --}}
    <button
        type="button"
        class="group ml-auto flex h-9 w-full max-w-xs items-center gap-2 rounded-md border border-line bg-surface px-3 text-sm text-content-muted transition-colors hover:border-line-strong"
        @click="$dispatch('open-command-palette')"
    >
        <x-ui.icon name="search" class="h-4 w-4" />
        <span class="hidden sm:inline">Search…</span>
        <kbd class="ml-auto hidden items-center gap-0.5 rounded border border-line bg-surface-raised px-1.5 py-0.5 font-sans text-[11px] text-content-muted sm:flex">
            <span class="text-sm leading-none">⌘</span>K
        </kbd>
    </button>

    {{-- Quick create --}}
    <div class="relative" x-data="{ open: false }" @keydown.escape="open = false">
        <button
            type="button"
            class="grid h-9 w-9 place-items-center rounded-md bg-primary text-primary-fg hover:bg-primary-hover"
            @click="open = !open"
            aria-label="Quick create"
        >
            <x-ui.icon name="plus" />
        </button>
        <div
            x-show="open"
            x-transition
            @click.outside="open = false"
            class="absolute right-0 mt-2 w-52 overflow-hidden rounded-lg border border-line bg-surface-overlay py-1 shadow-lg"
            style="display: none;"
        >
            @foreach ($navigation->quickCreate() as $action)
                @continue(($action['gate'] ?? null) && \Illuminate\Support\Facades\Gate::denies($action['gate']))
                <a
                    href="{{ Route::has($action['route']) ? route($action['route']) : '#' }}"
                    class="block px-3 py-2 text-sm text-content-secondary hover:bg-surface-sunken hover:text-content"
                >
                    {{ $action['label'] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Dark mode toggle --}}
    <button
        type="button"
        class="grid h-9 w-9 place-items-center rounded-md text-content-secondary hover:bg-surface-sunken"
        @click="$store.theme.toggle()"
        aria-label="Toggle theme"
    >
        <template x-if="!$store.theme.dark"><x-ui.icon name="moon" /></template>
        <template x-if="$store.theme.dark"><x-ui.icon name="sun" /></template>
    </button>

    {{-- Notifications: a real "what needs attention" feed (COD verification,
         low stock, pending reviews, abandoned carts), not a shortcut to the
         notification-template settings page. --}}
    <x-admin.notifications-bell />

    {{-- Profile --}}
    <div class="relative" x-data="{ open: false }" @keydown.escape="open = false">
        <button
            type="button"
            class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-surface-sunken text-sm font-medium text-content-secondary hover:bg-line"
            @click="open = !open"
            aria-label="Account menu"
        >
            {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
        </button>
        <div
            x-show="open"
            x-transition
            @click.outside="open = false"
            class="absolute right-0 mt-2 w-56 overflow-hidden rounded-lg border border-line bg-surface-overlay py-1 shadow-lg"
            style="display: none;"
        >
            <div class="border-b border-line px-3 py-2.5">
                <p class="truncate text-sm font-medium text-content">{{ auth()->user()?->name }}</p>
                <p class="truncate text-xs text-content-muted">{{ auth()->user()?->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full px-3 py-2 text-left text-sm text-content-secondary hover:bg-surface-sunken hover:text-content">
                    Sign out
                </button>
            </form>
        </div>
    </div>
</header>
