@props([
    'icon',
    'variant' => 'ghost',
    'size' => 'sm',
    'href' => null,
    'label' => null,   // accessible name + tooltip (required for icon-only buttons)
    'type' => 'button',
])

@php
    // Standard admin icon action button. Use for table row actions and inline
    // actions (edit, delete, view, duplicate…). Always pass a `label` for a11y.
    $variants = [
        'ghost' => 'text-content-secondary hover:bg-surface-sunken hover:text-content',
        'danger' => 'text-content-secondary hover:bg-danger-soft hover:text-danger',
        'primary' => 'text-content-secondary hover:bg-primary-soft hover:text-primary',
        'success' => 'text-content-secondary hover:bg-success-soft hover:text-success',
        'secondary' => 'border border-line text-content-secondary hover:bg-surface-sunken hover:text-content',
    ];
    $sizes = ['sm' => 'h-8 w-8', 'md' => 'h-9 w-9'];
    $classes = trim('inline-flex items-center justify-center rounded-md transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-1 focus-visible:ring-offset-surface disabled:opacity-50 disabled:pointer-events-none '
        .($sizes[$size] ?? $sizes['sm']).' '.($variants[$variant] ?? $variants['ghost']));
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="{{ $type }}" @endif
    @if ($label) title="{{ $label }}" aria-label="{{ $label }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    <x-ui.icon :name="$icon" class="h-4 w-4" />
</{{ $tag }}>
