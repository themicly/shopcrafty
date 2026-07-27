<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Catalog\Models\Attribute;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\Variant;

class VariantManager extends Component
{
    /** Hard ceiling on generated variant combinations (DoS guard — CAT-03). */
    public const MAX_COMBINATIONS = 250;

    public int $productId;

    /** @var array<int, int> selected attribute ids */
    public array $selectedAttributes = [];

    /** @var array<int, array<int, int>> attribute_id => [value_id,...] */
    public array $selectedValues = [];

    /** Editable matrix rows mirroring the DB variants. */
    public array $rows = [];

    /**
     * Variants whose combination fell out of the current selection. Nothing is
     * deleted until the owner confirms — regeneration is additive by default.
     *
     * @var array{ids?: array<int, int>, labels?: array<int, string>}
     */
    public array $pendingRemoval = [];

    public string $bulkStock = '';

    public function mount(int $product): void
    {
        $this->productId = $product;

        $config = Product::findOrFail($product)->variant_config ?? [];

        foreach ($config as $entry) {
            $this->selectedAttributes[] = (int) $entry['attribute_id'];
            $this->selectedValues[(int) $entry['attribute_id']] = array_map('intval', $entry['value_ids'] ?? []);
        }

        $this->loadRows();
    }

    protected function decimals(): int
    {
        return (int) settings('localization.currency_decimals', 2);
    }

    protected function toMajor(?int $minor): string
    {
        return $minor === null ? '' : number_format($minor / (10 ** $this->decimals()), $this->decimals(), '.', '');
    }

    protected function loadRows(): void
    {
        $this->rows = Product::findOrFail($this->productId)->variants->map(fn (Variant $v) => [
            'id' => $v->id,
            'label' => implode(' / ', array_values($v->options)),
            'sku' => (string) $v->sku,
            'price' => $this->toMajor($v->price),
            'compare' => $this->toMajor($v->compare_at_price),
            'stock' => (string) $v->stock_qty,
        ])->all();
    }

