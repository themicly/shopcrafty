@php $text = $theme['announcement'] ?? null; @endphp

{{-- Haven announcement: a slim espresso utility bar — quiet caps copy with a
     brass diamond, dismissible. --}}
@if ($text)
    <div style="background: var(--stt-haven-panel); color: var(--st-bg)" x-data="{ show: true }" x-show="show">
        <div class="st-container flex items-center justify-center gap-3 py-2">
            <span class="stt-haven-diamond" style="width: 5px; height: 5px; background: var(--stt-haven-brass-lt); transform: rotate(45deg)" aria-hidden="true"></span>
            <span class="text-center text-xs font-medium" style="letter-spacing: 0.1em">{{ $text }}</span>
            {{-- Negative block margin keeps the band slim while the hit-area stays ~36px. --}}
            <button @click="show = false" class="grid h-9 w-9 shrink-0 place-items-center text-base leading-none opacity-60 transition-opacity hover:opacity-100" style="margin-block: -0.5rem" aria-label="{{ __('storefront.dismiss') }}">&times;</button>
        </div>
    </div>
@endif
