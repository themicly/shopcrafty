@props(['count' => 0])

{{--
    Floating bulk-action bar: a neutral (dark) pill that rises from the bottom
    when rows are selected. Actions go in the slot; style them for a dark surface
    with the .bz-bulk-btn helper.
--}}
<div class="pointer-events-none fixed inset-x-0 bottom-6 z-40 flex justify-center px-4">
    <div class="pointer-events-auto flex items-center gap-3 rounded-2xl bg-content px-3 py-2 shadow-lg ring-1 ring-black/10">
        <span class="grid h-6 min-w-6 place-items-center rounded-full bg-surface px-2 text-xs font-semibold tabular-nums text-content">{{ $count }}</span>
        <span class="text-sm font-medium text-surface">selected</span>
        <div class="mx-0.5 h-5 w-px bg-surface/25"></div>
        {{ $slot }}
    </div>
</div>
