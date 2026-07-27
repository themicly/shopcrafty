{{-- Haven newsletter — the espresso closer: a full-width panel carrying the
     lowercase serif invitation, centered. The livewire component and its $s
     data contract are preserved exactly; the .stt-haven-invert context keeps
     every state readable on the dark ground. --}}
<section class="stt-haven-section" style="background: var(--st-bg); padding-bottom: 0">
    <div class="stt-haven-invert relative overflow-hidden" style="background: var(--stt-haven-panel)">
        <div class="st-container stt-haven-stagger st-reveal relative" style="padding-block: clamp(3.5rem, 8vw, 5.5rem)">
            <div class="mx-auto max-w-xl">
                <livewire:marketing.newsletter-form
                    :heading="$s['heading'] ?? 'Letters from the workshop'"
                    :subheading="$s['subheading'] ?? ''"
                />
            </div>
        </div>
    </div>
</section>
