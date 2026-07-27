@php
    use Illuminate\Support\Facades\Route;
    $addons = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class);

    $tabs = [
        ['route' => 'admin.catalog.products.index', 'label' => 'Products'],
        ['route' => 'admin.catalog.categories.index', 'label' => 'Categories'],
        ['route' => 'admin.catalog.brands.index', 'label' => 'Brands'],
        ['route' => 'admin.catalog.size-charts.index', 'label' => 'Size charts', 'addon' => 'size-chart'],
        ['route' => 'admin.catalog.inventory.index', 'label' => 'Inventory'],
        ['route' => 'admin.catalog.attributes.index', 'label' => 'Attributes'],
        ['route' => 'admin.catalog.reviews.index', 'label' => 'Reviews', 'addon' => 'reviews'],
    ];
@endphp

{{-- overflow-x-auto: on narrow viewports the 7 tabs don't fit on one line; without
     this the row overflows its own box and drags the whole page into horizontal
     scroll instead of just scrolling this strip. --}}
<div class="mb-6 flex flex-nowrap gap-1 overflow-x-auto border-b border-line">
    @foreach ($tabs as $tab)
        @continue(($tab['addon'] ?? null) && ! $addons->installed($tab['addon']))
        @php $exists = Route::has($tab['route']); $active = $exists && request()->routeIs($tab['route']); @endphp
        <a
            href="{{ $exists ? route($tab['route']) : '#' }}"
            @class([
                '-mb-px shrink-0 whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium transition-colors',
                'border-primary text-content' => $active,
                'border-transparent text-content-muted hover:text-content' => ! $active,
                'pointer-events-none opacity-40' => ! $exists,
            ])
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
