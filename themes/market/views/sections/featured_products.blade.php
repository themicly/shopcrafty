@php
    $products = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->when(! empty($s['scope_categories']), fn ($q) => $q->whereIn('category_id', $s['scope_categories']))
        ->latest()
        ->limit((int) ($s['limit'] ?? 10))
        ->get();
@endphp

@if ($products->isNotEmpty())
    {{-- Marketplace (WoodMart mould): boxed, high-density product grid under a
         consistent section header — red eyebrow · bold title · short red underline
         rule · right-aligned "View all →". --}}
    <section class="stt-market-section stt-market-section--surface">
        <div class="st-container">
            <div class="stt-market-shead st-reveal">
                <div>
                    <p class="stt-market-eyebrow">{{ __('storefront.curated_for_you') }}</p>
                    <h2 class="stt-market-title">{{ $s['heading'] ?? 'Featured products' }}</h2>
                    <span class="stt-market-rule"></span>
                </div>
                <a href="{{ url('/shop') }}" class="stt-market-viewall">
                    {{ __('storefront.view_all') }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0-6-6m6 6-6 6"/></svg>
                </a>
            </div>

            {{-- stt-market-stagger: incremental reveal delays on the dense grid (capped ~6). --}}
            <div class="stt-market-stagger grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach ($products as $product)
                    <x-st.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif
