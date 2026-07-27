<div>
    @php
        $segments = [
            'all' => 'All',
            'vip' => 'VIP',
            'repeat' => 'Repeat Buyer',
            'new' => 'New',
        ];
        $channelMap = [
            'whatsapp' => ['WhatsApp', 'bg-success-soft text-success'],
            'facebook' => ['Facebook', 'bg-info-soft text-info'],
            'web' => ['Web', 'bg-info-soft text-info'],
            'manual' => ['manual', 'bg-surface-sunken text-content-secondary'],
        ];
        $avatarPalette = ['bg-pink-500', 'bg-violet-500', 'bg-blue-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-indigo-500'];
    @endphp

    {{-- Summary cards --}}
    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-ui.stat-card :value="number_format($stats['total'])" label="Total Customers" />
        <x-ui.stat-card :value="number_format($stats['active'])" label="Active" />
        <x-ui.stat-card :value="number_format($stats['vip'])" label="VIP" accent="success" />
        <x-ui.stat-card :value="number_format($stats['new'])" label="New (30d)" accent="success" />
    </div>

    {{-- Search + segment tabs --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="min-w-56 max-w-sm flex-1">
            <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Search by name or phone…" />
        </div>
        <div class="flex flex-wrap items-center gap-1">
            @foreach ($segments as $key => $label)
                @php $on = $segment === $key; @endphp
                <button type="button" wire:click="$set('segment', '{{ $key }}')"
                    @class([
                        'rounded-full px-3 py-1.5 text-sm font-medium transition-colors',
                        'bg-success-soft text-success' => $on,
                        'text-content-secondary hover:bg-surface-sunken' => ! $on,
                    ])>{{ $label }}</button>
            @endforeach
        </div>
        @if (! empty($allTags))
            <select wire:model.live="tag" class="h-9 rounded-lg border border-line bg-surface-raised px-3 text-sm text-content-secondary focus:outline-none">
                <option value="">All tags</option>
                @foreach ($allTags as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Count + actions --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-content-muted"><span class="font-semibold text-content">{{ number_format($customers->total()) }}</span> customers</p>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="perPage" class="h-9 rounded-lg border border-line bg-surface-raised px-3 text-sm text-content-secondary focus:outline-none">
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
                <option value="100">100 / page</option>
            </select>
            <x-admin.toolbar-button icon="download" color="success" :href="route('admin.customers.export')">Export</x-admin.toolbar-button>
            <x-admin.toolbar-button icon="printer" color="primary" onclick="window.print()">Print</x-admin.toolbar-button>
            <x-admin.toolbar-primary :href="route('admin.customers.create')">Add Customer</x-admin.toolbar-primary>
        </div>
    </div>

    @if ($customers->isEmpty())
        <div class="rounded-2xl border border-line bg-surface-raised">
            <x-ui.empty-state icon="customers" title="No customers yet" description="Customers will appear here once they register or place an order." />
        </div>
    @else
        <x-ui.table printable title="Customers">
            <thead>
                <tr>
                    <th class="w-10 print:hidden"></th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th class="text-right">Orders</th>
                    <th class="text-right">LTV</th>
                    <th>Health</th>
                    <th>Channel</th>
                    <th class="text-right print:hidden">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    @php
                        $orders = (int) ($customer->orders_count ?? 0);
                        $ltv = (int) ($customer->orders_sum_grand_total ?? 0);
                        $recency = $customer->last_order_at ? (int) $customer->last_order_at->diffInDays(now()) : null;
                        $health = 30 + min(40, $orders * 15);
                        if ($recency !== null) {
                            $health += $recency <= 30 ? 15 : ($recency <= 90 ? 8 : 0);
                        }
                        $health = min(98, $health);
                        $tone = $health >= 70 ? 'success' : ($health >= 50 ? 'warning' : 'danger');
                        $ringClass = ['success' => 'ring-success', 'warning' => 'ring-warning', 'danger' => 'ring-danger'][$tone];
                        $dotClass = ['success' => 'bg-success', 'warning' => 'bg-warning', 'danger' => 'bg-danger'][$tone];
                        $avatar = $avatarPalette[abs(crc32($customer->name ?? '')) % count($avatarPalette)];
                        $src = strtolower((string) $customer->channel);
                        [$chLabel, $chClass] = $channelMap[$src] ?? ($src ? [$customer->channel, 'bg-surface-sunken text-content-secondary'] : ['manual', 'bg-surface-sunken text-content-secondary']);
                    @endphp
                    <tr wire:key="c-{{ $customer->id }}">
                        <td class="print:hidden">
                            <input type="checkbox" value="{{ $customer->id }}" wire:model.live="selected" class="h-4 w-4 rounded border-line text-primary focus:ring-primary">
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full text-sm font-semibold text-white ring-2 ring-offset-2 ring-offset-surface-raised {{ $avatar }} {{ $ringClass }}">
                                    {{ strtoupper(substr($customer->name ?? '?', 0, 1)) }}
                                </span>
                                <div class="flex min-w-0 items-center gap-2">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="font-semibold text-content hover:text-primary hover:underline focus-visible:underline focus-visible:outline-none">{{ $customer->name }}</a>
                                    @if ($ltv >= 500000)
                                        <span class="rounded-md border border-primary/30 px-1.5 py-0.5 text-[11px] font-medium text-primary">VIP</span>
                                    @endif
                                    @if ($orders >= 2)
                                        <span class="rounded-md border border-success/30 px-1.5 py-0.5 text-[11px] font-medium text-success">Repeat Buyer</span>
                                    @endif
                                    <span class="text-content-muted">+</span>
                                </div>
                            </div>
                        </td>
                        <td class="text-content-secondary">{{ $customer->mobile ?: '—' }}</td>
                        <td class="text-right tabular-nums text-content-secondary">{{ $orders }}</td>
                        <td class="text-right font-semibold tabular-nums text-success">{{ format_money($ltv) }}</td>
                        <td>
                            <span class="inline-flex items-center gap-1.5 tabular-nums text-content-secondary">
                                <span class="h-2 w-2 rounded-full {{ $dotClass }}"></span>{{ $health }}
                            </span>
                        </td>
                        <td>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $chClass }}">{{ $chLabel }}</span>
                        </td>
                        <td class="text-right print:hidden">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.icon-button icon="eye" variant="ghost" label="View" :href="route('admin.customers.show', $customer)" />
                                <x-ui.icon-button icon="trash" variant="danger" label="Delete" x-on:click="$dispatch('confirm', { title: 'Delete customer?', message: 'This permanently deletes the customer and cannot be undone.', confirmLabel: 'Delete', onConfirm: () => $wire.delete({{ $customer->id }}) })" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <div class="mt-4">{{ $customers->links() }}</div>
    @endif

    {{-- Floating bulk actions --}}
    @if (count($selected) > 0)
        <x-ui.bulk-action-bar :count="count($selected)">
            <button wire:click="bulk('activate')" class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">Activate</button>
            <button wire:click="bulk('block')" class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">Block</button>
            <button type="button" x-on:click="$dispatch('confirm', { title: 'Delete selected customers?', message: 'This permanently deletes every selected customer.', confirmLabel: 'Delete', onConfirm: () => $wire.bulk('delete') })" class="rounded-lg px-2.5 py-1 text-sm font-medium text-danger transition-colors hover:bg-danger/20">Delete</button>
            <button wire:click="$set('selected', [])" class="rounded-lg px-2 py-1 text-surface/60 transition-colors hover:bg-surface/15" aria-label="Clear">&times;</button>
        </x-ui.bulk-action-bar>
    @endif
</div>
