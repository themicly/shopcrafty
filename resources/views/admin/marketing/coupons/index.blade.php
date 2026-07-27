<x-layouts.admin title="Coupons">
    <x-admin.page-header title="Coupons" subtitle="Discount codes customers can apply at checkout.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.marketing.coupons.create')">New coupon</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>
    <livewire:marketing.coupon-list />
</x-layouts.admin>
