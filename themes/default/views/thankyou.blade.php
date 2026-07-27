@extends('theme::layout')

@section('title', __('checkout.order_confirmed_title'))

@section('content')
    <div class="st-container py-12 sm:py-16">
        <div class="mx-auto max-w-lg text-center">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-full" style="background: var(--st-surface)"
                x-data x-init="setTimeout(() => $el.animate([{transform:'scale(.6)',opacity:0},{transform:'scale(1)',opacity:1}],{duration:400,easing:'cubic-bezier(.2,.7,.2,1)'}), 50)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-8 w-8" style="color: var(--st-accent)"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </div>
            <h1 class="st-display mt-6 text-3xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.thank_you') }}</h1>
            <p class="mt-2 text-sm" style="color: var(--st-ink-soft)">{{ __('checkout.your_order') }} <strong style="color: var(--st-ink)">{{ $order->number }}</strong> {{ __('checkout.order_placed_message') }}</p>
        </div>

        <div class="mx-auto mt-10 max-w-lg border p-6" style="border-color: var(--st-line); border-radius: var(--st-radius)">
            <div class="space-y-3">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between text-sm">
                        <span style="color: var(--st-ink)">{{ $item->qty }} × {{ $item->name }}@if ($item->variant_label) <span style="color: var(--st-ink-soft)">({{ $item->variant_label }})</span>@endif</span>
                        <span class="font-medium" style="color: var(--st-ink)">{{ format_money($item->line_total) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 space-y-1.5 border-t pt-4 text-sm" style="border-color: var(--st-line)">
                <div class="flex justify-between" style="color: var(--st-ink-soft)"><span>{{ __('storefront.subtotal') }}</span><span>{{ format_money($order->subtotal) }}</span></div>
                <div class="flex justify-between" style="color: var(--st-ink-soft)"><span>{{ __('storefront.shipping') }}</span><span>{{ $order->shipping_total > 0 ? format_money($order->shipping_total) : __('storefront.free') }}</span></div>
                <div class="flex justify-between pt-1.5 text-base font-semibold" style="color: var(--st-ink)"><span>{{ __('storefront.total') }}</span><span>{{ format_money($order->grand_total) }}</span></div>
            </div>
            <div class="mt-4 border-t pt-4 text-sm" style="border-color: var(--st-line); color: var(--st-ink-soft)">
                <p><strong style="color: var(--st-ink)">{{ __('checkout.payment_label') }}</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
                @if ($order->shippingAddress)
                    <p class="mt-1"><strong style="color: var(--st-ink)">{{ __('checkout.ship_to') }}</strong> {{ $order->shippingAddress->name }}, {{ $order->shippingAddress->address }}, {{ $order->shippingAddress->city }}</p>
                @endif
            </div>
        </div>

        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ url('/shop') }}" class="inline-flex px-8 py-3.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.continue_shopping') }}</a>
            @include('theme::partials.invoice', ['order' => $order])
        </div>

        {{-- Cross-sell (frequently bought with this order) --}}
        @if ($suggestions->isNotEmpty())
            <div class="mt-16">
                <x-st.section-heading :eyebrow="__('storefront.before_you_go')" :title="__('storefront.you_might_also_like')" align="center" class="mb-8" />
                <div class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4">
                    @foreach ($suggestions as $p)
                        <x-st.product-card :product="$p" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @php $trackCurrency = app(\Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService::class); @endphp
    <script>
        (function () {
            // Reloading/back-navigating to this page must not re-fire a purchase —
            // GA4/FB Pixel don't dedupe conversions for us, so a per-order flag does.
            var key = 'bz-purchase-tracked-' + @js($order->number);
            if (localStorage.getItem(key)) return;
            try { localStorage.setItem(key, '1'); } catch (e) {}

            window.bzTrack && window.bzTrack('purchase', @js([
                'transaction_id' => $order->number,
                'currency' => strtoupper($order->currency),
                'value' => $trackCurrency->toBaseMajor($order->grand_total),
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->product_id,
                    'name' => $item->name,
                    'price' => $trackCurrency->toBaseMajor($item->price),
                    'quantity' => $item->qty,
                    'variant' => $item->variant_label,
                ])->values()->all(),
            ]));
        })();
    </script>
@endsection
