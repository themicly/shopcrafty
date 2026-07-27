<div class="space-y-6">
    {{-- Inventory value --}}
    <x-ui.card title="Inventory value" subtitle="Across all in-stock, tracked products">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Cost value</p>
                <p class="mt-1 text-2xl font-semibold text-content">{{ format_money($inventoryValue['costValue']) }}</p>
                <p class="mt-0.5 text-xs text-content-muted">What your stock cost you</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Selling value</p>
                <p class="mt-1 text-2xl font-semibold text-info">{{ format_money($inventoryValue['sellingValue']) }}</p>
                <p class="mt-0.5 text-xs text-content-muted">If everything sold at list price</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-content-muted">Potential profit</p>
                <p class="mt-1 text-2xl font-semibold text-success">{{ format_money($inventoryValue['potentialProfit']) }}</p>
                <p class="mt-0.5 text-xs text-content-muted">Selling value − cost value</p>
            </div>
        </div>
    </x-ui.card>

    <div class="grid gap-4 lg:grid-cols-2">
        {{-- Low stock --}}
        <x-ui.card title="Low stock" subtitle="At or below the product's own threshold">
            @if ($lowStock->isEmpty())
                <x-ui.empty-state icon="products" tone="success" title="All good" description="No products are running low." />
            @else
                <div class="space-y-2">
                    @foreach ($lowStock as $product)
                        <div class="flex items-center gap-3">
                            <x-reports.product-thumb :product="$product" :name="$product->name" />
                            <div class="min-w-0 flex-1"><x-reports.product-link :product="$product" :name="$product->name" class="block truncate text-sm text-content" /></div>
                            <x-ui.badge variant="warning">{{ $product->stock_qty }} left</x-ui.badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        {{-- Out of stock --}}
        <x-ui.card title="Out of stock" subtitle="Nothing left to sell — immediate action">
            @if ($outOfStock->isEmpty())
                <x-ui.empty-state icon="products" tone="success" title="All good" description="Nothing's sold out." />
            @else
                <div class="space-y-2">
                    @foreach ($outOfStock as $product)
                        <div class="flex items-center gap-3">
                            <x-reports.product-thumb :product="$product" :name="$product->name" />
                            <div class="min-w-0 flex-1"><x-reports.product-link :product="$product" :name="$product->name" class="block truncate text-sm text-content" /></div>
                            <x-ui.badge variant="danger">0 left</x-ui.badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- Dead stock --}}
    <x-ui.card title="Dead stock" subtitle="In stock, but zero units sold in the window">
        <x-slot:actions>
            <div class="flex items-center gap-1 rounded-md border border-line p-0.5">
                @foreach ([30, 60, 90] as $days)
                    <button wire:click="$set('deadStockDays', {{ $days }})" @class([
                        'rounded px-3 py-1 text-xs',
                        'bg-surface-sunken font-medium text-content' => $deadStockDays === $days,
                        'text-content-muted hover:text-content' => $deadStockDays !== $days,
                    ])>{{ $days }}d</button>
                @endforeach
            </div>
        </x-slot:actions>
        @if ($deadStock->isEmpty())
            <x-ui.empty-state icon="products" tone="success" title="Nothing dead" description="Everything in stock has sold at least once in the window." />
        @else
            <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($deadStock as $product)
                    <div class="flex items-center gap-3 rounded-lg border border-line p-2">
                        <x-reports.product-thumb :product="$product" :name="$product->name" />
                        <div class="min-w-0 flex-1">
                            <x-reports.product-link :product="$product" :name="$product->name" class="block truncate text-sm text-content" />
                            <p class="text-xs text-content-muted">{{ $product->stock_qty }} in stock</p>
                        </div>
                        <x-ui.badge variant="neutral">No sales</x-ui.badge>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>

    {{-- Fast moving --}}
    <x-ui.card title="Fast moving" subtitle="Highest units sold per day, last 30 days">
        @if ($fastMoving->isEmpty())
            <x-ui.empty-state icon="reports" title="No sales yet" description="Fast movers appear once orders come in." />
        @else
            <div class="space-y-2">
                @foreach ($fastMoving as $row)
                    <div class="flex items-center gap-3">
                        <x-reports.product-thumb :product="$row['product']" :name="$row['product']->name" />
                        <div class="min-w-0 flex-1"><x-reports.product-link :product="$row['product']" :name="$row['product']->name" class="block truncate text-sm text-content" /></div>
                        <span class="shrink-0 text-sm font-medium text-success">{{ $row['perDay'] }}/day</span>
                        <span class="shrink-0 text-xs text-content-muted">({{ $row['units'] }} total)</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>
</div>
