<section class="st-reveal py-10 sm:py-16" style="background: var(--st-bg)">
    <div class="st-container">
        <figure class="mx-auto max-w-4xl">
            @if (! empty($b['url']))
                <img src="{{ $b['url'] }}" alt="{{ $b['alt'] ?? '' }}"
                    class="w-full object-cover" style="border-radius: var(--st-radius)" />
            @endif

            @if (! empty($b['caption']))
                <figcaption class="mt-3 text-center text-sm" style="color: var(--st-ink-soft)">
                    {{ $b['caption'] }}
                </figcaption>
            @endif
        </figure>
    </div>
</section>
