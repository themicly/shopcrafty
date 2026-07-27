@php
    $products = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->when(! empty($s['scope_categories']), fn ($q) => $q->whereIn('category_id', $s['scope_categories']))
        ->latest()
        ->limit((int) ($s['limit'] ?? 8))
        ->get();
@endphp

@if ($products->isNotEmpty())
    {{-- Boutique: featured grid — centered uppercase retail head over the shared
         commerce cards (bordered style: badges, ratings, visible add-to-cart). --}}
    <section class="stt-boutique-section" style="background: var(--st-surface)">
        <div class="st-container stt-boutique-narrow">
            <div class="stt-boutique-head-center st-reveal stt-boutique-mb-loose mb-10">
                <span class="stt-boutique-eyebrow">{{ __('storefront.new_in_store') }}</span>
                <h2 class="stt-boutique-title">{{ $s['heading'] ?? 'The boutique' }}</h2>
                <span class="stt-boutique-mark" aria-hidden="true"></span>
            </div>

            <div class="stt-boutique-grid stt-boutique-stagger">
                @foreach ($products as $product)
                    <x-st.product-card :product="$product" />
                @endforeach
            </div>

            <div class="st-reveal mt-12 text-center">
                <a href="{{ url('/shop') }}" class="stt-boutique-btn-ghost">{{ __('storefront.shop_the_collection') }}</a>
            </div>
        </div>
    </section>
@endif
