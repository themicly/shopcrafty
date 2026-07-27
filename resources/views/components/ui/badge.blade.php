@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'neutral' => 'bg-surface-sunken text-content-secondary',
        'primary' => 'bg-primary-soft text-primary',
        'success' => 'bg-success-soft text-success',
        'warning' => 'bg-warning-soft text-warning',
        'danger' => 'bg-danger-soft text-danger',
        'info' => 'bg-info-soft text-info',
    ];
@endphp

<span {{ $attributes->merge([
    'class' => 'inline-flex items-center gap-1 rounded-sm px-2 py-0.5 text-xs font-medium ' . $variants[$variant],
]) }}>
    {{ $slot }}
</span>
