{{-- Bloom promo pair — two side-by-side "crate" promo panels on soft-green fields.
     Each has a leaf eyebrow, a Fraunces heading, its produce shot framed by the
     dashed organic ring, and a chunky green pill CTA. Panel A can show a live
     countdown rendered as rounded stat tiles. --}}
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
    <section class="stt-fresh-section stt-fresh-band-bg">
        <div class="st-container grid gap-6 lg:grid-cols-2">
            {{-- Panel A (image left · text right · optional countdown) --}}
            @if (! empty($s['a_heading']))
                <div class="stt-fresh-panel st-reveal relative grid grid-cols-1 items-center gap-5 overflow-hidden sm:grid-cols-2 sm:gap-6"
                    style="background: {{ $aBg }}">
                    {{-- soft blurred blob + leaf texture --}}
                    <span class="stt-fresh-blob" style="width: 15rem; height: 15rem; top: -5rem; left: -4rem;"></span>
                    <span class="stt-fresh-leaf" style="width: 2.4rem; height: 2.4rem; bottom: 1.5rem; left: 45%; transform: rotate(30deg); opacity: .5;"></span>

                    <div class="relative order-2 sm:order-1">
                        <div class="stt-fresh-ring mx-auto w-full" style="max-width: 15rem">
                            @if ($aImage)
                                <img src="{{ $aImage }}" alt="{{ $s['a_heading'] }}" loading="lazy"
                                    class="max-h-52 w-auto object-contain drop-shadow-xl">
                            @else
                                <div class="aspect-square w-32 rounded-full"
                                    style="background: color-mix(in srgb, var(--st-primary) 12%, transparent)"></div>
                            @endif
                        </div>
                    </div>

                    <div class="relative order-1 sm:order-2">
                        @if (! empty($s['a_eyebrow']))
                            <p class="stt-fresh-eyebrow mb-3">{{ $s['a_eyebrow'] }}</p>
                        @endif
                        <h3 class="stt-fresh-heading text-3xl sm:text-4xl">{{ $s['a_heading'] }}</h3>

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
                                @foreach (['d' => __('storefront.countdown_days'), 'h' => __('storefront.countdown_hrs'), 'm' => __('storefront.countdown_min'), 's' => __('storefront.countdown_sec')] as $key => $label)
                                    @unless ($loop->first)
                                        <span class="st-display text-2xl font-semibold" style="color: color-mix(in srgb, var(--st-ink) 35%, transparent)">:</span>
                                    @endunless
                                    <div class="grid place-items-center rounded-2xl px-3 py-2 text-center"
                                        style="background: color-mix(in srgb, var(--st-bg) 65%, transparent); border: 1px solid var(--st-line)">
                                        <span class="st-display block text-2xl font-semibold leading-none sm:text-3xl"
                                            style="color: var(--st-ink)" x-text="{{ $key }}">0</span>
                                        <span class="mt-1 text-[10px] font-semibold uppercase tracking-wide"
                                            style="color: var(--st-ink-soft)">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ url($aLink) }}" class="stt-fresh-btn mt-6">
                            {{ $s['a_link_label'] ?? 'Shop now' }}
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Panel B (text left · image right) --}}
            @if (! empty($s['b_heading']))
                <div class="stt-fresh-panel st-reveal relative grid grid-cols-1 items-center gap-5 overflow-hidden sm:grid-cols-2 sm:gap-6"
                    style="background: {{ $bBg }}">
                    <span class="stt-fresh-blob" style="width: 15rem; height: 15rem; top: -5rem; right: -4rem;"></span>
                    <span class="stt-fresh-leaf" style="width: 2.4rem; height: 2.4rem; top: 1.5rem; right: 42%; transform: rotate(70deg); opacity: .5;"></span>

                    <div class="relative">
                        @if (! empty($s['b_eyebrow']))
                            <p class="stt-fresh-eyebrow mb-3">{{ $s['b_eyebrow'] }}</p>
                        @endif
                        <h3 class="stt-fresh-heading text-3xl sm:text-4xl">{{ $s['b_heading'] }}</h3>
                        <a href="{{ url($bLink) }}" class="stt-fresh-btn mt-6">
                            {{ $s['b_link_label'] ?? 'Shop now' }}
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                    </div>

                    <div class="relative">
                        <div class="stt-fresh-ring mx-auto w-full" style="max-width: 15rem">
                            @if ($bImage)
                                <img src="{{ $bImage }}" alt="{{ $s['b_heading'] }}" loading="lazy"
                                    class="max-h-52 w-auto object-contain drop-shadow-xl">
                            @else
                                <div class="aspect-square w-32 rounded-full"
                                    style="background: color-mix(in srgb, var(--st-primary) 12%, transparent)"></div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
