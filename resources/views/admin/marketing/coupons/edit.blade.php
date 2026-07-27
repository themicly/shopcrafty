<x-layouts.admin title="Edit coupon">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-content">Edit coupon</h2>
        <p class="mt-1 text-sm text-content-muted">Update this discount code.</p>
    </div>
    <livewire:marketing.coupon-builder :coupon="$couponId" />
</x-layouts.admin>
