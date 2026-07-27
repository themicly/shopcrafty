@props([
    'variant' => 'info',   // info | tip | warning | success
    'title' => null,
    'icon' => null,
])

@php
    // Plain-language guidance box for non-technical admins. Colour + icon are
    // derived from the variant so callers only pick an intent, not styling.
    $variants = [
        'info' => ['icon' => 'info', 'ring' => 'border-info/30', 'bg' => 'bg-info-soft', 'fg' => 'text-info'],
        'tip' => ['icon' => 'light-bulb', 'ring' => 'border-primary/30', 'bg' => 'bg-primary-soft', 'fg' => 'text-primary'],
        'warning' => ['icon' => 'warning', 'ring' => 'border-warning/30', 'bg' => 'bg-warning-soft', 'fg' => 'text-warning'],
        'success' => ['icon' => 'check-circle', 'ring' => 'border-success/30', 'bg' => 'bg-success-soft', 'fg' => 'text-success'],
    ];
    $v = $variants[$variant] ?? $variants['info'];
    $iconName = $icon ?? $v['icon'];
@endphp

<div {{ $attributes->merge(['class' => 'flex gap-3 rounded-lg border px-4 py-3 '.$v['ring'].' '.$v['bg']]) }}>
    <x-ui.icon :name="$iconName" class="mt-0.5 h-5 w-5 shrink-0 {{ $v['fg'] }}" />
    <div class="min-w-0 text-sm text-content-secondary">
        @if ($title)
            <p class="font-semibold text-content">{{ $title }}</p>
        @endif
        <div @class(['mt-0.5' => $title, 'leading-relaxed']) >{{ $slot }}</div>
    </div>
</div>
