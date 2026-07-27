@props(['active'])

@php
    $items = [
        ['key' => 'overview', 'label' => 'Overview', 'route' => 'admin.reports.index'],
        ['key' => 'orders', 'label' => 'Orders', 'route' => 'admin.reports.orders'],
        ['key' => 'inventory', 'label' => 'Inventory', 'route' => 'admin.reports.inventory'],
        ['key' => 'customers', 'label' => 'Customers', 'route' => 'admin.reports.customers'],
        ['key' => 'coupons', 'label' => 'Coupons', 'route' => 'admin.reports.coupons'],
        ['key' => 'refunds', 'label' => 'Refunds', 'route' => 'admin.reports.refunds'],
        ['key' => 'search-terms', 'label' => 'Search terms', 'route' => 'admin.reports.search-terms'],
    ];
@endphp

<nav class="mb-6 flex flex-wrap items-center gap-1 border-b border-line" aria-label="Reports">
    @foreach ($items as $item)
        <a href="{{ route($item['route']) }}"
            @class([
                'border-b-2 px-3 py-2 text-sm font-medium transition-colors -mb-px',
                'border-primary text-primary' => $active === $item['key'],
                'border-transparent text-content-muted hover:text-content' => $active !== $item['key'],
            ])
        >{{ $item['label'] }}</a>
    @endforeach
</nav>
