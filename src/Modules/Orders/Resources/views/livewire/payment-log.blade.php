<div class="space-y-4">
    @php
        $actionLabels = [
            'create_session' => 'Session',
            'webhook' => 'Webhook',
            'return_confirm' => 'Return',
            'mark_paid' => 'Reconcile',
        ];
    @endphp

    {{-- Filters + live count. --}}
    <x-admin.list-toolbar :count="$total">
        <div class="min-w-48 flex-1">
            <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Search order number…" />
        </div>

        <x-ui.select wire:model.live="gateway" class="w-36">
            <option value="">All gateways</option>
        </x-ui.select>

        <x-ui.select wire:model.live="action" class="w-40">
            <option value="">All actions</option>
            <option value="create_session">Session</option>
            <option value="webhook">Webhook</option>
            <option value="return_confirm">Return confirm</option>
            <option value="mark_paid">Reconcile</option>
        </x-ui.select>

        <x-ui.select wire:model.live="outcome" class="w-36">
            <option value="">All outcomes</option>
            <option value="success">Success</option>
            <option value="failed">Failed</option>
        </x-ui.select>

        <div class="w-40"><x-ui.input type="date" wire:model.live="from" aria-label="From date" /></div>
        <div class="w-40"><x-ui.input type="date" wire:model.live="to" aria-label="To date" /></div>

        @if ($gateway || $action || $outcome || $search || $from || $to)
            <button type="button" wire:click="resetFilters" class="text-sm font-medium text-content-muted hover:text-content hover:underline">Clear</button>
        @endif
    </x-admin.list-toolbar>

    @if ($logs->isEmpty())
        <div class="rounded-2xl border border-line bg-surface-raised">
            <x-ui.empty-state
                icon="orders"
                title="No payment activity yet"
                description="Every gateway session, webhook and confirmation will appear here once shoppers start paying online."
            />
        </div>
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Gateway</th>
                    <th>Action</th>
                    <th>Order</th>
                    <th>Outcome</th>
                    <th>Message</th>
                    <th class="w-10"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                    @php
                        $isOpen = $expanded === $log->id;
                        $actLabel = $actionLabels[$log->action] ?? $log->action;
                    @endphp
                    <tr wire:key="pl-{{ $log->id }}" wire:click="toggle({{ $log->id }})" class="cursor-pointer">
                        <td class="whitespace-nowrap text-content-muted">
                            <span title="{{ $log->created_at?->format('j M Y, H:i:s') }}">{{ $log->created_at?->diffForHumans() }}</span>
                        </td>
                        <td class="capitalize text-content">{{ $log->gateway }}</td>
                        <td>
                            <span class="inline-flex items-center rounded-full bg-surface-sunken px-2.5 py-0.5 text-xs font-semibold text-content-secondary">{{ $actLabel }}</span>
                        </td>
                        <td>
                            @if ($log->order_id && $log->order_number)
                                <a href="{{ route('admin.orders.show', $log->order_id) }}" wire:click.stop class="font-mono text-xs font-semibold text-success hover:underline">{{ $log->order_number }}</a>
                            @elseif ($log->order_number)
                                <span class="font-mono text-xs text-content-muted">{{ $log->order_number }}</span>
                            @else
                                <span class="text-content-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if ($log->success)
                                <x-ui.badge variant="success">Success</x-ui.badge>
                            @else
                                <x-ui.badge variant="danger">Failed</x-ui.badge>
                            @endif
                        </td>
                        <td class="max-w-[22rem] truncate" title="{{ $log->message }}">
                            @if ($log->http_status)
                                <span class="mr-1.5 font-mono text-xs text-content-muted">{{ $log->http_status }}</span>
                            @endif
                            {{ $log->message ?: '—' }}
                        </td>
                        <td class="text-right text-content-muted">
                            <x-ui.icon name="{{ $isOpen ? 'arrow-up' : 'arrow-down' }}" class="h-4 w-4" />
                        </td>
                    </tr>

                    @if ($isOpen)
                        <tr wire:key="pl-detail-{{ $log->id }}" class="bg-surface-sunken/40">
                            <td colspan="7">
                                <div class="space-y-3 py-1">
                                    <div>
                                        <div class="mb-1 text-[11px] font-semibold uppercase tracking-wider text-content-muted">Message</div>
                                        <p class="text-sm text-content-secondary">{{ $log->message ?: '—' }}</p>
                                    </div>
                                    <div>
                                        <div class="mb-1 text-[11px] font-semibold uppercase tracking-wider text-content-muted">Context</div>
                                        @if (! empty($log->context))
                                            <pre class="overflow-x-auto rounded-lg bg-surface-sunken p-3 text-xs text-content-secondary">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @else
                                            <p class="text-sm text-content-muted">No context recorded.</p>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-content-muted">
                                        <span>HTTP status: <span class="font-mono text-content-secondary">{{ $log->http_status ?? '—' }}</span></span>
                                        <span>Recorded: <span class="text-content-secondary">{{ $log->created_at?->format('j M Y, H:i:s') }}</span></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </x-ui.table>

        <div>{{ $logs->links() }}</div>
    @endif
</div>
