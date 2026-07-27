@props([
    'align' => 'right',
    'width' => 'w-52',
])

<div class="relative" x-data="{ open: false }" @keydown.escape="open = false">
    <div @click="open = !open">
        {{ $trigger }}
    </div>

    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        @click="open = false"
        x-cloak
        class="absolute z-30 mt-2 {{ $width }} overflow-hidden rounded-lg border border-line bg-surface-overlay py-1 shadow-lg {{ $align === 'left' ? 'left-0' : 'right-0' }}"
        style="display: none;"
    >
        {{ $slot }}
    </div>
</div>
