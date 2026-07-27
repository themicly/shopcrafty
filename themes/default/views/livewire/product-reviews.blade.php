<div class="st-container border-t py-14" style="border-color: var(--st-line)">
    <div class="grid gap-10 lg:grid-cols-[1fr_1.4fr]">
        {{-- Summary + form --}}
        <div>
            <h2 class="st-display text-2xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.reviews') }}</h2>
            <div class="mt-2 flex items-center gap-2">
                <x-st.stars :rating="$product->reviews_avg" :count="$product->reviews_count" />
                @if ($product->reviews_count > 0)
                    <span class="text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.rating_average', ['rating' => number_format($product->reviews_avg, 1)]) }}</span>
                @endif
            </div>

            <form wire:submit="submit" class="mt-6 space-y-3">
                @php
                    $ctrl = 'w-full border px-3 py-2.5 text-sm outline-none';
                    $ctrlStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
                @endphp

                {{-- Star picker --}}
                <div x-data="{ r: @entangle('rating') }" class="flex items-center gap-1 text-2xl" style="color: var(--st-star)">
                    @for ($i = 1; $i <= 5; $i++)
                        <button type="button" x-on:click="r = {{ $i }}" aria-label="{{ __('storefront.star_label', ['count' => $i]) }}">
                            <span x-text="r >= {{ $i }} ? '★' : '☆'"></span>
                        </button>
                    @endfor
                </div>
                @error('rating')<p class="text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror

                @guest('customer')
                    <input wire:model="authorName" placeholder="{{ __('storefront.your_name') }}" class="{{ $ctrl }}" style="{{ $ctrlStyle }}">
                    @error('authorName')<p class="text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                @endguest

                <input wire:model="title" placeholder="{{ __('storefront.review_title_placeholder') }}" class="{{ $ctrl }}" style="{{ $ctrlStyle }}">
                <textarea wire:model="body" rows="3" placeholder="{{ __('storefront.review_body_placeholder') }}" class="{{ $ctrl }}" style="{{ $ctrlStyle }}"></textarea>
                @error('body')<p class="text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror

                <button type="submit" class="px-5 py-3 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">
                    <span wire:loading.remove wire:target="submit">{{ __('storefront.submit_review') }}</span>
                    <span wire:loading wire:target="submit">{{ __('storefront.submitting') }}</span>
                </button>
            </form>
        </div>

        {{-- Review list --}}
        <div>
            @forelse ($reviews as $review)
                <div class="border-b py-5 first:pt-0" style="border-color: var(--st-line)">
                    <div class="flex items-center justify-between gap-2">
                        <x-st.stars :rating="$review->rating" :show-count="false" />
                        <span class="text-xs" style="color: var(--st-ink-soft)">{{ $review->created_at?->format('M j, Y') }}</span>
                    </div>
                    @if ($review->title)
                        <p class="mt-2 text-sm font-semibold" style="color: var(--st-ink)">{{ $review->title }}</p>
                    @endif
                    <p class="mt-1 text-sm leading-relaxed" style="color: var(--st-ink)">{{ $review->body }}</p>
                    <p class="mt-2 text-xs" style="color: var(--st-ink-soft)">
                        {{ $review->author_name }}
                        @if ($review->verified_purchase)
                            · <span style="color: var(--st-primary)">{{ __('storefront.verified_purchase') }}</span>
                        @endif
                    </p>
                </div>
            @empty
                <p class="text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.no_reviews_hint') }}</p>
            @endforelse
        </div>
    </div>
</div>
