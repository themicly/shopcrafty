@php
    $brands = \Themicly\Shopcrafty\Modules\Catalog\Models\Brand::where('is_active', true)
        ->when(! empty($s['scope_brands']), fn ($q) => $q->whereIn('id', $s['scope_brands']))
        ->orderBy('name')->get();
    $withLogos = $brands->filter(fn ($b) => filled($b->logo_path));
@endphp

@if ($brands->isNotEmpty())
    <section class="py-16 sm:py-24" style="background: var(--st-bg)">
        <div class="st-container">
            <x-st.section-heading :eyebrow="__('storefront.eyebrow_trusted')" :title="$s['heading'] ?? 'Brands we carry'" align="center" />
            <span aria-hidden="true" style="display: block; width: 3rem; height: 3px; margin: 0.875rem auto 0; border-radius: 999px; background: linear-gradient(90deg, var(--st-primary), var(--st-accent))"></span>
        </div>

        @if ($withLogos->isNotEmpty())
            {{-- Logo wall: clean grid, grayscale that lifts to colour on hover. --}}
            <div class="st-container st-reveal mt-10 sm:mt-14">
                <div class="grid grid-cols-2 gap-px overflow-hidden sm:grid-cols-3 lg:grid-cols-5"
                    style="background: var(--st-line); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                    @foreach ($withLogos as $brand)
                        <div class="group grid aspect-[3/2] place-items-center p-6" style="background: var(--st-surface)">
                            <img src="{{ $brand->logo_path }}" alt="{{ $brand->name }}" loading="lazy"
                                class="max-h-12 w-auto max-w-full object-contain opacity-70 grayscale transition duration-300 group-hover:opacity-100 group-hover:grayscale-0">
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            {{-- No logos uploaded yet — fall back to the scrolling name marquee. --}}
            <div class="st-reveal mt-10 overflow-hidden sm:mt-14">
                <div class="st-marquee flex w-max items-center">
                    @foreach ($brands as $brand)
                        <span class="st-display whitespace-nowrap px-8 text-2xl" style="color: var(--st-ink-soft)">{{ $brand->name }}</span>
                    @endforeach
                    @foreach ($brands as $brand)
                        <span class="st-display whitespace-nowrap px-8 text-2xl" style="color: var(--st-ink-soft)" aria-hidden="true">{{ $brand->name }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </section>
@endif
