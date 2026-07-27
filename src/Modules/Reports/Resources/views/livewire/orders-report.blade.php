<div class="space-y-6">
    <x-reports.range-picker :range="$range" :from-date="$fromDate" :to-date="$toDate" />

    <div class="grid gap-4 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Orders per day</p>
            <div class="mt-4 text-info"><x-ui.chart :points="$ordersSeries" type="bar" class="h-24 w-full" /></div>
        </x-ui.card>

        <div class="grid gap-4">
            <x-ui.card>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Delivery success rate</p>
                @if ($deliverySuccessRate === null)
                    <p class="mt-1 text-2xl font-semibold text-content-muted">—</p>
                @else
                    <p class="mt-1 text-2xl font-semibold {{ $deliverySuccessRate >= 90 ? 'text-success' : ($deliverySuccessRate >= 70 ? 'text-warning' : 'text-danger') }}">{{ $deliverySuccessRate }}%</p>
                @endif
                <p class="mt-0.5 text-xs text-content-muted">Delivered ÷ (delivered + cancelled + returned)</p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Avg. processing time</p>
                <p class="mt-1 text-sm text-content">
                    <span class="font-semibold text-content">{{ $processingTimes['toConfirmed'] !== null ? $processingTimes['toConfirmed'].'h' : '—' }}</span> to confirm ·
                    <span class="font-semibold text-content">{{ $processingTimes['toShipped'] !== null ? $processingTimes['toShipped'].'h' : '—' }}</span> to ship
                </p>
                <p class="mt-0.5 text-xs text-content-muted">Average hours from order placed</p>
            </x-ui.card>
        </div>
    </div>

    {{-- Status breakdown --}}
    <x-ui.card title="Order status" subtitle="Where every order in range currently stands">
        @php $withCounts = array_filter($statusBreakdown, fn ($s) => $s['count'] > 0); $totalOrders = array_sum(array_column($statusBreakdown, 'count')); @endphp
        @if (empty($withCounts))
            <x-ui.empty-state icon="orders" title="No orders in range" />
        @else
            <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <x-ui.donut
                    :segments="collect($statusBreakdown)->map(fn ($s) => ['value' => $s['count'], 'colorClass' => 'text-'.($s['color'] === 'neutral' ? 'content-muted' : $s['color']), 'label' => $s['label']])->all()"
                    :center-value="$totalOrders"
                    center-label="Orders"
                    :size="140"
                />
                <div class="grid min-w-0 flex-1 grid-cols-2 gap-3 sm:grid-cols-2">
                @foreach ($statusBreakdown as $s)
                    <div class="flex items-center gap-2">
                        <x-ui.badge :variant="$s['color']">{{ $s['label'] }}</x-ui.badge>
                        <span class="text-sm font-medium text-content">{{ $s['count'] }}</span>
                        <span class="text-xs text-content-muted">({{ $s['pct'] }}%)</span>
                    </div>
                @endforeach
                </div>
            </div>
        @endif
    </x-ui.card>

    {{-- Carrier breakdown --}}
    <x-ui.card title="Orders by carrier" subtitle="Shipped/delivered orders in range, by the carrier logged at ship time">
        @if (empty($carrierBreakdown))
            <x-ui.empty-state icon="orders" title="No carrier data yet" description="Recorded when you mark an order shipped with a carrier name." />
        @else
            @php $carrierColors = ['bg-primary', 'bg-info', 'bg-success', 'bg-warning', 'bg-danger']; @endphp
            <x-ui.table flush>
                <thead><tr><th>Carrier</th><th class="text-right">Orders</th></tr></thead>
                <tbody>
                    @foreach ($carrierBreakdown as $i => $row)
                        <tr>
                            <td>
                                <span class="mr-1.5 inline-block h-2 w-2 rounded-full {{ $carrierColors[$i % count($carrierColors)] }}"></span>
                                {{ $row['carrier'] }}
                            </td>
                            <td class="text-right font-medium">{{ $row['orders'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        @endif
    </x-ui.card>
</div>
