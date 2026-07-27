{{-- Bloom feature showcase — a soft-green "produce crate" panel: left copy column
     (leaf eyebrow, Fraunces heading, benefit bullets as leaf-circle rows, a green
     pill CTA) beside a produce shot framed by the signature dashed organic RING
     with a dashed "100% Natural" SEAL prop. Each bullet may be "Title : Description"
     (split on the first colon). --}}
@php
    $image = trim((string) ($s['image'] ?? ''));
    $rawBullets = array_values(array_filter(array_map('trim', explode('|', (string) ($s['bullets'] ?? '')))));
    $bullets = array_map(function ($b) {
        $parts = array_map('trim', explode(':', $b, 2));

        return ['title' => $parts[0], 'desc' => $parts[1] ?? ''];
    }, array_slice($rawBullets, 0, 4));

    // Outlined icons (cart, badge/seal, box, truck) — cycled across the bullets.
    $icons = [
        'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a.626.626 0 0 0-.6-.82H5.106M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
        'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z',
        'm21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9',
        'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-5.25m0-11.25h1.5v4.5m-1.5-4.5H5.625c-.621 0-1.125.504-1.125 1.125v10.5c0 .621.504 1.125 1.125 1.125H9.75',
    ];
    $leaf = 'M17 8C8 10 5.9 16.17 3.82 21.34l1.06.66.66-1.06C7.5 18.5 9.5 17 12 17c5 0 9-4 9-9V4h-4z';
@endphp

<section class="stt-fresh-section stt-fresh-band-bg">
    <div class="st-container">
        <div class="stt-fresh-panel st-reveal">
            <div class="stt-fresh-feature-grid grid items-center gap-10 lg:grid-cols-2">
                {{-- Copy + bullets --}}
                <div class="order-2 lg:order-1">
                    <span class="stt-fresh-eyebrow">{{ __('storefront.farm_to_basket') }}</span>

                    <h2 class="stt-fresh-heading mt-4 text-3xl sm:text-4xl">{{ $s['heading'] ?? 'Naturally better' }}</h2>

                    @if (! empty($s['subheading']))
                        <p class="mt-4 max-w-md text-base leading-relaxed" style="font-family: var(--st-font-body); color: var(--st-ink-soft)">{{ $s['subheading'] }}</p>
                    @endif

                    @if ($bullets)
                        <div class="mt-8 space-y-5">
                            @foreach ($bullets as $i => $b)
                                <div class="flex items-start gap-4">
                                    <span class="stt-fresh-usp-icon shrink-0">
                                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$i % 4] }}" /></svg>
                                    </span>
                                    <div class="pt-1">
                                        <p class="text-base font-bold" style="font-family: var(--st-font-body); color: var(--st-ink)">{{ $b['title'] }}</p>
                                        @if ($b['desc'] !== '')
                                            <p class="mt-1 text-sm leading-relaxed" style="font-family: var(--st-font-body); color: var(--st-ink-soft)">{{ $b['desc'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if (! empty($s['cta_label']))
                        <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-fresh-btn mt-9">
                            {{ $s['cta_label'] }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h14m0 0-5-5m5 5-5 5" /></svg>
                        </a>
                    @endif
                </div>

                {{-- Produce shot inside a dashed organic ring + natural-food seal --}}
                <div class="relative order-1 flex justify-center lg:order-2">
                    <div class="stt-fresh-ring w-full max-w-md">
                        @if ($image)
                            <img src="{{ $image }}" alt="{{ $s['heading'] ?? '' }}" loading="lazy" class="max-h-[360px] w-auto object-contain drop-shadow-2xl">
                        @else
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-40 w-40" style="color: color-mix(in srgb, var(--st-primary) 45%, transparent)"><path d="{{ $leaf }}"/></svg>
                        @endif
                    </div>

                    <span class="stt-fresh-seal stt-fresh-seal-pos absolute right-2 h-24 w-24">
                        100%<br>Natural<br>Farm Fresh
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>
