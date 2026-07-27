<div>
    @php
        // Render a stored JSON value as a short, human-readable string.
        $display = function ($raw) {
            if ($raw === null) {
                return '—';
            }
            $value = json_decode($raw, true);
            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }
            if (is_array($value)) {
                return json_encode($value);
            }
            if ($value === null || $value === '') {
                return '—';
            }
            return (string) $value;
        };
    @endphp

    <div class="mb-4 max-w-sm">
        <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Search by key or person…" aria-label="Search change log" />
    </div>

    @if ($audits->isEmpty())
        <div class="rounded-2xl border border-line bg-surface-raised">
            <x-ui.empty-state
                icon="settings"
                title="No changes recorded yet"
                description="Configuration changes will appear here with who made them and when." />
        </div>
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>Setting</th>
                    <th>Change</th>
                    <th>Who</th>
                    <th>When</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($audits as $audit)
                    <tr wire:key="audit-{{ $audit->id }}">
                        <td class="font-medium text-content">{{ $audit->key }}</td>
                        <td>
                            <span class="inline-flex flex-wrap items-center gap-1.5">
                                <span class="rounded bg-surface-sunken px-1.5 py-0.5 text-xs text-content-muted line-through">{{ $display($audit->old_value) }}</span>
                                <span class="text-content-muted">&rarr;</span>
                                <span class="rounded bg-primary-soft px-1.5 py-0.5 text-xs font-medium text-primary">{{ $display($audit->new_value) }}</span>
                            </span>
                        </td>
                        <td class="text-content-secondary">{{ $audit->user_name ?: 'System' }}</td>
                        <td class="whitespace-nowrap text-content-muted" title="{{ $audit->created_at?->toDayDateTimeString() }}">
                            {{ $audit->created_at?->diffForHumans() }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <div class="mt-4">{{ $audits->links() }}</div>
    @endif
</div>
