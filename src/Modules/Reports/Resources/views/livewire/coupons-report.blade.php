<div class="space-y-6">
    <x-reports.range-picker :range="$range" :from-date="$fromDate" :to-date="$toDate" />

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Coupons used</p>
            <p class="mt-1 text-2xl font-semibold text-content">{{ $summary['uses'] }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Revenue generated</p>
            <p class="mt-1 text-2xl font-semibold text-success">{{ format_money($summary['revenueGenerated']) }}</p>
            <p class="mt-0.5 text-xs text-content-muted">From orders using a coupon</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Discount given</p>
            <p class="mt-1 text-2xl font-semibold text-danger">−{{ format_money($summary['discountGiven']) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Average discount</p>
            <p class="mt-1 text-2xl font-semibold text-content">{{ format_money($summary['avgDiscount']) }}</p>
            <p class="mt-0.5 text-xs text-content-muted">Per redemption</p>
        </x-ui.card>
    </div>

    <x-ui.card title="Coupons" subtitle="Ranked by redemptions in range">
        @if ($topCoupons->isEmpty())
            <x-ui.empty-state icon="marketing" title="No coupons used yet" description="Redemptions in range will appear here, ranked by how often each code was used." />
        @else
            <x-ui.table flush>
                <thead><tr><th>Coupon</th><th>Type</th><th class="text-right">Uses</th><th class="text-right">Discount given</th><th class="text-right">Revenue</th></tr></thead>
                <tbody>
                    @foreach ($topCoupons as $coupon)
                        <tr>
                            <td>
                                <span class="font-mono text-sm font-semibold text-content">{{ $coupon['code'] }}</span>
                                @if ($coupon['name'])<span class="ml-1.5 text-xs text-content-muted">{{ $coupon['name'] }}</span>@endif
                            </td>
                            <td>
                                @php $typeVariant = ['percentage' => 'info', 'fixed' => 'primary', 'free_shipping' => 'success'][$coupon['type']] ?? 'neutral'; @endphp
                                <x-ui.badge :variant="$typeVariant">{{ ucfirst(str_replace('_', ' ', $coupon['type'])) }}</x-ui.badge>
                            </td>
                            <td class="text-right font-medium">{{ $coupon['uses'] }}</td>
                            <td class="text-right text-danger">−{{ format_money($coupon['discountGiven']) }}</td>
                            <td class="text-right font-medium text-success">{{ format_money($coupon['revenue']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        @endif
    </x-ui.card>
</div>
