<div class="space-y-6">
    <x-reports.range-picker :range="$range" :from-date="$fromDate" :to-date="$toDate" />

    {{-- New customers --}}
    <x-ui.card>
        <div class="flex items-start justify-between">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">New customers</p>
                <p class="mt-1 text-2xl font-semibold text-content">{{ $newCustomersTotal }}</p>
            </div>
        </div>
        <div class="mt-4 text-primary"><x-ui.chart :points="$newCustomersSeries" type="bar" class="h-24 w-full" /></div>
    </x-ui.card>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Top customers --}}
        <x-ui.card title="Top customers" subtitle="All-time lifetime value">
            <x-slot:actions>
                <div class="flex items-center gap-1 rounded-md border border-line p-0.5">
                    <button wire:click="$set('topSort', 'spend')" @class(['rounded px-2.5 py-1 text-xs', 'bg-surface-sunken font-medium text-content' => $topSort === 'spend', 'text-content-muted hover:text-content' => $topSort !== 'spend'])>By spend</button>
                    <button wire:click="$set('topSort', 'orders')" @class(['rounded px-2.5 py-1 text-xs', 'bg-surface-sunken font-medium text-content' => $topSort === 'orders', 'text-content-muted hover:text-content' => $topSort !== 'orders'])>By orders</button>
                </div>
            </x-slot:actions>
            @if ($topCustomers->isEmpty())
                <x-ui.empty-state icon="customers" title="No customers yet" description="Top customers appear once orders come in." />
            @else
                <div class="space-y-2">
                    @foreach ($topCustomers as $customer)
                        <a href="{{ route('admin.customers.show', $customer) }}" class="flex items-center justify-between gap-3 rounded-md px-1 py-1 hover:bg-surface-sunken">
                            <span class="min-w-0 flex-1 truncate text-sm font-medium text-content">{{ $customer->name }}</span>
                            <span class="shrink-0 text-sm font-semibold text-success">{{ format_money($customer->revenue_sum) }}</span>
                            <x-ui.badge variant="info">{{ $customer->orders_count }} {{ \Illuminate\Support\Str::plural('order', $customer->orders_count) }}</x-ui.badge>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        {{-- Inactive customers --}}
        <x-ui.card title="Inactive customers" subtitle="Ordered before, but not recently — good for a re-engagement email">
            <x-slot:actions>
                <div class="flex items-center gap-1 rounded-md border border-line p-0.5">
                    @foreach ([30, 60, 90] as $days)
                        <button wire:click="$set('inactiveDays', {{ $days }})" @class([
                            'rounded px-2.5 py-1 text-xs',
                            'bg-surface-sunken font-medium text-content' => $inactiveDays === $days,
                            'text-content-muted hover:text-content' => $inactiveDays !== $days,
                        ])>{{ $days }}d</button>
                    @endforeach
                </div>
            </x-slot:actions>
            @if ($inactiveCustomers->isEmpty())
                <x-ui.empty-state icon="customers" tone="success" title="Everyone's active" description="No repeat customers have gone quiet in this window." />
            @else
                <div class="space-y-2">
                    @foreach ($inactiveCustomers as $customer)
                        <a href="{{ route('admin.customers.show', $customer) }}" class="flex items-center justify-between gap-3 rounded-md px-1 py-1 hover:bg-surface-sunken">
                            <span class="min-w-0 flex-1 truncate text-sm font-medium text-content">{{ $customer->name }}</span>
                            <x-ui.badge variant="warning">{{ $customer->last_order_at->diffForHumans() }}</x-ui.badge>
                        </a>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>
</div>
