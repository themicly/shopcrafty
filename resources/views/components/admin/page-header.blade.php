@props([
    'title',
    'subtitle' => null,
])

<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <h2 class="text-xl font-semibold text-content">{{ $title }}</h2>
        @if ($subtitle)
            <p class="mt-1 text-sm text-content-muted">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
