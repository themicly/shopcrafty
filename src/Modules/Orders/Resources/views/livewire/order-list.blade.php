<div>
    @php
        $statusPills = [
            ['key' => '', 'label' => 'All', 'dot' => 'bg-content-muted', 'count' => $counts['all']],
            ['key' => 'pending', 'label' => 'Pending', 'dot' => 'bg-warning', 'count' => $counts['pending']],
            ['key' => 'confirmed', 'label' => 'Confirmed', 'dot' => 'bg-info', 'count' => $counts['confirmed']],
            ['key' => 'processing', 'label' => 'Packed', 'dot' => 'bg-primary', 'count' => $counts['processing']],
            ['key' => 'shipped', 'label' => 'Shipped', 'dot' => 'bg-info', 'count' => $counts['shipped']],
            ['key' => 'delivered', 'label' => 'Delivered', 'dot' => 'bg-success', 'count' => $counts['delivered']],
            ['key' => 'returned', 'label' => 'Returned', 'dot' => 'bg-warning', 'count' => $counts['returned']],
            ['key' => 'cancelled', 'label' => 'Cancelled', 'dot' => 'bg-danger', 'count' => $counts['cancelled']],
        ];
        $pillClass = [
            'warning' => 'bg-warning-soft text-warning',
            'info' => 'bg-info-soft text-info',
            'primary' => 'bg-primary-soft text-primary',
            'success' => 'bg-success-soft text-success',
            'danger' => 'bg-danger-soft text-danger',
            'neutral' => 'bg-surface-sunken text-content-secondary',
        ];
        $statusMap = [
            'pending' => ['warning', 'pending'],
            'confirmed' => ['info', 'confirmed'],
            'processing' => ['primary', 'packed'],
            'shipped' => ['info', 'shipped'],
            'delivered' => ['success', 'delivered'],
            'returned' => ['warning', 'returned'],
            'cancelled' => ['danger', 'cancelled'],
        ];
        $channelMap = [
            'whatsapp' => ['WhatsApp', 'bg-success-soft text-success'],
            'facebook' => ['Facebook', 'bg-info-soft text-info'],
            'web' => ['Web', 'bg-info-soft text-info'],
            'manual' => ['manual', 'bg-surface-sunken text-content-secondary'],
        ];
    @endphp

    {{-- Status filter chips --}}
    <div class="mb-5 flex flex-wrap items-center gap-1.5">
        @foreach ($statusPills as $p)
            @php $on = $statusFilter === $p['key']; @endphp
            <button type="button" wire:click="$set('statusFilter', '{{ $p['key'] }}')"
                @class([
                    'inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-medium transition-colors',
                    'bg-surface-sunken text-content ring-1 ring-line-strong' => $on,
                    'text-content-secondary hover:bg-surface-sunken/60' => ! $on,
                ])>
                <span class="h-2 w-2 rounded-full {{ $p['dot'] }}"></span>
                {{ $p['label'] }}
                <span class="rounded-full bg-surface-sunken px-1.5 text-xs font-semibold tabular-nums text-content-muted">{{ $p['count'] }}</span>
            </button>
        @endforeach
    </div>

    {{-- Search + secondary filters --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <div class="min-w-56 flex-1">
            <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Search orders…" />
        </div>
        <x-ui.select wire:model.live="paymentFilter" class="w-40">
            <option value="">All payments</option>
            <option value="unpaid">Unpaid</option>
            <option value="paid">Paid</option>
            <option value="refunded">Refunded</option>
        </x-ui.select>
    </div>

    {{-- Count + actions --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-content-muted"><span class="font-semibold text-content">{{ number_format($orders->total()) }}</span> orders</p>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="perPage" class="h-9 rounded-lg border border-line bg-surface-raised px-3 text-sm text-content-secondary focus:outline-none">
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
                <option value="100">100 / page</option>
            </select>
            <x-admin.toolbar-button icon="download" color="success" :href="route('admin.orders.export')">Export</x-admin.toolbar-button>
            <x-admin.toolbar-button icon="printer" color="primary" onclick="window.print()">Print</x-admin.toolbar-button>
            <x-admin.toolbar-primary :href="route('admin.orders.create')">New Order</x-admin.toolbar-primary>
        </div>
    </div>

    @if ($orders->isEmpty())
        <div class="rounded-2xl border border-line bg-surface-raised">
            <x-ui.empty-state icon="orders" title="No orders found" description="Orders will appear here once customers check out." />
        </div>
    @else
        <x-ui.table printable title="Orders">
            <thead>
                <tr>
                    <th class="w-10 print:hidden"></th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th class="text-right">Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Channel</th>
                    <th>Date</th>
                    <th class="text-right print:hidden">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    @php
                        [$sv, $slabel] = $statusMap[$order->status] ?? ['neutral', $order->status];
                        $src = strtolower((string) $order->source);
                        [$chLabel, $chClass] = $channelMap[$src] ?? [$order->source ?: 'manual', 'bg-surface-sunken text-content-secondary'];
                        $addr = $order->shippingAddress;
                        $date = $order->placed_at ?? $order->created_at;
                    @endphp
                    <tr wire:key="o-{{ $order->id }}">
                        <td class="print:hidden">
                            <input type="checkbox" value="{{ $order->id }}" wire:model.live="selected" class="h-4 w-4 rounded border-line text-primary focus:ring-primary">
                        </td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-sm font-semibold text-success hover:underline focus-visible:underline focus-visible:outline-none">{{ $order->number }}</a></td>
                        <td>
                            <div class="font-semibold text-content">{{ $addr?->name ?? 'Guest' }}</div>
                            @if ($addr?->phone)
                                <div class="text-xs text-content-muted">{{ $addr->phone }}</div>
                            @endif
                        </td>
                        <td class="text-right font-semibold text-content">{{ format_money($order->grand_total) }}</td>
                        <td>
                            <span class="inline-flex items-center rounded-full border border-warning/30 px-2.5 py-0.5 text-xs font-semibold text-warning">{{ strtoupper($order->payment_method ?: 'COD') }}</span>
                        </td>
                        <td>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $pillClass[$sv] }}">{{ $slabel }}</span>
                        </td>
                        <td>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $chClass }}">{{ $chLabel }}</span>
                        </td>
                        <td class="whitespace-nowrap text-content-secondary">{{ $date?->format('j M') }}</td>
                        <td class="text-right print:hidden">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.icon-button icon="eye" variant="ghost" label="View" :href="route('admin.orders.show', $order)" />
                                <a href="{{ route('admin.orders.invoice', $order) }}" class="text-sm font-medium text-success hover:underline">Invoice</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <div class="mt-4">{{ $orders->links() }}</div>
    @endif

    {{-- Floating bulk actions --}}
    @if (count($selected) > 0)
        <x-ui.bulk-action-bar :count="count($selected)">
            <button wire:click="bulk('confirm')" class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">Confirm</button>
            <button wire:click="bulk('process')" class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">Pack</button>
            <button wire:click="bulk('ship')" class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">Ship</button>
            <button wire:click="bulk('deliver')" class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">Deliver</button>
            <button type="button" x-on:click="$dispatch('confirm', { title: 'Cancel selected orders?', message: 'This cancels every selected order. This cannot be undone.', confirmLabel: 'Cancel orders', onConfirm: () => $wire.bulk('cancel') })" class="rounded-lg px-2.5 py-1 text-sm font-medium text-danger transition-colors hover:bg-danger/20">Cancel</button>
            <button wire:click="$set('selected', [])" class="rounded-lg px-2 py-1 text-surface/60 transition-colors hover:bg-surface/15" aria-label="Clear">&times;</button>
        </x-ui.bulk-action-bar>
    @endif
</div>
