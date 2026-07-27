<section class="st-reveal py-10 sm:py-16" style="background: var(--st-surface)">
    <div class="st-container">
        <div class="mx-auto flex max-w-3xl flex-col items-center text-center">
            <h2 class="st-display text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">
                {{ $b['heading'] ?? '' }}
            </h2>

            @if (! empty($b['button_label']))
                <div class="mt-8">
                    <a href="{{ url($b['button_url'] ?? '/shop') }}"
                        class="inline-flex items-center px-8 py-4 text-sm font-semibold transition-transform duration-200 hover:-translate-y-0.5"
                        style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius)">
                        {{ $b['button_label'] }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
