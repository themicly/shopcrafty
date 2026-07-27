{{-- Boutique newsletter: a near-black closing band — bold uppercase NEWSLETTER head,
     boxed email field and a filled gold subscribe button (styling flips via
     .stt-boutique-invert in the layout). The livewire component + both $s keys are
     preserved exactly. --}}
<section class="stt-boutique-section stt-boutique-invert" style="background: var(--st-ink)">
    <div class="st-container">
        <div class="st-reveal mx-auto flex max-w-xl flex-col items-center text-center">
            <p class="stt-boutique-eyebrow">{{ __('storefront.stay_in_the_loop') }}</p>
            <span class="stt-boutique-mark" aria-hidden="true" style="margin-top: 1.25rem"></span>

            {{-- Shared subscribe form — heading, subheading + boxed input on this ink
                 band. Contract preserved verbatim. --}}
            <div class="mt-8 w-full">
                <livewire:marketing.newsletter-form
                    :heading="$s['heading'] ?? 'Newsletter'"
                    :subheading="$s['subheading'] ?? ''"
                />
            </div>
        </div>
    </div>
</section>
