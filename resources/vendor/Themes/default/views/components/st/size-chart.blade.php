@props(['product'])

{{--
    "Size chart" trigger + modal for the PDP. Theme-neutral on purpose: anonymous
    components are baked into the shared compiled-view cache across themes (THM-07),
    so this file styles itself with --st-* tokens only and renders NOTHING when the
    product's category tree has no chart attached — zero visual change on such PDPs.

    Resolution: walk up from the product's category through its parents; the
    NEAREST category with a chart wins (a "T-shirts" chart beats "Clothing"'s).
--}}
@php
    $stSizeChart = null;
    $stSizeCat = $product->category ?? null;
    $stGuard = 0;

    while ($stSizeCat && $stGuard++ < 25) {
        if ($stSizeCat->size_chart_id) {
            $stSizeChart = \Themicly\Shopcrafty\Modules\Catalog\Models\SizeChart::find($stSizeCat->size_chart_id);

            if ($stSizeChart) {
                break;
            }
        }

        $stSizeCat = $stSizeCat->parent;
    }
@endphp

@if ($stSizeChart)
    @php
        $stSizeCols = array_values((array) $stSizeChart->columns);
        $stSizeRows = (array) $stSizeChart->rows;
    @endphp
    <div x-data="{ open: false }" {{ $attributes }}>
        <button type="button"
            @click="open = true; $nextTick(() => $refs.stSizeClose.focus())"
            class="inline-flex items-center gap-1.5 text-sm font-medium underline underline-offset-4 transition-opacity hover:opacity-70"
            style="color: var(--st-ink)">
            {{-- Ruler --}}
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.7 13.9 13.9 2.7a1.5 1.5 0 0 1 2.1 0l5.3 5.3a1.5 1.5 0 0 1 0 2.1L10.1 21.3a1.5 1.5 0 0 1-2.1 0l-5.3-5.3a1.5 1.5 0 0 1 0-2.1Z" />
                <path stroke-linecap="round" d="m6.4 10.2 2.1 2.1m0-4.2 2.1 2.1m0-4.2 2.1 2.1m0-4.2 2.1 2.1" />
            </svg>
            {{ __('storefront.size_chart') }}
        </button>

        <div x-show="open" x-cloak x-transition.opacity
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-[70] flex items-center justify-center p-4"
            role="dialog" aria-modal="true" aria-label="{{ $stSizeChart->name }} {{ __('storefront.size_chart_suffix') }}">
            <div class="absolute inset-0 bg-black/60" @click="open = false" aria-hidden="true"></div>

            <div class="relative flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden"
                style="background: var(--st-bg); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                <div class="flex items-start justify-between gap-4 p-5 pb-4" style="border-bottom: 1px solid var(--st-line)">
                    <div>
                        <h2 class="text-base font-semibold" style="color: var(--st-ink)">{{ $stSizeChart->name }}</h2>
                        @if ($stSizeChart->note)
                            <p class="mt-0.5 text-sm" style="color: var(--st-ink-soft)">{{ $stSizeChart->note }}</p>
                        @endif
                    </div>
                    <button type="button" x-ref="stSizeClose" @click="open = false" aria-label="{{ __('storefront.close_size_chart') }}"
                        class="grid h-8 w-8 shrink-0 place-items-center text-xl transition-opacity hover:opacity-70"
                        style="color: var(--st-ink); border: 1px solid var(--st-line); border-radius: var(--st-radius-sm)">&times;</button>
                </div>

                <div class="overflow-auto p-5 pt-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm" style="border-collapse: collapse; color: var(--st-ink)">
                            <thead>
                                <tr style="background: var(--st-surface)">
                                    <th scope="col" class="px-3 py-2 text-left font-semibold" style="border: 1px solid var(--st-line)">{{ __('storefront.size') }}</th>
                                    @foreach ($stSizeCols as $col)
                                        <th scope="col" class="px-3 py-2 text-left font-semibold" style="border: 1px solid var(--st-line)">{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stSizeRows as $row)
                                    <tr>
                                        <th scope="row" class="px-3 py-2 text-left font-semibold" style="border: 1px solid var(--st-line); background: var(--st-surface)">{{ $row['label'] ?? '' }}</th>
                                        @foreach ($stSizeCols as $i => $col)
                                            <td class="px-3 py-2" style="border: 1px solid var(--st-line)">{{ $row['values'][$i] ?? '—' }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
