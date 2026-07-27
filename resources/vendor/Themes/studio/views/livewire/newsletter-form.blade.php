{{-- Studio newsletter form — classic fashion treatment: an UPPERCASE serif
     heading, quiet subline, boxed email input and a solid black Subscribe.
     Colors flip automatically inside .stt-studio-invert (black footer). All
     wire: bindings, the $done / $email state and validation are preserved from
     the default view; only the presentation changes. --}}
<div>
    <h2 class="stt-studio-nl-heading">{{ $heading }}</h2>

    @if ($subheading)
        <p class="stt-studio-nl-sub mt-4 max-w-md text-sm leading-relaxed">{{ $subheading }}</p>
    @endif

    @if ($done)
        <p class="stt-studio-nl-done mt-7 flex items-center gap-2.5 text-sm font-semibold">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
            {{ __('storefront.newsletter_done_studio') }}
        </p>
    @else
        <form wire:submit="subscribe" class="mt-7 max-w-md">
            <div class="flex flex-col gap-3 sm:flex-row">
                <label for="stt-news-{{ $this->getId() }}" class="sr-only">{{ __('storefront.email_address') }}</label>
                <div class="relative flex-1">
                    <input type="email" id="stt-news-{{ $this->getId() }}" wire:model="email" required placeholder="{{ __('storefront.enter_your_email') }}"
                        class="stt-studio-input w-full">
                    <span class="pointer-events-none absolute right-3 top-2.5 text-xs leading-none" style="color: var(--st-accent)" aria-hidden="true" title="Required">*</span>
                </div>
                <button type="submit" class="stt-studio-btn shrink-0">
                    <span wire:loading.remove wire:target="subscribe">{{ __('storefront.subscribe') }}</span>
                    <span wire:loading wire:target="subscribe">…</span>
                </button>
            </div>
            @error('email')
                <p class="stt-studio-nl-error mt-3 text-xs">{{ $message }}</p>
            @enderror
        </form>
    @endif
</div>
