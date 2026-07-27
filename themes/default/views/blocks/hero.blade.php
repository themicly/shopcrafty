<section class="st-reveal relative overflow-hidden" style="background: var(--st-surface)">
    <div class="st-container">
        <div class="mx-auto flex max-w-3xl flex-col items-center py-16 text-center sm:py-24">
            <h1 class="st-display text-4xl font-semibold leading-[1.05] sm:text-6xl" style="color: var(--st-ink)">
                {{ $b['heading'] ?? 'Welcome' }}
            </h1>

            @if (! empty($b['subheading']))
                <p class="mt-6 max-w-xl text-base leading-relaxed sm:text-lg" style="color: var(--st-ink-soft)">
                    {{ $b['subheading'] }}
                </p>
            @endif

            @if (! empty($b['cta_label']))
                <div class="mt-10">
                    <a href="{{ url($b['cta_url'] ?? '/shop') }}"
                        class="inline-flex items-center px-8 py-4 text-sm font-semibold transition-transform duration-200 hover:-translate-y-0.5"
                        style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius)">
                        {{ $b['cta_label'] }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
