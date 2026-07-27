@extends('theme::layout')

@section('title', __('account.order_title', ['number' => $order->number]))

@section('content')
    @php
        $steps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
        $isClosed = in_array($order->status, ['cancelled', 'returned'], true);
        $placeholder = 'm2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 15.75h.008M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z';
    @endphp

    <div class="st-container py-12">
        <div class="flex flex-col gap-8 lg:flex-row">
            @include('theme::partials.account-nav')

            <div class="flex-1">
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <a href="{{ route('storefront.account.index') }}" class="text-xs font-medium" style="color: var(--st-ink-soft)">&larr; {{ __('account.back_to_orders') }}</a>
                        <h1 class="st-display mt-1 text-2xl font-semibold" style="color: var(--st-ink)">{{ $order->number }}</h1>
                        <p class="text-xs" style="color: var(--st-ink-soft)">{{ __('account.placed_on', ['date' => $order->placed_at?->format('M j, Y') ?? $order->created_at?->format('M j, Y')]) }}</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize" style="background: var(--st-surface); color: var(--st-ink)">{{ $order->status }}</span>
                </div>

                {{-- Actions --}}
                <div class="mb-8 flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('storefront.account.orders.reorder', $order->number) }}">
                        @csrf
                        <button type="submit" class="px-5 py-2.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('account.buy_again') }}</button>
                    </form>
                    <button type="button" onclick="window.print()" class="border px-5 py-2.5 text-sm font-semibold" style="border-color: var(--st-line); color: var(--st-ink); border-radius: var(--st-radius-sm)">{{ __('account.print') }}</button>
                </div>

                {{-- Status stepper --}}
                @unless ($isClosed)
                    <div class="mb-8 border p-6" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                        <div class="flex items-center">
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
                    </div>
                @endunless

                {{-- Tracking --}}
                @if ($order->tracking_number || $order->carrier)
                    <div class="mb-8 border p-5 text-sm" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                        <p class="font-semibold" style="color: var(--st-ink)">{{ __('account.shipment_tracking') }}</p>
                        <div class="mt-2 space-y-1" style="color: var(--st-ink-soft)">
                            @if ($order->carrier)<p><strong style="color: var(--st-ink)">{{ __('account.carrier') }}</strong> {{ $order->carrier }}</p>@endif
                            @if ($order->tracking_number)<p><strong style="color: var(--st-ink)">{{ __('account.tracking_number') }}</strong> {{ $order->tracking_number }}</p>@endif
                        </div>
                    </div>
                @endif

                {{-- Line items --}}
                <div class="border p-5" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                    <p class="mb-4 font-semibold" style="color: var(--st-ink)">{{ __('account.items') }}</p>
                    <div class="space-y-4">
                        @foreach ($order->items as $item)
                            @php $img = $item->product?->media->first()?->path; @endphp
                            <div class="flex items-center gap-4">
                                <div class="h-16 w-16 shrink-0 overflow-hidden" style="background: var(--st-surface); border-radius: var(--st-radius-sm)">
                                    @if ($img)
                                        <img src="{{ $img }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="grid h-full w-full place-items-center" style="color: var(--st-line)"><svg fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $placeholder }}" /></svg></div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium" style="color: var(--st-ink)">{{ $item->name }}</p>
                                    @if ($item->variant_label)<p class="text-xs" style="color: var(--st-ink-soft)">{{ $item->variant_label }}</p>@endif
                                    <p class="text-xs" style="color: var(--st-ink-soft)">{{ __('account.qty') }} {{ $item->qty }} × {{ format_money($item->price) }}</p>
                                </div>
                                <span class="text-sm font-semibold" style="color: var(--st-ink)">{{ format_money($item->line_total) }}</span>
                            </div>
                        @endforeach
                    </div>

                    {{-- Totals --}}
                    <div class="mt-5 space-y-1.5 border-t pt-4 text-sm" style="border-color: var(--st-line)">
                        <div class="flex justify-between" style="color: var(--st-ink-soft)"><span>{{ __('storefront.subtotal') }}</span><span>{{ format_money($order->subtotal) }}</span></div>
                        @if ($order->discount_total > 0)
                            <div class="flex justify-between" style="color: var(--st-ink-soft)"><span>{{ __('storefront.discount') }}</span><span>−{{ format_money($order->discount_total) }}</span></div>
                        @endif
                        <div class="flex justify-between" style="color: var(--st-ink-soft)"><span>{{ __('storefront.shipping') }}</span><span>{{ $order->shipping_total > 0 ? format_money($order->shipping_total) : __('storefront.free') }}</span></div>
                        @if ($order->tax_total > 0)
                            <div class="flex justify-between" style="color: var(--st-ink-soft)"><span>{{ __('storefront.tax') }}</span><span>{{ format_money($order->tax_total) }}</span></div>
                        @endif
                        <div class="flex justify-between pt-1.5 text-base font-semibold" style="color: var(--st-ink)"><span>{{ __('storefront.total') }}</span><span>{{ format_money($order->grand_total) }}</span></div>
                    </div>
                </div>

                {{-- Shipping address --}}
                @if ($order->shippingAddress)
                    @php $a = $order->shippingAddress; @endphp
                    <div class="mt-6 border p-5 text-sm" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                        <p class="mb-2 font-semibold" style="color: var(--st-ink)">{{ __('storefront.shipping_address') }}</p>
                        <div style="color: var(--st-ink-soft)">
                            <p>{{ $a->name }}</p>
                            @if ($a->phone)<p>{{ $a->phone }}</p>@endif
                            <p>{{ collect([$a->address, $a->city, $a->region, $a->postcode])->filter()->join(', ') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Status timeline --}}
                @if ($order->history->isNotEmpty())
                    <div class="mt-6 border p-5" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                        <p class="mb-4 font-semibold" style="color: var(--st-ink)">{{ __('account.history') }}</p>
                        <ol class="space-y-3">
                            @foreach ($order->history as $entry)
                                <li class="flex gap-3 text-sm">
                                    <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" style="background: var(--st-accent)"></span>
                                    <div>
                                        <p class="font-medium capitalize" style="color: var(--st-ink)">{{ str_replace('_', ' ', $entry->to_status) }}</p>
                                        @if ($entry->note)<p class="text-xs" style="color: var(--st-ink-soft)">{{ $entry->note }}</p>@endif
                                        <p class="text-xs" style="color: var(--st-ink-soft)">{{ $entry->created_at?->format('M j, Y g:i A') }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
