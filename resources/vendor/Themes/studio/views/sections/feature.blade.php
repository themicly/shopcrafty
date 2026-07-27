{{-- Studio feature — the split banner: a deep leafy-green panel carrying a white
     UPPERCASE serif statement, copy, benefit notes and a white button, beside a
     full-bleed photograph. --}}
@php
    $bullets = array_values(array_filter(array_map('trim', explode('|', (string) ($s['bullets'] ?? '')))));
    $image = trim((string) ($s['image'] ?? ''));
@endphp

<section class="stt-studio-section" style="background: var(--st-bg)">
    <div class="st-container">
        <div class="stt-studio-split st-reveal">
            {{-- Statement panel --}}
            <div class="stt-studio-split-panel">
                @if (! empty($s['eyebrow']))
                    <p class="stt-studio-eyebrow" style="color: rgba(255,255,255,.75)">{{ $s['eyebrow'] }}</p>
                @endif

                <h2 class="stt-studio-display" style="color: #fff; font-size: clamp(1.6rem, 3.2vw, 2.4rem)">
                    {{ $s['heading'] ?? 'We made your everyday fashion better!' }}
                </h2>

                @if (! empty($s['subheading']))
                    <p class="max-w-md text-sm leading-relaxed sm:text-base" style="color: rgba(255,255,255,.82)">{{ $s['subheading'] }}</p>
                @endif

                @if ($bullets)
                    <ul class="space-y-2.5">
                        @foreach ($bullets as $bullet)
                            <li class="flex items-baseline gap-3 text-sm" style="color: rgba(255,255,255,.9)">
                                <span class="inline-block h-1.5 w-1.5 shrink-0 rounded-full" style="background: var(--st-band); transform: translateY(-2px)" aria-hidden="true"></span>
                                <span>{{ $bullet }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (! empty($s['cta_label']))
                    <a href="{{ url($s['cta_url'] ?? '/shop') }}" class="stt-studio-btn stt-studio-btn--light mt-2">{{ $s['cta_label'] }}</a>
                @endif
            </div>

            {{-- Photo panel --}}
            <div class="stt-studio-split-media">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $s['heading'] ?? '' }}" loading="lazy">
                @else
                    <div class="absolute inset-0 grid place-items-center" style="background: color-mix(in srgb, var(--st-band) 60%, var(--st-bg))">
                        <span class="stt-studio-plaque">{{ settings('general.store_name', 'Studio') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
