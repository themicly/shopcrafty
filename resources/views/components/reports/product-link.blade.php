@props([
    'product', // Themicly\Shopcrafty\Modules\Catalog\Models\Product|null — null when the product was since deleted
    'name' => '',
])

@php $label = $name !== '' ? $name : ($product?->name ?? '—'); @endphp

@if ($product)
    <a href="{{ route('admin.catalog.products.edit', $product) }}"
        {{ $attributes->merge(['class' => 'font-medium text-content hover:text-primary hover:underline focus-visible:underline focus-visible:outline-none']) }}
        title="Open {{ $label }} in the catalog">{{ $label }}</a>
@else
    {{-- No live product to link to (deleted since this data was recorded). --}}
    <span {{ $attributes->merge(['class' => 'text-content']) }}>{{ $label }}</span>
@endif
