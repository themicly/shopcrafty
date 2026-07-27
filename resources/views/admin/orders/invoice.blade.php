<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $order->number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 40px;
            font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.5;
            color: #111;
            background: #fff;
        }
        .invoice { max-width: 720px; margin: 0 auto; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
        .store-name { font-size: 20px; font-weight: 700; }
        .doc-title { font-size: 22px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .muted { color: #666; }
        .meta { text-align: right; }
        .meta div { margin-top: 2px; }
        .parties { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 28px; }
        .parties h4 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; border-bottom: 2px solid #111; padding: 8px 6px; }
        td { padding: 8px 6px; border-bottom: 1px solid #ddd; }
        .right { text-align: right; }
        .totals { width: 260px; margin-left: auto; }
        .totals td { border: none; padding: 4px 6px; }
        .totals .grand td { border-top: 2px solid #111; font-weight: 700; font-size: 15px; padding-top: 8px; }
        .foot { margin-top: 32px; padding-top: 16px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @php $ship = $order->shippingAddress; @endphp
    <div class="invoice">
        <div class="head">
            <div>
                <div class="store-name">{{ settings('general.store_name', config('app.name', 'Shopcrafty')) }}</div>
                <div class="muted">Invoice</div>
            </div>
            <div class="meta">
                <div class="doc-title">Invoice</div>
                <div><strong>{{ $order->number }}</strong></div>
                <div class="muted">{{ $order->placed_at?->format('M j, Y') }}</div>
            </div>
        </div>

        <div class="parties">
            <div>
                <h4>Bill to</h4>
                @if ($ship)
                    <div><strong>{{ $ship->name }}</strong></div>
                    @if ($ship->phone)<div>{{ $ship->phone }}</div>@endif
                    @if ($ship->email)<div>{{ $ship->email }}</div>@endif
                    <div>{{ $ship->address }}</div>
                    <div>{{ collect([$ship->city, $ship->region, $ship->postcode])->filter()->join(', ') }}</div>
                @else
                    <div class="muted">—</div>
                @endif
            </div>
            <div class="meta">
                <h4>Payment</h4>
                <div>{{ strtoupper($order->payment_method) }}</div>
                <div class="muted">{{ ucfirst($order->payment_status) }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="right">Qty</th>
                    <th class="right">Price</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->name }}
                            @if ($item->variant_label)
                                <div class="muted">{{ $item->variant_label }}</div>
                            @endif
                        </td>
                        <td class="right">{{ $item->qty }}</td>
                        <td class="right">{{ format_money($item->price) }}</td>
                        <td class="right">{{ format_money($item->line_total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td>Subtotal</td>
                <td class="right">{{ format_money($order->subtotal) }}</td>
            </tr>
            <tr>
                <td>Discount</td>
                <td class="right">−{{ format_money($order->discount_total) }}</td>
            </tr>
            <tr>
                <td>Shipping</td>
                <td class="right">{{ format_money($order->shipping_total) }}</td>
            </tr>
            @if ($order->tax_total > 0)
                <tr>
                    <td>{{ settings('tax.label', 'Tax') }}{{ settings('tax.inclusive', false) ? ' (incl.)' : '' }}</td>
                    <td class="right">{{ format_money($order->tax_total) }}</td>
                </tr>
            @endif
            <tr class="grand">
                <td>Total</td>
                <td class="right">{{ format_money($order->grand_total) }}</td>
            </tr>
        </table>

        <div class="foot">
            Thank you for your order.
        </div>
    </div>
</body>
</html>
