<div>
    {{-- Existing request: show its status timeline. --}}
    @if ($existing)
        <div class="mt-2 rounded" style="background: var(--st-surface); padding: 12px; border-radius: var(--st-radius-sm)">
            <p class="text-xs font-semibold" style="color: var(--st-ink)">{{ __('account.return_word') }} {{ ucfirst($existing->status) }}</p>
            <ol class="mt-2 space-y-1.5">
                @foreach ($existing->timeline() as $step)
                    <li class="flex items-center gap-2 text-xs">
                        <span class="grid h-4 w-4 place-items-center rounded-full text-[10px]"
                            style="{{ $step['at'] ? 'background: var(--st-primary); color: var(--st-primary-ink)' : 'background: var(--st-line); color: var(--st-ink-soft)' }}">
                            {{ $step['at'] ? '✓' : '' }}
                        </span>
                        <span style="color: {{ $step['at'] ? 'var(--st-ink)' : 'var(--st-ink-soft)' }}">
                            {{ $step['label'] }}
                            @if ($step['at'])
                                <span style="color: var(--st-ink-soft)">· {{ $step['at']->format('M j') }}</span>
                            @endif
                        </span>
                    </li>
                @endforeach
            </ol>
            @if ($existing->items->isNotEmpty())
                <p class="mt-2 text-xs" style="color: var(--st-ink-soft)">{{ __('account.items_requested', ['count' => $existing->items->sum('qty')]) }}</p>
            @endif
        </div>
    @elseif ($eligible && ! $hasOpen)
        @if ($open)
            <form wire:submit="submit" class="mt-3 space-y-3">
                {{-- Item picker --}}
                @if ($items->isNotEmpty())
                    <div class="space-y-2">
                        <p class="text-xs font-medium" style="color: var(--st-ink)">{{ __('account.which_items') }}</p>
                        @foreach ($items as $item)
                            <div class="flex items-center justify-between gap-3" wire:key="ri-{{ $item->id }}">
                                <span class="min-w-0 flex-1 truncate text-xs" style="color: var(--st-ink)">
                                    {{ $item->name }}
                                    <span style="color: var(--st-ink-soft)">× {{ $item->qty }}</span>
                                </span>
                                <label class="sr-only" for="ret-qty-{{ $item->id }}">{{ __('account.return_qty_for', ['name' => $item->name]) }}</label>
                                <input id="ret-qty-{{ $item->id }}" type="number" min="0" max="{{ $item->qty }}"
                                    wire:model="selections.{{ $item->id }}" placeholder="0"
                                    class="w-16 border px-2 py-1 text-xs outline-none"
                                    style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                            </div>
                        @endforeach
                    </div>
                @endif

                <div>
                    <label class="sr-only" for="ret-reason-{{ $orderId }}">{{ __('account.reason_for_return') }}</label>
                    <textarea id="ret-reason-{{ $orderId }}" wire:model="reason" rows="2" placeholder="{{ __('account.reason_for_return') }}"
                        class="w-full border px-3 py-2 text-sm outline-none"
                        style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)"></textarea>
                    @error('reason')<p class="text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>

                {{-- Photos --}}
                <div>
                    <label class="text-xs font-medium" for="ret-photos-{{ $orderId }}" style="color: var(--st-ink)">{{ __('storefront.photos_optional') }}</label>
                    <input id="ret-photos-{{ $orderId }}" type="file" wire:model="photos" multiple accept="image/*"
                        class="mt-1 block w-full text-xs" style="color: var(--st-ink-soft)">
                    @error('photos.*')<p class="text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                    <div wire:loading wire:target="photos" class="mt-1 text-xs" style="color: var(--st-ink-soft)">{{ __('account.uploading') }}</div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-3 py-2 text-xs font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('account.submit_request') }}</button>
                    <button type="button" wire:click="$set('open', false)" class="px-3 py-2 text-xs" style="color: var(--st-ink-soft)">{{ __('storefront.cancel') }}</button>
                </div>
            </form>
        @else
            <button type="button" wire:click="$set('open', true)" class="text-xs font-medium" style="color: var(--st-accent)">{{ __('storefront.return_request') }}</button>
        @endif
    @elseif ($hasOpen)
        <span class="text-xs font-medium" style="color: var(--st-ink-soft)">{{ __('account.return_requested') }}</span>
    @endif
</div>
