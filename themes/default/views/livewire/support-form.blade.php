<div>
    @if ($sent)
        <div class="rounded p-4 text-sm" style="background: var(--st-surface); color: var(--st-ink); border-radius: var(--st-radius-sm)">
            {{ __('storefront.support_sent') }}
            <button type="button" wire:click="$set('sent', false)" class="ml-2 font-medium" style="color: var(--st-accent)">{{ __('storefront.send_another') }}</button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="support-name" class="mb-1 block text-xs font-medium" style="color: var(--st-ink)">{{ __('storefront.your_name') }}</label>
                    <input id="support-name" type="text" wire:model="name"
                        class="w-full border px-3 py-2 text-sm outline-none"
                        style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                    @error('name')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="support-email" class="mb-1 block text-xs font-medium" style="color: var(--st-ink)">{{ __('storefront.email') }}</label>
                    <input id="support-email" type="email" wire:model="email"
                        class="w-full border px-3 py-2 text-sm outline-none"
                        style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                    @error('email')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="support-subject" class="mb-1 block text-xs font-medium" style="color: var(--st-ink)">{{ __('storefront.subject') }}</label>
                <input id="support-subject" type="text" wire:model="subject"
                    class="w-full border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                @error('subject')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="support-message" class="mb-1 block text-xs font-medium" style="color: var(--st-ink)">{{ __('storefront.message') }}</label>
                <textarea id="support-message" wire:model="message" rows="5"
                    class="w-full border px-3 py-2 text-sm outline-none"
                    style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)"></textarea>
                @error('message')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="px-5 py-2.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">
                {{ __('storefront.send_message') }}
            </button>
        </form>
    @endif
</div>
