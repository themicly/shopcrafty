@php
    $categories = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots();
    if (! empty($s['scope_categories'])) {
        $categories = $categories->whereIn('id', $s['scope_categories']);
    }
    $categories = $categories->take((int) ($s['limit'] ?? 6));
@endphp

@if ($categories->isNotEmpty())
    {{-- Studio: the category trio — tall photo cards, each carrying a centered
         white plaque label near the base (the theme's signature mark). --}}
    <section class="stt-studio-section" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="stt-studio-head st-reveal">
                <p class="stt-studio-eyebrow">{{ __('storefront.collections') }}</p>
                <h2 class="stt-studio-title stt-studio-title--rule">{{ $s['heading'] ?? 'Shop by category' }}</h2>
            </div>

            <div class="stt-studio-cat-grid stt-studio-stagger">
                @foreach ($categories as $category)
                    @php $tileImage = $category->image_path ?: $category->image; @endphp
                    <a href="{{ url('/category/' . $category->slug) }}" class="stt-studio-catcard st-reveal" style="aspect-ratio: 3 / 4; background: var(--st-surface)">
                        @if ($tileImage)
                            <img src="{{ $tileImage }}" alt="{{ $category->name }}" loading="lazy">
                        @else
                            {{-- No photo yet: a sage field with an oversized serif initial. --}}
                            <span class="grid h-full w-full place-items-center" style="background: color-mix(in srgb, var(--st-band) 55%, var(--st-bg))" aria-hidden="true">
                                <span style="font-family: var(--st-font-display); font-weight: 600; font-size: 4.5rem; color: color-mix(in srgb, var(--st-ink) 55%, var(--st-band))">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                            </span>
                        @endif
                        <span class="stt-studio-plaque">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
