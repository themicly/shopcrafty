{{-- Volt hero — clean WoodMart split: a neutral surface field with a product image on
     one side and eyebrow / heading / subheading + a brand-blue squared primary CTA and
     an underline secondary link on the other. Squared corners, calm, product-forward. --}}
@php
    $image = trim((string) ($s['image'] ?? ''));
    $badge = trim((string) ($s['badge'] ?? ''));
    $primary = ! empty($s['cta_label']);
    $secondary = ! empty($s['cta2_label']);
@endphp

<section class="overflow-hidden" style="background: var(--st-surface); color: var(--st-ink)">
    <div class="st-container grid items-center gap-10 py-14 sm:py-16 lg:grid-cols-2 lg:gap-12 lg:py-0 lg:min-h-[540px]">
        {{-- Copy --}}
        <div class="st-reveal {{ $image ? '' : 'mx-auto max-w-2xl text-center' }}">
            @if (! empty($s['eyebrow']))
                <p class="mb-4 text-xs font-semibold uppercase tracking-[0.22em]" style="color: var(--st-accent)">{{ $s['eyebrow'] }}</p>
            @endif

            <h1 class="st-display text-4xl font-bold leading-[1.05] tracking-tight sm:text-5xl lg:text-6xl" style="color: var(--st-ink)">
                {{ $s['heading'] ?? 'Shop the new collection' }}
            </h1>

            @if (! empty($s['subheading']))
                <p class="mt-5 max-w-md text-sm leading-relaxed sm:text-base {{ $image ? '' : 'mx-auto' }}" style="color: var(--st-ink-soft)">
                    {{ $s['subheading'] }}
                </p>
            @endif

            @if ($primary || $secondary)
                <div class="mt-8 flex flex-wrap items-center gap-x-7 gap-y-3 {{ $image ? '' : 'justify-center' }}">
                    @if ($primary)
                        <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-market-btn stt-market-btn--lg">
                            {{ $s['cta_label'] }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0-6-6m6 6-6 6"/></svg>
                        </a>
                    @endif
                    @if ($secondary)
                        <a href="{{ url($s['cta2_url'] ?? '/shop') }}"
                            class="inline-flex items-center border-b-2 pb-0.5 text-sm font-semibold uppercase tracking-wide transition-opacity duration-200 hover:opacity-60"
                            style="color: var(--st-primary); border-color: var(--st-primary)">
                            {{ $s['cta2_label'] }}
                        </a>
                    @endif
                </div>
            @endif
        </div>

        {{-- Image (graceful when absent) --}}
        @if ($image)
            <div class="st-reveal relative flex justify-center lg:justify-end">
                <div class="relative overflow-hidden" style="border-radius: var(--st-radius)">
                    <img src="{{ $image }}" alt="{{ $s['heading'] ?? '' }}" loading="eager"
                        class="max-h-[300px] w-auto object-cover sm:max-h-[440px]">
                    @if ($badge)
                        <span class="absolute left-4 top-4 px-3 py-1 text-xs font-bold uppercase tracking-wide"
                            style="background: var(--st-accent); color: #fff; border-radius: var(--st-radius)">{{ $badge }}</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</section>
