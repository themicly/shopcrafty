{{-- Promo pair — two side-by-side promotional cards on soft pastel fields, each with
     an eyebrow, bold heading and a dark pill CTA. Card A can show a live countdown. --}}
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

    $pill = 'inline-flex items-center gap-2 rounded-full px-7 py-3 text-sm font-bold uppercase tracking-wide transition-transform duration-200 hover:-translate-y-0.5';
    $halftone = 'background-image: radial-gradient(currentColor 1.3px, transparent 1.5px); background-size: 12px 12px;';
@endphp

@if (! empty($s['a_heading']) || ! empty($s['b_heading']))
    <section class="py-10 sm:py-14" style="background: var(--st-bg)">
        <div class="st-container st-reveal stt-aurora-stagger grid gap-5 lg:grid-cols-2">
            {{-- Card A (image left · text right · optional countdown) --}}
            @if (! empty($s['a_heading']))
                <div class="stt-aurora-lift relative grid grid-cols-1 items-center gap-4 overflow-hidden rounded-2xl p-6 sm:grid-cols-2 sm:p-8" style="background: {{ $aBg }}; color: var(--st-ink)">
                    <div class="pointer-events-none absolute left-4 top-4 h-20 w-28 opacity-30" style="color: var(--st-ink); {{ $halftone }}"></div>
                    <div class="relative order-2 sm:order-1">
                        @if ($aImage)
                            <img src="{{ $aImage }}" alt="{{ $s['a_heading'] }}" loading="lazy" class="mx-auto max-h-52 w-auto object-contain drop-shadow-xl">
                        @else
                            <div class="mx-auto aspect-square w-40 rounded-2xl" style="background: color-mix(in srgb, var(--st-ink) 8%, transparent)"></div>
                        @endif
                    </div>
                    <div class="relative order-1 sm:order-2">
                        @if (! empty($s['a_eyebrow']))<p class="mb-2 text-sm font-semibold">{{ $s['a_eyebrow'] }}</p>@endif
                        <h3 class="st-display text-2xl font-extrabold leading-tight sm:text-3xl">{{ $s['a_heading'] }}</h3>

                        @if ($countdown)
                            <div class="mt-4 flex items-center gap-2"
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
                                @foreach (['d' => __('storefront.days'), 'h' => __('storefront.hrs'), 'm' => __('storefront.min'), 's' => __('storefront.sec')] as $key => $label)
                                    @unless ($loop->first)<span class="text-2xl font-extrabold opacity-40">:</span>@endunless
                                    <div class="text-center">
                                        <span class="st-display block text-2xl font-extrabold leading-none sm:text-3xl" x-text="{{ $key }}">0</span>
                                        <span class="text-[10px] font-medium uppercase tracking-wide opacity-70">{{ $label }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ url($aLink) }}" class="{{ $pill }} mt-5" style="background: var(--st-ink); color: #ffffff">{{ $s['a_link_label'] ?? 'Shop now' }}</a>
                    </div>
                </div>
            @endif

            {{-- Card B (text left · image right) --}}
            @if (! empty($s['b_heading']))
                <div class="stt-aurora-lift relative grid grid-cols-1 items-center gap-4 overflow-hidden rounded-2xl p-6 sm:grid-cols-2 sm:p-8" style="background: {{ $bBg }}; color: var(--st-ink)">
                    <div class="pointer-events-none absolute right-4 top-4 h-20 w-28 opacity-25" style="color: var(--st-ink); {{ $halftone }}"></div>
                    <div class="relative">
                        @if (! empty($s['b_eyebrow']))<p class="mb-2 text-sm font-semibold">{{ $s['b_eyebrow'] }}</p>@endif
                        <h3 class="st-display text-2xl font-extrabold leading-tight sm:text-3xl">{{ $s['b_heading'] }}</h3>
                        <a href="{{ url($bLink) }}" class="{{ $pill }} mt-5" style="background: var(--st-ink); color: #ffffff">{{ $s['b_link_label'] ?? 'Shop now' }}</a>
                    </div>
                    <div class="relative">
                        @if ($bImage)
                            <img src="{{ $bImage }}" alt="{{ $s['b_heading'] }}" loading="lazy" class="mx-auto max-h-52 w-auto object-contain drop-shadow-xl">
                        @else
                            <div class="mx-auto aspect-square w-40 rounded-2xl" style="background: color-mix(in srgb, var(--st-ink) 8%, transparent)"></div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
