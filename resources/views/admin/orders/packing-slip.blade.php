<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Packing slip {{ $order->number }}</title>
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
        .slip { max-width: 720px; margin: 0 auto; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; }
        .store-name { font-size: 20px; font-weight: 700; }
        .doc-title { font-size: 22px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .muted { color: #666; }
        .meta { text-align: right; }
        .meta div { margin-top: 2px; }
        h4 { margin: 0 0 6px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; }
        .ship-to { margin-bottom: 28px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; border-bottom: 2px solid #111; padding: 8px 6px; }
        td { padding: 8px 6px; border-bottom: 1px solid #ddd; }
        .right { text-align: right; }
        .foot { margin-top: 32px; padding-top: 16px; border-top: 1px solid #ddd; font-size: 12px; color: #666; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    @php $ship = $order->shippingAddress; @endphp
    <div class="slip">
        <div class="head">
            <div>
                <div class="store-name">{{ settings('general.store_name', config('app.name', 'Shopcrafty')) }}</div>
                <div class="muted">Packing slip</div>
            </div>
            <div class="meta">
                <div class="doc-title">Packing slip</div>
                <div><strong>{{ $order->number }}</strong></div>
                <div class="muted">{{ $order->placed_at?->format('M j, Y') }}</div>
                @if ($order->tracking_number || $order->carrier)
                    <div class="muted">{{ trim($order->carrier.' '.$order->tracking_number) }}</div>
                @endif
            </div>
        </div>

        <div class="ship-to">
            <h4>Ship to</h4>
            @if ($ship)
                <div><strong>{{ $ship->name }}</strong></div>
                @if ($ship->phone)<div>{{ $ship->phone }}</div>@endif
                <div>{{ $ship->address }}</div>
                <div>{{ collect([$ship->city, $ship->region, $ship->postcode])->filter()->join(', ') }}</div>
            @else
                <div class="muted">—</div>
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th class="right">Qty</th>
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
                        <td class="muted">{{ $item->sku ?? '—' }}</td>
                        <td class="right">{{ $item->qty }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if (filled($order->notes))
            <div style="margin-top:16px;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px">
                <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin-bottom:4px">Customer note</div>
                <div style="white-space:pre-line">{{ $order->notes }}</div>
            </div>
        @endif

        <div class="foot">
            {{ $order->items->sum('qty') }} item(s) · Please check contents against this slip before dispatch.
        </div>
    </div>
</body>
</html>
