@php
    $products = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->when(! empty($s['scope_categories']), fn ($q) => $q->whereIn('category_id', $s['scope_categories']))
        ->latest()
        ->limit((int) ($s['limit'] ?? 8))
        ->get();
@endphp

@if ($products->isNotEmpty())
    {{-- Studio: FEATURED PRODUCTS — the centered uppercase serif head closed by
         an underline rule, over a four-up commerce grid of classic cards
         (image, heart, title, stars, bold price, black Add-to-cart). --}}
    <section class="stt-studio-section" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="stt-studio-head st-reveal">
                <h2 class="stt-studio-title stt-studio-title--rule">{{ $s['heading'] ?? 'Featured products' }}</h2>
            </div>

            <div class="stt-studio-grid stt-studio-stagger">
                @foreach ($products as $product)
                    <x-st.product-card :product="$product" />
                @endforeach
            </div>

            <div class="st-reveal mt-12 text-center">
                <a href="{{ url('/shop') }}" class="stt-studio-btn">{{ __('storefront.view_all_products') }}</a>
            </div>
        </div>
    </section>
@endif
