@php $text = $theme['announcement'] ?? null; @endphp

@if ($text)
    {{-- Marketplace announcement: brand-blue utility strip, uppercase micro-label feel. --}}
    <div class="stt-market-utilbar text-center font-semibold" x-data="{ show: true }" x-show="show">
        <div class="st-container flex items-center justify-center gap-3 py-2">
            <span class="uppercase tracking-wide">{{ $text }}</span>
            <button @click="show = false" class="grid h-8 w-8 place-items-center text-base leading-none opacity-60 hover:opacity-100" style="margin-block: -0.375rem" aria-label="{{ __('storefront.dismiss') }}">&times;</button>
        </div>
    </div>
@endif
