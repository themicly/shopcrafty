@props([
    'label',
    'value',
    'delta' => null,
    'trend' => null,
    'hint' => null,
    'accent' => null,
])

@php
    $trend ??= ($delta !== null ? ($delta >= 0 ? 'up' : 'down') : null);
    $deltaClass = $trend === 'up' ? 'text-success' : ($trend === 'down' ? 'text-danger' : 'text-content-muted');
    $valueColor = match ($accent) {
        'success' => 'text-success',
        'warning' => 'text-warning',
        'danger' => 'text-danger',
        'primary' => 'text-primary',
        default => 'text-content',
    };
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border border-line bg-surface-raised p-5 shadow-sm']) }}>
    <div class="flex items-center justify-between gap-2">
        <p class="text-xs font-medium uppercase tracking-wide text-content-muted">{{ $label }}</p>
        @if ($delta !== null)
            <span class="text-xs font-medium {{ $deltaClass }}">
                {{ $delta >= 0 ? '+' : '' }}{{ $delta }}%
            </span>
        @endif
    </div>
    <p class="mt-2 text-2xl font-semibold tabular-nums {{ $valueColor }}">{{ $value }}</p>
    @if ($hint || isset($sparkline))
        <div class="mt-1 flex items-end justify-between gap-2">
            @if ($hint)<p class="text-xs text-content-muted">{{ $hint }}</p>@endif
            @isset($sparkline)<div class="text-primary">{{ $sparkline }}</div>@endisset
        </div>
    @endif
</div>
