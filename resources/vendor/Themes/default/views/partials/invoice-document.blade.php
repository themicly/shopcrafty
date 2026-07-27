{{--
    Invoice document markup (no page chrome). Shared fallback partial rendered
    under every theme. Used by the standalone printable invoice page
    (theme::invoice-print). Expects: $order.
--}}
@php
    $invStore = settings('general.store_name', config('app.name'));
    $invTagline = settings('general.store_tagline');
    $invEmail = settings('general.store_email');
    $invPhone = settings('general.store_phone');
    $invDate = $order->placed_at ?? $order->created_at;
    $invShip = $order->shippingAddress;
@endphp
<div class="st-invoice">
    <div class="st-inv-head">
        <div>
            <div class="st-inv-store">{{ $invStore }}</div>
            @if ($invTagline)<div class="st-inv-soft">{{ $invTagline }}</div>@endif
            @if ($invEmail)<div class="st-inv-soft">{{ $invEmail }}</div>@endif
            @if ($invPhone)<div class="st-inv-soft">{{ $invPhone }}</div>@endif
        </div>
        <div>
            <div class="st-inv-title">{{ __('storefront.invoice') }}</div>
            <div class="st-inv-soft" style="text-align: right; margin-top: 4px">{{ $order->number }}</div>
            @if ($invDate)<div class="st-inv-soft" style="text-align: right">{{ $invDate->format('M j, Y') }}</div>@endif
        </div>
    </div>

    <div class="st-inv-meta">
        @if ($invShip)
            <div>
                <div class="st-inv-label">{{ __('storefront.bill_ship_to') }}</div>
                <div><strong>{{ $invShip->name }}</strong></div>
                @if ($invShip->address)<div>{{ $invShip->address }}</div>@endif
                <div>{{ collect([$invShip->city, $invShip->region, $invShip->postcode])->filter()->implode(', ') }}</div>
                @if ($invShip->phone)<div class="st-inv-soft">{{ $invShip->phone }}</div>@endif
                @if ($invShip->email)<div class="st-inv-soft">{{ $invShip->email }}</div>@endif
            </div>
        @endif
        <div>
            <div class="st-inv-label">{{ __('storefront.payment') }}</div>
            <div>{{ ucwords(str_replace('_', ' ', $order->payment_method ?? '')) ?: '—' }}</div>
            <div class="st-inv-soft" style="text-transform: capitalize">{{ str_replace('_', ' ', $order->payment_status ?? '') }}</div>
        </div>
    </div>

    <table class="st-inv-table">
        <thead>
            <tr>
                <th scope="col">{{ __('storefront.item') }}</th>
                <th scope="col" class="st-inv-num">{{ __('storefront.qty') }}</th>
                <th scope="col" class="st-inv-num">{{ __('storefront.unit_price') }}</th>
                <th scope="col" class="st-inv-num">{{ __('storefront.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        {{ $item->name }}
                        @if ($item->variant_label)<span class="st-inv-soft"> — {{ $item->variant_label }}</span>@endif
                    </td>
                    <td class="st-inv-num">{{ $item->qty }}</td>
                    <td class="st-inv-num">{{ format_money($item->price) }}</td>
                    <td class="st-inv-num">{{ format_money($item->line_total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="st-inv-totals">
        <div class="st-inv-row st-inv-soft"><span>{{ __('storefront.subtotal') }}</span><span>{{ format_money($order->subtotal) }}</span></div>
        @if ($order->discount_total > 0)
            <div class="st-inv-row st-inv-soft"><span>{{ __('storefront.discount') }}{{ $order->coupon_code ? ' ('.$order->coupon_code.')' : '' }}</span><span>&minus;{{ format_money($order->discount_total) }}</span></div>
        @endif
        <div class="st-inv-row st-inv-soft"><span>{{ __('storefront.shipping') }}</span><span>{{ $order->shipping_total > 0 ? format_money($order->shipping_total) : __('storefront.free') }}</span></div>
        @if ($order->tax_total > 0)
            <div class="st-inv-row st-inv-soft"><span>{{ __('storefront.tax') }}</span><span>{{ format_money($order->tax_total) }}</span></div>
        @endif
        <div class="st-inv-row st-inv-grand"><span>{{ __('storefront.total') }}</span><span>{{ format_money($order->grand_total) }}</span></div>
    </div>

    <div class="st-inv-foot">{{ __('storefront.thank_you_shopping', ['store' => $invStore]) }}</div>
</div>
