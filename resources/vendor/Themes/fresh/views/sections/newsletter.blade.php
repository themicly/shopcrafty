{{-- Bloom "market mailing list" — a soft-green field holding a single dark,
     fully-rounded produce CRATE. The shared newsletter-form livewire styles its
     text in --st-bg (built for a dark band), so we seat it on a var(--st-ink)
     crate and dress the crate with Bloom's signatures: blurred green blobs, a
     couple of translucent leaf glyphs and the leaf eyebrow above the heading
     the form renders. Nothing boxed in hard corners. --}}
<section class="stt-fresh-section stt-fresh-band-surface">
    <div class="st-container">
        <div class="relative overflow-hidden st-reveal"
            style="background: var(--st-ink); color: var(--st-bg); border-radius: 32px; padding: clamp(2.5rem, 6vw, 4.5rem) clamp(1.5rem, 5vw, 3rem)">

            {{-- Blurred soft-green radial blobs — background texture. --}}
            <span class="stt-fresh-blob" style="width: 22rem; height: 22rem; top: -8rem; left: -6rem"></span>
            <span class="stt-fresh-blob" style="width: 18rem; height: 18rem; bottom: -7rem; right: -5rem"></span>

            {{-- Decorative petal/leaf glyphs floating in the field. --}}
            <span class="stt-fresh-leaf" style="width: 2.75rem; height: 2.75rem; top: 2.5rem; right: 3.5rem; transform: rotate(20deg)"></span>
            <span class="stt-fresh-leaf" style="width: 1.75rem; height: 1.75rem; bottom: 2.75rem; left: 3rem; transform: rotate(-15deg)"></span>

            {{-- Leaf eyebrow above the heading the livewire form outputs. --}}
            <div class="relative z-10 mb-4 text-center">
                <span class="stt-fresh-eyebrow">{{ __('storefront.farmers_market_list') }}</span>
            </div>

            {{-- Preserved data contract: shared newsletter form, same props. --}}
            <div class="relative z-10">
                <livewire:marketing.newsletter-form
                    :heading="$s['heading'] ?? 'Join the list'"
                    :subheading="$s['subheading'] ?? ''"
                />
            </div>

            {{-- Warm, human reassurance line under the form. --}}
            <p class="relative z-10 mx-auto mt-6 max-w-md text-center text-xs"
                style="color: var(--st-bg); opacity: 0.6">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: inline-block; width: 0.85rem; height: 0.85rem; vertical-align: -0.12em; margin-right: 0.15rem" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12" /></svg> {{ __('storefront.fresh_newsletter_reassurance') }}
            </p>
        </div>
    </div>
</section>
