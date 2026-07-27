{{-- Studio newsletter — a two-panel closer: a sage lifestyle panel (plaque over
     the band, standing in for photography until the shop supplies one) beside
     the SUBSCRIBE TO OUR NEWSLETTER column with the boxed email form and black
     submit. The livewire component and its $s data contract are preserved
     exactly. --}}
<section class="stt-studio-section" style="background: var(--st-bg)">
    <div class="st-container">
        <div class="stt-studio-nl-grid st-reveal" style="border: 1px solid var(--st-line)">
            {{-- Sage panel --}}
            <div class="stt-studio-band relative grid place-items-center" style="min-height: 16rem">
                <span class="stt-studio-plaque">{{ settings('general.store_name', 'Studio') }}</span>
            </div>

            {{-- Subscribe column --}}
            <div class="flex flex-col justify-center" style="padding: clamp(2rem, 5vw, 3.5rem)">
                <livewire:marketing.newsletter-form
                    :heading="$s['heading'] ?? 'Subscribe to our newsletter'"
                    :subheading="$s['subheading'] ?? ''"
                />
            </div>
        </div>
    </div>
</section>
