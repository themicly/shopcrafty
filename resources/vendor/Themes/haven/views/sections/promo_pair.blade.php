{{-- Haven promo pair — an asymmetric 3:2 duo of espresso-scrimmed photo tiles.
     Lowercase serif statements, underline-draw links, an optional live
     countdown chip (init()/destroy() so timers die with the DOM). The
     promo_pair data contract (a_* / b_* keys) is preserved exactly. --}}
@php
    $aBg = $s['a_bg'] ?? '#efe8dc';
    $bBg = $s['b_bg'] ?? '#e7dcc8';
    $aLink = preg_match('#^(https?://|/|\#|mailto:|tel:)#i', (string) ($s['a_link'] ?? '')) ? $s['a_link'] : '/shop';
    $bLink = preg_match('#^(https?://|/|\#|mailto:|tel:)#i', (string) ($s['b_link'] ?? '')) ? $s['b_link'] : '/shop';
    $aImage = trim((string) ($s['a_image'] ?? ''));
    $bImage = trim((string) ($s['b_image'] ?? ''));

    $countdown = null;
    if (! empty($s['a_countdown'])) {
        try {
            $c = \Illuminate\Support\Carbon::parse($s['a_countdown']);
            $countdown = $c->isFuture() ? $c : null;
        } catch (\Throwable $e) {
            $countdown = null;
        }
    }
@endphp

@if (! empty($s['a_heading']) || ! empty($s['b_heading']))
    <section class="stt-haven-section" style="background: var(--st-bg); padding-top: 0">
        <div class="st-container">
            <div class="stt-haven-promo-grid stt-haven-stagger st-reveal">
                {{-- Tile A — the wide lead (optional countdown chip) --}}
                @if (! empty($s['a_heading']))
                    <div class="stt-haven-promo {{ $aImage ? 'stt-haven-promo--photo' : '' }}"
                        @unless ($aImage) style="background: {{ $aBg }}; color: var(--st-ink)" @endunless>
                        @if ($aImage)
                            <img src="{{ $aImage }}" alt="" loading="lazy">
                        @endif

                        @if ($countdown)
                            <span class="stt-haven-badge absolute" style="top: 1.5rem; left: 1.5rem; background: var(--st-bg); color: var(--st-accent)"
                                x-data="{
                                    target: new Date('{{ $countdown->toIso8601String() }}').getTime(),
                                    label: '',
                                    timer: null,
                                    tick() {
                                        const sec = Math.max(0, Math.floor((this.target - Date.now()) / 1000));
                                        const d = Math.floor(sec / 86400), h = Math.floor((sec % 86400) / 3600), m = Math.floor((sec % 3600) / 60);
                                        this.label = 'Ends in ' + (d > 0 ? d + 'd ' : '') + h + 'h ' + m + 'm';
                                    },
                                    init() { this.tick(); this.timer = setInterval(() => this.tick(), 30000); },
                                    destroy() { clearInterval(this.timer); },
                                }" x-text="label">{{ __('storefront.limited_time') }}</span>
                        @endif

                        <div class="relative flex flex-col items-start gap-3">
                            @if (! empty($s['a_eyebrow']))
                                <p class="stt-haven-eyebrow" @if ($aImage) style="color: var(--stt-haven-brass-lt)" @endif>{{ $s['a_eyebrow'] }}</p>
                            @endif
                            <h3 class="stt-haven-display" style="color: inherit; font-size: clamp(1.6rem, 3.2vw, 2.4rem)">{{ $s['a_heading'] }}</h3>
                            <a href="{{ url($aLink) }}" class="stt-haven-link stt-haven-link--caps mt-2" style="color: inherit">{{ $s['a_link_label'] ?? 'Shop now' }}</a>
                        </div>
                    </div>
                @endif

                {{-- Tile B — the tall companion --}}
                @if (! empty($s['b_heading']))
                    <div class="stt-haven-promo {{ $bImage ? 'stt-haven-promo--photo' : '' }}"
                        @unless ($bImage) style="background: {{ $bBg }}; color: var(--st-ink)" @endunless>
                        @if ($bImage)
                            <img src="{{ $bImage }}" alt="" loading="lazy">
                        @endif

                        <div class="relative flex flex-col items-start gap-3">
                            @if (! empty($s['b_eyebrow']))
                                <p class="stt-haven-eyebrow" @if ($bImage) style="color: var(--stt-haven-brass-lt)" @endif>{{ $s['b_eyebrow'] }}</p>
                            @endif
                            <h3 class="stt-haven-display" style="color: inherit; font-size: clamp(1.4rem, 2.6vw, 2rem)">{{ $s['b_heading'] }}</h3>
                            <a href="{{ url($bLink) }}" class="stt-haven-link stt-haven-link--caps mt-2" style="color: inherit">{{ $s['b_link_label'] ?? 'Shop now' }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
