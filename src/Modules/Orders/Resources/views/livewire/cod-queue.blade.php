<div class="space-y-4">
    @if ($orders->isEmpty())
        <x-ui.card>
            <x-ui.empty-state
                icon="orders"
                tone="success"
                title="All caught up"
                description="No Cash-on-Delivery orders are waiting for verification."
            />
        </x-ui.card>
    @else
        <p class="text-sm text-content-muted">{{ $orders->count() }} order(s) awaiting Cash-on-Delivery verification.</p>

        @foreach ($orders as $order)
            @php
                $phone = $order->shippingAddress?->phone;
                $waPhone = $phone ? preg_replace('/\D+/', '', $phone) : null;
            @endphp
            <x-ui.card>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-content hover:text-primary">{{ $order->number }}</a>
                            <x-ui.badge variant="warning">Unverified</x-ui.badge>
                        </div>
                        <p class="mt-1 text-sm text-content-secondary">
                            {{ $order->shippingAddress?->name ?? '—' }}
                            @if ($phone)
                                · <a href="tel:{{ $phone }}" class="hover:text-primary">{{ $phone }}</a>
                            @endif
                        </p>
                        <p class="mt-0.5 text-sm font-medium text-content">{{ format_money($order->grand_total) }} · placed {{ $order->placed_at?->diffForHumans() }}</p>
                        @if ($phone)
                            {{-- Same call/WhatsApp pattern as the order-detail COD banner. --}}
                            <div class="mt-2 flex gap-2">
                                <x-ui.button size="sm" variant="secondary" :href="'tel:'.$phone">
                                    <x-ui.icon name="phone" class="h-4 w-4" />
                                    Call
                                </x-ui.button>
                                <x-ui.button size="sm" variant="secondary" :href="'https://wa.me/'.$waPhone" target="_blank">
                                    WhatsApp
                                </x-ui.button>
                            </div>
                        @endif
                    </div>

                    <div class="flex w-full flex-col gap-2 sm:w-72">
                        <x-ui.input wire:model="notes.{{ $order->id }}" placeholder="Note (optional)…" />
                        <div class="flex gap-2">
                            <x-ui.button size="sm" variant="primary" x-on:click="$dispatch('confirm', { title: 'Verify COD order?', message: 'This verifies and confirms the cash-on-delivery order.', confirmLabel: 'Verify', variant: 'primary', onConfirm: () => $wire.verify({{ $order->id }}) })">
                                Verify
                            </x-ui.button>
                            <x-ui.button size="sm" variant="danger" x-on:click="$dispatch('confirm', { title: 'Reject COD order?', message: 'This rejects and cancels the order.', confirmLabel: 'Reject', onConfirm: () => $wire.reject({{ $order->id }}) })">
                                Reject
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </x-ui.card>
        @endforeach
    @endif
</div>
