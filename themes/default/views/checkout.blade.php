@extends('theme::checkout-layout')

@section('title', __('checkout.checkout'))

@section('content')
    {{--
        Fired from the outer page (not the Livewire component's own view) so it
        runs exactly once per real page load — the component below re-renders on
        every field blur / coupon apply, which would otherwise re-fire this event.
    --}}
    @php
        $trackCart = app(\Themicly\Shopcrafty\Modules\Orders\Services\CartService::class);
        $trackCurrency = app(\Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService::class);
    @endphp
    @unless ($trackCart->isEmpty())
        <script>
            window.bzTrack && window.bzTrack('begin_checkout', @js([
                'currency' => $trackCurrency->baseCode(),
                'value' => $trackCurrency->toBaseMajor($trackCart->subtotal()),
                'items' => $trackCart->items()->map(fn ($item) => [
                    'id' => $item->product_id,
                    'name' => $item->product?->name,
                    'price' => $trackCurrency->toBaseMajor($item->unitPrice()),
                    'quantity' => $item->qty,
                ])->values()->all(),
            ]));
        </script>
    @endunless

    <livewire:orders.checkout />
@endsection
