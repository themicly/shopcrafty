@props([
    'name',
    'title' => null,
    'width' => 'md',
])

@php
    $w = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl'][$width] ?? 'max-w-md';
@endphp

<div
    x-data="{ show: false }"
    @open-drawer.window="if ($event.detail === '{{ $name }}') show = true"
    @close-drawer.window="if ($event.detail === '{{ $name }}') show = false"
    @keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50"
    style="display: none;"
>
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black/50" @click="show = false"></div>

    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 flex w-full {{ $w }} flex-col border-l border-line bg-surface-overlay shadow-lg"
    >
        <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
            <h3 class="text-sm font-semibold text-content">{{ $title }}</h3>
            <button type="button" @click="show = false" class="grid h-8 w-8 place-items-center rounded-md text-content-muted hover:bg-surface-sunken" aria-label="Close">
                <span class="text-lg leading-none">&times;</span>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-5">{{ $slot }}</div>

        @isset($footer)
            <div class="flex items-center justify-end gap-2 border-t border-line px-5 py-4">{{ $footer }}</div>
        @endisset
    </div>
</div>
