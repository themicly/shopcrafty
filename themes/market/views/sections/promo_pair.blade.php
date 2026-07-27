{{-- Promo pair (Marketplace) — two side-by-side boxed promo modules, each a hairline
     white box with a red uppercase eyebrow, bold heading, short red rule and a squared
     brand-blue CTA. Card A can show a live squared countdown. No pills, no gradients. --}}
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

    // The customizer bg colors tint the squared image pad only — the card stays a
    // white hairline box, keeping the marketplace look while honoring the fields.
    $padA = 'color-mix(in srgb, '.$aBg.' 45%, var(--st-surface))';
    $padB = 'color-mix(in srgb, '.$bBg.' 45%, var(--st-surface))';
@endphp

@if (! empty($s['a_heading']) || ! empty($s['b_heading']))
    <section class="stt-market-section stt-market-section--bg">
        <div class="st-container stt-market-stagger grid gap-4 lg:grid-cols-2">
            {{-- Card A (image left · text right · optional countdown) --}}
            @if (! empty($s['a_heading']))
                <div class="stt-market-box st-reveal grid grid-cols-1 items-center gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div class="order-2 sm:order-1">
                        <div class="grid aspect-square place-items-center overflow-hidden p-4" style="background: {{ $padA }}; border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                            @if ($aImage)
                                <img src="{{ $aImage }}" alt="{{ $s['a_heading'] }}" loading="lazy" class="w-auto object-contain" style="max-height: 100%">
                            @else
                                <div class="h-24 w-24" style="background: color-mix(in srgb, var(--st-primary) 8%, transparent); border-radius: var(--st-radius)"></div>
                            @endif
                        </div>
                    </div>
                    <div class="order-1 sm:order-2">
                        @if (! empty($s['a_eyebrow']))<p class="stt-market-eyebrow">{{ $s['a_eyebrow'] }}</p>@endif
                        <h3 class="stt-market-title st-display">{{ $s['a_heading'] }}</h3>
                        <span class="stt-market-rule"></span>

                        @if ($countdown)
                            <div class="mt-5 flex items-center gap-2"
                                x-data="{
                                    target: new Date('{{ $countdown->toIso8601String() }}').getTime(),
                                    d: '0', h: '00', m: '00', s: '00',
                                    tick() {
                                        const sec = Math.max(0, Math.floor((this.target - Date.now()) / 1000));
                                        this.d = String(Math.floor(sec / 86400));
                                        this.h = String(Math.floor((sec % 86400) / 3600)).padStart(2, '0');
                                        this.m = String(Math.floor((sec % 3600) / 60)).padStart(2, '0');
                                        this.s = String(sec % 60).padStart(2, '0');
                                    },
                                    init() { this.tick(); this.timer = setInterval(() => this.tick(), 1000); },
                                    destroy() { clearInterval(this.timer); },
                                }">
                                @foreach (['d' => __('storefront.cd_days'), 'h' => __('storefront.cd_hrs'), 'm' => __('storefront.cd_min'), 's' => __('storefront.cd_sec')] as $key => $label)
                                    <div class="px-2 py-1.5 text-center" style="min-width: 3rem; border: 1px solid var(--st-line); border-radius: var(--st-radius); background: var(--st-surface)">
                                        <span class="st-display block text-xl font-bold leading-none sm:text-2xl" style="color: var(--st-ink)" x-text="{{ $key }}">0</span>
                                        <span class="mt-1 block text-[10px] font-bold uppercase tracking-wider" style="color: var(--st-ink-soft)">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ url($aLink) }}" class="stt-market-btn stt-market-btn--lg mt-5">{{ $s['a_link_label'] ?? 'Shop now' }}</a>
                    </div>
                </div>
            @endif

            {{-- Card B (text left · image right) --}}
            @if (! empty($s['b_heading']))
                <div class="stt-market-box st-reveal grid grid-cols-1 items-center gap-4 p-5 sm:grid-cols-2 sm:p-6">
                    <div>
                        @if (! empty($s['b_eyebrow']))<p class="stt-market-eyebrow">{{ $s['b_eyebrow'] }}</p>@endif
                        <h3 class="stt-market-title st-display">{{ $s['b_heading'] }}</h3>
                        <span class="stt-market-rule"></span>
                        <a href="{{ url($bLink) }}" class="stt-market-btn stt-market-btn--lg mt-5">{{ $s['b_link_label'] ?? 'Shop now' }}</a>
                    </div>
                    <div>
                        <div class="grid aspect-square place-items-center overflow-hidden p-4" style="background: {{ $padB }}; border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                            @if ($bImage)
                                <img src="{{ $bImage }}" alt="{{ $s['b_heading'] }}" loading="lazy" class="w-auto object-contain" style="max-height: 100%">
                            @else
                                <div class="h-24 w-24" style="background: color-mix(in srgb, var(--st-primary) 8%, transparent); border-radius: var(--st-radius)"></div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
