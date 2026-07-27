@php
    $brands = \Themicly\Shopcrafty\Modules\Catalog\Models\Brand::where('is_active', true)
        ->when(! empty($s['scope_brands']), fn ($q) => $q->whereIn('id', $s['scope_brands']))
        ->orderBy('name')->get();
    $withLogos = $brands->filter(fn ($b) => filled($b->logo_path));
@endphp

@if ($brands->isNotEmpty())
    {{-- Marketplace: WoodMart-mould "Brands we carry" — a hairline-boxed logo wall on a soft
         surface pad, each cell squared to the theme radius and split by 1px lines. Section opens
         with a red eyebrow, bold title and short red underline rule; a "View all →" link sits on
         the same baseline. Rationed red, no gradients or overlays — honest boxes only. --}}
    <section class="stt-market-section stt-market-section--surface">
        <div class="st-container">
            <div class="stt-market-shead st-reveal">
                <div>
                    <p class="stt-market-eyebrow">{{ __('storefront.trusted_by_the_best') }}</p>
                    <h2 class="stt-market-title">{{ $s['heading'] ?? 'Brands we carry' }}</h2>
                    <span class="stt-market-rule"></span>
                </div>
                <a href="{{ url('/shop') }}" class="stt-market-viewall">
                    {{ __('storefront.view_all') }}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0-6-6m6 6-6 6"/></svg>
                </a>
            </div>

            @if ($withLogos->isNotEmpty())
                {{-- Logo wall: a single boxed module divided into crisp cells by 1px hairlines.
                     Logos sit grayscale/dim and lift to full colour on hover — no card theatrics. --}}
                <div class="stt-market-box st-reveal overflow-hidden">
                    <div class="grid grid-cols-2 gap-px sm:grid-cols-3 lg:grid-cols-5"
                        style="background: var(--st-line)">
                        @foreach ($withLogos as $brand)
                            <div class="group grid aspect-[3/2] place-items-center p-6" style="background: var(--st-bg)">
                                <img src="{{ $brand->logo_path }}" alt="{{ $brand->name }}" loading="lazy"
                                    class="max-h-12 w-auto max-w-full object-contain opacity-70 grayscale transition duration-300 group-hover:opacity-100 group-hover:grayscale-0">
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- No logos uploaded yet — fall back to a boxed name marquee, uppercase and
                     spec-sheet flat, framed in the same hairline module. --}}
                <div class="stt-market-box st-reveal overflow-hidden">
                    <div class="st-marquee flex w-max items-center py-6">
                        @foreach ($brands as $brand)
                            <span class="whitespace-nowrap px-8 text-lg font-bold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ $brand->name }}</span>
                        @endforeach
                        @foreach ($brands as $brand)
                            <span class="whitespace-nowrap px-8 text-lg font-bold uppercase tracking-wide" style="color: var(--st-ink-soft)" aria-hidden="true">{{ $brand->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
