<x-layouts.admin title="Orders">
    <x-admin.page-header title="Orders" subtitle="Track, verify, and fulfil customer orders.">
        <x-slot:actions>
            <x-ui.button variant="secondary" :href="route('admin.orders.cod-queue')">COD queue</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>
    <livewire:orders.order-list />
</x-layouts.admin>
