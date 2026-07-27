@props([
    // [['value' => int, 'colorClass' => 'text-primary', 'label' => string], ...] — colorClass
    // is a `text-*` token class; the stroke reads it via currentColor, same technique the
    // line/bar chart component uses, so segments stay on the shared design-system palette
    // (no raw hex here).
    'segments' => [],
    'centerLabel' => null,
    'centerValue' => null,
    'size' => 160,
])

@php
    $total = array_sum(array_column($segments, 'value'));
    $radius = 40;
    $circumference = 2 * M_PI * $radius;
    $offset = 0;
@endphp

<div class="relative inline-flex items-center justify-center" style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg viewBox="0 0 100 100" class="-rotate-90" style="width: {{ $size }}px; height: {{ $size }}px;">
        <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="currentColor" class="text-surface-sunken" stroke-width="12" />
        @if ($total > 0)
            @foreach ($segments as $segment)
                @php
                    $fraction = $segment['value'] / $total;
                    $length = $fraction * $circumference;
                @endphp
                @if ($segment['value'] > 0)
                    <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke="currentColor" stroke-width="12"
                        class="{{ $segment['colorClass'] ?? 'text-primary' }}"
                        stroke-dasharray="{{ round($length, 2) }} {{ round($circumference - $length, 2) }}"
                        stroke-dashoffset="{{ round(-$offset, 2) }}" stroke-linecap="butt">
                        <title>{{ $segment['label'] }}: {{ $segment['value'] }}</title>
                    </circle>
                    @php $offset += $length; @endphp
                @endif
            @endforeach
        @endif
    </svg>
    @php $innerBox = (int) round($size * 0.62); @endphp
    @if ($centerLabel || $centerValue)
        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
            @if ($centerValue)
                <span class="overflow-hidden text-ellipsis whitespace-nowrap text-sm font-semibold text-content"
                    style="max-width: {{ $innerBox }}px;" title="{{ $centerValue }}">{{ $centerValue }}</span>
            @endif
            @if ($centerLabel)<span class="text-[11px] text-content-muted">{{ $centerLabel }}</span>@endif
        </div>
    @endif
</div>
