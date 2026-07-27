<div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
    {{-- Products --}}
    <div class="space-y-4">
        <x-ui.card title="Products">
            <div class="relative">
                <x-ui.input wire:model.live.debounce.300ms="productSearch" placeholder="Search products to add…" />
                @if ($results->isNotEmpty())
                    <div class="absolute z-10 mt-1 w-full overflow-hidden rounded-lg border border-line bg-surface-overlay shadow-lg">
                        @foreach ($results as $r)
                            <button type="button" wire:click="addProduct({{ $r->id }})" class="flex w-full items-center justify-between px-3 py-2 text-left text-sm hover:bg-surface-sunken">
                                <span class="text-content">{{ $r->name }}</span>
                                <span class="text-content-muted">{{ format_money($r->price) }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            @error('lines')<p class="mt-2 text-xs text-danger">{{ $message }}</p>@enderror

            @if (! empty($lines))
                <div class="mt-4 divide-y divide-line">
                    @foreach ($lines as $i => $line)
                        <div class="flex items-center gap-3 py-2">
                            <span class="min-w-0 flex-1 truncate text-sm text-content">{{ $line['name'] }}</span>
                            <input type="number" min="1" wire:model.live="lines.{{ $i }}.qty" class="h-8 w-16 rounded-md border border-line bg-surface-raised px-2 text-sm text-content">
                            <span class="w-24 text-right text-sm text-content">{{ format_money($line['price'] * $line['qty']) }}</span>
                            <x-ui.icon-button icon="trash" variant="danger" label="Remove" wire:click="removeLine({{ $i }})" />
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 flex justify-between border-t border-line pt-3 text-sm font-semibold text-content">
                    <span>Subtotal</span><span>{{ format_money($subtotal) }}</span>
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- Customer + payment --}}
    <form wire:submit="create" class="space-y-4">
        <x-ui.card title="Customer">
            <div class="space-y-3">
                <x-ui.input wire:model="name" label="Name" :error="$errors->first('name')" />
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.input wire:model="phone" label="Phone" :error="$errors->first('phone')" />
                    <x-ui.input wire:model="email" type="email" label="Email" :error="$errors->first('email')" />
                </div>
                <x-ui.input wire:model="address" label="Address" />
                <div class="grid gap-3 sm:grid-cols-2">
                    <x-ui.input wire:model="city" label="City" />
                    <x-ui.input wire:model="region" label="Region" />
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Payment & shipping">
            <div class="space-y-3">
                <x-ui.select wire:model="paymentMethod" label="Payment method">
                    @foreach ($methods as $key => $method)
                        <option value="{{ $key }}">{{ $method->label() }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.money-input wire:model="shippingTotal" label="Shipping charge" :symbol="settings('localization.currency_symbol', '$')" />
                <x-ui.textarea wire:model="notes" label="Notes" rows="2" />
            </div>
        </x-ui.card>

        @if ($blockedMessage)
            <p class="rounded-lg border border-danger/30 bg-danger-soft px-3 py-2 text-sm text-danger">{{ $blockedMessage }}</p>
        @endif

        <x-ui.save-button target="create" label="Create order" />
    </form>
</div>
