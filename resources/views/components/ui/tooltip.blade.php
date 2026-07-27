@props([
    'text',
    'position' => 'top',
])

@php
    $pos = [
        'top' => 'bottom-full left-1/2 -translate-x-1/2 mb-1.5',
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-1.5',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-1.5',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-1.5',
    ][$position] ?? 'bottom-full left-1/2 -translate-x-1/2 mb-1.5';
@endphp

<span class="relative inline-flex" x-data="{ show: false }" @mouseenter="show = true" @mouseleave="show = false">
    {{ $slot }}
    <span
        x-show="show"
        x-transition.opacity
        x-cloak
        class="pointer-events-none absolute z-40 {{ $pos }} whitespace-nowrap rounded-md bg-content px-2 py-1 text-xs font-medium text-surface shadow-sm"
        style="display: none;"
    >
        {{ $text }}
    </span>
</span>
