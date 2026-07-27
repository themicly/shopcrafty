@php
    $categories = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots();
    if (! empty($s['scope_categories'])) {
        $categories = $categories->whereIn('id', $s['scope_categories']);
    }
    $categories = $categories->take((int) ($s['limit'] ?? 8));
@endphp

@if ($categories->isNotEmpty())
    {{-- Bloom: "Shop by category" round crate circles on a warm cream market band —
         the same circle motif previously bolted onto Featured Products, now its own
         configurable section. --}}
    <section class="stt-fresh-section" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="st-reveal">
                <p class="stt-fresh-eyebrow">{{ __('storefront.fill_your_basket') }}</p>
                <h2 class="stt-fresh-heading mt-2 text-2xl sm:text-3xl">{{ $s['heading'] ?? 'Shop by category' }}</h2>
            </div>

            <div class="st-reveal stt-fresh-stagger mt-7 flex gap-5 overflow-x-auto pb-2 sm:grid sm:grid-cols-4 sm:gap-6 lg:grid-cols-8">
                @foreach ($categories as $category)
                    <a href="{{ url('/category/' . $category->slug) }}" class="stt-fresh-circle-link group flex shrink-0 flex-col items-center gap-2.5 text-center" style="width: 5.5rem">
                        <span class="stt-fresh-circle">
                            @if ($category->image)
                                <img src="{{ $category->image }}" alt="{{ $category->name }}">
                            @else
                                <span class="st-display text-2xl font-semibold" style="color: var(--st-primary)">{{ strtoupper(substr($category->name, 0, 1)) }}</span>
                            @endif
                        </span>
                        <span class="stt-fresh-circle-label w-full truncate">{{ $category->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
