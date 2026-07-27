@props(['count' => null])

{{-- Standard list toolbar: filters/search on the left, a live item count on the right. --}}
<div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <div class="flex flex-1 flex-wrap items-center gap-2">{{ $slot }}</div>
    @if ($count !== null)
        <p class="shrink-0 text-sm tabular-nums text-content-muted">{{ number_format($count) }} {{ \Illuminate\Support\Str::plural('item', $count) }}</p>
    @endif
</div>
