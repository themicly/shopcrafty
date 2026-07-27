<div class="space-y-4">
    <x-admin.list-toolbar :count="$totalCount">
        <div class="flex items-center gap-1 rounded-md border border-line p-0.5">
            @foreach (['all' => 'All', 'subscribed' => 'Subscribed', 'unsubscribed' => 'Unsubscribed'] as $value => $label)
                <button wire:click="$set('filter', '{{ $value }}')" @class([
                    'rounded px-3 py-1.5 text-sm',
                    'bg-surface-sunken font-medium text-content' => $filter === $value,
                    'text-content-muted hover:text-content' => $filter !== $value,
                ])>{{ $label }}</button>
            @endforeach
        </div>
        <span class="text-sm text-content-muted">{{ $subscribedCount }} active</span>
    </x-admin.list-toolbar>

    @if ($subscribers->isEmpty())
        <x-ui.card>
            <x-ui.empty-state icon="customers" title="No subscribers yet" description="They'll appear here as shoppers join from the storefront." />
        </x-ui.card>
    @else
        <x-ui.table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($subscribers as $subscriber)
                    <tr>
                        <td class="font-medium text-content">{{ $subscriber->email }}</td>
                        <td>{{ $subscriber->name ?? '—' }}</td>
                        <td><x-ui.badge :variant="$subscriber->status === 'subscribed' ? 'success' : 'neutral'">{{ ucfirst($subscriber->status) }}</x-ui.badge></td>
                        <td class="text-content-muted">{{ $subscriber->created_at?->format('M j, Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <div>{{ $subscribers->links() }}</div>
    @endif
</div>
