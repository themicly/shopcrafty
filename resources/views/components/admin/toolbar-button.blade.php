@props([
    'icon' => null,
    'color' => 'neutral',
    'href' => null,
])

@php
    $colors = [
        'info' => 'border-info/30 text-info hover:bg-info-soft',
        'success' => 'border-success/40 text-success hover:bg-success-soft',
        'primary' => 'border-primary/30 text-primary hover:bg-primary-soft',
        'neutral' => 'border-line text-content-secondary hover:bg-surface-sunken',
    ];
    $classes = 'inline-flex h-9 items-center gap-1.5 rounded-lg border bg-surface-raised px-3.5 text-sm font-medium transition-colors '.($colors[$color] ?? $colors['neutral']);
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="button" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if ($icon)
        <x-ui.icon :name="$icon" class="h-4 w-4" />
    @endif
    {{ $slot }}
</{{ $tag }}>
