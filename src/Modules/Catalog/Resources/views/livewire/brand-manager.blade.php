<div class="relative">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-content-muted">Brands shown on products and storefront filters.</p>
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" /> Add brand
        </x-ui.button>
    </div>

    @if ($brands->isEmpty())
        <div class="rounded-lg border border-line bg-surface-raised">
            <x-ui.empty-state icon="products" title="No brands yet" description="Add a brand to tag your products." />
        </div>
    @else
        <x-ui.table>
            <thead>
                <tr><th>Logo</th><th>Name</th><th>Status</th><th class="text-right">Actions</th></tr>
            </thead>
            <tbody>
                @foreach ($brands as $brand)
                    <tr wire:key="brand-{{ $brand->id }}">
                        <td>
                            @if ($brand->logo_path)
                                <img src="{{ $brand->logo_path }}" alt="{{ $brand->name }}" class="h-8 w-auto max-w-[6rem] object-contain">
                            @else
                                <span class="text-content-muted">&mdash;</span>
                            @endif
                        </td>
                        <td class="font-medium">{{ $brand->name }}</td>
                        <td>
                            @if ($brand->is_active)
                                <x-ui.badge variant="success">Active</x-ui.badge>
                            @else
                                <x-ui.badge>Hidden</x-ui.badge>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <x-ui.icon-button icon="pencil" variant="ghost" label="Edit" type="button" wire:click="edit({{ $brand->id }})" />
                                <x-ui.icon-button icon="trash" variant="danger" label="Delete" type="button" x-on:click="$dispatch('confirm', { title: 'Delete brand?', message: 'This permanently deletes the brand.', confirmLabel: 'Delete', onConfirm: () => $wire.delete({{ $brand->id }}) })" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
    @endif

    @if ($showForm)
        <div class="fixed inset-0 z-50">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showForm', false)"></div>
            <div class="fixed inset-y-0 right-0 flex w-full max-w-md flex-col border-l border-line bg-surface-overlay shadow-lg">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h3 class="text-sm font-semibold text-content">{{ $editingId ? 'Edit brand' : 'New brand' }}</h3>
                    <button type="button" wire:click="$set('showForm', false)" class="grid h-8 w-8 place-items-center rounded-md text-content-muted hover:bg-surface-sunken" aria-label="Close">&times;</button>
                </div>
                <form wire:submit="save" class="flex flex-1 flex-col">
                    <div class="flex-1 space-y-5 p-5">
                        <x-ui.input wire:model="name" label="Name" :error="$errors->first('name')" />

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-content-secondary">Logo (optional)</label>
                            @if ($logoPath)
                                <div class="flex items-center gap-3">
                                    <img src="{{ $logoPath }}" alt="Logo preview" class="h-12 w-auto max-w-[8rem] rounded-md border border-line object-contain p-1">
                                    <button type="button" wire:click="removeLogo" class="rounded px-2 py-1 text-sm text-danger hover:bg-danger-soft">Remove</button>
                                </div>
                            @endif
                            <label class="flex cursor-pointer flex-col items-center justify-center rounded-md border border-dashed border-line bg-surface-raised px-3 py-6 text-center text-sm text-content-muted hover:border-primary">
                                <input type="file" wire:model="uploadLogo" accept="image/*" class="hidden">
                                <span wire:loading.remove wire:target="uploadLogo">Click to upload a logo</span>
                                <span wire:loading wire:target="uploadLogo" class="text-primary">Uploading…</span>
                            </label>
                            @error('uploadLogo') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        <x-ui.toggle wire:model="is_active" label="Visible in storefront" />
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
