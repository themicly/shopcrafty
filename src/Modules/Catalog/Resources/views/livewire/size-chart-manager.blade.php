<div class="relative">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-content-muted">Reusable measurement grids you attach to categories — shoppers see them on product pages.</p>
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" /> Add size chart
        </x-ui.button>
    </div>

    <x-admin.list-toolbar :count="$charts->count()">
        <div class="w-full max-w-xs">
            <x-ui.input wire:model.live.debounce.300ms="search" type="search" placeholder="Search charts…" aria-label="Search size charts" />
        </div>
    </x-admin.list-toolbar>

    @if ($charts->isEmpty())
        <div class="rounded-lg border border-line bg-surface-raised">
            <x-ui.empty-state icon="products" title="No size charts yet" description="Create a chart once (e.g. T-shirt) and attach it to the categories that need it." />
        </div>
    @else
        <x-ui.table>
            <thead>
                <tr><th>Name</th><th>Measurements</th><th>Sizes</th><th>Used by</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
                @foreach ($charts as $chart)
                    <tr wire:key="size-chart-{{ $chart->id }}">
                        <td class="font-medium text-content">{{ $chart->name }}</td>
                        <td>{{ count($chart->columns ?? []) }} {{ \Illuminate\Support\Str::plural('column', count($chart->columns ?? [])) }}</td>
                        <td>{{ count($chart->rows ?? []) }} {{ \Illuminate\Support\Str::plural('size', count($chart->rows ?? [])) }}</td>
                        <td>
                            @if ($chart->categories_count > 0)
                                {{ $chart->categories_count }} {{ \Illuminate\Support\Str::plural('category', $chart->categories_count) }}
                            @else
                                <span class="text-content-muted">Not attached</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.icon-button icon="pencil" variant="ghost" label="Edit" type="button" wire:click="edit({{ $chart->id }})" />
                                <x-ui.icon-button icon="trash" variant="danger" label="Delete" type="button" x-on:click="$dispatch('confirm', { title: 'Delete size chart?', message: 'Categories using it will simply stop showing a size chart.', confirmLabel: 'Delete', onConfirm: () => $wire.delete({{ $chart->id }}) })" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif

    {{-- Slide-over editor (Livewire-controlled) --}}
    @if ($showForm)
        <div class="fixed inset-0 z-50">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showForm', false)"></div>
            <div class="fixed inset-y-0 right-0 flex w-full max-w-2xl flex-col border-l border-line bg-surface-overlay shadow-lg">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h3 class="text-sm font-semibold text-content">{{ $editingId ? 'Edit size chart' : 'New size chart' }}</h3>
                    <button type="button" wire:click="$set('showForm', false)" class="grid h-8 w-8 place-items-center rounded-md text-content-muted hover:bg-surface-sunken" aria-label="Close">&times;</button>
                </div>

                <form wire:submit="save" class="flex flex-1 flex-col overflow-y-auto">
                    <div class="flex-1 space-y-5 p-5">
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-content-secondary">Start from a template <span class="text-xs font-normal text-content-muted">(replaces the grid below)</span></p>
                            <div class="flex flex-wrap gap-2">
                                <x-ui.button type="button" variant="secondary" size="sm" wire:click="applyTemplate('t-shirt')">T-shirt</x-ui.button>
                                <x-ui.button type="button" variant="secondary" size="sm" wire:click="applyTemplate('shirt')">Shirt</x-ui.button>
                                <x-ui.button type="button" variant="secondary" size="sm" wire:click="applyTemplate('pants')">Pants</x-ui.button>
                            </div>
                        </div>

                        <x-ui.input wire:model="name" label="Name" required :error="$errors->first('name')" hint="A common garment type, e.g. T-shirt — you attach it to categories later." />

                        <x-ui.input wire:model="note" label="Note" optional :error="$errors->first('note')" placeholder="e.g. Measurements in cm" hint="Shown under the chart title on the product page." />

                        {{-- Measurement grid — sunken fill, no inner borders (single-frame rule). --}}
                        <div class="space-y-2">
                            <p class="text-sm font-medium text-content-secondary">Measurements <span class="text-danger" title="Required" aria-hidden="true">*</span></p>
                            <div class="overflow-x-auto rounded-lg bg-surface-sunken p-3">
                                <table class="w-full border-separate" style="border-spacing: 0.375rem;">
                                    <thead>
                                        <tr>
                                            <th class="min-w-[6rem] text-left text-xs font-semibold uppercase tracking-wider text-content-muted">Size</th>
                                            @foreach ($columns as $c => $column)
                                                <th class="min-w-[7rem]" wire:key="col-head-{{ $c }}">
                                                    <div class="flex items-center gap-1">
                                                        <x-ui.input wire:model="columns.{{ $c }}" placeholder="e.g. Chest" aria-label="Measurement column {{ $c + 1 }} name" />
                                                        @if (count($columns) > 1)
                                                            <x-ui.icon-button icon="trash" variant="ghost" label="Remove column" type="button" wire:click="removeColumn({{ $c }})" />
                                                        @endif
                                                    </div>
                                                </th>
                                            @endforeach
                                            <th class="w-9"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rows as $r => $row)
                                            <tr wire:key="row-{{ $r }}">
                                                <td>
                                                    <x-ui.input wire:model="rows.{{ $r }}.label" placeholder="e.g. M" aria-label="Size row {{ $r + 1 }} label" />
                                                </td>
                                                @foreach ($columns as $c => $column)
                                                    <td wire:key="cell-{{ $r }}-{{ $c }}">
                                                        <x-ui.input wire:model="rows.{{ $r }}.values.{{ $c }}" aria-label="{{ ($column ?: 'Column '.($c + 1)).' for size '.($row['label'] ?: 'row '.($r + 1)) }}" />
                                                    </td>
                                                @endforeach
                                                <td class="w-9">
                                                    @if (count($rows) > 1)
                                                        <x-ui.icon-button icon="trash" variant="ghost" label="Remove size" type="button" wire:click="removeRow({{ $r }})" />
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if ($errors->first('columns') || $errors->first('columns.*') || $errors->first('rows') || $errors->first('rows.*.label'))
                                <p class="text-xs text-danger">{{ $errors->first('columns') ?: ($errors->first('columns.*') ?: ($errors->first('rows') ?: $errors->first('rows.*.label'))) }}</p>
                            @endif
                            <div class="flex gap-2">
                                <x-ui.button type="button" variant="ghost" size="sm" wire:click="addColumn"><x-ui.icon name="plus" class="h-4 w-4" /> Add measurement</x-ui.button>
                                <x-ui.button type="button" variant="ghost" size="sm" wire:click="addRow"><x-ui.icon name="plus" class="h-4 w-4" /> Add size</x-ui.button>
                            </div>
                        </div>

                        <p class="text-xs text-content-muted">Fields marked * are required.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-line px-5 py-4">
                        <x-ui.button type="button" variant="ghost" wire:click="$set('showForm', false)">Cancel</x-ui.button>
                        <x-ui.save-button target="save" label="Save" />
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
