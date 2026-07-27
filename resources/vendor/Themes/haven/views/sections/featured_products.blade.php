@php
    $products = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->when(! empty($s['scope_categories']), fn ($q) => $q->whereIn('category_id', $s['scope_categories']))
        ->latest()
        ->limit((int) ($s['limit'] ?? 8))
        ->get();
@endphp

@if ($products->isNotEmpty())
    {{-- Haven: featured pieces — left-aligned head behind its ghost numeral,
         a "view all" underline-draw link on the rule line, then a four-up grid
         of gallery cards revealed in a stagger. --}}
    <section class="stt-haven-section" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="stt-haven-head st-reveal">
                <p class="stt-haven-eyebrow mb-3">{{ __('storefront.the_collection') }}</p>
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <h2 class="stt-haven-display stt-haven-title">{{ $s['heading'] ?? 'Pieces of the season' }}</h2>
                    <a href="{{ url('/shop') }}" class="stt-haven-link stt-haven-link--caps mb-1.5">{{ __('storefront.view_all_pieces') }}</a>
                </div>
            </div>

            <div class="stt-haven-divider st-reveal mb-10" aria-hidden="true"></div>

            <div class="stt-haven-grid stt-haven-stagger st-reveal">
                @foreach ($products as $product)
                    <x-st.product-card :product="$product" />
                @endforeach
            </div>

            <div class="st-reveal mt-14 text-center">
                <a href="{{ url('/shop') }}" class="stt-haven-btn stt-haven-btn--ghost">
                    {{ __('storefront.browse_full_collection') }}
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
        </div>
    </section>
@endif
