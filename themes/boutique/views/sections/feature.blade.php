@php
    $bullets = array_values(array_filter(array_map('trim', explode('|', (string) ($s['bullets'] ?? '')))));
    $image = trim((string) ($s['image'] ?? ''));
@endphp

{{-- Boutique feature/story: image beside bold uppercase copy, gold bullet ticks and a
     filled CTA — the brand-story block between the product grid and social proof. --}}
<section class="stt-boutique-section">
    <div class="st-container stt-boutique-narrow">
        <div class="stt-boutique-hero stt-boutique-hero--reverse">
            {{-- Story image on a soft surface block. --}}
            <div class="st-reveal stt-boutique-hero-media">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $s['heading'] ?? '' }}" loading="lazy">
                @else
                    <div class="h-full w-full" style="background: linear-gradient(135deg, color-mix(in srgb, var(--st-accent) 14%, var(--st-surface)), var(--st-surface))"></div>
                @endif
            </div>

            {{-- Copy column. --}}
            <div class="st-reveal stt-boutique-hero-copy">
                <p class="stt-boutique-eyebrow">{{ __('storefront.our_story') }}</p>
                <h2 class="stt-boutique-title" style="font-size: clamp(1.6rem, 3.2vw, 2.4rem)">{{ $s['heading'] ?? 'Made for real life' }}</h2>
                <span class="stt-boutique-mark" aria-hidden="true"></span>

                @if (! empty($s['subheading']))
                    <p class="stt-boutique-measure leading-relaxed" style="font-size: 15px; color: var(--st-ink-soft)">{{ $s['subheading'] }}</p>
                @endif

                @if ($bullets)
                    <ul class="mt-1 flex flex-col gap-3">
                        @foreach ($bullets as $bullet)
                            <li class="flex items-center gap-3 text-sm font-semibold" style="color: var(--st-ink)">
                                <span class="grid h-5 w-5 shrink-0 place-items-center" aria-hidden="true" style="background: var(--st-accent); color: #fff; border-radius: var(--st-radius); font-size: 10px">&#10003;</span>
                                {{ $bullet }}
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($s['cta_label']))
                    <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-boutique-btn mt-3">
                        {{ $s['cta_label'] }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
