<div class="space-y-4">
    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <x-ui.select wire:model.live="channel" class="max-w-[10rem]">
            <option value="">All channels</option>
            <option value="email">Email</option>
            <option value="sms">SMS</option>
        </x-ui.select>
        <x-ui.select wire:model.live="status" class="max-w-[10rem]">
            <option value="">All statuses</option>
            <option value="sent">Sent</option>
            <option value="failed">Failed</option>
            <option value="skipped">Skipped</option>
        </x-ui.select>
    </div>

    @if ($logs->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                icon="bell"
                title="No messages yet"
                description="Delivery attempts will appear here once your store starts sending notifications."
            />
        </x-ui.card>
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>When</th>
                    <th>Event</th>
                    <th>Channel</th>
                    <th>Gateway</th>
                    <th>Recipient</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    <tr>
                        <td class="whitespace-nowrap text-content-muted">{{ $log->created_at?->diffForHumans() }}</td>
                        <td class="font-mono text-xs">{{ $log->event_key }}</td>
                        <td>{{ ucfirst($log->channel) }}</td>
                        <td class="text-content-muted">{{ $log->gateway ?? '—' }}</td>
                        <td class="max-w-[16rem] truncate">{{ $log->recipient }}</td>
                        <td>
                            @php
                                $variant = match ($log->status) {
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    'skipped' => 'warning',
                                    default => 'neutral',
                                };
                            @endphp
                            <div class="flex items-center gap-2">
                                <x-ui.badge :variant="$variant">{{ ucfirst($log->status) }}</x-ui.badge>
                                @if ($log->error)
                                    <span class="max-w-[14rem] truncate text-xs text-content-muted" title="{{ $log->error }}">{{ $log->error }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <div>{{ $logs->links() }}</div>
    @endif
</div>
