@php
    // Prefer real, approved, high-rated reviews so the social proof is genuine.
    // Only fall back to configured/placeholder items when there are none yet.
    $reviewModel = app('Themicly\\Shopcrafty\\Core\\Module\\AddonRegistry')->all()['reviews']['review_model'] ?? null;
    $realReviews = $reviewModel ? $reviewModel::query()
        ->where('status', 'approved')
        ->where('rating', '>=', 4)
        ->whereNotNull('body')
        ->latest()
        ->limit(6)
        ->get() : collect();

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
@endphp

@if (! empty($items))

<section class="py-16 sm:py-24" style="background: var(--st-bg)">
    <div class="st-container">
        <div class="mb-10 sm:mb-14">
            <x-st.section-heading :eyebrow="__('storefront.eyebrow_real_reviews')" :title="$s['heading'] ?? 'What customers say'" align="center" />
            <span aria-hidden="true" style="display: block; width: 3rem; height: 3px; margin: 0.875rem auto 0; border-radius: 999px; background: linear-gradient(90deg, var(--st-primary), var(--st-accent))"></span>
        </div>

        <div class="-mx-4 flex snap-x snap-mandatory gap-5 overflow-x-auto px-4 pb-2 md:mx-0 md:grid md:grid-cols-3 md:gap-6 md:overflow-visible md:px-0 md:pb-0">
            @foreach ($items as $item)
                <figure
                    class="st-reveal flex min-w-[80%] shrink-0 snap-center flex-col border p-6 sm:min-w-[60%] md:min-w-0"
                    style="border-color: var(--st-line); border-radius: var(--st-radius); background: var(--st-surface)">
                    @php $rating = (int) ($item['rating'] ?? 5); @endphp
                    <div class="mb-4 flex items-center gap-1">
                        @for ($i = 0; $i < 5; $i++)
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="{{ $i < $rating ? 'var(--st-star)' : 'var(--st-line)' }}" aria-hidden="true">
                                <path d="M10 1.5l2.472 5.007 5.528.803-4 3.898.944 5.506L10 14.11l-4.944 2.604.944-5.506-4-3.898 5.528-.803L10 1.5z" />
                            </svg>
                        @endfor
                        <span class="sr-only">{{ __('storefront.rating_out_of_5_stars', ['rating' => $rating]) }}</span>
                    </div>
                    <blockquote class="flex-1 text-base leading-relaxed" style="color: var(--st-ink)">
                        &ldquo;{{ $item['quote'] }}&rdquo;
                    </blockquote>
                    <figcaption class="mt-5 flex items-center gap-2 text-sm font-medium" style="color: var(--st-ink-soft)">
                        {{ $item['name'] }}
                        @if ($item['verified'] ?? false)
                            <span class="inline-flex items-center gap-1 text-xs" style="color: var(--st-success)">✓ {{ __('storefront.verified_purchase') }}</span>
                        @endif
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif
