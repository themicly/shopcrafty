{{-- Text colors are inherited (currentColor) so this form stays legible on any
     container: dark newsletter bands set `color` on their wrapper; light contexts
     (e.g. the footer_newsletter slot) simply inherit the page ink. --}}
<div class="st-reveal mx-auto max-w-2xl text-center">
    <h2 class="st-display text-3xl font-semibold sm:text-4xl">{{ $heading }}</h2>

    @if ($subheading)
        <p class="mt-3 text-base sm:text-lg" style="opacity: 0.75">{{ $subheading }}</p>
    @endif

    @if ($done)
        <p class="mx-auto mt-8 max-w-md text-base font-medium">
            {{ __('storefront.newsletter_success') }}
        </p>
    @else
        <form wire:submit="subscribe" class="mx-auto mt-8 flex w-full max-w-md flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <input type="email" wire:model="email" required aria-label="{{ __('storefront.email_placeholder') }}" placeholder="{{ __('storefront.email_placeholder') }}"
                    class="w-full border px-4 py-3 text-sm outline-none"
                    style="border-color: var(--st-line); background: var(--st-surface); color: var(--st-ink); border-radius: var(--st-radius)">
                <span class="pointer-events-none absolute right-3 top-2.5 text-xs leading-none" style="color: var(--st-accent)" aria-hidden="true" title="Required">*</span>
                @error('email')<p class="mt-1 text-left text-xs font-medium" style="color: var(--st-accent)">{{ $message }}</p>@enderror
            </div>
            <button type="submit"
                class="whitespace-nowrap px-6 py-3 text-sm font-semibold transition hover:opacity-90"
                style="background: var(--st-accent); color: #fff; border-radius: var(--st-radius)">
                <span wire:loading.remove wire:target="subscribe">{{ __('storefront.subscribe') }}</span>
                <span wire:loading wire:target="subscribe">…</span>
            </button>
        </form>
    @endif
</div>
