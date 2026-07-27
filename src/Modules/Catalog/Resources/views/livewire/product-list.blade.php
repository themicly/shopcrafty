<div>
    @php
        $decimals = (int) settings('localization.currency_decimals', 2);
        $symbol = (string) settings('localization.currency_symbol', '$');
        $compact = function ($n) {
            $a = abs($n);
            if ($a >= 1000000) return number_format($n / 1000000, 1).'M';
            if ($a >= 1000) return number_format($n / 1000, 1).'k';
            return (string) round($n);
        };
        $inventoryValue = $symbol.$compact($stats['value'] / (10 ** $decimals));

        $stockPill = [
            'success' => 'border-success/30 bg-success-soft text-success',
            'warning' => 'border-warning/30 bg-warning-soft text-warning',
            'danger' => 'border-danger/30 bg-danger-soft text-danger',
        ];
        $stockState = function ($p) {
            if ($p->track_inventory && $p->stock_qty <= 0) return ['danger', 'Out of Stock'];
            if ($p->isLowStock()) return ['warning', 'Low Stock'];
            return ['success', 'In Stock'];
        };
    @endphp

    {{-- Summary cards --}}
    <div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-ui.stat-card :value="number_format($stats['total'])" label="Total Products" />
        <x-ui.stat-card :value="number_format($stats['stock'])" label="Total Stock" />
        <x-ui.stat-card :value="number_format($stats['low'])" label="Low / Out of Stock" accent="warning" />
        <x-ui.stat-card :value="$inventoryValue" label="Inventory Value" accent="success" />
    </div>

    {{-- Search + filters + view toggle --}}
    <div class="mb-4 flex flex-wrap items-center gap-2">
        <div class="min-w-56 max-w-sm flex-1">
            <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Search products, SKU…" />
        </div>
        <x-ui.select wire:model.live="categoryFilter" class="w-48">
            <option value="">All Categories</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </x-ui.select>
        <x-ui.select wire:model.live="statusFilter" class="w-36">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="archived">Archived</option>
        </x-ui.select>
        <div class="inline-flex rounded-lg border border-line bg-surface-raised p-0.5">
            <button type="button" wire:click="$set('view', 'table')" @class(['rounded-md px-3 py-1 text-sm font-medium transition-colors', 'bg-surface-sunken text-content' => $view === 'table', 'text-content-muted hover:text-content' => $view !== 'table'])>Table</button>
            <button type="button" wire:click="$set('view', 'grid')" @class(['rounded-md px-3 py-1 text-sm font-medium transition-colors', 'bg-surface-sunken text-content' => $view === 'grid', 'text-content-muted hover:text-content' => $view !== 'grid'])>Grid</button>
        </div>
    </div>

    {{-- Count + actions --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-content-muted"><span class="font-semibold text-content">{{ number_format($products->total()) }}</span> products</p>
        <div class="flex flex-wrap items-center gap-2">
            <select wire:model.live="perPage" class="h-9 rounded-lg border border-line bg-surface-raised px-3 text-sm text-content-secondary focus:outline-none">
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
                <option value="100">100 / page</option>
            </select>
            <x-admin.toolbar-button icon="upload" color="info" x-data @click="$dispatch('open-drawer', 'import-products')">Import</x-admin.toolbar-button>
            <x-admin.toolbar-button icon="download" color="success" :href="route('admin.catalog.products.export')">Export</x-admin.toolbar-button>
            <x-admin.toolbar-button icon="printer" color="primary" onclick="window.print()">Print</x-admin.toolbar-button>
            <x-admin.toolbar-primary :href="route('admin.catalog.products.create')">Add Product</x-admin.toolbar-primary>
        </div>
    </div>

    @if ($products->isEmpty())
        <div class="rounded-2xl border border-line bg-surface-raised">
            <x-ui.empty-state icon="products" title="No products found" description="Adjust your filters, or add your first product.">
                <x-slot:action>
                    <x-ui.button :href="route('admin.catalog.products.create')" size="sm">Add product</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    @elseif ($view === 'grid')
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            @foreach ($products as $product)
                @php $img = $product->media->first()?->path; [$sv, $slabel] = $stockState($product); $onSale = $product->compare_at_price && $product->compare_at_price > $product->price; @endphp
                <a href="{{ route('admin.catalog.products.edit', $product) }}" wire:key="pg-{{ $product->id }}" class="group rounded-2xl border border-line bg-surface-raised p-3 shadow-sm transition-shadow hover:shadow-md">
                    <div class="mb-3 grid aspect-square place-items-center overflow-hidden rounded-xl bg-success-soft text-lg font-semibold text-success">
                        @if ($img)<img src="{{ $img }}" alt="" class="h-full w-full object-cover">@else{{ strtoupper(substr($product->name, 0, 1)) }}@endif
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="truncate text-sm font-semibold text-content">{{ $product->name }}</span>
                        @if ($product->type === 'variable')<x-ui.badge variant="primary">Variable</x-ui.badge>
                        @elseif ($product->type === 'digital')<x-ui.badge variant="info">Digital</x-ui.badge>@endif
                    </div>
                    <div class="mt-0.5 text-xs text-content-muted">{{ $product->sku ?: '—' }}</div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="font-semibold text-success">{{ format_money($product->price) }}</span>
                        <span class="inline-flex rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $stockPill[$sv] }}">{{ $slabel }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-4">{{ $products->links() }}</div>
    @else
        <x-ui.table printable title="Products">
            <thead>
                <tr>
                    <th class="w-10 print:hidden"></th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th class="text-right">Price</th>
                    <th class="text-right">Stock</th>
                    <th>Status</th>
                    <th class="text-right print:hidden">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    @php
                        $img = $product->media->first()?->path;
                        $onSale = $product->compare_at_price && $product->compare_at_price > $product->price;
                        [$sv, $slabel] = $stockState($product);
                        $cat = $product->category;
                        $parent = $cat?->parent;
                        $barPct = max(4, min(100, (int) $product->stock_qty));
                        $barColor = $sv === 'danger' ? 'bg-danger' : ($sv === 'warning' ? 'bg-warning' : 'bg-success');
                        $numColor = $sv === 'success' ? 'text-content' : ($sv === 'warning' ? 'text-warning' : 'text-danger');
                    @endphp
                    <tr wire:key="p-{{ $product->id }}">
                        <td class="print:hidden">
                            <input type="checkbox" value="{{ $product->id }}" wire:model.live="selected" class="h-4 w-4 rounded border-line text-primary focus:ring-primary">
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="grid h-10 w-10 shrink-0 place-items-center overflow-hidden rounded-xl bg-success-soft text-sm font-semibold text-success">
                                    @if ($img)<img src="{{ $img }}" alt="" class="h-full w-full object-cover">@else{{ strtoupper(substr($product->name, 0, 1)) }}@endif
                                </span>
                                <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1">
                                    <a href="{{ route('admin.catalog.products.edit', $product) }}" class="font-semibold text-content hover:text-primary hover:underline focus-visible:underline focus-visible:outline-none">{{ $product->name }}</a>
                                    @if ($product->type === 'variable')<x-ui.badge variant="primary">Variable</x-ui.badge>
                                    @elseif ($product->type === 'digital')<x-ui.badge variant="info">Digital</x-ui.badge>@endif
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap font-mono text-xs text-content-muted">{{ $product->sku ?: '—' }}</td>
                        <td>
                            @if ($cat)
                                <span class="inline-flex items-center gap-1.5 text-sm text-content-secondary">
                                    <span class="rounded-md bg-surface-sunken px-2 py-0.5 text-xs font-medium text-content-secondary">{{ $parent?->name ?? $cat->name }}</span>
                                    @if ($parent)<span class="text-content-muted">&rsaquo; {{ $cat->name }}</span>@endif
                                </span>
                            @else
                                <span class="text-content-muted">—</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="font-semibold text-success">{{ format_money($product->price) }}</div>
                            @if ($onSale)
                                <div class="text-xs text-content-muted line-through">{{ format_money($product->compare_at_price) }}</div>
                            @endif
                        </td>
                        <td class="text-right">
                            @if ($product->track_inventory)
                                <div class="font-semibold tabular-nums {{ $numColor }}">{{ $product->stock_qty }}</div>
                                <div class="ml-auto mt-1 h-1 w-16 overflow-hidden rounded-full bg-surface-sunken">
                                    <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $barPct }}%"></div>
                                </div>
                            @else
                                <span class="text-content-muted">∞</span>
                            @endif
                        </td>
                        <td>
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold {{ $stockPill[$sv] }}">{{ $slabel }}</span>
                        </td>
                        <td class="whitespace-nowrap text-right print:hidden">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.icon-button icon="pencil" variant="ghost" label="Edit" :href="route('admin.catalog.products.edit', $product)" />
                                <x-ui.icon-button icon="copy" variant="ghost" label="Duplicate" type="button" wire:click="clone({{ $product->id }})" />
                                <x-ui.icon-button icon="trash" variant="danger" label="Delete" type="button" x-on:click="$dispatch('confirm', { title: 'Delete product?', message: 'This permanently deletes the product and cannot be undone.', confirmLabel: 'Delete', onConfirm: () => $wire.delete({{ $product->id }}) })" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <div class="mt-4">{{ $products->links() }}</div>
    @endif

    {{-- Floating bulk actions --}}
    @if (count($selected) > 0)
        <x-ui.bulk-action-bar :count="count($selected)">
            <button wire:click="bulk('activate')" class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">Activate</button>
            <button wire:click="bulk('archive')" class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">Archive</button>
            <button type="button" x-data @click="$dispatch('open-modal', 'bulk-edit')" class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">Edit fields</button>
            <button type="button" x-on:click="$dispatch('confirm', { title: 'Delete selected products?', message: 'This permanently deletes every selected product.', confirmLabel: 'Delete', onConfirm: () => $wire.bulk('delete') })" class="rounded-lg px-2.5 py-1 text-sm font-medium text-danger transition-colors hover:bg-danger/20">Delete</button>
            <button wire:click="$set('selected', [])" class="rounded-lg px-2 py-1 text-surface/60 transition-colors hover:bg-surface/15" aria-label="Clear">&times;</button>
        </x-ui.bulk-action-bar>
    @endif

    {{-- Bulk field edit modal (category / price / status) --}}
    <x-ui.modal name="bulk-edit" title="Edit {{ count($selected) }} selected products" maxWidth="lg">
        <div class="space-y-4">
            <p class="text-sm text-content-muted">Leave a field on “No change” to keep it as-is.</p>

            <x-ui.select wire:model="bulkCategory" label="Category">
                <option value="">No change</option>
                <option value="0">Uncategorized</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model="bulkStatus" label="Status">
                <option value="">No change</option>
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="archived">Archived</option>
            </x-ui.select>

            <div>
                <label class="block text-sm font-medium text-content-secondary">Price</label>
                <div class="mt-1.5 flex gap-2">
                    <x-ui.select wire:model.live="bulkPriceMode" class="w-44">
                        <option value="">No change</option>
                        <option value="set">Set to</option>
                        <option value="increase">Increase by %</option>
                        <option value="decrease">Decrease by %</option>
                    </x-ui.select>
                    @if ($bulkPriceMode !== '')
                        <div class="flex-1">
                            <x-ui.input
                                type="number"
                                step="0.01"
                                min="0"
                                wire:model="bulkPriceValue"
                                :error="$errors->first('bulkPriceValue')"
                                placeholder="{{ $bulkPriceMode === 'set' ? 'Amount' : 'Percent' }}"
                            />
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <x-slot:footer>
            <x-ui.button variant="ghost" x-on:click="$dispatch('close-modal', 'bulk-edit')">Cancel</x-ui.button>
            <x-ui.button wire:click="bulkEdit">Apply changes</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
