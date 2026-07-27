@php
    // "Shop by gram" tiles sourced from recent product imagery (no extra data required).
    $limit = (int) ($s['limit'] ?? 6);
    $tiles = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->when(! empty($s['scope_categories']), fn ($q) => $q->whereIn('category_id', $s['scope_categories']))
        ->latest()->limit($limit * 2)->get()
        ->filter(fn ($p) => $p->media->first()?->path)
        ->take($limit);
    $handle = '@' . \Illuminate\Support\Str::slug(settings('general.store_name', config('app.name')));
@endphp

@if ($tiles->isNotEmpty())
    {{-- Boutique: shop-the-gram — square tiles with a slide-up "Shop this look" bar. --}}
    <section class="stt-boutique-section">
        <div class="st-container stt-boutique-narrow">
            <div class="stt-boutique-head-center st-reveal stt-boutique-mb-loose mb-10">
                <span class="stt-boutique-eyebrow">{{ $handle }}</span>
                <h2 class="stt-boutique-title">{{ $s['heading'] ?? 'Shop the gram' }}</h2>
                <span class="stt-boutique-mark" aria-hidden="true"></span>
            </div>

            <div class="stt-boutique-ig-grid stt-boutique-stagger grid grid-cols-3 gap-4 lg:grid-cols-6">
                @foreach ($tiles as $product)
                    <a href="{{ url('/product/' . $product->slug) }}" class="stt-boutique-card st-reveal group block">
                        <div class="stt-boutique-card-media">
                            <img src="{{ $product->media->first()->path }}" alt="{{ $product->name }}" loading="lazy">
                            <div class="stt-boutique-card-add">{{ __('storefront.shop') }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
