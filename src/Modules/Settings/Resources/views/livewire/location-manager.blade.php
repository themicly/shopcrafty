<div class="space-y-5">
    {{-- Level labels --}}
    <x-ui.card title="Address levels" subtitle="Define your country's address hierarchy, top level first (e.g. Division, District, Area).">
        <form wire:submit="saveLevels" class="flex flex-wrap items-end gap-3">
            <div class="min-w-64 flex-1">
                <x-ui.input wire:model="levelsInput" label="Levels (comma separated)" hint="Works for any country — e.g. State, City, Zone" />
            </div>
            <x-ui.save-button target="saveLevels" label="Save levels" />
        </form>
    </x-ui.card>

    @if (empty($levels))
        <x-ui.card>
            <p class="text-sm text-content-muted">Add at least one level above to start building your location tree.</p>
        </x-ui.card>
    @else
        {{-- Tree browser --}}
        <x-ui.card>
            <x-slot:title>
                <div class="flex flex-wrap items-center gap-1.5 text-sm">
                    <button type="button" wire:click="goTo(-1)" class="font-medium text-primary hover:underline">All</button>
                    @foreach ($trail as $i => $crumb)
                        <span class="text-content-muted">/</span>
                        <button type="button" wire:click="goTo({{ $i }})" class="font-medium text-primary hover:underline">{{ $crumb['name'] }}</button>
                    @endforeach
                </div>
            </x-slot:title>
            <x-slot:subtitle>
                @if ($atMaxDepth)
                    Deepest level reached — these are leaf areas.
                @else
                    Managing <span class="font-medium text-content">{{ $currentLevelLabel }}</span> level. Open a row to add its sub-locations.
                @endif
            </x-slot:subtitle>

            {{-- Add form --}}
            <div class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-line bg-surface-sunken/40 p-3">
                <div class="min-w-48 flex-1">
                    <x-ui.input wire:model="newName" :label="'New '.$currentLevelLabel" wire:keydown.enter.prevent="addNode" :error="$errors->first('newName')" />
                </div>
                <div class="w-56">
                    <x-ui.select wire:model="newZoneId" label="Shipping zone (optional)">
                        <option value="">— inherit from parent —</option>
                        @foreach ($zones as $zone)
                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <x-ui.button type="button" wire:click="addNode">Add</x-ui.button>
            </div>

            @if ($nodes->isEmpty())
                <p class="py-4 text-sm text-content-muted">No {{ strtolower($currentLevelLabel) }} entries yet.</p>
            @else
                <x-ui.table flush>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Shipping zone</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($nodes as $node)
                            <tr wire:key="loc-{{ $node->id }}">
                                @if ($editingId === $node->id)
                                    <td><x-ui.input wire:model="editName" wire:keydown.enter.prevent="saveEdit" /></td>
                                    <td>
                                        <x-ui.select wire:model="editZoneId" class="w-48">
                                            <option value="">— inherit —</option>
                                            @foreach ($zones as $zone)
                                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                            @endforeach
                                        </x-ui.select>
                                    </td>
                                    <td colspan="2" class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <x-ui.button type="button" size="sm" variant="ghost" wire:click="$set('editingId', null)">Cancel</x-ui.button>
                                            <x-ui.save-button type="button" target="saveEdit" label="Save" size="sm" wire:click="saveEdit" />
                                        </div>
                                    </td>
                                @else
                                    <td class="font-medium text-content">{{ $node->name }}</td>
                                    <td class="text-content-secondary">{{ $node->shippingZone?->name ?? '—' }}</td>
                                    <td>
                                        @if ($node->is_active)
                                            <x-ui.badge variant="success">Active</x-ui.badge>
                                        @else
                                            <x-ui.badge>Hidden</x-ui.badge>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            @unless ($atMaxDepth)
                                                <x-ui.icon-button icon="eye" variant="ghost" label="Open" wire:click="open({{ $node->id }})" />
                                            @endunless
                                            <x-ui.icon-button icon="pencil" variant="ghost" label="Edit" wire:click="edit({{ $node->id }})" />
                                            <x-ui.button size="sm" variant="ghost" wire:click="toggle({{ $node->id }})">{{ $node->is_active ? 'Hide' : 'Show' }}</x-ui.button>
                                            <x-ui.icon-button icon="trash" variant="danger" label="Delete" wire:click="delete({{ $node->id }})" wire:confirm="Delete this location and everything under it?" />
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </x-ui.table>
            @endif
        </x-ui.card>

        {{-- CSV import --}}
        <x-ui.card title="Bulk upload" subtitle="One row per full path — a column per level, plus an optional trailing Shipping zone column.">
            <p class="mb-3 rounded-lg border border-line bg-surface-sunken/40 px-3 py-2 font-mono text-xs text-content-secondary">
                {{ implode(',', $levels) }},Shipping zone<br>
                @if (count($levels) >= 2){{ $levels[0] }} A,{{ $levels[1] }} 1{{ count($levels) > 2 ? ','.$levels[2].' X' : '' }},Standard shipping @endif
            </p>
            <form wire:submit="import" class="flex flex-wrap items-end gap-3">
                <div class="min-w-56 flex-1">
                    <input type="file" wire:model="csv" accept=".csv,.txt" class="block w-full text-sm text-content-secondary file:mr-3 file:rounded-lg file:border-0 file:bg-surface-sunken file:px-3 file:py-2 file:text-sm file:font-medium">
                    @error('csv')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
                <x-ui.button type="submit" variant="secondary">
                    <span wire:loading.remove wire:target="import">Upload CSV</span>
                    <span wire:loading wire:target="import">Importing…</span>
                </x-ui.button>
            </form>

            @if ($importResult)
                <div class="mt-3 text-sm">
                    <p class="text-success">Added {{ $importResult['created'] }} new location(s).</p>
                    @if (! empty($importResult['errors']))
                        <ul class="mt-1 list-inside list-disc text-warning">
                            @foreach (array_slice($importResult['errors'], 0, 8) as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </x-ui.card>
    @endif
</div>
