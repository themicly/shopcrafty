@props([
    'name',
    'title' => null,
    'maxWidth' => 'lg',
])

@php
    $max = [
        'sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg',
        'xl' => 'max-w-xl', '2xl' => 'max-w-2xl', '3xl' => 'max-w-3xl',
    ][$maxWidth] ?? 'max-w-lg';
@endphp

<div
    x-data="{ show: false }"
    @open-modal.window="if ($event.detail === '{{ $name }}') show = true"
    @close-modal.window="if ($event.detail === '{{ $name }}') show = false"
    @keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    style="display: none;"
>
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black/50" @click="show = false"></div>

    <div class="flex min-h-full items-start justify-center p-4 sm:p-6">
        <div
            x-show="show"
            x-transition
            @click.outside="show = false"
            class="relative mt-[8vh] w-full {{ $max }} rounded-xl border border-line bg-surface-overlay shadow-lg"
        >
            @if ($title || isset($header))
                <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
                    <h3 class="text-sm font-semibold text-content">{{ $header ?? $title }}</h3>
                    <button type="button" @click="show = false" class="grid h-8 w-8 place-items-center rounded-md text-content-muted hover:bg-surface-sunken" aria-label="Close">
                        <span class="text-lg leading-none">&times;</span>
                    </button>
                </div>
            @endif

            <div class="p-5">{{ $slot }}</div>

            @isset($footer)
                <div class="flex items-center justify-end gap-2 border-t border-line px-5 py-4">{{ $footer }}</div>
            @endisset
        </div>
    </div>
</div>
