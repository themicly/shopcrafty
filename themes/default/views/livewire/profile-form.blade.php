@php
    $inputCls = 'w-full border px-4 py-3 text-sm outline-none';
    $inputStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
@endphp

<form wire:submit="save" class="space-y-4">
    <div>
        <label class="mb-1 block text-sm font-medium" style="color: var(--st-ink)">{{ __('account.name') }}</label>
        <input wire:model="name" class="{{ $inputCls }}" style="{{ $inputStyle }}">
        @error('name')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" style="color: var(--st-ink)">{{ __('storefront.email') }}</label>
        <input wire:model="email" type="email" class="{{ $inputCls }}" style="{{ $inputStyle }}">
        @error('email')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" style="color: var(--st-ink)">{{ __('account.mobile') }}</label>
        <input wire:model="mobile" class="{{ $inputCls }}" style="{{ $inputStyle }}">
        @error('mobile')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="mb-1 block text-sm font-medium" style="color: var(--st-ink)">{{ __('storefront.new_password') }} <span style="color: var(--st-ink-soft)">{{ __('account.optional') }}</span></label>
        <input wire:model="password" type="password" placeholder="{{ __('account.leave_blank_password') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
        @error('password')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="px-6 py-3 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">
        <span wire:loading.remove wire:target="save">{{ __('storefront.save_changes') }}</span>
        <span wire:loading wire:target="save">{{ __('account.saving') }}</span>
    </button>
</form>
