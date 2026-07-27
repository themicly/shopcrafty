@php $text = $theme['announcement'] ?? null; @endphp

{{-- Bloom announcement: a warm cream sliver above the green delivery bar, carrying
     the signature leaf glyph and an orange accent so it reads as a friendly market note. --}}
@if ($text)
    <div class="text-center" style="background: var(--st-surface); color: var(--st-ink)" x-data="{ show: true }" x-show="show">
        <div class="st-container flex items-center justify-center gap-3 py-2 text-xs font-medium">
            <span class="stt-fresh-eyebrow" style="color: var(--st-accent)"></span>
            <span>{{ $text }}</span>
            {{-- Enlarged tap target (negative margin keeps the sliver bar slim). --}}
            <button @click="show = false" class="grid h-10 w-10 place-items-center rounded-full text-base leading-none opacity-50 hover:opacity-100 focus-visible:opacity-100" style="color: var(--st-ink); margin-block: -0.5rem" aria-label="{{ __('storefront.dismiss') }}">&times;</button>
        </div>
    </div>
@endif
