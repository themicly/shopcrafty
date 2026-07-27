<div class="space-y-6">
    {{-- Range picker (presets + custom dates) + export --}}
    <x-reports.range-picker :range="$range" :from-date="$fromDate" :to-date="$toDate">
        <x-slot:actions>
            <x-ui.button variant="secondary" size="sm" wire:click="exportOrders">Export orders CSV</x-ui.button>
        </x-slot:actions>
    </x-reports.range-picker>

    {{-- AI summary (only when the feature is on in Settings → AI) --}}
    @if ($this->aiSummaryEnabled)
        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium uppercase tracking-wide text-content-muted">AI summary</p>
                    @if ($aiSummary)
                        <p class="mt-2 whitespace-pre-line text-sm text-content-secondary">{{ $aiSummary }}</p>
                    @else
                        <p class="mt-1 text-sm text-content-muted">A plain-language read on what changed this period and what to act on.</p>
                    @endif
                </div>
                <x-ui.button type="button" variant="secondary" size="sm" wire:click="generateSummary">
                    <span wire:loading.remove wire:target="generateSummary">{{ $aiSummary ? 'Update summary' : 'Generate summary' }}</span>
                    <span wire:loading wire:target="generateSummary">Summarizing…</span>
                </x-ui.button>
            </div>
        </x-ui.card>
    @endif

    {{-- Monthly goal — a fixed business expectation, independent of the range picker above --}}
    <x-ui.card>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Monthly goal</p>
                <p class="mt-0.5 text-xs text-content-muted">{{ now()->format('F Y') }} · {{ $monthlyGoal['daysRemaining'] }} {{ \Illuminate\Support\Str::plural('day', $monthlyGoal['daysRemaining']) }} left</p>
            </div>
            @unless ($editingGoal)
                <button type="button" wire:click="editGoal" class="text-xs font-medium text-primary hover:underline">
                    {{ $monthlyGoal['goal'] > 0 ? 'Edit goal' : 'Set a goal' }}
                </button>
            @endunless
        </div>

        @if ($editingGoal)
            <div class="mt-3 flex flex-wrap items-end gap-2">
                <x-ui.input wire:model="goalInput" type="number" step="0.01" min="0" label="Monthly revenue goal" :error="$errors->first('goalInput')" class="max-w-[200px]" />
                <x-ui.button size="sm" wire:click="saveGoal">Save</x-ui.button>
                <x-ui.button size="sm" variant="ghost" wire:click="$set('editingGoal', false)">Cancel</x-ui.button>
            </div>
        @elseif ($monthlyGoal['goal'] > 0)
            <div class="mt-3">
                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span class="font-semibold text-content">{{ format_money($monthlyGoal['revenue']) }} <span class="font-normal text-content-muted">of {{ format_money($monthlyGoal['goal']) }}</span></span>
                    @if ($monthlyGoal['met'])
                        <x-ui.badge variant="success">Goal met 🎉</x-ui.badge>
                    @else
                        <x-ui.badge :variant="$monthlyGoal['onTrack'] ? 'success' : 'warning'">{{ $monthlyGoal['onTrack'] ? 'On track' : 'Behind pace' }}</x-ui.badge>
                    @endif
                </div>
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-surface-sunken">
                    <div class="h-full rounded-full {{ $monthlyGoal['met'] || $monthlyGoal['onTrack'] ? 'bg-success' : 'bg-warning' }}" style="width: {{ min(100, $monthlyGoal['progressPct']) }}%"></div>
                </div>
                <p class="mt-1.5 text-xs text-content-muted">
                    @if ($monthlyGoal['met'])
                        Goal reached with {{ $monthlyGoal['daysRemaining'] }} {{ \Illuminate\Support\Str::plural('day', $monthlyGoal['daysRemaining']) }} to spare
                    @else
                        {{ $monthlyGoal['progressPct'] }}% of goal so far · projected {{ format_money($monthlyGoal['projected']) }} by month end
                    @endif
                </p>
            </div>
        @else
            <p class="mt-3 text-sm text-content-muted">Set a monthly revenue goal to track progress against it right here.</p>
        @endif
    </x-ui.card>

    {{-- Revenue + orders cards with charts --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <x-ui.card>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Revenue</p>
                    <p class="mt-1 text-2xl font-semibold text-content">{{ format_money($revenue) }}</p>
                </div>
                @if ($revenueDelta !== null)
                    <span class="text-sm font-medium {{ $revenueDelta >= 0 ? 'text-success' : 'text-danger' }}">{{ $revenueDelta >= 0 ? '+' : '' }}{{ $revenueDelta }}%</span>
                @endif
            </div>
            <div class="mt-4 text-primary"><x-ui.chart :points="$revenueSeries" type="line" class="h-24 w-full" /></div>
        </x-ui.card>

        <x-ui.card>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Orders</p>
                    <p class="mt-1 text-2xl font-semibold text-content">{{ $orders }}</p>
                </div>
                @if ($ordersDelta !== null)
                    <span class="text-sm font-medium {{ $ordersDelta >= 0 ? 'text-success' : 'text-danger' }}">{{ $ordersDelta >= 0 ? '+' : '' }}{{ $ordersDelta }}%</span>
                @endif
            </div>
            <div class="mt-4 text-info"><x-ui.chart :points="$ordersSeries" type="bar" class="h-24 w-full" /></div>
        </x-ui.card>
    </div>

    {{-- Secondary metrics --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Avg. order value</p>
            <p class="mt-1 text-2xl font-semibold text-content">{{ format_money($aov) }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Repeat rate</p>
            <p class="mt-1 text-2xl font-semibold text-content">{{ $repeatRate }}%</p>
            <p class="mt-0.5 text-xs text-content-muted">Customers with 2+ orders</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">New customers</p>
            <p class="mt-1 text-2xl font-semibold text-content">{{ $newCustomers }}</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">{{ settings('tax.label', 'Tax') }} collected</p>
            <p class="mt-1 text-2xl font-semibold text-content">{{ format_money($taxCollected) }}</p>
        </x-ui.card>
        <x-ui.card>
            <div class="flex items-start justify-between">
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Visitors</p>
                @if ($visitorsDelta !== null)
                    <span class="text-sm font-medium {{ $visitorsDelta >= 0 ? 'text-success' : 'text-danger' }}">{{ $visitorsDelta >= 0 ? '+' : '' }}{{ $visitorsDelta }}%</span>
                @endif
            </div>
            <p class="mt-1 text-2xl font-semibold text-content">{{ number_format($visitors) }}</p>
            <p class="mt-0.5 text-xs text-content-muted">Storefront sessions in range</p>
        </x-ui.card>
        <x-ui.card>
            <div class="flex items-start justify-between">
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Conversion</p>
                @if ($conversionDelta !== null)
                    <span class="text-sm font-medium {{ $conversionDelta >= 0 ? 'text-success' : 'text-danger' }}">{{ $conversionDelta >= 0 ? '+' : '' }}{{ $conversionDelta }}%</span>
                @endif
            </div>
            <p class="mt-1 text-2xl font-semibold text-content">{{ $visitors > 0 ? $conversion.'%' : '—' }}</p>
            <p class="mt-0.5 text-xs text-content-muted">Orders ÷ visitors</p>
        </x-ui.card>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Top products --}}
        <x-ui.card title="Top products" subtitle="By revenue in range">
            @if ($topProducts->isEmpty())
                <x-ui.empty-state icon="products" title="No sales yet" />
            @else
                <div class="space-y-2">
                    @foreach ($topProducts as $p)
                        <div class="flex items-center gap-3">
                            <x-reports.product-thumb :product="$p['product']" :name="$p['name']" />
                            <div class="min-w-0 flex-1">
                                <x-reports.product-link :product="$p['product']" :name="$p['name']" class="block truncate text-sm text-content" />
                                @if ($p['profit'] !== null)
                                    <p class="text-xs {{ $p['profit'] >= 0 ? 'text-success' : 'text-danger' }}">~{{ format_money($p['profit']) }} profit</p>
                                @endif
                            </div>
                            <span class="shrink-0 text-sm font-medium text-content">{{ format_money($p['revenue']) }} · {{ $p['qty'] }} sold</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        {{-- Product insights --}}
        <x-ui.card title="Product insights" subtitle="Views → conversion">
            @if ($insights->isEmpty())
                <x-ui.empty-state icon="reports" title="No views yet" description="Insights appear as visitors browse." />
            @else
                <x-ui.table flush>
                    <thead><tr><th>Product</th><th class="text-right">Views</th><th class="text-right">Sold</th><th class="text-right">Conv.</th></tr></thead>
                    <tbody>
                        @foreach ($insights as $row)
                            <tr>
                                <td>
                                    <div class="flex min-w-0 items-center gap-2.5">
                                        <x-reports.product-thumb :product="$row['product']" :name="$row['name']" />
                                        <x-reports.product-link :product="$row['product']" :name="$row['name']" class="block truncate" />
                                    </div>
                                </td>
                                <td class="text-right">{{ $row['views'] }}</td>
                                <td class="text-right">{{ $row['units'] }}</td>
                                <td class="text-right">
                                    <span @class(['text-content-muted' => $row['conversion'] == 0])>{{ $row['conversion'] }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card>
    </div>

    {{-- Low product views — published products no one's looking at (flip side of "Top products") --}}
    <x-ui.card title="Low product views" subtitle="Published products that need attention — {{ $lowViewsThreshold }} views or fewer in range">
        @if ($lowViewProducts->isEmpty())
            <x-ui.empty-state icon="products" tone="success" title="All good" description="Every published product is getting at least some traffic." />
        @else
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($lowViewProducts as $row)
                    <div class="flex items-center gap-3 rounded-lg border border-line p-2">
                        <x-reports.product-thumb :product="$row['product']" :name="$row['product']->name" />
                        <div class="min-w-0 flex-1">
                            <x-reports.product-link :product="$row['product']" :name="$row['product']->name" class="block truncate text-sm text-content" />
                        </div>
                        <x-ui.badge :variant="$row['views'] === 0 ? 'danger' : 'warning'">{{ $row['views'] }} {{ \Illuminate\Support\Str::plural('view', $row['views']) }}</x-ui.badge>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>

    {{-- Sales breakdown — gross sales down to net revenue, using columns already
         on the order (subtotal/discount/shipping/tax/refunds), just not
         previously shown together as a reconciliation. --}}
    <x-ui.card title="Sales breakdown" subtitle="Gross sales down to net revenue in range">
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Gross sales</p>
                <p class="mt-1 text-lg font-semibold text-content">{{ format_money($salesBreakdown['gross']) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Discounts</p>
                <p class="mt-1 text-lg font-semibold text-danger">−{{ format_money($salesBreakdown['discounts']) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Shipping income</p>
                <p class="mt-1 text-lg font-semibold text-info">+{{ format_money($salesBreakdown['shipping']) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">{{ settings('tax.label', 'Tax') }} collected</p>
                <p class="mt-1 text-lg font-semibold text-info">+{{ format_money($salesBreakdown['tax']) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Refunds</p>
                <p class="mt-1 text-lg font-semibold text-danger">−{{ format_money($salesBreakdown['refunds']) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Net revenue</p>
                <p class="mt-1 text-lg font-semibold text-success">{{ format_money($salesBreakdown['net']) }}</p>
            </div>
        </div>
    </x-ui.card>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Sales by category --}}
        <x-ui.card title="Sales by category" subtitle="Revenue share in range">
            @if ($salesByCategory->isEmpty())
                <x-ui.empty-state icon="products" title="No sales yet" description="Category totals appear once orders come in." />
            @else
                @php
                    $categoryTextColors = ['text-primary', 'text-info', 'text-success', 'text-warning', 'text-danger'];
                    $categoryBgColors = ['bg-primary', 'bg-info', 'bg-success', 'bg-warning', 'bg-danger'];
                    $totalRevenue = $salesByCategory->sum('revenue');
                @endphp
                <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                    <x-ui.donut
                        :segments="$salesByCategory->map(fn ($row, $i) => ['value' => $row['revenue'], 'colorClass' => $categoryTextColors[$i % count($categoryTextColors)], 'label' => $row['category']])->all()"
                        :center-value="format_money($totalRevenue)"
                        center-label="Total"
                        :size="140"
                    />
                    <div class="min-w-0 flex-1 space-y-2.5">
                        @foreach ($salesByCategory as $i => $row)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="h-2 w-2 shrink-0 rounded-full {{ $categoryBgColors[$i % count($categoryBgColors)] }}"></span>
                                <span class="min-w-0 flex-1 truncate text-content">{{ $row['category'] }}</span>
                                <span class="shrink-0 text-xs text-content-muted">{{ $row['units'] }} units</span>
                                <span class="shrink-0 font-medium text-content">{{ format_money($row['revenue']) }}</span>
                                <span class="w-10 shrink-0 text-right text-xs text-content-muted">{{ $row['share'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-ui.card>

        {{-- Payment method split --}}
        <x-ui.card title="Payment methods" subtitle="Revenue by payment method in range">
            @if ($paymentSplit->isEmpty())
                <x-ui.empty-state icon="orders" title="No payments yet" description="The split appears once orders come in." />
            @else
                @php $methodColors = ['cod' => 'bg-warning', 'stripe' => 'bg-primary', 'paypal' => 'bg-info', 'bank_transfer' => 'bg-success']; @endphp
                <x-ui.table flush>
                    <thead><tr><th>Method</th><th class="text-right">Orders</th><th class="text-right">Revenue</th></tr></thead>
                    <tbody>
                        @foreach ($paymentSplit as $row)
                            <tr>
                                <td>
                                    <span class="mr-1.5 inline-block h-2 w-2 rounded-full {{ $methodColors[$row['method']] ?? 'bg-content-muted' }}"></span>
                                    {{ $row['method'] === 'cod' ? 'Cash on delivery' : ucfirst(str_replace('_', ' ', $row['method'])) }}
                                </td>
                                <td class="text-right">{{ $row['orders'] }}</td>
                                <td class="text-right font-medium">{{ format_money($row['revenue']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif

            {{-- Gateway reliability from the payment_logs audit trail — Stripe
                 session/webhook/confirm activity. COD has nothing to log here. --}}
            @if ($paymentReliability['total'] > 0)
                <div class="mt-4 border-t border-line pt-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Gateway reliability</p>
                        <x-ui.badge :variant="$paymentReliability['successRate'] >= 95 ? 'success' : ($paymentReliability['successRate'] >= 80 ? 'warning' : 'danger')">
                            {{ $paymentReliability['successRate'] }}% success
                        </x-ui.badge>
                    </div>
                    <p class="mt-1 text-sm text-content">
                        <span class="font-semibold {{ $paymentReliability['failures'] > 0 ? 'text-danger' : 'text-content' }}">{{ $paymentReliability['failures'] }}</span>
                        failed of <span class="font-medium">{{ $paymentReliability['total'] }}</span> gateway calls in range
                    </p>
                    @if ($paymentReliability['recentFailures']->isNotEmpty())
                        <ul class="mt-2 space-y-1">
                            @foreach ($paymentReliability['recentFailures'] as $failure)
                                <li class="truncate text-xs text-content-muted">
                                    <span class="font-medium text-danger">{{ ucfirst($failure->gateway) }}</span>
                                    · {{ $failure->action }} · {{ $failure->message ?: 'no message' }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </x-ui.card>
    </div>
</div>
