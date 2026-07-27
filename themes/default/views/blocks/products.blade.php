@php
    $products = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->whereIn('id', $b['product_ids'] ?? [])
        ->get();
@endphp

@if ($products->isNotEmpty())
    <section class="st-reveal py-16 sm:py-24" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="mb-10 sm:mb-14">
                <x-st.section-heading :title="$b['heading'] ?? 'Products'" />
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4">
                @foreach ($products as $product)
                    <x-st.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif
