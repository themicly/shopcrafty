@props([
    'name' => '',
    'src' => null,
    'size' => 'md',
])

@php
    $sizes = ['xs' => 'h-6 w-6 text-xs', 'sm' => 'h-8 w-8 text-sm', 'md' => 'h-9 w-9 text-sm', 'lg' => 'h-12 w-12 text-base'];
    $s = $sizes[$size] ?? $sizes['md'];
    $initial = strtoupper(mb_substr(trim($name) ?: '?', 0, 1));
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}" {{ $attributes->merge(['class' => "$s shrink-0 rounded-full object-cover"]) }}>
@else
    <span {{ $attributes->merge(['class' => "$s grid shrink-0 place-items-center rounded-full bg-surface-sunken font-medium text-content-secondary"]) }}>
        {{ $initial }}
    </span>
@endif
