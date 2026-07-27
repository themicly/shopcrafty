<div class="space-y-6">
    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-content">Good to see you 👋</h2>
            <p class="mt-1 text-sm text-content-muted">Here's what's happening across your store.</p>
        </div>
        <x-ui.button variant="primary" :href="route('admin.catalog.products.create')">
            <x-ui.icon name="plus" class="h-4 w-4" /> Add product
        </x-ui.button>
    </div>

    {{-- Guided journey --}}
    @unless ($journeyComplete)
        @php $pct = (int) round($journeyCompleted / max(1, $journeyTotal) * 100); @endphp
        <div class="overflow-hidden rounded-xl border border-line bg-gradient-to-br from-primary-soft via-surface-raised to-info-soft p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-primary text-primary-fg"><x-ui.icon name="rocket" class="h-5 w-5" /></span>
                    <div>
                        <h3 class="text-sm font-semibold text-content">Your journey to launch</h3>
                        <p class="text-xs text-content-muted">{{ $journeyCompleted }} of {{ $journeyTotal }} steps done</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="h-2 w-40 overflow-hidden rounded-full bg-surface-sunken">
                        <div class="h-full rounded-full bg-primary transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>
                    <span class="text-sm font-semibold text-primary">{{ $pct }}%</span>
                </div>
            </div>

            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($journeySteps as $step)
                    <a href="{{ route($step['route']) }}" @class([
                        'group flex items-start gap-2.5 rounded-lg border p-3 transition',
                        'border-line bg-surface-raised/70' => ! $step['done'],
                        'border-success/30 bg-success-soft' => $step['done'],
                    ])>
                        <span @class([
                            'mt-0.5 grid h-5 w-5 shrink-0 place-items-center rounded-full text-[11px] font-bold',
                            'bg-success text-white' => $step['done'],
                            'bg-surface-sunken text-content-muted' => ! $step['done'],
                        ])>
                            @if ($step['done'])✓@else<x-ui.icon :name="$step['icon']" class="h-3 w-3" />@endif
                        </span>
                        <div class="min-w-0">
                            <p @class(['text-xs font-medium', 'text-content line-through opacity-70' => $step['done'], 'text-content' => ! $step['done']])>{{ $step['label'] }}</p>
                            @unless ($step['done'])<p class="mt-0.5 text-[11px] leading-snug text-content-muted">{{ $step['description'] }}</p>@endunless
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endunless

    @php
        // Literal class strings so Tailwind's scanner keeps them.
        $toneMap = [
            'primary' => ['bar' => 'bg-primary', 'chip' => 'bg-primary-soft text-primary'],
            'info' => ['bar' => 'bg-info', 'chip' => 'bg-info-soft text-info'],
            'success' => ['bar' => 'bg-success', 'chip' => 'bg-success-soft text-success'],
            'warning' => ['bar' => 'bg-warning', 'chip' => 'bg-warning-soft text-warning'],
            'danger' => ['bar' => 'bg-danger', 'chip' => 'bg-danger-soft text-danger'],
        ];
    @endphp

    {{-- Today row: the morning numbers, compared to yesterday --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Revenue today" :value="format_money($today['revenue'])" :delta="$today['revenueDelta']" hint="vs yesterday" />
        <x-ui.stat-card label="Orders today" :value="(string) $today['orders']" :delta="$today['ordersDelta']" hint="vs yesterday" />
        <x-ui.stat-card label="Visitors today" :value="number_format($today['visitors'])" :delta="$today['visitorsDelta']" hint="vs yesterday" />
    </div>

    {{-- Needs attention: pending work across the store, each tile jumps to its screen --}}
    <div>
        <h3 class="text-sm font-semibold text-content">Needs attention</h3>
        @if (count($attention))
            <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                @foreach ($attention as $item)
                    @php $chip = ($toneMap[$item['tone']] ?? $toneMap['primary'])['chip']; @endphp
                    <a href="{{ $item['href'] }}" class="flex items-center gap-3 rounded-lg border border-line bg-surface-raised px-4 py-3 shadow-sm transition hover:border-primary/40 hover:shadow focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                        <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg {{ $chip }}"><x-ui.icon :name="$item['icon']" class="h-4 w-4" /></span>
                        <span class="min-w-0">
                            <span class="block text-lg font-semibold leading-tight text-content">{{ $item['count'] }}</span>
                            <span class="block truncate text-xs text-content-muted">{{ $item['label'] }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="mt-2 flex items-center gap-2 rounded-lg border border-success/30 bg-success-soft px-4 py-3">
                <x-ui.icon name="check-circle" class="h-4 w-4 shrink-0 text-success" />
                <p class="text-sm text-content-secondary">All clear — nothing needs your attention right now.</p>
            </div>
        @endif
    </div>

    {{-- AI insights (only when the feature is on in Settings → AI) --}}
    @if ($this->aiInsightsEnabled)
        <x-ui.card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-medium uppercase tracking-wide text-content-muted">AI insights</p>
                    @if ($aiInsights)
                        <ul class="mt-2 space-y-1.5 text-sm text-content-secondary">
                            @foreach (preg_split('/\r?\n/', trim($aiInsights), -1, PREG_SPLIT_NO_EMPTY) as $line)
                                @php $line = ltrim(trim($line), "-•* \t"); @endphp
                                @continue($line === '')
                                <li class="flex gap-2">
                                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-primary" aria-hidden="true"></span>
                                    <span>{{ $line }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-1 text-sm text-content-muted">A quick read on what changed and what needs attention today.</p>
                    @endif
                </div>
                <x-admin.ai-button action="generateInsights" :label="$aiInsights ? 'Regenerate' : 'Generate insights'" />
            </div>
        </x-ui.card>
    @endif

    {{-- Colorful 30-day stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            @php
                $t = $toneMap[$stat['tone']] ?? $toneMap['primary'];
                $delta = $stat['delta'] ?? null;
                $series = $stat['series'] ?? [];
            @endphp
            <div class="relative overflow-hidden rounded-xl border border-line bg-surface-raised p-5 shadow-sm">
                <div class="absolute inset-x-0 top-0 h-1 {{ $t['bar'] }}"></div>
                <div class="flex items-center justify-between">
                    <p class="text-xs font-medium uppercase tracking-wide text-content-muted">{{ $stat['label'] }}</p>
                    <span class="grid h-8 w-8 place-items-center rounded-lg {{ $t['chip'] }}"><x-ui.icon :name="$stat['icon']" class="h-4 w-4" /></span>
                </div>
                <div class="mt-3 flex items-end justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-2xl font-semibold text-content">{{ $stat['value'] }}</p>
                        <div class="mt-1 flex items-center gap-1.5 text-xs">
                            @if (! is_null($delta))
                                @php $up = $delta >= 0; @endphp
                                <span class="inline-flex items-center gap-0.5 font-semibold {{ $up ? 'text-success' : 'text-danger' }}">
                                    <x-ui.icon :name="$up ? 'arrow-up' : 'arrow-down'" class="h-3 w-3" />{{ abs($delta) }}%
                                </span>
                            @endif
                            <span class="truncate text-content-muted">{{ $stat['hint'] }}</span>
                        </div>
                    </div>
                    @if (! empty($series))
                        <x-ui.chart :points="$series" type="line" :height="36" class="h-9 w-24 shrink-0 {{ str_replace('bg-', 'text-', $t['bar']) }}" />
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Revenue trend + sales by category --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <x-ui.card class="lg:col-span-2">
            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Revenue over time</p>
            <p class="mt-1 text-2xl font-semibold text-content">{{ format_money($today['revenue']) }} <span class="text-sm font-normal text-content-muted">today</span></p>
            <div class="mt-4 text-primary"><x-ui.chart :points="$revenueSeries" type="line" :height="64" class="h-32 w-full" /></div>
            <p class="mt-2 text-xs text-content-muted">Last 30 days</p>
        </x-ui.card>

        <x-ui.card title="Sales by category" subtitle="Last 30 days">
            @if (empty($salesByCategory))
                <x-ui.empty-state icon="products" title="No sales yet" description="Category totals appear once orders come in." />
            @else
                @php
                    $dashCategoryColors = ['text-primary', 'text-info', 'text-success', 'text-warning', 'text-danger'];
                    $dashCategoryBg = ['bg-primary', 'bg-info', 'bg-success', 'bg-warning', 'bg-danger'];
                    $dashCategoryTotal = array_sum(array_column($salesByCategory, 'revenue'));
                @endphp
                <div class="flex flex-col items-center gap-4">
                    <x-ui.donut
                        :segments="collect($salesByCategory)->map(fn ($row, $i) => ['value' => $row['revenue'], 'colorClass' => $dashCategoryColors[$i % count($dashCategoryColors)], 'label' => $row['category']])->all()"
                        :center-value="format_money($dashCategoryTotal)"
                        center-label="Total"
                        :size="128"
                    />
                    <div class="w-full space-y-2">
                        @foreach ($salesByCategory as $i => $row)
                            <div class="flex items-center gap-2 text-sm">
                                <span class="h-2 w-2 shrink-0 rounded-full {{ $dashCategoryBg[$i % count($dashCategoryBg)] }}"></span>
                                <span class="min-w-0 flex-1 truncate text-content">{{ $row['category'] }}</span>
                                <span class="shrink-0 text-xs text-content-muted">{{ $row['share'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- Two-column: recent orders + low stock --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-ui.card title="Recent orders" subtitle="Newest orders across all channels">
                @if ($recentOrders->isEmpty())
                    <x-ui.empty-state icon="orders" title="No orders yet" description="Orders will appear here once customers start buying." />
                @else
                    <x-ui.table flush>
                        <thead><tr><th>Order</th><th>Status</th><th class="text-right">Total</th></tr></thead>
                        <tbody>
                            @foreach ($recentOrders as $order)
                                <tr>
                                    <td class="font-medium"><a href="{{ route('admin.orders.show', $order) }}" class="hover:text-primary hover:underline focus-visible:underline focus-visible:outline-none">{{ $order->number }}</a></td>
                                    <td>
                                        @php $t = match ($order->status) { 'delivered' => 'success', 'cancelled', 'returned' => 'danger', 'pending' => 'warning', default => 'info' }; @endphp
                                        <x-ui.badge :variant="$t">{{ ucfirst($order->status) }}</x-ui.badge>
                                    </td>
                                    <td class="text-right font-medium">{{ format_money($order->grand_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </x-ui.table>
                @endif
            </x-ui.card>
        </div>

        <div>
            <x-ui.card title="Low stock" subtitle="Items that need restocking">
                @if ($lowStock->isEmpty())
                    <x-ui.empty-state icon="products" tone="success" title="All good" description="No products are running low." />
                @else
                    <div class="space-y-2">
                        @foreach ($lowStock as $product)
                            <a href="{{ route('admin.catalog.products.edit', $product) }}" class="flex items-center justify-between rounded-md px-2 py-1.5 hover:bg-surface-sunken">
                                <span class="truncate text-sm text-content">{{ $product->name }}</span>
                                <x-ui.badge variant="warning">{{ $product->stock_qty }} left</x-ui.badge>
                            </a>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>

    @if (app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->installed('ai') && isset($aiReview))
        <x-admin.ai-review-modal :items="$aiReview" />
    @endif
</div>
