<div>
    @php
        $statusVariant = match ($order->status) {
            'pending' => 'warning',
            'confirmed', 'processing', 'shipped' => 'info',
            'delivered' => 'success',
            'cancelled', 'returned' => 'danger',
            default => 'neutral',
        };
        $paymentVariant = match ($order->payment_status) {
            'paid' => 'success',
            'partially_refunded' => 'warning',
            'refunded' => 'neutral',
            default => 'warning', // unpaid — needs attention
        };
        $ship = $order->shippingAddress;
        $waPhone = $ship ? preg_replace('/\D+/', '', $ship->phone) : null;

        // Guided-journey stepper: only the "happy path" statuses have a place in
        // a linear progress bar. Cancelled/returned are terminal branches shown
        // as a banner instead — forcing them onto the same 5-step rail would be
        // misleading (e.g. a cancelled order isn't "40% delivered").
        $journey = ['pending' => 'Placed', 'confirmed' => 'Confirmed', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
        $isTerminalBranch = in_array($order->status, ['cancelled', 'returned'], true);
        $journeySteps = collect($journey)->map(fn ($title) => ['title' => $title])->values()->all();
        $journeyCurrent = array_search($order->status, array_keys($journey), true);
        $journeyCurrent = $journeyCurrent === false ? 1 : $journeyCurrent + 1;
        $nextStepHint = match ($order->status) {
            'pending' => $order->payment_status === 'unpaid' && $order->payment_method !== 'cod' ? 'Waiting on payment.' : 'Confirm the order to commit stock.',
            'confirmed' => 'Start processing this order.',
            'processing' => 'Ship it once it\'s packed.',
            'shipped' => 'Mark delivered once the customer receives it.',
            default => null,
        };

        // Plain-text summary — copyable anywhere (WhatsApp, email, SMS) with the
        // full picture: invoice info, items, payment and shipping in one paste.
        $summaryLines = [];
        $summaryLines[] = "Order {$order->number}";
        $summaryLines[] = 'Placed: '.($order->placed_at?->format('M j, Y g:i A') ?? '—');
        $summaryLines[] = 'Status: '.ucfirst($order->status).' · Payment: '.ucfirst(str_replace('_', ' ', $order->payment_status)).' ('.strtoupper($order->payment_method ?? 'n/a').')';
        $summaryLines[] = '';
        if ($ship) {
            $summaryLines[] = 'Customer:';
            $summaryLines[] = $ship->name;
            if ($ship->phone) {
                $summaryLines[] = $ship->phone;
            }
            if ($ship->email) {
                $summaryLines[] = $ship->email;
            }
            $summaryLines[] = '';
            $summaryLines[] = 'Shipping address:';
            $summaryLines[] = $ship->address;
            $summaryLines[] = collect([$ship->city, $ship->region, $ship->postcode])->filter()->join(', ');
            $summaryLines[] = '';
        }
        $summaryLines[] = 'Items:';
        foreach ($order->items as $item) {
            $label = $item->name.($item->variant_label ? " ({$item->variant_label})" : '');
            $summaryLines[] = "- {$label} × {$item->qty} — ".format_money($item->line_total);
        }
        $summaryLines[] = '';
        $summaryLines[] = 'Subtotal: '.format_money($order->subtotal);
        if ($order->discount_total > 0) {
            $summaryLines[] = 'Discount: -'.format_money($order->discount_total);
        }
        $summaryLines[] = 'Shipping: '.format_money($order->shipping_total);
        $summaryLines[] = 'Total: '.format_money($order->grand_total);
        if ($order->tracking_number) {
            $summaryLines[] = '';
            $summaryLines[] = 'Tracking: '.trim($order->carrier.' '.$order->tracking_number);
        }
        $summaryText = implode("\n", $summaryLines);
        // @js() only compiles when it sits in raw template text; inside an
        // <x-component> tag attribute the tag compiler runs first and freezes
        // the attribute as a literal string, so @js() there is never expanded.
        // Pre-render the JS-safe literal here and interpolate it with {{ }}.
        $summaryJs = \Illuminate\Support\Js::from($summaryText);
    @endphp

    {{-- Header --}}
    <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
        <div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h2 class="text-xl font-semibold text-content">Order {{ $order->number }}</h2>
                <x-ui.badge :variant="$statusVariant">{{ ucfirst($order->status) }}</x-ui.badge>
                <x-ui.badge :variant="$paymentVariant">{{ ucfirst(str_replace('_', ' ', $order->payment_status)) }}</x-ui.badge>
            </div>
            <p class="mt-1 text-sm text-content-muted">
                Placed {{ $order->placed_at?->format('M j, Y \a\t g:i A') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <x-ui.button variant="secondary" size="sm" x-data="{ copied: false }"
                x-on:click="navigator.clipboard?.writeText({{ $summaryJs }}).then(() => { copied = true; window.toast('Order summary copied', 'success'); setTimeout(() => copied = false, 1500) }, () => window.toast('Could not copy summary', 'warning'))">
                <x-ui.icon x-show="!copied" name="copy" class="h-4 w-4" />
                <x-ui.icon x-show="copied" x-cloak name="check" class="h-4 w-4 text-success" />
                <span :class="copied && 'text-success'" x-text="copied ? 'Copied!' : 'Copy order summary'"></span>
            </x-ui.button>
            <x-ui.button variant="secondary" size="sm" :href="route('admin.orders.packing-slip', $order)" target="_blank">
                Packing slip
            </x-ui.button>
            <x-ui.button variant="secondary" size="sm" :href="route('admin.orders.invoice', $order)" target="_blank">
                <x-ui.icon name="printer" class="h-4 w-4" />
                Print invoice
            </x-ui.button>
        </div>
    </div>

    {{-- Guided journey --}}
    <div class="mb-6">
        @if ($isTerminalBranch)
            <div @class([
                'flex items-center gap-3 rounded-lg border p-4',
                'border-danger/30 bg-danger-soft' => $order->status === 'cancelled',
                'border-warning/30 bg-warning-soft' => $order->status === 'returned',
            ])>
                <x-ui.icon name="warning" @class(['h-5 w-5 shrink-0', 'text-danger' => $order->status === 'cancelled', 'text-warning' => $order->status === 'returned']) />
                <p @class(['text-sm font-medium', 'text-danger' => $order->status === 'cancelled', 'text-warning' => $order->status === 'returned'])>
                    @if ($order->status === 'cancelled')
                        This order was cancelled and its items were restocked.
                    @else
                        This order was returned{{ $order->refunded_total > 0 ? ' and refunded' : '' }}.
                    @endif
                </p>
            </div>
        @else
            <x-admin.steps :steps="$journeySteps" :current="$journeyCurrent" />
            @if ($nextStepHint)
                <p class="mt-2 flex items-center gap-1.5 text-xs text-content-muted">
                    <x-ui.icon name="light-bulb" class="h-3.5 w-3.5 shrink-0" />
                    {{ $nextStepHint }}
                </p>
            @endif
        @endif
    </div>

    {{-- Highlight row: payment, customer, shipping — the three things worth a
         glance before reading anything else. --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div @class([
            'rounded-lg border-l-4 bg-surface-raised p-4 shadow-sm',
            'border-l-success' => $paymentVariant === 'success',
            'border-l-warning' => $paymentVariant === 'warning',
            'border-l-line' => $paymentVariant === 'neutral',
        ])>
            <div class="flex items-center gap-2 text-content-muted">
                <x-ui.icon name="credit-card" class="h-4 w-4" />
                <span class="text-xs font-semibold uppercase tracking-wide">Payment</span>
            </div>
            <p class="mt-2 text-lg font-semibold text-content">{{ format_money($order->grand_total) }}</p>
            <p class="mt-0.5 text-sm text-content-secondary">
                {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }} · {{ strtoupper($order->payment_method ?? '—') }}
            </p>
            @if ($order->refunded_total > 0)
                <p class="mt-1 text-xs text-content-muted">{{ format_money((int) $order->refunded_total) }} refunded</p>
            @endif
            @if ($order->payment_status !== 'paid')
                <x-ui.button size="sm" variant="secondary" class="mt-3" wire:target="markPaid" wire:loading.attr="disabled"
                    x-on:click="$dispatch('confirm', { title: 'Mark this order as paid?', message: 'Records payment for this order and confirms it (committing stock). Use this once the transfer or offline payment has cleared.', confirmLabel: 'Mark as paid', variant: 'primary', onConfirm: () => $wire.markPaid() })">
                    Mark as paid
                </x-ui.button>
            @endif
        </div>

        <div class="rounded-lg border-l-4 border-l-info bg-surface-raised p-4 shadow-sm">
            <div class="flex items-center gap-2 text-content-muted">
                <x-ui.icon name="customers" class="h-4 w-4" />
                <span class="text-xs font-semibold uppercase tracking-wide">Customer</span>
                @if ($order->customer_id)
                    <x-ui.badge variant="info">Registered</x-ui.badge>
                @else
                    <x-ui.badge>Guest</x-ui.badge>
                @endif
            </div>
            <p class="mt-2 text-lg font-semibold text-content">{{ $ship?->name ?? '—' }}</p>
            <div class="mt-0.5 space-y-0.5 text-sm text-content-secondary">
                @if ($ship?->phone)
                    <p class="flex items-center gap-1.5"><x-ui.icon name="phone" class="h-3.5 w-3.5 shrink-0 text-content-muted" /><a href="tel:{{ $ship->phone }}" class="hover:text-primary hover:underline">{{ $ship->phone }}</a></p>
                @endif
                @if ($ship?->email)
                    <p class="flex items-center gap-1.5"><x-ui.icon name="mail" class="h-3.5 w-3.5 shrink-0 text-content-muted" /><a href="mailto:{{ $ship->email }}" class="truncate hover:text-primary hover:underline">{{ $ship->email }}</a></p>
                @endif
            </div>
            @if ($order->customer_id)
                <x-ui.button size="sm" variant="secondary" class="mt-3" :href="route('admin.customers.show', $order->customer_id)">
                    View profile
                </x-ui.button>
            @endif
        </div>

        <div class="rounded-lg border-l-4 border-l-brand-2 bg-surface-raised p-4 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <div class="flex items-center gap-2 text-content-muted">
                    <x-ui.icon name="map-pin" class="h-4 w-4" />
                    <span class="text-xs font-semibold uppercase tracking-wide">Shipping</span>
                </div>
                @if ($ship)
                    <button type="button" title="Copy address"
                        x-on:click="navigator.clipboard?.writeText(@js(collect([$ship->address, collect([$ship->city, $ship->region, $ship->postcode])->filter()->join(', ')])->filter()->join(', '))).then(() => window.toast('Address copied', 'success'), () => window.toast('Could not copy address', 'warning'))"
                        class="text-content-muted hover:text-primary" aria-label="Copy shipping address">
                        <x-ui.icon name="copy" class="h-4 w-4" />
                    </button>
                @endif
            </div>
            @if ($ship)
                <p class="mt-2 text-sm font-medium text-content">{{ $ship->address }}</p>
                <p class="mt-0.5 text-sm text-content-secondary">{{ collect([$ship->city, $ship->region, $ship->postcode])->filter()->join(', ') }}</p>
            @else
                <p class="mt-2 text-sm text-content-muted">No shipping address on file.</p>
            @endif
            @if ($order->tracking_number || $order->carrier)
                <p class="mt-2 text-xs text-content-muted">
                    Tracking: <span class="font-medium text-content">{{ trim($order->carrier.' '.$order->tracking_number) }}</span>
                    @if ($order->shipped_at)
                        · shipped {{ $order->shipped_at->format('M j, Y') }}
                    @endif
                </p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Left column --}}
        <div class="space-y-6 lg:col-span-2">
            <x-ui.card title="Items">
                <div class="divide-y divide-line">
                    @foreach ($order->items as $item)
                        <div class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-line bg-surface-sunken">
                                @if ($image = $item->product?->featuredImage())
                                    <img src="{{ $image->path }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="grid h-full w-full place-items-center text-content-muted">
                                        <x-ui.icon name="photo" class="h-5 w-5" />
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-content">{{ $item->name }}</div>
                                @if ($item->variant_label)
                                    <div class="text-xs text-content-muted">{{ $item->variant_label }}</div>
                                @endif
                                <div class="mt-0.5 text-xs text-content-secondary">
                                    {{ $item->qty }} × {{ format_money($item->price) }}
                                </div>
                            </div>
                            <div class="whitespace-nowrap font-medium text-content">{{ format_money($item->line_total) }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 space-y-1.5 border-t border-line pt-4 text-sm">
                    <div class="flex justify-between text-content-secondary">
                        <span>Subtotal</span>
                        <span>{{ format_money($order->subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-content-secondary">
                        <span>Discount</span>
                        <span>−{{ format_money($order->discount_total) }}</span>
                    </div>
                    <div class="flex justify-between text-content-secondary">
                        <span>Shipping</span>
                        <span>{{ format_money($order->shipping_total) }}</span>
                    </div>
                    <div class="flex justify-between border-t border-line pt-1.5 text-base font-semibold text-content">
                        <span>Total</span>
                        <span>{{ format_money($order->grand_total) }}</span>
                    </div>
                </div>
            </x-ui.card>

            @if (filled($order->notes))
                <x-ui.card title="Customer note" subtitle="Left by the customer at checkout.">
                    <p class="whitespace-pre-line text-sm text-content-secondary">{{ $order->notes }}</p>
                </x-ui.card>
            @endif

            {{-- Once the order is paid, COD verification is moot — hide it rather
                 than leave a stale "unverified" nag on a settled order. --}}
            @if ($order->payment_method === 'cod' && $order->cod_verification_status === 'unverified' && $order->payment_status !== 'paid')
                <div class="rounded-lg border-l-4 border-l-warning bg-surface-raised p-5 shadow-sm">
                    <div class="flex items-center gap-2">
                        <x-ui.icon name="phone" class="h-4 w-4 text-warning" />
                        <h3 class="text-sm font-semibold text-content">COD verification needed</h3>
                    </div>
                    <p class="mt-1 text-xs text-content-muted">Call or message the customer to confirm this cash-on-delivery order before it ships.</p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <x-ui.button variant="primary" wire:click="verify">
                            <x-ui.icon name="check-circle" class="h-4 w-4" />
                            Verify order
                        </x-ui.button>
                        @if ($ship?->phone)
                            <x-ui.button variant="secondary" :href="'tel:' . $ship->phone">
                                <x-ui.icon name="phone" class="h-4 w-4" />
                                Call
                            </x-ui.button>
                            <x-ui.button variant="secondary" :href="'https://wa.me/' . $waPhone" target="_blank">
                                WhatsApp
                            </x-ui.button>
                        @endif
                        <button type="button" class="ml-auto text-sm font-medium text-danger hover:underline"
                            x-on:click="$dispatch('confirm', { title: 'Reject order?', message: 'This rejects and cancels the order.', confirmLabel: 'Reject order', onConfirm: () => $wire.reject() })">
                            Reject order
                        </button>
                    </div>
                </div>
            @endif
        </div>

        {{-- Right column --}}
        <div class="space-y-6">
            <x-ui.card title="Order status">
                @if ($order->tracking_number || $order->carrier)
                    <div class="mb-3 text-sm">
                        <span class="text-content-muted">Tracking:</span>
                        <span class="font-medium text-content">{{ trim($order->carrier.' '.$order->tracking_number) }}</span>
                        @if ($order->shipped_at)
                            <span class="text-content-muted">· shipped {{ $order->shipped_at->format('M j, Y') }}</span>
                        @endif
                    </div>
                @endif

                @if (count($allowed) > 0)
                    {{-- Highlighted action zone: this is the one thing most visits to
                         this page are here to do, so it gets its own tinted panel
                         instead of blending into the rest of the card. --}}
                    <div class="space-y-3 rounded-lg border border-primary/20 bg-primary-soft/40 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-primary">Update status</p>
                        <x-ui.input wire:model="note" placeholder="Add a note (optional)…" />

                        @php
                            // The first non-destructive option reads as "the obvious next
                            // step" and gets the primary button; alternatives stay secondary.
                            $primaryStatus = collect($allowed)->first(fn ($s) => ! in_array($s, ['cancelled', 'returned', 'shipped'], true));
                        @endphp
                        <div class="flex flex-wrap gap-2">
                            @foreach ($allowed as $status)
                                @continue($status === 'shipped')
                                @if (in_array($status, ['cancelled', 'returned'], true))
                                    {{-- Irreversible: restocks items and emails the customer — confirm first. --}}
                                    <x-ui.button variant="secondary"
                                        x-on:click="$dispatch('confirm', { title: 'Mark order {{ $status }}?', message: 'This {{ $status }}s the order, restocks its items and notifies the customer. This cannot be undone.', confirmLabel: 'Mark {{ $status }}', variant: 'danger', onConfirm: () => $wire.changeStatus('{{ $status }}') })">
                                        Mark {{ $status }}
                                    </x-ui.button>
                                @else
                                    <x-ui.button :variant="$status === $primaryStatus ? 'primary' : 'secondary'" wire:click="changeStatus('{{ $status }}')">
                                        Mark {{ $status }}
                                    </x-ui.button>
                                @endif
                            @endforeach
                        </div>

                        @if (in_array('shipped', $allowed, true))
                            <div class="space-y-2 rounded-lg border border-line bg-surface-raised p-3">
                                <p class="text-xs font-medium text-content-secondary">Ship this order</p>
                                <x-ui.input wire:model="carrier" placeholder="Carrier (e.g. Pathao, DHL)" />
                                <x-ui.input wire:model="trackingNumber" placeholder="Tracking number" />
                                <x-ui.button variant="primary" wire:click="ship" wire:target="ship" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="ship">Mark shipped</span>
                                    <span wire:loading wire:target="ship">Shipping…</span>
                                </x-ui.button>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-content-muted">No further transitions available.</p>
                @endif
            </x-ui.card>

            {{-- Returns & refunds --}}
            <x-ui.card title="Returns & refunds">
                @if ($order->refunded_total > 0)
                    <div class="mb-4 flex items-center justify-between rounded-lg bg-surface-sunken/50 px-3 py-2 text-sm">
                        <span class="text-content-secondary">Refunded</span>
                        <span class="font-semibold text-content">{{ format_money((int) $order->refunded_total) }} of {{ format_money((int) $order->grand_total) }}</span>
                    </div>
                @endif

                {{-- Return requests --}}
                @forelse ($order->returns as $return)
                    <div class="mb-3 rounded-lg border border-line p-3">
                        <div class="flex items-center justify-between gap-2">
                            <x-ui.badge :variant="match ($return->status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' }">
                                {{ ucfirst($return->status) }}
                            </x-ui.badge>
                            <span class="text-xs text-content-muted">{{ $return->created_at?->diffForHumans() }}</span>
                        </div>
                        @if ($return->reason)
                            <p class="mt-2 text-sm text-content-secondary">“{{ $return->reason }}”</p>
                        @endif

                        @if ($return->items->isNotEmpty())
                            <ul class="mt-2 space-y-1 text-xs text-content-secondary">
                                @foreach ($return->items as $returnItem)
                                    <li class="flex justify-between gap-2">
                                        <span class="min-w-0 truncate">{{ $returnItem->orderItem?->name ?? 'Item' }}@if ($returnItem->orderItem?->variant_label) <span class="text-content-muted">({{ $returnItem->orderItem->variant_label }})</span>@endif</span>
                                        <span class="whitespace-nowrap">× {{ $returnItem->qty }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if (filled($return->photos))
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($return->photos as $photo)
                                    <a href="{{ $photo }}" target="_blank" rel="noopener"
                                        class="block h-16 w-16 overflow-hidden rounded-lg border border-line">
                                        <img src="{{ $photo }}" alt="Return evidence" class="h-full w-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @can('manage-refunds')
                            @if ($return->isOpen())
                                <div class="mt-3 flex gap-2">
                                    <x-ui.button size="sm" variant="primary" x-on:click="$dispatch('confirm', { title: 'Approve return?', message: 'This approves the return and refunds {{ format_money($return->computedRefund()) }}, restocking the returned items.', confirmLabel: 'Approve and refund', variant: 'primary', onConfirm: () => $wire.approveReturn({{ $return->id }}) })">
                                        Approve &amp; refund
                                    </x-ui.button>
                                    <x-ui.button size="sm" variant="ghost" wire:click="rejectReturn({{ $return->id }})">Reject</x-ui.button>
                                </div>
                            @endif
                        @endcan
                    </div>
                @empty
                    <p class="text-sm text-content-muted">No return requests.</p>
                @endforelse

                {{-- Manual refund --}}
                @can('manage-refunds')
                    @if ($order->refundableAmount() > 0)
                        <div class="mt-4 space-y-2 border-t border-line pt-4">
                            <p class="text-xs font-medium text-content-secondary">Record a refund (up to {{ format_money($order->refundableAmount()) }})</p>
                            <x-ui.money-input wire:model="refundAmount" :symbol="settings('localization.currency_symbol', '$')" :error="$errors->first('refundAmount')" />
                            <x-ui.input wire:model="refundReason" placeholder="Reason (optional)…" />
                            <x-ui.toggle wire:model="refundRestock" label="Restock items" />
                            <x-ui.button size="sm" wire:click="issueRefund">
                                <span wire:loading.remove wire:target="issueRefund">Record refund</span>
                                <span wire:loading wire:target="issueRefund">Recording…</span>
                            </x-ui.button>
                        </div>
                    @elseif ($order->refunded_total > 0)
                        <p class="mt-4 border-t border-line pt-4 text-sm text-content-muted">Fully refunded.</p>
                    @endif
                @endcan
            </x-ui.card>

            <x-ui.card title="Timeline">
                @if ($order->history->isEmpty())
                    <p class="text-sm text-content-muted">No history yet.</p>
                @else
                    <ol class="space-y-4">
                        @foreach ($order->history as $entry)
                            <li class="flex gap-3">
                                <div class="mt-1 h-2 w-2 shrink-0 rounded-full bg-primary"></div>
                                <div class="min-w-0">
                                    <div class="text-sm font-medium text-content">{{ ucfirst($entry->to_status) }}</div>
                                    @if ($entry->note)
                                        <div class="text-xs text-content-secondary">{{ $entry->note }}</div>
                                    @endif
                                    <div class="mt-0.5 text-xs text-content-muted">
                                        {{ $entry->actor }} · {{ $entry->created_at?->diffForHumans() }}
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </x-ui.card>
        </div>
    </div>
</div>
