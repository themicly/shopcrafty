<div class="relative">
    <div class="mb-4 flex items-center justify-between">
        <p class="text-sm text-content-muted">Organize products into a nested category tree.</p>
        <x-ui.button wire:click="create">
            <x-ui.icon name="plus" class="h-4 w-4" /> Add category
        </x-ui.button>
    </div>

    <x-admin.list-toolbar :count="$categoryCount">
        <div class="w-full max-w-xs">
            <x-ui.input wire:model.live.debounce.300ms="search" type="search" placeholder="Search categories…" aria-label="Search categories" />
        </div>
    </x-admin.list-toolbar>

    <div class="rounded-lg border border-line bg-surface-raised">
        @forelse ($roots as $category)
            @include('catalog::livewire.partials.category-row', ['category' => $category, 'depth' => 0])
        @empty
            @if ($searching)
                <x-ui.empty-state icon="search" title="No matching categories" description="Try a different name, or clear the search to see the full tree." />
            @else
                <x-ui.empty-state icon="products" title="No categories yet" description="Add your first category to organize the catalog." />
            @endif
        @endforelse
    </div>

    {{-- Slide-over form (Livewire-controlled) --}}
    @if ($showForm)
        <div class="fixed inset-0 z-50">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showForm', false)"></div>
            <div class="fixed inset-y-0 right-0 flex w-full max-w-md flex-col border-l border-line bg-surface-overlay shadow-lg">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h3 class="text-sm font-semibold text-content">{{ $editingId ? 'Edit category' : 'New category' }}</h3>
                    <button type="button" wire:click="$set('showForm', false)" class="grid h-8 w-8 place-items-center rounded-md text-content-muted hover:bg-surface-sunken" aria-label="Close">&times;</button>
                </div>

                <form wire:submit="save" class="flex flex-1 flex-col overflow-y-auto">
                    <div class="flex-1 space-y-5 p-5">
                        <x-ui.input wire:model="name" label="Name" required :error="$errors->first('name')" hint="What shoppers see when browsing." />

                        <x-ui.select wire:model="parent_id" label="Parent category" optional :error="$errors->first('parent_id')" hint="Nest this under another category, or leave top-level.">
                            <option value="">— None (top level) —</option>
                            @foreach ($parentOptions as $opt)
                                @if ($opt->id !== $editingId)
                                    <option value="{{ $opt->id }}">{{ $opt->label }}</option>
                                @endif
                            @endforeach
                        </x-ui.select>

                        <div class="space-y-1.5">
                            @if ($this->aiEnabled)
                                <div class="flex justify-end">
                                    <x-admin.ai-button action="generateWithAi" label="Generate with AI" />
                                </div>
                            @endif
                            <x-ui.textarea wire:model="description" label="Description" optional rows="3" hint="{{ $this->aiEnabled ? '“Generate with AI” fills the description and SEO fields from the category name and its products.' : '' }}" />
                        </div>

                        <x-ui.input wire:model="icon" label="Icon (emoji or short glyph)" optional placeholder="e.g. 🛍️" :error="$errors->first('icon')" />

                        <livewire:settings.image-picker wire:model="image_path" label="Feature image" :key="'cat-image-'.($editingId ?? 'new')" />
                        @error('image_path')<p class="text-xs text-danger">{{ $message }}</p>@enderror

                        <x-ui.toggle wire:model="is_active" label="Visible in storefront" />

                        <div class="space-y-4 border-t border-line pt-4">
                            <p class="text-xs font-medium uppercase tracking-wide text-content-muted">SEO</p>
                            <x-ui.input wire:model="seo_title" label="SEO title" optional :error="$errors->first('seo_title')" />
                            <x-ui.textarea wire:model="seo_description" label="SEO description" optional rows="2" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-line px-5 py-4">
                        <x-ui.button type="button" variant="ghost" wire:click="$set('showForm', false)">Cancel</x-ui.button>
                        <x-ui.save-button target="save" label="Save" />
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if (app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->installed('ai') && isset($aiReview))
        <x-admin.ai-review-modal :items="$aiReview" />
    @endif
</div>
