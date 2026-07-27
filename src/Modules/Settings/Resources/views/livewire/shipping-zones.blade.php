<div class="max-w-3xl space-y-6">
    <x-ui.card title="Shipping zones" subtitle="Flat rate per zone, with an optional free-shipping threshold.">
        <x-slot:actions>
            <x-ui.button size="sm" wire:click="create">Add zone</x-ui.button>
        </x-slot:actions>

        @if ($showForm)
            <form wire:submit="save" class="mb-6 space-y-4 rounded-lg border border-primary/30 bg-primary-soft/30 p-4">
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-ui.input wire:model="name" label="Zone name" :error="$errors->first('name')" />
                    <x-ui.money-input wire:model="rate" label="Rate" :symbol="$symbol" :error="$errors->first('rate')" />
                    <x-ui.money-input wire:model="freeAbove" label="Free above (optional)" :symbol="$symbol" :error="$errors->first('freeAbove')" />
                </div>
                <x-ui.toggle wire:model="isActive" label="Active" />
                <div class="flex items-center gap-3">
                    <x-ui.save-button target="save" label="Save zone" />
                    <x-ui.button type="button" variant="ghost" wire:click="$set('showForm', false)">Cancel</x-ui.button>
                </div>
            </form>
        @endif

        @if ($zones->isEmpty())
            <x-ui.empty-state icon="orders" title="No shipping zones yet" description="Add a zone so customers can be charged for delivery." />
        @else
            <x-ui.table flush>
                <thead>
                    <tr>
                        <th>Zone</th>
                        <th>Rate</th>
                        <th>Free above</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($zones as $zone)
                        <tr>
                            <td class="font-medium">{{ $zone->name }}</td>
                            <td>{{ format_money((int) $zone->rate) }}</td>
                            <td>{{ $zone->free_above !== null ? format_money((int) $zone->free_above) : '—' }}</td>
                            <td>
                                <button type="button" wire:click="toggle({{ $zone->id }})">
                                    <x-ui.badge :variant="$zone->is_active ? 'success' : 'neutral'">
                                        {{ $zone->is_active ? 'Active' : 'Off' }}
                                    </x-ui.badge>
                                </button>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <x-ui.button size="sm" variant="ghost" icon wire:click="move({{ $zone->id }}, 'up')" title="Move up" aria-label="Move {{ $zone->name }} up">↑</x-ui.button>
                                    <x-ui.button size="sm" variant="ghost" icon wire:click="move({{ $zone->id }}, 'down')" title="Move down" aria-label="Move {{ $zone->name }} down">↓</x-ui.button>
                                    <x-ui.icon-button icon="pencil" variant="ghost" label="Edit" wire:click="edit({{ $zone->id }})" />
                                    <x-ui.icon-button icon="trash" variant="danger" label="Delete" wire:click="delete({{ $zone->id }})" wire:confirm="Delete this zone?" />
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </x-ui.table>
        @endif
    </x-ui.card>
</div>
