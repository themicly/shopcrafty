@php
    // Built-in icon set indexed to the four editable labels (shipping, returns, secure, support).
    // Each slot keeps its own SVG so an empty middle label never shifts the icons around.
    $icons = [
        'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-5.25m0-11.25h1.5v4.5m-1.5-4.5H5.625c-.621 0-1.125.504-1.125 1.125v10.5c0 .621.504 1.125 1.125 1.125H9.75',
        'M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3',
        'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z',
        'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z',
    ];
    // Pair each label with its optional uploaded icon (iconN, an image path from the
    // customizer) and its slot's built-in SVG fallback.
    $items = [];
    foreach ([1, 2, 3, 4] as $n) {
        $label = trim((string) ($s['item'.$n] ?? ''));
        if ($label !== '') {
            $items[] = ['label' => $label, 'icon' => trim((string) ($s['icon'.$n] ?? '')), 'svg' => $icons[$n - 1]];
        }
    }
@endphp

@if ($items)
    {{-- Marketplace: WoodMart-mould USP / trust strip. A hairlined band on --st-surface
         (.stt-market-usp) carrying the four editable value props as boxed items — a squared
         blue-tinted icon tile beside a bold label, split by 1px hairline dividers so it
         reads as a spec-sheet trust reminder. An uploaded icon image replaces the built-in
         SVG per item. No red here; red stays rationed to deals. --}}
    <div class="stt-market-usp">
        <div class="stt-market-usp-grid st-container grid grid-cols-2 gap-6 md:grid-cols-4">
            @foreach ($items as $item)
                <div class="stt-market-usp-item st-reveal">
                    <span class="stt-market-usp-ico">
                        @if ($item['icon'] !== '')
                            <img src="{{ $item['icon'] }}" alt="" loading="lazy" class="object-contain">
                        @else
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['svg'] }}" /></svg>
                        @endif
                    </span>
                    <span class="stt-market-usp-label">{{ $item['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
@endif
