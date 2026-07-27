<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search products…" class="w-56" />
            <label class="flex items-center gap-2 text-sm text-content-secondary">
                <input type="checkbox" wire:model.live="lowOnly"> Low stock only
                @if ($lowCount > 0)<x-ui.badge variant="warning">{{ $lowCount }}</x-ui.badge>@endif
            </label>
        </div>
    </div>

    {{-- Bulk price --}}
    @if (! empty($selected))
        <x-ui.card>
            <div class="flex flex-wrap items-end gap-3">
                <p class="text-sm text-content-secondary">{{ count($selected) }} selected</p>
                <x-ui.select wire:model="priceMode" label="Price change" class="w-40">
                    <option value="percent">Adjust by %</option>
                    <option value="set">Set price to</option>
                </x-ui.select>
                <x-ui.input wire:model="priceValue" type="number" step="0.01" label=" " placeholder="{{ $priceMode === 'percent' ? 'e.g. -10' : 'e.g. 29.00' }}" class="w-36" :error="$errors->first('priceValue')" />
                <x-ui.button wire:click="applyBulkPrice">Apply</x-ui.button>
            </div>
        </x-ui.card>
    @endif

    <x-ui.table>
        <thead>
            <tr>
                <th class="w-8"></th>
                <th>Product</th>
                <th>In stock</th>
                <th>Adjust</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($products as $product)
                @php
                    $low = $product->stock_qty <= $product->low_stock_threshold;
                    $isVariable = $product->type === 'variable';
                @endphp
                <tr wire:key="row-{{ $product->id }}" @class(['align-top' => $isVariable])>
                    <td>
                        <input type="checkbox" wire:model.live="selected" value="{{ $product->id }}">
                    </td>
                    <td>
                        <div @if ($isVariable) x-data="{ open: false }" @endif>
                            <div class="flex items-center gap-2">
                                @if ($isVariable)
                                    <button type="button" @click="open = !open"
                                        class="grid h-5 w-5 shrink-0 place-items-center rounded text-content-muted transition-transform hover:bg-surface-sunken hover:text-content"
                                        :class="open && 'rotate-90'" :aria-expanded="open.toString()"
                                        aria-label="Toggle variant breakdown">
                                        <x-ui.icon name="chevron-left" class="h-3.5 w-3.5 rotate-180" />
                                    </button>
                                @endif
                                <div class="min-w-0">
                                    <a href="{{ route('admin.catalog.products.edit', $product->id) }}" class="font-medium text-content hover:text-primary">{{ $product->name }}</a>
                                    <div class="text-xs text-content-muted">{{ $product->sku ?? '—' }} · {{ format_money($product->price) }}</div>
                                </div>
                                @if ($isVariable)
                                    <x-ui.badge variant="info" class="shrink-0">{{ $product->variants->count() }} {{ \Illuminate\Support\Str::plural('variant', $product->variants->count()) }}</x-ui.badge>
                                @endif
                            </div>

                            @if ($isVariable)
                                {{-- Expandable variant breakdown: this product's stock_qty is a
                                     rolled-up sum, so it's adjusted per-variant here instead. --}}
                                <div x-show="open" x-transition x-cloak class="mt-3 space-y-2 border-l-2 border-line pl-4">
                                    @forelse ($product->variants as $variant)
                                        @php $variantLow = $variant->stock_qty <= $product->low_stock_threshold; @endphp
                                        <div class="flex flex-wrap items-center gap-2 rounded-md bg-surface-sunken/40 px-2.5 py-2" wire:key="variant-{{ $variant->id }}">
                                            <span class="min-w-0 flex-1 truncate text-sm text-content">{{ implode(' / ', array_values($variant->options)) }}</span>
                                            <span @class(['w-8 shrink-0 text-right text-sm font-semibold tabular-nums', 'text-warning' => $variantLow, 'text-content' => ! $variantLow])>{{ $variant->stock_qty }}</span>
                                            <div class="flex shrink-0 items-center gap-1">
                                                <button type="button" wire:click="quickAdjustVariant({{ $variant->id }}, -1)"
                                                    class="grid h-6 w-6 place-items-center rounded border border-line text-content-secondary hover:border-danger hover:text-danger disabled:opacity-30"
                                                    @if ($variant->stock_qty <= 0) disabled @endif aria-label="Decrease by 1">−</button>
                                                <button type="button" wire:click="quickAdjustVariant({{ $variant->id }}, 1)"
                                                    class="grid h-6 w-6 place-items-center rounded border border-line text-content-secondary hover:border-success hover:text-success" aria-label="Increase by 1">+</button>
                                            </div>
                                            <input type="number" wire:model="variantDelta.{{ $variant->id }}" placeholder="±0"
                                                class="h-7 w-16 shrink-0 rounded-md border border-line bg-surface-raised px-2 text-xs text-content">
                                            <input type="text" wire:model="variantReason.{{ $variant->id }}" placeholder="reason"
                                                class="h-7 w-24 shrink-0 rounded-md border border-line bg-surface-raised px-2 text-xs text-content">
                                            <x-ui.button size="sm" variant="ghost" wire:click="applyVariantAdjust({{ $variant->id }})">Apply</x-ui.button>
                                        </div>
                                    @empty
                                        <p class="text-xs text-content-muted">No variants generated yet.</p>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span @class(['font-semibold', 'text-warning' => $low, 'text-content' => ! $low])>{{ $product->stock_qty }}</span>
                        @if ($low)<span class="ml-1 text-xs text-warning">low</span>@endif
                        @if ($isVariable)<div class="text-[11px] text-content-muted">total across variants</div>@endif
                    </td>
                    <td>
                        @if ($isVariable)
                            <span class="text-xs text-content-muted">Adjust per variant &larr;</span>
                        @else
                            <div class="flex items-center gap-1.5">
                                <button type="button" wire:click="quickAdjust({{ $product->id }}, -1)"
                                    class="grid h-7 w-7 shrink-0 place-items-center rounded border border-line text-content-secondary hover:border-danger hover:text-danger disabled:opacity-30"
                                    @if ($product->stock_qty <= 0) disabled @endif aria-label="Decrease by 1">−</button>
                                <button type="button" wire:click="quickAdjust({{ $product->id }}, 1)"
                                    class="grid h-7 w-7 shrink-0 place-items-center rounded border border-line text-content-secondary hover:border-success hover:text-success" aria-label="Increase by 1">+</button>
                                <input type="number" wire:model="delta.{{ $product->id }}" placeholder="±0"
                                    class="h-8 w-16 rounded-md border border-line bg-surface-raised px-2 text-sm text-content">
                                <input type="text" wire:model="reason.{{ $product->id }}" placeholder="reason"
                                    class="h-8 w-28 rounded-md border border-line bg-surface-raised px-2 text-sm text-content">
                                <x-ui.button size="sm" variant="ghost" wire:click="applyAdjust({{ $product->id }})">Apply</x-ui.button>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </x-ui.table>
    <div>{{ $products->links() }}</div>

    {{-- Recent adjustments --}}
    @if ($adjustments->isNotEmpty())
        <x-ui.card title="Recent stock adjustments">
            <div class="divide-y divide-line text-sm">
                @foreach ($adjustments as $adj)
                    <div class="flex items-center justify-between gap-3 py-2">
                        <span class="min-w-0 truncate text-content">
                            {{ $adj->product?->name ?? '—' }}
                            @if ($adj->variant)
                                <span class="text-content-muted">({{ implode(' / ', array_values($adj->variant->options)) }})</span>
                            @endif
                        </span>
                        <span class="shrink-0 text-content-muted">
                            <span class="{{ $adj->delta >= 0 ? 'text-success' : 'text-danger' }}">{{ $adj->delta >= 0 ? '+' : '' }}{{ $adj->delta }}</span>
                            → {{ $adj->after_qty }}
                            @if ($adj->reason)· {{ $adj->reason }}@endif
                            · {{ $adj->created_at?->diffForHumans() }}
                        </span>
                    </div>
                @endforeach
            </div>
        </x-ui.card>
    @endif
</div>
