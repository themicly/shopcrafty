{{-- Studio: "Big saving zone" — a light-grey band with photographic promo tiles.
     Each tile carries a serif uppercase title, a subline, an optional countdown
     badge and a small outlined SHOP NOW button over a soft ink scrim. The
     promo_pair data contract (a_* / b_* keys) is preserved exactly. --}}
@php
    $aBg = $s['a_bg'] ?? '#c7ede7';
    $bBg = $s['b_bg'] ?? '#f3eccf';
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
    <section class="stt-studio-section stt-studio-band-grey">
        <div class="st-container">
            <div class="stt-studio-head st-reveal">
                <h2 class="stt-studio-title stt-studio-title--rule">{{ $theme['text_promo_title'] ?? 'Big saving zone' }}</h2>
            </div>

            <div class="stt-studio-promo-grid">
                {{-- Tile A (optional countdown badge) --}}
                @if (! empty($s['a_heading']))
                    <div class="stt-studio-promo {{ $aImage ? 'stt-studio-promo--photo' : '' }} st-reveal" @unless ($aImage) style="background: {{ $aBg }}; color: var(--st-ink)" @endunless>
                        @if ($aImage)
                            <img src="{{ $aImage }}" alt="" loading="lazy">
                        @endif

                        @if ($countdown)
                            {{-- Live countdown badge, top-left, init()/destroy() so timers die with the DOM. --}}
                            <span class="stt-studio-badge absolute" style="top: 1.25rem; left: 1.25rem"
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
                                }" x-text="label">{{ __('storefront.limited_stock') }}</span>
                        @endif

                        <div class="relative flex flex-col items-start gap-3">
                            <h3 class="stt-studio-promo-title" @unless ($aImage) style="color: inherit" @endunless>{{ $s['a_heading'] }}</h3>
                            @if (! empty($s['a_eyebrow']))
                                <p class="text-sm font-semibold" style="letter-spacing: 0.04em; opacity: .9">{{ $s['a_eyebrow'] }}</p>
                            @endif
                            <a href="{{ url($aLink) }}" class="stt-studio-btn-outline mt-2">{{ $s['a_link_label'] ?? 'Shop now' }}</a>
                        </div>
                    </div>
                @endif

                {{-- Tile B --}}
                @if (! empty($s['b_heading']))
                    <div class="stt-studio-promo {{ $bImage ? 'stt-studio-promo--photo' : '' }} st-reveal" @unless ($bImage) style="background: {{ $bBg }}; color: var(--st-ink)" @endunless>
                        @if ($bImage)
                            <img src="{{ $bImage }}" alt="" loading="lazy">
                        @endif

                        <div class="relative flex flex-col items-start gap-3">
                            <h3 class="stt-studio-promo-title" @unless ($bImage) style="color: inherit" @endunless>{{ $s['b_heading'] }}</h3>
                            @if (! empty($s['b_eyebrow']))
                                <p class="text-sm font-semibold" style="letter-spacing: 0.04em; opacity: .9">{{ $s['b_eyebrow'] }}</p>
                            @endif
                            <a href="{{ url($bLink) }}" class="stt-studio-btn-outline mt-2">{{ $s['b_link_label'] ?? 'Shop now' }}</a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endif
