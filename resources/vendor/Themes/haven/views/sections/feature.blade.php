{{-- Haven feature — the signature offset composition: photography on the right,
     an ivory statement panel overlapping it from the left. Bullets read as
     brass-dashed notes. --}}
@php
    $bullets = array_values(array_filter(array_map('trim', explode('|', (string) ($s['bullets'] ?? '')))));
    $image = trim((string) ($s['image'] ?? ''));
@endphp

<section class="stt-haven-section" style="background: var(--st-surface)">
    <div class="st-container">
        <div class="stt-haven-head st-reveal">
            <p class="stt-haven-eyebrow mb-3">{{ __('storefront.our_craft') }}</p>
        </div>

        <div class="stt-haven-feature st-reveal">
            {{-- Photography --}}
            <div class="stt-haven-feature-media">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $s['heading'] ?? '' }}" loading="lazy" style="min-height: 20rem">
                @else
                    {{-- No photo yet: an espresso field carries the wordmark in serif. --}}
                    <div class="stt-haven-invert grid place-items-center" style="min-height: 24rem; background: var(--stt-haven-panel)">
                        <span class="stt-haven-display" style="color: var(--st-bg); font-size: clamp(1.8rem, 4vw, 2.8rem)">{{ settings('general.store_name', 'Haven') }}</span>
                    </div>
                @endif
            </div>

            {{-- Overlapping statement panel --}}
            <div class="stt-haven-feature-panel">
                <h2 class="stt-haven-display stt-haven-title">{{ $s['heading'] ?? 'Made for the long life of a home' }}</h2>

                @if (! empty($s['subheading']))
                    <p class="mt-5 max-w-md text-sm leading-relaxed sm:text-base" style="color: var(--st-ink-soft)">{{ $s['subheading'] }}</p>
                @endif

                @if ($bullets)
                    <ul class="mt-6 space-y-3">
                        @foreach ($bullets as $bullet)
                            <li class="flex items-baseline gap-4 text-sm" style="color: var(--st-ink)">
                                <span class="inline-block h-px w-6 shrink-0" style="background: var(--st-accent); transform: translateY(-4px)" aria-hidden="true"></span>
                                <span>{{ $bullet }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($s['cta_label']))
                    <a href="{{ url($s['cta_url'] ?? '/about') }}" class="stt-haven-btn mt-8">
                        {{ $s['cta_label'] }}
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
