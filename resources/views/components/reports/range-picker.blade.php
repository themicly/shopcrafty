@props(['range', 'fromDate', 'toDate'])

<div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-1 rounded-md border border-line p-0.5">
            @foreach ([7 => '7 days', 30 => '30 days', 90 => '90 days'] as $days => $label)
                <button wire:click="$set('range', {{ $days }})" @class([
                    'rounded px-3 py-1.5 text-sm',
                    'bg-surface-sunken font-medium text-content' => $range === $days,
                    'text-content-muted hover:text-content' => $range !== $days,
                ])>{{ $label }}</button>
            @endforeach
            @if ($range === 0)
                <span class="rounded bg-surface-sunken px-3 py-1.5 text-sm font-medium text-content">Custom</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <label for="report-from" class="text-xs font-medium text-content-muted">From</label>
            <input id="report-from" type="date" wire:model.live="fromDate" max="{{ now()->toDateString() }}"
                   class="h-9 rounded-md border border-line bg-surface-raised px-2 text-sm text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" />
            <label for="report-to" class="text-xs font-medium text-content-muted">To</label>
            <input id="report-to" type="date" wire:model.live="toDate" max="{{ now()->toDateString() }}"
                   class="h-9 rounded-md border border-line bg-surface-raised px-2 text-sm text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary" />
        </div>
    </div>
    @isset($actions)
        <div class="flex items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
