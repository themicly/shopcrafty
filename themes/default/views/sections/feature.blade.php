@php
    $bullets = array_values(array_filter(array_map('trim', explode('|', (string) ($s['bullets'] ?? '')))));
    $image = trim((string) ($s['image'] ?? ''));
@endphp

{{-- Feature showcase: large visual + heading + bulleted benefits + CTA (WoodMart/Glozin). --}}
<section class="py-16 sm:py-24" style="background: var(--st-bg)">
    <div class="st-container grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
        <div class="st-reveal order-2 lg:order-1">
            <h2 class="st-display text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">{{ $s['heading'] ?? 'Crafted with care' }}</h2>
            @if (! empty($s['subheading']))
                <p class="mt-4 max-w-md text-base leading-relaxed" style="color: var(--st-ink-soft)">{{ $s['subheading'] }}</p>
            @endif

            @if ($bullets)
                <ul class="mt-6 space-y-3">
                    @foreach ($bullets as $bullet)
                        <li class="flex items-start gap-3 text-sm" style="color: var(--st-ink)">
                            <span class="mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full" style="background: color-mix(in srgb, var(--st-primary) 15%, transparent); color: var(--st-primary)">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="h-3 w-3"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            </span>
                            {{ $bullet }}
                        </li>
                    @endforeach
                </ul>
            @endif

            @if (! empty($s['cta_label']))
                <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="mt-8 inline-flex items-center px-7 py-3.5 text-sm font-semibold transition-transform hover:-translate-y-0.5"
                    style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius)">
                    {{ $s['cta_label'] }}
                </a>
            @endif
        </div>

        <div class="st-reveal order-1 overflow-hidden lg:order-2" style="border-radius: var(--st-radius)">
            <div class="aspect-[4/3] w-full">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $s['heading'] ?? '' }}" loading="lazy" class="h-full w-full object-cover">
                @else
                    <div class="h-full w-full" style="background: linear-gradient(135deg, color-mix(in srgb, var(--st-primary) 22%, var(--st-bg)), color-mix(in srgb, var(--st-accent) 18%, var(--st-bg)))"></div>
                @endif
            </div>
        </div>
    </div>
</section>
