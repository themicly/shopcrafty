@php
    // Prefer real, approved, high-rated reviews so the social proof is genuine.
    // Only fall back to configured/placeholder items when there are none yet.
    $realReviews = \Themicly\Shopcrafty\Modules\Catalog\Models\ProductReview::query()
        ->where('status', 'approved')
        ->where('rating', '>=', 4)
        ->whereNotNull('body')
        ->latest()
        ->limit(6)
        ->get();

    if ($realReviews->isNotEmpty()) {
        $items = $realReviews->map(fn ($r) => [
            'quote' => $r->body,
            'name' => $r->author_name,
            'rating' => (int) $r->rating,
            'verified' => (bool) $r->verified_purchase,
        ])->all();
    } else {
        $items = $s['items'] ?? [];
    }
    $items = array_slice($items, 0, 6);
@endphp

@if (! empty($items))
    {{-- Haven testimonials — "from their homes": offset quote columns on the
         linen band (the middle column drops a notch), each card opened by an
         oversized italic brass quote mark and closed above a hairline rule by the
         reader's name. No carousel — nothing to pause, nothing to trap keys. --}}
    <section class="stt-haven-section" style="background: var(--st-surface)">
        <div class="st-container">
            <div class="stt-haven-head st-reveal">
                <p class="stt-haven-eyebrow mb-3">{{ __('storefront.from_their_homes') }}</p>
                <h2 class="stt-haven-display stt-haven-title">{{ $s['heading'] ?? 'Living with our pieces' }}</h2>
            </div>

            <div class="stt-haven-quote-grid stt-haven-stagger st-reveal">
                @foreach ($items as $item)
                    @php $rating = (int) ($item['rating'] ?? 5); @endphp
                    <figure class="stt-haven-quote">
                        <span class="stt-haven-quote-mark" aria-hidden="true">&ldquo;</span>
                        <div class="mt-4 flex items-center gap-1">
                            @for ($i = 0; $i < 5; $i++)
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="{{ $i < $rating ? 'var(--st-accent)' : 'var(--st-line)' }}" aria-hidden="true">
                                    <path d="M10 1.5l2.472 5.007 5.528.803-4 3.898.944 5.506L10 14.11l-4.944 2.604.944-5.506-4-3.898 5.528-.803L10 1.5z" />
                                </svg>
                            @endfor
                            <span class="sr-only">{{ __('storefront.stars_out_of_5', ['rating' => $rating]) }}</span>
                        </div>
                        <blockquote class="mt-4 flex-1 leading-relaxed" style="font-family: var(--st-font-display); font-weight: 400; font-size: 1.05rem; color: var(--st-ink)">
                            {{ $item['quote'] }}
                        </blockquote>
                        <div class="stt-haven-divider mt-6" aria-hidden="true"></div>
                        <figcaption class="mt-4 flex items-center justify-between gap-2">
                            <span class="stt-haven-crumb" style="color: var(--st-ink)">{{ $item['name'] }}</span>
                            @if ($item['verified'] ?? false)
                                <span class="text-xs font-medium" style="color: var(--st-accent)">{{ __('storefront.verified_purchase') }}</span>
                            @endif
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
