@php
    $inputCls = 'w-full border px-4 py-3 text-sm outline-none';
    $inputStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
    $steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
@endphp

<div class="mx-auto max-w-lg">
    <form wire:submit="search" class="flex flex-col gap-3 sm:flex-row">
        <input wire:model="number" placeholder="{{ __('checkout.order_number_placeholder') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
        <input wire:model="phone" placeholder="{{ __('checkout.phone_number') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
        <button type="submit" class="whitespace-nowrap px-6 py-3 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('checkout.track') }}</button>
    </form>

    @if ($searched)
        @if ($order)
            <div class="mt-8 border p-6" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                <div class="flex items-center justify-between">
                    <p class="st-display text-lg font-semibold" style="color: var(--st-ink)">{{ $order->number }}</p>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize" style="background: var(--st-surface); color: var(--st-ink)">{{ $order->status }}</span>
                </div>

                @unless (in_array($order->status, ['cancelled', 'returned']))
                    <div class="mt-6 flex items-center">
                        @foreach ($steps as $i => $step)
                            @php $done = array_search($order->status, $steps) >= $i; @endphp
                            <div class="flex flex-1 items-center last:flex-none">
                                <div class="grid h-7 w-7 place-items-center rounded-full text-xs font-bold" style="background: {{ $done ? 'var(--st-ink)' : 'var(--st-surface)' }}; color: {{ $done ? 'var(--st-bg)' : 'var(--st-ink-soft)' }}">{{ $i + 1 }}</div>
                                @if (! $loop->last)<div class="h-0.5 flex-1" style="background: {{ $done ? 'var(--st-ink)' : 'var(--st-line)' }}"></div>@endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-2 flex justify-between text-[11px] capitalize" style="color: var(--st-ink-soft)">
                        @foreach ($steps as $step)<span>{{ $step }}</span>@endforeach
                    </div>
                @endunless

                <div class="mt-6 border-t pt-4 text-sm" style="border-color: var(--st-line)">
                    @foreach ($order->items as $item)
                        <div class="flex justify-between py-1" style="color: var(--st-ink-soft)"><span>{{ $item->qty }} × {{ $item->name }}</span><span>{{ format_money($item->line_total) }}</span></div>
                    @endforeach
                    <div class="mt-2 flex justify-between border-t pt-2 font-semibold" style="border-color: var(--st-line); color: var(--st-ink)"><span>{{ __('storefront.total') }}</span><span>{{ format_money($order->grand_total) }}</span></div>
                </div>

                <div class="mt-6 border-t pt-4" style="border-color: var(--st-line)">
                    @include('theme::partials.invoice', ['order' => $order])
                </div>
            </div>
        @else
            <p class="mt-6 text-center text-sm" style="color: var(--st-ink-soft)">{{ __('checkout.no_order_found') }}</p>
        @endif
    @endif
</div>
