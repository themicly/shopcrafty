<div class="space-y-6">
    <x-reports.range-picker :range="$range" :from-date="$fromDate" :to-date="$toDate" />

    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Refund amount</p>
            <p class="mt-1 text-2xl font-semibold text-danger">{{ format_money($summary['refundedAmount']) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Refund rate</p>
            @if ($summary['refundPct'] === null)
                <p class="mt-1 text-2xl font-semibold text-content-muted">—</p>
            @else
                <p class="mt-1 text-2xl font-semibold {{ $summary['refundPct'] <= 5 ? 'text-success' : ($summary['refundPct'] <= 15 ? 'text-warning' : 'text-danger') }}">{{ $summary['refundPct'] }}%</p>
            @endif
            <p class="mt-0.5 text-xs text-content-muted">Of revenue from orders placed in range</p>
        </x-ui.card>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Reasons --}}
        <x-ui.card title="Refund reasons" subtitle="As entered by staff, grouped loosely">
            @if ($topReasons->isEmpty())
                <x-ui.empty-state icon="orders" tone="success" title="No refunds in range" />
            @else
                <div class="space-y-2">
                    @foreach ($topReasons as $row)
                        <div class="flex items-center justify-between gap-3">
                            <span class="min-w-0 flex-1 truncate text-sm capitalize text-content">{{ $row['reason'] }}</span>
                            <span class="shrink-0 text-sm font-medium text-danger">{{ format_money($row['amount']) }}</span>
                            <x-ui.badge variant="neutral">{{ $row['uses'] }}×</x-ui.badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        {{-- Most refunded categories --}}
        <x-ui.card title="Most refunded categories" subtitle="Units returned in range">
            @if ($mostRefundedCategories->isEmpty())
                <x-ui.empty-state icon="products" tone="success" title="No itemized returns in range" />
            @else
                <x-ui.table flush>
                    <thead><tr><th>Category</th><th class="text-right">Units refunded</th></tr></thead>
                    <tbody>
                        @foreach ($mostRefundedCategories as $row)
                            <tr>
                                <td>{{ $row['category'] }}</td>
                                <td class="text-right font-medium text-danger">{{ $row['qty'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card>
    </div>

    {{-- Most refunded products --}}
    <x-ui.card title="Most refunded products" subtitle="Units returned in range, via itemized returns">
        @if ($mostRefundedProducts->isEmpty())
            <x-ui.empty-state icon="products" tone="success" title="No itemized returns in range" description="Manual refunds without a linked return aren't attributed to a product." />
        @else
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($mostRefundedProducts as $row)
                    <div class="flex items-center gap-3 rounded-lg border border-line p-2">
                        <x-reports.product-thumb :product="$row['product']" :name="$row['product']->name" />
                        <div class="min-w-0 flex-1"><x-reports.product-link :product="$row['product']" :name="$row['product']->name" class="block truncate text-sm text-content" /></div>
                        <x-ui.badge variant="danger">{{ $row['qty'] }}×</x-ui.badge>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>
</div>
