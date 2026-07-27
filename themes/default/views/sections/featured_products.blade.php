@php
    $products = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->when(! empty($s['scope_categories']), fn ($q) => $q->whereIn('category_id', $s['scope_categories']))
        ->latest()
        ->limit((int) ($s['limit'] ?? 8))
        ->get();
@endphp

@if ($products->isNotEmpty())
    <section class="py-16 sm:py-24" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="st-reveal mb-10 flex flex-wrap items-end justify-between gap-4 sm:mb-14">
                <div>
                    <x-st.section-heading :eyebrow="__('storefront.eyebrow_curated')" :title="$s['heading'] ?? 'Featured'" />
                    <span aria-hidden="true" style="display: block; width: 3rem; height: 3px; margin-top: 0.875rem; border-radius: 999px; background: linear-gradient(90deg, var(--st-primary), var(--st-accent))"></span>
                </div>
                <a href="{{ url('/shop') }}"
                    class="stt-aurora-viewall hidden text-sm font-semibold sm:inline-flex">
                    {{ __('storefront.view_all') }}
                </a>
            </div>

            <div class="st-reveal stt-aurora-stagger grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4">
                @foreach ($products as $product)
                    <x-st.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif
