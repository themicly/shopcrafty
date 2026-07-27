@php
    $products = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->when(! empty($s['scope_categories']), fn ($q) => $q->whereIn('category_id', $s['scope_categories']))
        ->latest()
        ->limit((int) ($s['limit'] ?? 10))
        ->get();
@endphp

{{-- Bloom: signature grocery homepage — a soft basket grid of fresh picks on a
     warm cream market band. Category browsing lives in its own "Category
     tiles" section (sections/categories.blade.php), not bolted on here. --}}
<section class="stt-fresh-section" style="background: var(--st-bg)">
    <div class="st-container">
        @if ($products->isNotEmpty())
            <div class="st-reveal flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="stt-fresh-eyebrow">{{ __('storefront.fresh_picks') }}</p>
                    <h2 class="stt-fresh-heading mt-2 text-2xl sm:text-3xl">{{ $s['heading'] ?? 'Featured' }}</h2>
                </div>
                <a href="{{ url('/shop') }}" class="stt-fresh-viewall">{{ __('storefront.view_all') }} &rarr;</a>
            </div>

            <div class="st-reveal stt-fresh-stagger mt-8 grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($products as $product)
                    <x-st.product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </div>
</section>
