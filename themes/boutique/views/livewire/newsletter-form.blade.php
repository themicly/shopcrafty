{{-- Boutique newsletter form — bold uppercase heading over a boxed email field with a
     filled subscribe button. All wire: bindings, the $done / $email state and
     validation are preserved from the default view; only the presentation changes.
     Colors adapt automatically when wrapped in .stt-boutique-invert (the ink
     newsletter band) — see layout.blade.php. --}}
<div class="mx-auto w-full max-w-xl text-center">
    @if ($heading !== '')
        <h2 class="stt-boutique-nl-heading">{{ $heading }}</h2>
    @endif

    @if ($subheading)
        <p class="stt-boutique-nl-sub mx-auto mt-4 max-w-md text-sm leading-relaxed">{{ $subheading }}</p>
    @endif

    @if ($done)
        <p class="stt-boutique-label mt-8 inline-flex flex-col items-center gap-4">
            {{ __('storefront.newsletter_on_the_list') }}
            <span class="stt-boutique-mark" aria-hidden="true"></span>
        </p>
    @else
        <form wire:submit="subscribe" class="mx-auto mt-8 max-w-md text-left">
            <div class="flex flex-col gap-2 sm:flex-row">
                <div class="relative flex-1">
                    <input type="email" wire:model="email" required aria-label="{{ __('storefront.email_address') }}" placeholder="{{ __('storefront.email_placeholder') }}"
                        autocomplete="email" class="stt-boutique-input w-full">
                    <span class="pointer-events-none absolute right-3 top-2.5 text-xs leading-none" style="color: var(--st-accent)" aria-hidden="true" title="Required">*</span>
                </div>
                <button type="submit" class="stt-boutique-btn shrink-0">
                    <span wire:loading.remove wire:target="subscribe">{{ __('storefront.subscribe') }}</span>
                    <span wire:loading wire:target="subscribe">&hellip;</span>
                </button>
            </div>
            @error('email')
                <p class="stt-boutique-nl-error mt-3 text-xs font-semibold">{{ $message }}</p>
            @enderror
        </form>
    @endif
</div>
