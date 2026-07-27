{{-- Haven newsletter form — lowercase serif invitation, hairline input and an
     ivory submit. Colors flip automatically inside .stt-haven-invert (espresso
     panels) and stay readable in both contexts. All wire: bindings, the
     $done / $email state and validation are preserved from the default view;
     only the presentation changes. --}}
<div>
    <p class="stt-haven-eyebrow mb-4">{{ __('storefront.the_newsletter') }}</p>
    <h2 class="stt-haven-display" style="font-size: clamp(1.6rem, 3.2vw, 2.3rem)">{{ $heading }}</h2>

    @if ($subheading)
        <p class="stt-haven-nl-sub mt-4 max-w-md text-sm leading-relaxed">{{ $subheading }}</p>
    @endif

    @if ($done)
        <p class="stt-haven-nl-done mt-7 flex items-center gap-2.5 text-sm font-semibold">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            {{ __('storefront.newsletter_done_haven') }}
        </p>
    @else
        <form wire:submit="subscribe" class="mt-7 max-w-md">
            <div class="flex flex-col gap-3 sm:flex-row">
                <label for="stt-news-{{ $this->getId() }}" class="sr-only">{{ __('storefront.email_address') }}</label>
                <div class="relative flex-1">
                    <input type="email" id="stt-news-{{ $this->getId() }}" wire:model="email" required placeholder="{{ __('storefront.your_email_address') }}"
                        class="stt-haven-input w-full">
                    <span class="pointer-events-none absolute right-3 top-2.5 text-xs leading-none" style="color: var(--st-accent)" aria-hidden="true" title="Required">*</span>
                </div>
                <button type="submit" class="stt-haven-btn stt-haven-btn--light shrink-0" style="border-color: color-mix(in srgb, var(--st-ink) 45%, transparent)">
                    <span wire:loading.remove wire:target="subscribe">{{ __('storefront.subscribe') }}</span>
                    <span wire:loading wire:target="subscribe">&hellip;</span>
                </button>
            </div>
            @error('email')
                <p class="stt-haven-nl-error mt-3 text-xs">{{ $message }}</p>
            @enderror
        </form>
    @endif
</div>
