@php
    $brands = \Themicly\Shopcrafty\Modules\Catalog\Models\Brand::where('is_active', true)
        ->when(! empty($s['scope_brands']), fn ($q) => $q->whereIn('id', $s['scope_brands']))
        ->orderBy('name')->get();
    $withLogos = $brands->filter(fn ($b) => filled($b->logo_path));
@endphp

@if ($brands->isNotEmpty())
    {{-- Boutique: brand logo wall — hairline-gridded cells, grayscale logos lifting to
         colour on hover; name marquee fallback until logos are uploaded. --}}
    <section class="stt-boutique-section" style="background: var(--st-surface)">
        <div class="st-container stt-boutique-narrow">
            <div class="stt-boutique-head-center st-reveal mb-10">
                <span class="stt-boutique-eyebrow">{{ __('storefront.trusted_labels') }}</span>
                <h2 class="stt-boutique-title">{{ $s['heading'] ?? 'Our brands' }}</h2>
                <span class="stt-boutique-mark" aria-hidden="true"></span>
            </div>

            @if ($withLogos->isNotEmpty())
                <div class="st-reveal">
                    <div class="grid grid-cols-2 gap-px overflow-hidden sm:grid-cols-3 lg:grid-cols-5"
                        style="background: var(--st-line); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                        @foreach ($withLogos as $brand)
                            <div class="stt-boutique-brandcell group">
                                <img src="{{ $brand->logo_path }}" alt="{{ $brand->name }}" loading="lazy"
                                    class="max-h-12 w-auto max-w-full object-contain opacity-70 grayscale transition duration-300 group-hover:opacity-100 group-hover:grayscale-0">
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- No logos uploaded yet — fall back to the scrolling name marquee. --}}
                <div class="st-reveal overflow-hidden">
                    <div class="st-marquee flex w-max items-center">
                        @foreach ($brands as $brand)
                            <span class="whitespace-nowrap px-8 text-xl font-bold uppercase" style="letter-spacing: 0.1em; color: var(--st-ink-soft)">{{ $brand->name }}</span>
                        @endforeach
                        @foreach ($brands as $brand)
                            <span class="whitespace-nowrap px-8 text-xl font-bold uppercase" style="letter-spacing: 0.1em; color: var(--st-ink-soft)" aria-hidden="true">{{ $brand->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
