@if (! empty($b['items']))
    <section class="st-reveal py-16 sm:py-24" style="background: var(--st-bg)">
        <div class="st-container">
            @if (! empty($b['heading']))
                <div class="mb-10 sm:mb-14">
                    <x-st.section-heading :title="$b['heading']" align="center" />
                </div>
            @endif

            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($b['items'] as $item)
                    <figure class="flex flex-col border p-6"
                        style="border-color: var(--st-line); border-radius: var(--st-radius); background: var(--st-surface)">
                        <blockquote class="flex-1 text-base leading-relaxed" style="color: var(--st-ink)">
                            &ldquo;{{ $item['quote'] ?? '' }}&rdquo;
                        </blockquote>
                        <figcaption class="mt-5 text-sm font-medium" style="color: var(--st-ink-soft)">
                            {{ $item['name'] ?? '' }}
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>
@endif
