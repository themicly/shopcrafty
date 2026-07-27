<x-layouts.admin title="Products">
    <x-admin.page-header title="Catalog" subtitle="Products, categories, brands, and attributes." />
    <x-admin.index-menu key="products" />
    <x-admin.catalog-nav />
    <livewire:catalog.product-list />

    {{-- Import slide-over --}}
    <x-ui.drawer name="import-products" title="Import products" width="xl">
        <livewire:catalog.product-import />
    </x-ui.drawer>
</x-layouts.admin>
