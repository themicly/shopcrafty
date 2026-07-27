@props([
    'tabs' => [],
    'default' => null,
])

@php
    $first = $default ?? ($tabs[0]['key'] ?? null);
@endphp

<div x-data="{ tab: @js($first) }" {{ $attributes }}>
    <div class="flex flex-nowrap gap-1 overflow-x-auto border-b border-line" role="tablist">
        @foreach ($tabs as $t)
            <button
                type="button"
                @click="tab = @js($t['key'])"
                :class="tab === @js($t['key'])
                    ? 'border-primary text-content'
                    : 'border-transparent text-content-muted hover:text-content'"
                class="-mb-px shrink-0 whitespace-nowrap border-b-2 px-3 py-2 text-sm font-medium transition-colors"
            >
                {{ $t['label'] }}
            </button>
        @endforeach
    </div>

    <div class="pt-4">
        {{ $slot }}
    </div>
</div>
