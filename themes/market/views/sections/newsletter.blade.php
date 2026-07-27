{{-- Marketplace (WoodMart mould): the newsletter renders as a single boxed module —
     a squared white hairline panel sitting on an alternating field with the theme's
     compact section rhythm. A short red rule and an uppercase micro-eyebrow give it
     the spec-sheet, "act now" flavour while red stays rationed.
     The livewire form (heading, subheading, email input + submit) is preserved exactly;
     only the surrounding structure and framing are themed. --}}
<section class="stt-market-section stt-market-section--surface">
    <div class="st-container">
        <div class="stt-market-box stt-market-nl-box st-reveal relative overflow-hidden px-6 py-12 text-center sm:py-14"
            style="color: var(--st-ink)">
            {{-- top red accent rule — the theme's signature heading device --}}
            <span class="absolute inset-x-0 top-0 block" style="height: 3px; background: var(--st-accent)"></span>

            <p class="mx-auto mb-3 font-semibold uppercase tracking-[0.16em]" style="font-size: 0.6875rem; color: var(--st-accent)">
                {{ __('storefront.newsletter') }}
            </p>

            <livewire:marketing.newsletter-form
                :heading="$s['heading'] ?? 'Join the list'"
                :subheading="$s['subheading'] ?? ''"
            />
        </div>
    </div>
</section>
