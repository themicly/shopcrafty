@php
    $inputCls = 'w-full border px-4 py-3 text-sm outline-none';
    $inputStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
@endphp

<div>
    <div class="mb-4">
        <button wire:click="create" class="px-5 py-2.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.add_address') }}</button>
    </div>

    @if ($addresses->isEmpty())
        <div class="border p-10 text-center" style="border-color: var(--st-line); border-radius: var(--st-radius)">
            <p style="color: var(--st-ink-soft)">{{ __('account.no_saved_addresses') }}</p>
        </div>
    @else
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ($addresses as $address)
                <div class="border p-4" style="border-color: var(--st-line); border-radius: var(--st-radius)" wire:key="addr-{{ $address->id }}">
                    <div class="mb-1 flex items-center gap-2">
                        <span class="text-sm font-semibold" style="color: var(--st-ink)">{{ $address->label ?: $address->name }}</span>
                        @if ($address->is_default)<span class="rounded-full px-2 py-0.5 text-[11px]" style="background: var(--st-surface); color: var(--st-ink-soft)">{{ __('storefront.default_address') }}</span>@endif
                    </div>
                    <p class="text-sm" style="color: var(--st-ink-soft)">{{ $address->name }}<br>{{ $address->address }}, {{ $address->city }} {{ $address->region }}<br>{{ $address->phone }}</p>
                    <div class="mt-3 flex gap-3 text-sm">
                        <button wire:click="edit({{ $address->id }})" style="color: var(--st-ink)">{{ __('storefront.edit') }}</button>
                        <button wire:click="delete({{ $address->id }})" wire:confirm="{{ __('account.remove_address_confirm') }}" style="color: var(--st-accent)">{{ __('storefront.remove') }}</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($showForm)
        <div class="fixed inset-0 z-50">
            <div class="fixed inset-0 bg-black/40" wire:click="$set('showForm', false)"></div>
            <div class="fixed inset-y-0 right-0 flex w-full max-w-md flex-col" style="background: var(--st-bg)">
                <div class="flex items-center justify-between border-b px-5 py-4" style="border-color: var(--st-line)">
                    <h3 class="st-display text-lg font-semibold" style="color: var(--st-ink)">{{ $editingId ? __('account.edit_address') : __('account.new_address') }}</h3>
                    <button wire:click="$set('showForm', false)" class="text-2xl" style="color: var(--st-ink-soft)">&times;</button>
                </div>
                <form wire:submit="save" class="flex-1 space-y-3 overflow-y-auto p-5">
                    <input wire:model="label" placeholder="{{ __('account.address_label_placeholder') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                    <input wire:model="name" placeholder="{{ __('storefront.full_name') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                    @error('name')<p class="text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                    <input wire:model="phone" placeholder="{{ __('storefront.phone') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                    <input wire:model="address" placeholder="{{ __('checkout.street_address') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                    @error('address')<p class="text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                    <div class="grid grid-cols-2 gap-3">
                        <input wire:model="city" placeholder="{{ __('storefront.city') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                        <input wire:model="region" placeholder="{{ __('account.region') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                    </div>
                    <label class="flex items-center gap-2 text-sm" style="color: var(--st-ink-soft)">
                        <input type="checkbox" wire:model="is_default"> {{ __('account.set_as_default') }}
                    </label>
                    <button type="submit" class="w-full py-3 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('account.save_address') }}</button>
                </form>
            </div>
        </div>
    @endif
</div>
