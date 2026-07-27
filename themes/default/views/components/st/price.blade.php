@props([
    'price',
    'compareAt' => null,
    'size' => 'md',
])

@php
    $onSale = $compareAt && $compareAt > $price;
    $sizeCls = $size === 'lg' ? 'text-2xl' : ($size === 'sm' ? 'text-sm' : 'text-base');
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-baseline gap-2']) }}>
    <span class="{{ $sizeCls }} font-semibold" style="color: var(--st-ink)">{{ format_money($price) }}</span>
    @if ($onSale)
        <span class="text-sm line-through" style="color: var(--st-ink-soft)">{{ format_money($compareAt) }}</span>
        <span class="rounded-full px-1.5 py-0.5 text-[11px] font-semibold" style="background: var(--st-accent); color: #fff">
            -{{ (int) round((($compareAt - $price) / $compareAt) * 100) }}%
        </span>
    @endif
</span>
