<div
    class="space-y-5"
    x-data
    {{-- Server queues deletions, the global confirm bridge presents them; the
         destructive path only ever runs through $wire.confirmRemoval(). --}}
    x-on:variant-removal-pending.window="$dispatch('confirm', {
        title: 'Delete removed variants?',
        message: $event.detail.message,
        confirmLabel: 'Delete ' + $event.detail.count + ($event.detail.count === 1 ? ' variant' : ' variants'),
        onConfirm: () => $wire.confirmRemoval(),
    })"
>
    {{-- Attribute + value selection --}}
    <div>
        <p class="mb-3 text-sm font-medium text-content">Options</p>

        @if ($attributeList->isEmpty())
            <div class="rounded-lg bg-surface-sunken p-4">
                <p class="text-sm text-content-secondary">No attributes yet. Start with the most common ones — every value stays editable in Catalog &rarr; Attributes.</p>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <x-ui.button type="button" variant="secondary" size="sm" wire:click="addCommonAttributes">Add Size &amp; Color</x-ui.button>
                    <span class="text-xs text-content-muted">Size: S, M, L, XL, XXL &middot; Color: Black, White, Red, Blue</span>
                </div>
            </div>
        @else
            <div class="space-y-3">
                @foreach ($attributeList as $attribute)
                    <div class="rounded-lg bg-surface-sunken p-3" wire:key="attr-{{ $attribute->id }}">
                        <label class="flex items-center gap-2 text-sm font-medium text-content">
                            <input type="checkbox" value="{{ $attribute->id }}" wire:model.live="selectedAttributes" class="h-4 w-4 rounded border-line text-primary focus:ring-primary">
                            {{ $attribute->name }}
                        </label>
                        @if (in_array($attribute->id, $selectedAttributes))
                            <div class="mt-2 pl-6">
                                <x-ui.multiselect
                                    :wire-model="'selectedValues.'.$attribute->id"
                                    :options="$attribute->values->map(fn ($v) => ['value' => $v->id, 'label' => $v->value, 'color' => $v->color_code])->all()"
                                    :value="$selectedValues[$attribute->id] ?? []"
                                    :placeholder="'Add '.strtolower($attribute->name).' values…'"
                                />
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-3">
                <x-ui.button wire:click="generate" wire:loading.attr="disabled" wire:target="generate">Generate variants</x-ui.button>
                <span class="text-xs text-content-muted">Existing variants keep their SKU, price and stock — removed combinations are only deleted after you confirm.</span>
            </div>
        @endif
    </div>

    {{-- Variants queued for deletion (regeneration never destroys silently) --}}
    @if (! empty($pendingRemoval['ids']))
        <x-admin.note variant="warning" title="{{ count($pendingRemoval['ids']) }} {{ count($pendingRemoval['ids']) === 1 ? 'variant no longer matches' : 'variants no longer match' }} the selected options">
            {{ implode(', ', $pendingRemoval['labels'] ?? []) }} — nothing is deleted until you confirm.
            <div class="mt-2 flex items-center gap-2">
                <x-ui.button type="button" size="sm" variant="danger" wire:click="requestRemoval">Delete {{ count($pendingRemoval['ids']) === 1 ? 'it' : 'them' }}&hellip;</x-ui.button>
                <x-ui.button type="button" size="sm" variant="ghost" wire:click="cancelRemoval">Keep {{ count($pendingRemoval['ids']) === 1 ? 'it' : 'them' }}</x-ui.button>
            </div>
        </x-admin.note>
    @endif

    {{-- Matrix --}}
    @if (! empty($rows))
        <div>
            <div class="mb-3 flex flex-wrap items-end gap-3">
                <p class="text-sm font-medium text-content">{{ count($rows) }} {{ count($rows) === 1 ? 'variant' : 'variants' }}</p>
                <div class="ml-auto flex items-end gap-2">
                    <div class="w-24"><x-ui.input wire:model="bulkStock" type="number" min="0" label="Set all stock" /></div>
                    <x-ui.button variant="secondary" size="sm" wire:click="applyBulk">Apply</x-ui.button>
                </div>
            </div>

            <x-ui.table>
                <thead>
                    <tr>
                        <th>Variant</th>
                        <th>Qty in stock</th>
                        <th>SKU</th>
                        <th class="text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $i => $row)
                        @php $queued = in_array($row['id'], $pendingRemoval['ids'] ?? []); @endphp
                        <tr wire:key="row-{{ $row['id'] }}" @class(['opacity-60' => $queued])>
                            <td class="font-medium text-content">
                                {{ $row['label'] }}
                                @if ($queued)
                                    <x-ui.badge variant="warning">pending deletion</x-ui.badge>
                                @elseif ((int) ($row['stock'] ?: 0) <= 0)
                                    <x-ui.badge variant="danger">sold out</x-ui.badge>
                                @endif
                            </td>
                            <td><x-ui.input wire:model="rows.{{ $i }}.stock" type="number" min="0" class="!h-8 w-24" /></td>
                            <td><x-ui.input wire:model="rows.{{ $i }}.sku" class="!h-8 w-32" /></td>
                            <td class="text-right">
                                <x-ui.icon-button icon="trash" variant="danger" label="Delete variant" type="button" x-on:click="$dispatch('confirm', { title: 'Delete this variant?', message: 'The variant and its stock record will be permanently removed.', confirmLabel: 'Delete', onConfirm: () => $wire.deleteVariant({{ $row['id'] }}) })" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>

            <p class="mt-2 text-xs text-content-muted">Every variant sells at the product's price (set in the Details tab). Only stock and SKU vary per option.</p>

            <div class="mt-4 flex items-center gap-3">
                <x-ui.save-button type="button" wire:click="saveRows" target="saveRows" label="Save variants" />
                <span wire:dirty class="text-xs font-medium text-warning">Unsaved changes</span>
            </div>
        </div>
    @endif
</div>
