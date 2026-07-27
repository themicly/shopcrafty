<div class="grid gap-6 lg:grid-cols-[320px_1fr]">
    {{-- Attributes list + create --}}
    <div class="space-y-4">
        <x-ui.card title="Attributes">
            @if ($attributeList->isEmpty())
                <p class="py-4 text-center text-sm text-content-muted">No attributes yet.</p>
            @else
                <div class="-mx-2 space-y-1">
                    @foreach ($attributeList as $attribute)
                        <button
                            type="button"
                            wire:click="select({{ $attribute->id }})"
                            @class([
                                'flex w-full items-center justify-between rounded-md px-2 py-2 text-left text-sm',
                                'bg-primary-soft text-primary' => $selectedId === $attribute->id,
                                'text-content-secondary hover:bg-surface-sunken' => $selectedId !== $attribute->id,
                            ])
                        >
                            <span class="font-medium">{{ $attribute->name }}</span>
                            <span class="text-xs text-content-muted">{{ $attribute->type }} · {{ $attribute->values_count }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        <x-ui.card title="New attribute">
            <form wire:submit="addAttribute" class="space-y-4">
                <x-ui.input wire:model="newName" label="Name" placeholder="Color, Size…" :error="$errors->first('newName')" />
                <x-ui.select wire:model="newType" label="Type">
                    <option value="select">Dropdown</option>
                    <option value="color">Color swatch</option>
                    <option value="button">Button</option>
                </x-ui.select>
                <x-ui.save-button target="addAttribute" label="Add attribute" class="w-full" />
            </form>
        </x-ui.card>
    </div>

    {{-- Selected attribute values --}}
    <div>
        @if ($selected)
            <x-ui.card :title="$selected->name . ' values'" :subtitle="ucfirst($selected->type) . ' attribute'">
                <x-slot:actions>
                    <x-ui.icon-button icon="trash" variant="danger" label="Delete attribute" type="button" x-on:click="$dispatch('confirm', { title: 'Delete attribute?', message: 'This permanently deletes the attribute and all of its values.', confirmLabel: 'Delete', onConfirm: () => $wire.deleteAttribute({{ $selected->id }}) })" />
                </x-slot:actions>

                <div class="mb-4 flex flex-wrap gap-2">
                    @forelse ($selected->values as $value)
                        <span class="inline-flex items-center gap-2 rounded-md border border-line bg-surface px-2.5 py-1 text-sm" wire:key="val-{{ $value->id }}">
                            @if ($selected->type === 'color' && $value->color_code)
                                <span class="h-3.5 w-3.5 rounded-full border border-line" style="background: {{ $value->color_code }}"></span>
                            @endif
                            {{ $value->value }}
                            <button type="button" wire:click="deleteValue({{ $value->id }})" class="text-content-muted hover:text-danger" aria-label="Remove {{ $value->value }}">&times;</button>
                        </span>
                    @empty
                        <p class="text-sm text-content-muted">No values yet.</p>
                    @endforelse
                </div>

                <form wire:submit="addValue" class="flex items-end gap-3">
                    <div class="flex-1">
                        <x-ui.input wire:model="newValue" label="Add value" placeholder="e.g. Red, XL" :error="$errors->first('newValue')" />
                    </div>
                    @if ($selected->type === 'color')
                        <input type="color" wire:model="newColor" class="h-9 w-12 cursor-pointer rounded-md border border-line bg-surface-raised">
                    @endif
                    <x-ui.save-button target="addValue" label="Add" />
                </form>
            </x-ui.card>
        @else
            <div class="rounded-lg border border-dashed border-line">
                <x-ui.empty-state title="Select an attribute" description="Choose an attribute on the left to manage its values." />
            </div>
        @endif
    </div>
</div>
