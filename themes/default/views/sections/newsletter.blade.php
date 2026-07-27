<section class="py-16 sm:py-20" style="background: var(--st-ink); color: var(--st-bg)">
    <div class="st-container st-reveal">
        <livewire:marketing.newsletter-form
            :heading="$s['heading'] ?? 'Join the list'"
            :subheading="$s['subheading'] ?? ''"
        />
    </div>
</section>
