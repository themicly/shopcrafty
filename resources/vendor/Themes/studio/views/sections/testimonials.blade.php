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
@endphp

@if (! empty($items))
    {{-- Studio: THIS IS WHAT OUR CUSTOMERS SAY — a centered serif underlined
         head over a snap-scroll carousel of white cards, steered by round
         prev/next arrows. --}}
    <section class="stt-studio-section stt-studio-band-grey">
        <div class="st-container">
            <div class="stt-studio-head st-reveal">
                <h2 class="stt-studio-title stt-studio-title--rule">{{ $s['heading'] ?? 'This is what our customers say' }}</h2>
            </div>

            <div class="st-reveal" x-data="{
                    scroll(dir) {
                        const el = $refs.track;
                        const card = el.querySelector('.stt-studio-testi-card');
                        const step = card ? card.getBoundingClientRect().width + 24 : el.clientWidth * 0.8;
                        el.scrollBy({ left: dir * step, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
                    }
                }">
                <div class="stt-studio-testi-track" x-ref="track" tabindex="0" role="group" aria-label="{{ __('storefront.customer_reviews') }}">
                    @foreach ($items as $item)
                        @php $rating = (int) ($item['rating'] ?? 5); @endphp
                        <figure class="stt-studio-testi-card">
                            <div class="flex items-center gap-4">
                                {{-- Monogram avatar on a sage disc --}}
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full" aria-hidden="true"
                                    style="background: var(--st-band); font-family: var(--st-font-display); font-weight: 600; font-size: 1.15rem; color: var(--st-ink)">{{ strtoupper(substr((string) ($item['name'] ?? '·'), 0, 1)) }}</span>
                                <div>
                                    <div class="flex items-center gap-1" aria-hidden="true">
                                        @for ($i = 0; $i < 5; $i++)
                                            <svg class="h-4 w-4" viewBox="0 0 20 20"
                                                fill="{{ $i < $rating ? 'var(--st-accent)' : 'var(--st-line)' }}">
                                                <path d="M10 1.5l2.472 5.007 5.528.803-4 3.898.944 5.506L10 14.11l-4.944 2.604.944-5.506-4-3.898 5.528-.803L10 1.5z" />
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="sr-only">{{ __('storefront.stars_out_of_5', ['rating' => $rating]) }}</span>
                                </div>
                            </div>

                            <blockquote class="mt-5 flex-1 text-sm leading-relaxed" style="color: var(--st-ink)">
                                &ldquo;{{ $item['quote'] }}&rdquo;
                            </blockquote>

                            <figcaption class="mt-6 flex items-center justify-between gap-3 pt-4" style="border-top: 1px solid var(--st-line)">
                                <span style="font-family: var(--st-font-display); font-weight: 600; font-size: 0.95rem; color: var(--st-ink)">{{ $item['name'] }}</span>
                                @if ($item['verified'] ?? false)
                                    <span class="stt-studio-crumb">{{ __('storefront.verified_buyer') }}</span>
                                @endif
                            </figcaption>
                        </figure>
                    @endforeach
                </div>

                @if (count($items) > 1)
                    <div class="mt-2 flex items-center justify-center gap-3">
                        <button type="button" class="stt-studio-arrow" @click="scroll(-1)" aria-label="{{ __('storefront.previous_reviews') }}">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                        </button>
                        <button type="button" class="stt-studio-arrow" @click="scroll(1)" aria-label="{{ __('storefront.next_reviews') }}">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
