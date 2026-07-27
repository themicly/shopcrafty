@php $text = $theme['announcement'] ?? null; @endphp

{{-- Studio announcement: slim solid-black utility bar, quiet caps copy. --}}
@if ($text)
    <div style="background: var(--st-ink); color: #fff" x-data="{ show: true }" x-show="show">
        <div class="st-container flex items-center justify-center gap-4 py-2">
            <span class="text-center text-xs font-semibold" style="letter-spacing: 0.08em">{{ $text }}</span>
            {{-- Negative block margin keeps the band slim while the hit-area stays ~36px. --}}
            <button @click="show = false" class="grid h-9 w-9 shrink-0 place-items-center text-base leading-none opacity-60 transition-opacity hover:opacity-100" style="margin-block: -0.5rem" aria-label="{{ __('storefront.dismiss') }}">&times;</button>
        </div>
    </div>
@endif
