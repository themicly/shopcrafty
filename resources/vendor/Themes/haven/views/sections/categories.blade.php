@php
    $categories = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots();
    if (! empty($s['scope_categories'])) {
        $categories = $categories->whereIn('id', $s['scope_categories']);
    }
    $categories = $categories->take((int) ($s['limit'] ?? 6));
@endphp

@if ($categories->isNotEmpty())
    {{-- Haven: rooms collage — offset editorial tiles (middle column drops a
         notch), each closed by an index numeral, a lowercase serif label with a
         self-drawing underline, and a slow image zoom on hover. --}}
    <section class="stt-haven-section" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="stt-haven-head st-reveal">
                <p class="stt-haven-eyebrow mb-3">{{ __('storefront.the_rooms') }}</p>
                <h2 class="stt-haven-display stt-haven-title">{{ $s['heading'] ?? 'Shop by room' }}</h2>
            </div>

            <div class="stt-haven-cat-grid stt-haven-stagger st-reveal">
                @foreach ($categories as $category)
                    @php $tileImage = $category->image_path ?: $category->image; @endphp
                    <a href="{{ url('/category/' . $category->slug) }}" class="stt-haven-catcard group">
                        <figure style="aspect-ratio: 4 / 5">
                            @if ($tileImage)
                                <img src="{{ $tileImage }}" alt="" loading="lazy" class="h-full w-full object-cover">
                            @else
                                {{-- No photo yet: a linen field with an oversized lowercase serif initial. --}}
                                <span class="grid h-full w-full place-items-center" aria-hidden="true">
                                    <span style="font-family: var(--st-font-display); font-weight: 400; font-style: italic; font-size: 5.5rem; text-transform: lowercase; color: color-mix(in srgb, var(--st-ink) 22%, var(--st-surface))">{{ mb_substr($category->name, 0, 1) }}</span>
                                </span>
                            @endif
                        </figure>
                        <span class="mt-4 flex items-baseline justify-between gap-3">
                            <span class="stt-haven-display stt-haven-link" style="font-size: 1.35rem">{{ $category->name }}</span>
                            <span class="stt-haven-crumb" style="font-style: normal">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        </span>
                        @if ($category->description)
                            <span class="mt-1.5 block text-sm leading-relaxed" style="color: var(--st-ink-soft)">{{ \Illuminate\Support\Str::limit($category->description, 70) }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
