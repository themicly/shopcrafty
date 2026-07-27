@php
    $categories = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots();
    if (! empty($s['scope_categories'])) {
        $categories = $categories->whereIn('id', $s['scope_categories']);
    }
    $categories = $categories->take((int) ($s['limit'] ?? 6));
    // One grouped query for tile counts (avoids N+1).
    $counts = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->selectRaw('category_id, count(*) as c')->groupBy('category_id')->pluck('c', 'category_id');
@endphp

@if ($categories->isNotEmpty())
    {{-- Boutique: category showcase — square tiles, uppercase names, item counts,
         slide-up "Shop now" bar on hover. --}}
    <section class="stt-boutique-section">
        <div class="st-container stt-boutique-narrow">
            <div class="stt-boutique-head-center st-reveal stt-boutique-mb-loose mb-10">
                <span class="stt-boutique-eyebrow">{{ __('storefront.collections') }}</span>
                <h2 class="stt-boutique-title">{{ $s['heading'] ?? 'Shop by category' }}</h2>
                <span class="stt-boutique-mark" aria-hidden="true"></span>
            </div>

            <div class="stt-boutique-grid stt-boutique-stagger">
                @foreach ($categories as $category)
                    @php $tileImage = $category->image_path ?: $category->image; @endphp
                    <a href="{{ url('/category/' . $category->slug) }}" class="stt-boutique-card st-reveal block" style="text-decoration: none;">
                        <div class="stt-boutique-card-media">
                            @if ($tileImage)
                                <img src="{{ $tileImage }}" alt="{{ $category->name }}" loading="lazy">
                            @elseif ($category->icon)
                                <div class="grid h-full w-full place-items-center">
                                    <span class="text-4xl leading-none">{{ $category->icon }}</span>
                                </div>
                            @else
                                <div class="grid h-full w-full place-items-center">
                                    <span class="text-5xl font-bold" style="color: color-mix(in srgb, var(--st-accent) 45%, var(--st-surface))">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                </div>
                            @endif
                            <div class="stt-boutique-card-add">{{ __('storefront.shop_now') }}</div>
                        </div>
                        <div class="stt-boutique-card-body">
                            <p class="stt-boutique-card-title">{{ $category->name }}</p>
                            <p class="mt-1 text-xs font-semibold" style="color: var(--st-ink-soft)">{{ __('storefront.items_count', ['count' => $counts[$category->id] ?? 0]) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="st-reveal mt-12 text-center">
                <a href="{{ url('/shop') }}" class="stt-boutique-btn">{{ __('storefront.view_all_products') }}</a>
            </div>
        </div>
    </section>
@endif
