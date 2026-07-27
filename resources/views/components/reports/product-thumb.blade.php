@props([
    'product', // Themicly\Shopcrafty\Modules\Catalog\Models\Product|null — null when the product was since deleted
    'name' => '',
])

@php
    $img = $product?->featuredImage()?->path;
    $initial = strtoupper(substr($name !== '' ? $name : ($product?->name ?? '?'), 0, 1));
@endphp

<span {{ $attributes->merge(['class' => 'grid h-8 w-8 shrink-0 place-items-center overflow-hidden rounded-lg bg-success-soft text-xs font-semibold text-success']) }}>
    @if ($img)
        <img src="{{ $img }}" alt="" class="h-full w-full object-cover">
    @else
        {{ $initial }}
    @endif
</span>
