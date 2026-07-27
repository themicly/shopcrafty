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
    {{-- Marketplace: WoodMart-mould "Featured Categories" — hairline-boxed squared tiles,
         a square product image on a soft surface pad, then the name and a grey "N products"
         count. Section opens with a red eyebrow, bold title, short red underline rule and a
         right-aligned "View all →" link. Rationed red; no gradients or overlays. --}}
    <section class="stt-market-section stt-market-section--bg">
        <div class="st-container">
            <div class="stt-market-shead st-reveal">
                <div>
                    <p class="stt-market-eyebrow">{{ __('storefront.browse_the_store') }}</p>
                    <h2 class="stt-market-title">{{ $s['heading'] ?? 'Featured categories' }}</h2>
                    <span class="stt-market-rule"></span>
                </div>
                <a href="{{ url('/shop') }}" class="stt-market-viewall">
                    {{ __('storefront.view_all') }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0-6-6m6 6-6 6"/></svg>
                </a>
            </div>

            {{-- stt-market-stagger: incremental reveal delays across the tile row (capped ~6). --}}
            <div class="stt-market-stagger grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($categories as $category)
                    @php $tileImage = $category->image_path ?: $category->image; @endphp
                    <a href="{{ url('/category/' . $category->slug) }}" class="stt-market-tile st-reveal">
                        <div class="stt-market-tile-media">
                            @if ($tileImage)
                                <img src="{{ $tileImage }}" alt="{{ $category->name }}" loading="lazy">
                            @elseif ($category->icon)
                                <div class="grid h-full w-full place-items-center">
                                    <span class="text-4xl leading-none">{{ $category->icon }}</span>
                                </div>
                            @else
                                <div class="grid h-full w-full place-items-center">
                                    <span class="st-display text-4xl font-semibold" style="color: color-mix(in srgb, var(--st-primary) 45%, var(--st-surface))">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="px-3 py-4">
                            <p class="stt-market-tile-name truncate">{{ $category->name }}</p>
                            <p class="stt-market-tile-count">{{ $counts[$category->id] ?? 0 }} {{ \Illuminate\Support\Str::plural('product', $counts[$category->id] ?? 0) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
