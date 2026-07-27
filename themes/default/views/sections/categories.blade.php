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
    {{-- Category tiles: image + name + product count (WoodMart/Glozin "Top Collections"). --}}
    <section class="py-16 sm:py-20" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="mb-8 sm:mb-10">
                <x-st.section-heading :eyebrow="__('storefront.eyebrow_browse_store')" :title="$s['heading'] ?? 'Shop by category'" />
                <span aria-hidden="true" style="display: block; width: 3rem; height: 3px; margin-top: 0.875rem; border-radius: 999px; background: linear-gradient(90deg, var(--st-primary), var(--st-accent))"></span>
            </div>

            <div class="st-reveal stt-aurora-stagger grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($categories as $category)
                    @php $tileImage = $category->image_path ?: $category->image; @endphp
                    <a href="{{ url('/category/' . $category->slug) }}" class="group stt-aurora-lift relative block overflow-hidden" style="border-radius: var(--st-radius); background: var(--st-surface)">
                        <div class="aspect-square w-full overflow-hidden">
                            @if ($tileImage)
                                <img src="{{ $tileImage }}" alt="{{ $category->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @elseif ($category->icon)
                                <div class="grid h-full w-full place-items-center">
                                    <span class="text-4xl leading-none">{{ $category->icon }}</span>
                                </div>
                            @else
                                <div class="grid h-full w-full place-items-center">
                                    <span class="st-display text-4xl font-semibold" style="color: color-mix(in srgb, var(--st-primary) 55%, var(--st-surface))">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="absolute inset-x-0 bottom-0 p-3" style="background: linear-gradient(to top, color-mix(in srgb, var(--st-ink) 78%, transparent), transparent)">
                            <p class="flex items-center gap-1.5 truncate text-sm font-semibold" style="color: #fff">
                                @if ($category->icon)<span class="shrink-0 leading-none">{{ $category->icon }}</span>@endif
                                <span class="truncate">{{ $category->name }}</span>
                            </p>
                            <p class="text-xs" style="color: rgba(255,255,255,.75)">{{ __('storefront.items_count', ['count' => $counts[$category->id] ?? 0]) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
