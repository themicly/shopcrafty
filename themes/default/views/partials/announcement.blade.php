@php $text = $theme['announcement'] ?? null; @endphp

@if ($text)
    <div class="stt-aurora-announce text-center" x-data="{ show: true }" x-show="show">
        <div class="st-container flex items-center justify-center gap-3 py-2">
            <span>{{ $text }}</span>
            <button @click="show = false" class="grid h-6 w-6 place-items-center rounded-full opacity-70 transition-opacity hover:opacity-100" aria-label="{{ __('storefront.dismiss') }}">&times;</button>
        </div>
    </div>
@endif
