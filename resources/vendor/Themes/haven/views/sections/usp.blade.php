{{-- Haven service highlights — the values marquee: an infinite strip of
     lowercase italic serif promises separated by brass diamonds, running
     under a single hairline divider. Pauses on hover/focus; under
     prefers-reduced-motion the track stands still (content remains readable
     from its start). Each item carries a built-in icon; an uploaded icon
     (icon1-4) replaces it when present. --}}
@php
    // Built-in icon set indexed to the four editable labels (shipping, returns, secure, support).
    $icons = [
        'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-5.25m0-11.25h1.5v4.5m-1.5-4.5H5.625c-.621 0-1.125.504-1.125 1.125v10.5c0 .621.504 1.125 1.125 1.125H9.75',
        'M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3',
        'M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z',
        'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z',
    ];
    $items = [];
    foreach ([1, 2, 3, 4] as $i) {
        $label = trim((string) ($s["item{$i}"] ?? ''));
        if ($label !== '') {
            $items[] = ['label' => $label, 'icon' => trim((string) ($s["icon{$i}"] ?? '')), 'svg' => $icons[$i - 1]];
        }
    }
@endphp

@if ($items)
    <section aria-label="{{ __('storefront.why_shop_with_us') }}" class="st-reveal" style="background: var(--st-bg)">
        <div class="stt-haven-divider st-reveal" aria-hidden="true"></div>
        <div class="stt-haven-marquee" tabindex="0" role="marquee" aria-label="{{ implode(', ', array_column($items, 'label')) }}">
            <div class="stt-haven-marquee-track">
                {{-- Two identical runs make the -50% translate loop seamless. --}}
                @foreach ([false, true] as $dup)
                    <span class="stt-haven-marquee-item" @if ($dup) aria-hidden="true" @endif>
                        @foreach ($items as $item)
                            <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center" style="color: var(--st-accent)" aria-hidden="true">
                                @if ($item['icon'])
                                    <img src="{{ $item['icon'] }}" alt="" loading="lazy" class="h-full w-full object-contain">
                                @else
                                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-full w-full"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['svg'] }}" /></svg>
                                @endif
                            </span>
                            <span>{{ $item['label'] }}</span>
                            <span class="stt-haven-diamond" aria-hidden="true"></span>
                        @endforeach
                    </span>
                @endforeach
            </div>
        </div>
    </section>
@endif
