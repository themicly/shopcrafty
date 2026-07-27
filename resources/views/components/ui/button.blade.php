@props([
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
    'href' => null,
    'icon' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-medium rounded-md transition-colors '
        . 'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 '
        . 'focus-visible:ring-offset-surface disabled:opacity-50 disabled:pointer-events-none select-none';

    $variants = [
        'primary' => 'bg-gradient-to-r from-primary to-brand-2 text-primary-fg shadow-sm hover:opacity-95',
        'secondary' => 'bg-surface-sunken text-content hover:bg-line border border-line',
        'ghost' => 'text-content-secondary hover:bg-surface-sunken hover:text-content',
        'danger' => 'bg-danger text-white hover:opacity-90',
    ];

    $sizes = $icon
        ? ['sm' => 'h-8 w-8 text-sm', 'md' => 'h-9 w-9']
        : ['sm' => 'h-8 px-3 text-sm', 'md' => 'h-9 px-4 text-sm'];

    $classes = trim("$base {$variants[$variant]} {$sizes[$size]}");
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</{{ $tag }}>
