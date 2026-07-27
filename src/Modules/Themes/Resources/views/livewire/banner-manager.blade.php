<div class="relative">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-content-muted">Slider and promo banners</p>
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" /> Add banner
        </x-ui.button>
    </div>

    @if ($banners->isEmpty())
        <div class="rounded-lg border border-line bg-surface-raised">
            <x-ui.empty-state icon="themes" title="No banners yet" description="Add a slider or promo banner to feature on the storefront." />
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($banners as $banner)
                <div wire:key="banner-{{ $banner->id }}" class="flex flex-col overflow-hidden rounded-lg border border-line bg-surface-raised">
                    @if ($banner->image_large)
                        <img src="{{ $banner->image_large }}" alt="{{ $banner->title }}" class="aspect-video w-full rounded-t-lg object-cover">
                    @endif
                    <div class="flex flex-1 flex-col gap-3 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-content">{{ $banner->title ?: 'Untitled banner' }}</p>
                                @if ($banner->subtitle)
                                    <p class="truncate text-xs text-content-muted">{{ $banner->subtitle }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <button wire:click="move({{ $banner->id }}, 'up')" class="text-content-muted hover:text-content" title="Move up" aria-label="Move {{ $banner->title ?: 'banner' }} up">↑</button>
                                <button wire:click="move({{ $banner->id }}, 'down')" class="text-content-muted hover:text-content" title="Move down" aria-label="Move {{ $banner->title ?: 'banner' }} down">↓</button>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-ui.badge variant="info">{{ $banner->placement === 'home_slider' ? 'Home slider' : 'Promo strip' }}</x-ui.badge>
                            @if ($banner->is_active)
                                <x-ui.badge variant="success">Active</x-ui.badge>
                            @else
                                <x-ui.badge>Hidden</x-ui.badge>
                            @endif
                        </div>

                        <div class="mt-auto flex items-center justify-end gap-1 pt-2">
                            <x-ui.button variant="ghost" size="sm" wire:click="toggle({{ $banner->id }})">
                                {{ $banner->is_active ? 'Hide' : 'Show' }}
                            </x-ui.button>
                            <x-ui.icon-button icon="pencil" variant="ghost" label="Edit" wire:click="edit({{ $banner->id }})" />
                            <x-ui.icon-button icon="trash" variant="danger" label="Delete" x-on:click="$dispatch('confirm', { title: 'Delete banner?', message: 'This permanently deletes the banner.', confirmLabel: 'Delete', onConfirm: () => $wire.delete({{ $banner->id }}) })" />
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($showForm)
        <div class="fixed inset-0 z-50">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showForm', false)"></div>
            <div class="fixed inset-y-0 right-0 flex w-full max-w-md flex-col border-l border-line bg-surface-overlay shadow-lg">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h3 class="text-sm font-semibold text-content">{{ $editingId ? 'Edit banner' : 'New banner' }}</h3>
                    <button type="button" wire:click="$set('showForm', false)" class="grid h-8 w-8 place-items-center rounded-md text-content-muted hover:bg-surface-sunken" aria-label="Close">&times;</button>
                </div>
                <form wire:submit="save" class="flex flex-1 flex-col overflow-y-auto">
                    <div class="flex-1 space-y-5 p-5">
                        <x-ui.input wire:model="title" label="Title" />
                        <x-ui.input wire:model="subtitle" label="Subtitle" />

                        <div class="space-y-2">
                            <livewire:settings.image-picker wire:model="imageLarge" label="Large image" rendition="large" required :key="'banner-large-'.($editingId ?? 'new')" />
                            @error('imageLarge') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-2">
                            <livewire:settings.image-picker wire:model="imageSmall" label="Small image (mobile, optional)" rendition="large" :key="'banner-small-'.($editingId ?? 'new')" />
                            @error('imageSmall') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                        </div>

                        <x-ui.input wire:model="linkUrl" label="Link URL" />
                        <x-ui.input wire:model="linkLabel" label="Link label" />

                        <x-ui.select wire:model="placement" label="Placement" required :error="$errors->first('placement')">
                            <option value="home_slider">Home slider</option>
                            <option value="promo_strip">Promo strip</option>
                        </x-ui.select>

                        <x-ui.input type="number" wire:model="sort" label="Sort" />

                        <x-ui.toggle wire:model="isActive" label="Active" />

                        <x-ui.input type="datetime-local" wire:model="startsAt" label="Starts at (optional)" />
                        <x-ui.input type="datetime-local" wire:model="endsAt" label="Ends at (optional)" />
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
