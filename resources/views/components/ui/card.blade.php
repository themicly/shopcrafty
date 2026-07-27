@props([
    'title' => null,
    'subtitle' => null,
])

<div {{ $attributes->merge([
    'class' => 'rounded-lg border border-line bg-surface-raised shadow-sm',
]) }}>
    @if ($title || $subtitle || isset($actions))
        <div class="flex items-start justify-between gap-4 border-b border-line px-5 py-4">
            <div>
                @if ($title)
                    <h3 class="text-sm font-semibold text-content">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-content-muted">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="p-5">
        {{ $slot }}
    </div>
</div>
