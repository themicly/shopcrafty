<div>
    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold text-content">{{ $customer->name }}</h2>
                @if ($customer->status === 'active')
                    <x-ui.badge variant="success">Active</x-ui.badge>
                @else
                    <x-ui.badge variant="danger">Blocked</x-ui.badge>
                @endif
            </div>
            <p class="mt-1 text-sm text-content-muted">Customer since {{ $customer->created_at?->format('M j, Y') }}</p>
        </div>
        <x-ui.button variant="secondary" x-on:click="$dispatch('confirm', { title: 'Change customer status?', message: 'This blocks or unblocks the customer.', confirmLabel: 'Continue', variant: 'primary', onConfirm: () => $wire.block() })">
            {{ $customer->status === 'active' ? 'Block customer' : 'Unblock customer' }}
        </x-ui.button>
    </div>

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Orders</p>
            <p class="mt-1 text-2xl font-semibold text-content">{{ $orderCount }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Lifetime value</p>
            <p class="mt-1 text-2xl font-semibold text-content">{{ format_money($lifetimeValue) }}</p>
        </x-ui.card>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Profile --}}
        <x-ui.card title="Profile" class="lg:col-span-1">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-content-muted">Mobile</dt>
                    <dd class="mt-1 text-content">{{ $customer->mobile ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-content-muted">Email</dt>
                    <dd class="mt-1 text-content">{{ $customer->email ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-content-muted">Tags</dt>
                    <dd class="mt-1.5">
                        <div class="flex flex-wrap gap-1.5">
                            @forelse ($tags as $t)
                                <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2 py-0.5 text-xs font-medium text-primary">
                                    {{ $t }}
                                    <button type="button" wire:click="removeTag('{{ $t }}')" class="text-primary/60 hover:text-primary" aria-label="Remove {{ $t }}">&times;</button>
                                </span>
                            @empty
                                <span class="text-xs text-content-muted">No tags yet</span>
                            @endforelse
                        </div>
                        <form wire:submit.prevent="addTag" class="mt-2 flex gap-2">
                            <input wire:model="newTag" placeholder="Add tag…" class="h-8 flex-1 rounded-md border border-line bg-surface-raised px-2.5 text-sm text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            <x-ui.button type="submit" size="sm" variant="secondary">Add</x-ui.button>
                        </form>
                    </dd>
                </div>
            </dl>
        </x-ui.card>

        {{-- Addresses --}}
        <x-ui.card title="Addresses" class="lg:col-span-2">
            @if ($customer->addresses->isEmpty())
                <p class="text-sm text-content-muted">No saved addresses.</p>
            @else
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach ($customer->addresses as $address)
                        <div wire:key="address-{{ $address->id }}" class="rounded-lg border border-line p-4">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-content">{{ $address->label ?: 'Address' }}</span>
                                @if ($address->is_default)
                                    <x-ui.badge variant="primary">Default</x-ui.badge>
                                @endif
                            </div>
                            <p class="mt-2 text-sm text-content-secondary">{{ $address->name }}</p>
                            <p class="text-sm text-content-secondary">{{ $address->address }}</p>
                            <p class="text-sm text-content-secondary">{{ $address->city }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- Order history --}}
    <div class="mt-6">
        <x-ui.card title="Order history">
            @if ($orders->isEmpty())
                <p class="text-sm text-content-muted">This customer has not placed any orders yet.</p>
            @else
                <x-ui.table flush>
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr wire:key="order-{{ $order->id }}">
                                <td class="font-medium text-content">
                                    @if (\Illuminate\Support\Facades\Route::has('admin.orders.show'))
                                        <a href="{{ route('admin.orders.show', $order) }}" class="text-primary hover:underline">{{ $order->number }}</a>
                                    @else
                                        {{ $order->number }}
                                    @endif
                                </td>
                                <td><x-ui.badge>{{ ucfirst($order->status) }}</x-ui.badge></td>
                                <td class="text-right font-medium">{{ format_money($order->grand_total) }}</td>
                                <td class="text-right text-content-secondary">{{ $order->placed_at?->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card>
    </div>
</div>