    /**
     * One-click starter attributes for the most common apparel setup. Values
     * are ordinary attribute values afterwards, editable in Catalog → Attributes.
     */
    public function addCommonAttributes(): void
    {
        $presets = [
            ['name' => 'Size', 'type' => 'select', 'values' => [['S', null], ['M', null], ['L', null], ['XL', null], ['XXL', null]]],
            ['name' => 'Color', 'type' => 'color', 'values' => [['Black', '#000000'], ['White', '#FFFFFF'], ['Red', '#EF4444'], ['Blue', '#3B82F6']]],
        ];

        foreach ($presets as $preset) {
            $attribute = Attribute::firstOrCreate(['name' => $preset['name']], ['type' => $preset['type']]);

            if ($attribute->values()->doesntExist()) {
                foreach ($preset['values'] as $position => [$value, $color]) {
                    $attribute->values()->create(['value' => $value, 'color_code' => $color, 'position' => $position]);
                }
            }

            if (! in_array($attribute->id, array_map('intval', $this->selectedAttributes), true)) {
                $this->selectedAttributes[] = $attribute->id;
            }
            $this->selectedValues[$attribute->id] = $attribute->values()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $this->dispatch('toast', message: 'Size and Color added — untick any values you don\'t sell, then generate.', type: 'success');
    }

    public function generate(): void
    {
        $this->pendingRemoval = [];

        $product = Product::findOrFail($this->productId);

        $groups = [];
        foreach ($this->selectedAttributes as $attrId) {
            $attribute = Attribute::with('values')->find($attrId);
            $valueIds = $this->selectedValues[$attrId] ?? [];
            $values = $attribute?->values->whereIn('id', $valueIds)->pluck('value')->values()->all() ?? [];

            if (! empty($values)) {
                $groups[$attribute->name] = $values;
            }
        }

        if (empty($groups)) {
            $this->dispatch('toast', message: 'Select at least one attribute value.', type: 'warning');

            return;
        }

        // Guard against a combinatorial explosion before building anything (CAT-03).
        $total = array_product(array_map('count', $groups));
        if ($total > self::MAX_COMBINATIONS) {
            $this->dispatch('toast', message: "That's {$total} combinations — narrow the selection to ".self::MAX_COMBINATIONS.' or fewer.', type: 'warning');

            return;
        }

        // Cartesian product.
        $combos = [[]];
        foreach ($groups as $name => $values) {
            $next = [];
            foreach ($combos as $combo) {
                foreach ($values as $value) {
                    $next[] = $combo + [$name => $value];
                }
            }
            $combos = $next;
        }

        $existing = $product->variants()->get()->keyBy('options_key');
        $keep = [];
        $created = 0;
        $position = 0;

        foreach ($combos as $options) {
            $key = Variant::keyFor($options);
            $keep[] = $key;

            if ($existing->has($key)) {
                // Combination survives — only refresh label/ordering, never
                // touch its sku/price/compare/stock.
                $existing[$key]->update(['options' => $options, 'position' => $position]);
            } else {
                $product->variants()->create([
                    'options' => $options,
                    'options_key' => $key,
                    'price' => $product->price,
                    'stock_qty' => 0,
                    'position' => $position,
                ]);
                $created++;
            }
            $position++;
        }

        // Combinations that fell out of the selection are NOT deleted here —
        // they're queued and only removed via confirmRemoval() after the owner
        // approves the list in a confirm dialog.
        $stale = $existing->reject(fn (Variant $v) => in_array($v->options_key, $keep, true));

        $config = [];
        foreach ($this->selectedAttributes as $attrId) {
            $config[] = ['attribute_id' => (int) $attrId, 'value_ids' => array_map('intval', $this->selectedValues[$attrId] ?? [])];
        }
        $product->update(['variant_config' => $config, 'type' => 'variable']);

        $this->syncParentStock();
        $this->loadRows();

        $message = $created > 0
            ? $created.' new '.($created === 1 ? 'variant' : 'variants').' added — existing variants kept their SKU, price and stock.'
            : 'Variants updated — existing SKU, price and stock kept.';
        $this->dispatch('toast', message: $message, type: 'success');

        if ($stale->isNotEmpty()) {
            $this->pendingRemoval = [
                'ids' => $stale->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
                'labels' => $stale->map(fn (Variant $v) => implode(' / ', array_values($v->options)))->values()->all(),
            ];
            $this->dispatchRemovalConfirm();
        }
    }

    /** Re-open the deletion confirm for the queued variants (from the notice). */
    public function requestRemoval(): void
    {
        if (! empty($this->pendingRemoval['ids'])) {
            $this->dispatchRemovalConfirm();
        }
    }

    protected function dispatchRemovalConfirm(): void
    {
        $count = count($this->pendingRemoval['ids']);
        $message = $count === 1
            ? 'This variant and its stock record will be deleted: '
            : "These {$count} variants and their stock records will be deleted: ";

        $this->dispatch('variant-removal-pending',
            count: $count,
            message: $message.implode(', ', $this->pendingRemoval['labels']).'.',
        );
    }

    /** Destructive half of regeneration — only ever reached via the confirm dialog. */
    public function confirmRemoval(): void
    {
        $ids = array_map('intval', $this->pendingRemoval['ids'] ?? []);
        $this->pendingRemoval = [];

        if (empty($ids)) {
            return;
        }

        // Scoped to this product so tampered ids can't reach another product (CAT-14).
        $deleted = Variant::where('product_id', $this->productId)->whereIn('id', $ids)->delete();

        $this->syncParentStock();
        $this->loadRows();
        $this->dispatch('toast', message: $deleted.' '.($deleted === 1 ? 'variant' : 'variants').' deleted', type: 'success');
    }

    /** Keep the queued variants (owner declined the deletion). */
    public function cancelRemoval(): void
    {
        $this->pendingRemoval = [];
        $this->dispatch('toast', message: 'All variants kept.', type: 'success');
    }

    /**
     * Roll variant stock up into the parent so every storefront read (card,
     * detail, in-stock filter) reflects real availability (CAT-07).
     */
    protected function syncParentStock(): void
    {
        $product = Product::find($this->productId);

        if ($product && $product->variants()->exists()) {
            // Only roll up the stock total — whether that stock BLOCKS ordering is the
            // owner's choice via the "block when out of stock" toggle (track_inventory).
            $product->update([
                'stock_qty' => (int) $product->variants()->sum('stock_qty'),
            ]);
        }
    }

    public function applyBulk(): void
    {
        foreach ($this->rows as $i => $row) {
            if ($this->bulkStock !== '') {
                $this->rows[$i]['stock'] = $this->bulkStock;
            }
        }
    }

    public function saveRows(): void
    {
        foreach ($this->rows as $row) {
            // Scope every mutation to this product so a tampered row id can't edit
            // another product's variants (CAT-14). Price is NOT edited here — all
            // variants share the product's base price (no per-variant pricing).
            Variant::where('product_id', $this->productId)->whereKey($row['id'])->update([
                'sku' => $row['sku'] ?: null,
                'stock_qty' => max(0, (int) ($row['stock'] ?: 0)),
            ]);
        }

        $this->syncParentStock();
        $this->loadRows();
        $this->dispatch('toast', message: 'Variants saved', type: 'success');
    }

    public function deleteVariant(int $id): void
    {
        Variant::where('product_id', $this->productId)->whereKey($id)->delete();
        $this->pendingRemoval = [];
        $this->syncParentStock();
        $this->loadRows();
        $this->dispatch('toast', message: 'Variant deleted', type: 'success');
    }

    public function render()
    {
        return View::make('catalog::livewire.variant-manager', [
            // NB: must not be named `attributes` — inside a component rendered via the
            // <livewire:…> tag that variable is shadowed by Livewire's (empty) attribute
            // bag, which silently forces the empty-state to always show (CAT-16).
            'attributeList' => Attribute::with('values')->orderBy('name')->get(),
            'currencySymbol' => settings('localization.currency_symbol', '$'),
        ]);
    }
}
