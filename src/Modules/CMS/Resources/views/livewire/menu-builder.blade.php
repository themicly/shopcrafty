<div class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
    {{-- Add item --}}
    <div class="space-y-4">
        <x-ui.card title="Menu">
            <x-ui.select wire:model.live="location" label="Which menu">
                <option value="header">Header navigation</option>
                <option value="footer">Footer links</option>
            </x-ui.select>
        </x-ui.card>

        <x-ui.card title="Add a link">
            <form wire:submit="addItem" class="space-y-4">
                <x-ui.select wire:model.live="linkType" label="Links to">
                    <option value="custom">Custom URL</option>
                    <option value="page">A page</option>
                    <option value="category">A category</option>
                </x-ui.select>

                @if ($linkType === 'custom')
                    <x-ui.input wire:model="url" label="URL" placeholder="/shop or https://…" :error="$errors->first('url')" />
                    <x-ui.input wire:model="label" label="Label" :error="$errors->first('label')" />
                @elseif ($linkType === 'page')
                    <x-ui.select wire:model="targetId" label="Page" :error="$errors->first('targetId')">
                        <option value="">Choose a page…</option>
                        @foreach ($pages as $page)<option value="{{ $page->id }}">{{ $page->title }}</option>@endforeach
                    </x-ui.select>
                    <x-ui.input wire:model="label" label="Label (optional)" />
                @else
                    <x-ui.select wire:model="targetId" label="Category" :error="$errors->first('targetId')">
                        <option value="">Choose a category…</option>
                        @foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
                    </x-ui.select>
                    <x-ui.input wire:model="label" label="Label (optional)" />
                @endif

                <x-ui.select wire:model="parentId" label="Add under" hint="Nest under a top-level item to create a dropdown / mega menu.">
                    <option value="">Top level</option>
                    @foreach ($parents as $p)<option value="{{ $p->id }}">{{ $p->label }}</option>@endforeach
                </x-ui.select>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-content">Tile image <span class="font-normal text-content-muted">(optional)</span></label>
                    @if ($image)
                        <div class="flex items-center gap-3">
                            <img src="{{ $image }}" alt="" class="h-14 w-14 rounded-md object-cover ring-1 ring-black/10">
                            <button type="button" wire:click="clearImage" class="text-sm text-content-muted hover:text-danger">Remove</button>
                        </div>
                    @else
                        <input type="file" wire:model="upload" accept="image/*" class="block w-full text-sm text-content-muted file:mr-3 file:rounded-md file:border-0 file:bg-surface-sunken file:px-3 file:py-1.5 file:text-sm file:text-content">
                        <p wire:loading wire:target="upload" class="mt-1 text-xs text-content-muted">Uploading…</p>
                        @error('upload')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                        <p class="mt-1 text-xs text-content-muted">Shows in mega-menu dropdowns. Category links fall back to the category image.</p>
                    @endif
                </div>

                <x-ui.button type="submit">Add to menu</x-ui.button>
            </form>
        </x-ui.card>
    </div>

    {{-- Current items --}}
    <x-ui.card title="{{ ucfirst($location) }} menu items">
        @if ($items->isEmpty())
            <x-ui.empty-state icon="content" title="No links yet" description="Add links on the left. The storefront falls back to your categories until then." />
        @else
            <div class="divide-y divide-line">
                @foreach ($items as $item)
                    <div wire:key="mi-{{ $item->id }}">
                        <div class="flex items-center gap-3 py-2.5">
                            <div class="flex flex-col">
                                <button wire:click="move({{ $item->id }}, 'up')" class="text-content-muted hover:text-content" @disabled($loop->first) title="Move up" aria-label="Move {{ $item->label }} up">↑</button>
                                <button wire:click="move({{ $item->id }}, 'down')" class="text-content-muted hover:text-content" @disabled($loop->last) title="Move down" aria-label="Move {{ $item->label }} down">↓</button>
                            </div>
                            @if ($item->image)
                                <img src="{{ $item->image }}" alt="" class="h-9 w-9 shrink-0 rounded-md object-cover ring-1 ring-black/10">
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-content">{{ $item->label }}</p>
                                <p class="truncate text-xs text-content-muted">{{ $item->url }}</p>
                            </div>
                            <x-ui.icon-button icon="trash" variant="danger" label="Remove" x-on:click="$dispatch('confirm', { title: 'Remove link?', message: 'This removes the link and its submenu.', confirmLabel: 'Remove', onConfirm: () => $wire.removeItem({{ $item->id }}) })" />
                        </div>

                        {{-- Submenu (one level) --}}
                        @if ($item->children->isNotEmpty())
                            <div class="ml-8 border-l border-line pl-3">
                                @foreach ($item->children as $child)
                                    <div class="flex items-center gap-3 py-2" wire:key="mi-{{ $child->id }}">
                                        <div class="flex flex-col">
                                            <button wire:click="move({{ $child->id }}, 'up')" class="text-content-muted hover:text-content" @disabled($loop->first) title="Move up" aria-label="Move {{ $child->label }} up">↑</button>
                                            <button wire:click="move({{ $child->id }}, 'down')" class="text-content-muted hover:text-content" @disabled($loop->last) title="Move down" aria-label="Move {{ $child->label }} down">↓</button>
                                        </div>
                                        @if ($child->image)
                                            <img src="{{ $child->image }}" alt="" class="h-8 w-8 shrink-0 rounded-md object-cover ring-1 ring-black/10">
                                        @endif
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm text-content">{{ $child->label }}</p>
                                            <p class="truncate text-xs text-content-muted">{{ $child->url }}</p>
                                        </div>
                                        <x-ui.icon-button icon="trash" variant="danger" label="Remove" x-on:click="$dispatch('confirm', { title: 'Remove link?', message: 'This removes the link.', confirmLabel: 'Remove', onConfirm: () => $wire.removeItem({{ $child->id }}) })" />
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>
</div>
