@php $text = $theme['announcement'] ?? null; @endphp

{{-- Boutique announcement: slim near-black utility bar, bold uppercase retail copy. --}}
@if ($text)
    <div style="background: var(--st-ink); color: #fff" x-data="{ show: true }" x-show="show">
        {{-- Relative row: the close button is absolutely positioned so the notice
             text stays truly centered on every width (not offset by the button). --}}
        <div class="st-container relative flex items-center py-2">
            <span class="flex-1 px-8 text-center text-[11px] font-bold uppercase" style="letter-spacing: 0.14em">{{ $text }}</span>
            {{-- Negative block margin keeps the band slim while the hit-area stays ~36px. --}}
            <button @click="show = false" class="absolute right-2 top-1/2 grid h-9 w-9 shrink-0 -translate-y-1/2 place-items-center text-base leading-none opacity-60 transition-opacity hover:opacity-100" aria-label="{{ __('storefront.dismiss') }}">&times;</button>
        </div>
    </div>
@endif
