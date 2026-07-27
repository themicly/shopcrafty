@props([
    'points' => [],
    'type' => 'line',
    'height' => 48,
])

@php
    // Dependency-free inline SVG (line or bar). Token-colored via currentColor;
    // set the text color on the element (e.g. class="text-primary").
    $points = array_values(array_map('floatval', $points));
    $n = count($points);
    $w = 100;
    $h = (float) $height;
    $max = $n ? max($points) : 0;
    $min = $n ? min($points) : 0;
    $range = ($max - $min) ?: 1;
    $x = fn ($i) => $n > 1 ? ($i / ($n - 1)) * $w : $w / 2;
    $y = fn ($v) => $h - (($v - $min) / $range) * ($h - 4) - 2;
@endphp

<svg viewBox="0 0 {{ $w }} {{ $height }}" preserveAspectRatio="none" {{ $attributes->merge(['class' => 'h-12 w-full overflow-visible']) }} aria-hidden="true">
    @if ($n === 0)
        <line x1="0" y1="{{ $h / 2 }}" x2="{{ $w }}" y2="{{ $h / 2 }}" stroke="currentColor" stroke-opacity="0.2" stroke-width="1" />
    @elseif ($type === 'bar')
        @php $bw = $w / max($n * 1.6, 1); @endphp
        @foreach ($points as $i => $v)
            <rect x="{{ $x($i) - $bw / 2 }}" y="{{ $y($v) }}" width="{{ $bw }}" height="{{ max($h - $y($v) - 2, 0) }}" rx="1" fill="currentColor" fill-opacity="0.85" />
        @endforeach
    @else
        @php
            $line = implode(' ', array_map(fn ($v, $i) => round($x($i), 2).','.round($y($v), 2), $points, array_keys($points)));
            $area = '0,'.$h.' '.$line.' '.$w.','.$h;
        @endphp
        <polygon points="{{ $area }}" fill="currentColor" fill-opacity="0.08" />
        <polyline points="{{ $line }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
    @endif
</svg>
