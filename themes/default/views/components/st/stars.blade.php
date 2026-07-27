@props([
    'rating' => 0,
    'count' => null,
    'showCount' => true,
])

@php $r = (float) $rating; @endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5']) }}>
    <span class="inline-flex text-sm" style="color: var(--st-star); letter-spacing: 1px" aria-label="{{ __('storefront.rating_out_of_5', ['rating' => number_format($r, 1)]) }}">
        @for ($i = 1; $i <= 5; $i++)<span aria-hidden="true">{{ $i <= round($r) ? '★' : '☆' }}</span>@endfor
    </span>
    @if ($showCount && $count !== null)
        <span class="text-xs" style="color: var(--st-ink-soft)">{{ $count > 0 ? '('.$count.')' : __('storefront.no_reviews_short') }}</span>
    @endif
</span>
