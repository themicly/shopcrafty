<div x-data="{
        v: Date.now(),
        showPreview: false,
        showSettings: false,
        dragI: null,    // an existing block being reordered (its current index)
        dragKey: null,  // a catalog block type being dragged in from the palette
        drop(i) {
            if (this.dragKey !== null) {
                $wire.insertBefore(this.dragKey, i);
                this.dragKey = null;
                return;
            }
            if (this.dragI === null || this.dragI === i) { this.dragI = null; return; }
            const idx = [...$el.querySelectorAll('[data-bidx]')].map(n => Number(n.dataset.bidx));
            const from = idx.indexOf(this.dragI), to = idx.indexOf(i);
            if (from < 0 || to < 0) { this.dragI = null; return; }
            idx.splice(to, 0, idx.splice(from, 1)[0]);
            $wire.reorderBlocks(idx);
            this.dragI = null;
        },
        dropEnd() {
            if (this.dragKey !== null) {
                $wire.addBlock(this.dragKey);
                this.dragKey = null;
                return;
            }
            if (this.dragI === null) return;
            const idx = [...$el.querySelectorAll('[data-bidx]')].map(n => Number(n.dataset.bidx));
            const from = idx.indexOf(this.dragI);
            if (from < 0) { this.dragI = null; return; }
            idx.push(idx.splice(from, 1)[0]);
            $wire.reorderBlocks(idx);
            this.dragI = null;
        },
    }" @preview-updated.window="v = Date.now()" @keydown.escape.window="showSettings = false">
    {{-- Action bar --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.cms.pages.index') }}" class="grid h-9 w-9 place-items-center rounded-md text-content-secondary hover:bg-surface-sunken"><x-ui.icon name="chevron-left" class="h-5 w-5" /></a>
            <div>
                <h2 class="text-lg font-semibold text-content">{{ $title !== '' ? $title : 'New page' }}</h2>
                <div class="mt-0.5">
                    @if ($status === 'published')<x-ui.badge variant="success">Published</x-ui.badge>@else<x-ui.badge variant="warning">Draft</x-ui.badge>@endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <x-ui.button type="button" variant="ghost" @click="showSettings = true" aria-haspopup="dialog">
                <x-ui.icon name="settings" class="h-4 w-4" /> Page settings
            </x-ui.button>
            @if ($pageId)
                <x-ui.button type="button" variant="ghost" @click="showPreview = !showPreview">
                    <span x-text="showPreview ? 'Hide preview' : 'Live preview'"></span>
                </x-ui.button>
            @endif
            <x-ui.button variant="secondary" wire:click="save">Save draft</x-ui.button>
            <x-ui.button wire:click="publish">Publish</x-ui.button>
        </div>
    </div>

    <x-ui.input wire:model.blur="title" label="Page title" :error="$errors->first('title')" class="mb-6 max-w-xl" />

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">
        {{-- Blocks — current page content --}}
        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-content-muted">This page</h3>
            <p class="mb-3 text-xs text-content-muted">Drag blocks from the palette onto the page, or drag rows to reorder.</p>

            <div class="space-y-3" :class="dragKey !== null && 'rounded-lg ring-2 ring-primary/40'">
                @forelse ($blocks as $i => $block)
                    <x-ui.card wire:key="block-{{ $i }}" data-bidx="{{ $i }}"
                        x-on:dragover.prevent x-on:drop.prevent="drop({{ $i }})"
                        x-bind:class="dragI === {{ $i }} ? 'opacity-40' : ''">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <button type="button" draggable="true" x-on:dragstart="dragI = {{ $i }}" x-on:dragend="dragI = null"
                                    class="cursor-grab text-content-muted hover:text-content active:cursor-grabbing" aria-label="Drag to reorder">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><circle cx="9" cy="6" r="1.4"/><circle cx="15" cy="6" r="1.4"/><circle cx="9" cy="12" r="1.4"/><circle cx="15" cy="12" r="1.4"/><circle cx="9" cy="18" r="1.4"/><circle cx="15" cy="18" r="1.4"/></svg>
                                </button>
                                <span class="text-sm font-semibold text-content">{{ $catalog[$block['type']]['label'] ?? $block['type'] }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button wire:click="moveBlock({{ $i }}, 'up')" class="grid h-7 w-7 place-items-center rounded text-content-muted hover:bg-surface-sunken" title="Move up" aria-label="Move block up">↑</button>
                                <button wire:click="moveBlock({{ $i }}, 'down')" class="grid h-7 w-7 place-items-center rounded text-content-muted hover:bg-surface-sunken" title="Move down" aria-label="Move block down">↓</button>
                                <x-ui.icon-button icon="trash" variant="danger" label="Remove" x-on:click="$dispatch('confirm', { title: 'Remove block?', message: 'This removes the block from the page.', confirmLabel: 'Remove', onConfirm: () => $wire.removeBlock({{ $i }}) })" />
                            </div>
                        </div>

                        <div class="space-y-3">
                            @php $fields = $catalog[$block['type']]['fields'] ?? null; @endphp
                            @if ($fields)
                                @foreach ($fields as $field)
                                    <x-admin.field :field="$field"
                                        :wireModel="'blocks.'.$i.'.settings.'.$field['key']"
                                        :value="$block['settings'][$field['key']] ?? null"
                                        :productQuery="$productQuery" :productPath="$productPath" :productMatches="$productMatches" />
                                @endforeach
                            @else
                                {{-- Backward-compat: blocks with no typed schema fall back to inferred controls. --}}
                                @foreach (($block['settings'] ?? []) as $key => $value)
                                    @if (is_bool($value))
                                        <x-ui.toggle wire:model="blocks.{{ $i }}.settings.{{ $key }}" label="{{ ucwords(str_replace('_', ' ', $key)) }}" />
                                    @elseif (! is_array($value))
                                        <x-ui.input wire:model.blur="blocks.{{ $i }}.settings.{{ $key }}" label="{{ ucwords(str_replace('_', ' ', $key)) }}" />
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </x-ui.card>
                @empty
                    <div @dragover.prevent @drop.prevent="dropEnd()"
                        class="rounded-lg border border-dashed border-line p-8 text-center text-sm text-content-muted">
                        No blocks yet — drag one in from the palette on the right.
                    </div>
                @endforelse

                @if (! empty($blocks))
                    {{-- Tail drop zone: drop here to add/move to the end. --}}
                    <div @dragover.prevent @drop.prevent="dropEnd()"
                        x-show="dragI !== null || dragKey !== null" x-cloak
                        class="rounded-lg border-2 border-dashed border-line px-3 py-3 text-center text-xs text-content-muted">
                        Drop here to place at the end
                    </div>
                @endif
            </div>
        </div>

        {{-- Palette — every available block, draggable onto the page --}}
        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-content-muted">Available blocks</h3>
            <div class="max-h-[calc(100vh-11rem)] overflow-y-auto lg:sticky lg:top-4">
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($catalog as $type => $meta)
                        <div draggable="true" @dragstart="dragKey = '{{ $type }}'" @dragend="dragKey = null"
                            :class="dragKey === '{{ $type }}' && 'opacity-40'"
                            class="group relative flex cursor-grab flex-col rounded-lg border border-line bg-surface-raised p-2 transition-colors hover:border-primary/50 active:cursor-grabbing"
                            wire:key="palette-{{ $type }}">
                            <div class="aspect-video w-full">
                                <x-admin.block-preview :type="$type" fill />
                            </div>
                            <div class="mt-2 min-w-0">
                                <p class="truncate text-sm font-medium text-content">{{ $meta['label'] ?? $type }}</p>
                                <p class="line-clamp-2 text-[11px] leading-snug text-content-muted">{{ $meta['description'] ?? 'Drag onto the page' }}</p>
                            </div>
                            <x-ui.icon-button icon="plus" size="sm" variant="primary" label="Add {{ $meta['label'] ?? $type }} to the page"
                                wire:click="addBlock('{{ $type }}')"
                                class="absolute right-1.5 top-1.5 border border-line bg-surface opacity-0 shadow-sm transition-opacity group-hover:opacity-100" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Page settings drawer: type/template + SEO — moved off the main column so
         the block palette can stay permanently visible instead. --}}
    <div x-show="showSettings" x-cloak class="fixed inset-0 z-50">
        <div class="fixed inset-0 bg-black/40" @click="showSettings = false"></div>
        <div x-show="showSettings" x-transition:enter="transition transform duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            class="fixed inset-y-0 right-0 flex w-full max-w-sm flex-col overflow-y-auto border-l border-line bg-surface-overlay shadow-2xl"
            role="dialog" aria-modal="true" aria-label="Page settings">
            <div class="flex items-center justify-between border-b border-line px-4 py-3">
                <h3 class="text-sm font-semibold text-content">Page settings</h3>
                <button type="button" @click="showSettings = false" x-effect="if (showSettings) $nextTick(() => $el.focus())"
                    class="grid h-8 w-8 place-items-center rounded-md text-content-muted hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
                    aria-label="Close">&times;</button>
            </div>
            <div class="flex-1 space-y-4 p-4">
                <x-ui.card title="Page">
                    <div class="space-y-3">
                        <x-ui.select wire:model.blur="type" label="Type">
                            <option value="page">Page</option>
                            <option value="landing">Landing page</option>
                        </x-ui.select>
                        <x-ui.select wire:model.blur="template" label="Template">
                            <option value="default">Default</option>
                            <option value="full-width">Full width</option>
                            <option value="blank">Blank</option>
                        </x-ui.select>
                    </div>
                </x-ui.card>
                <x-ui.card title="SEO">
                    <div class="space-y-3">
                        <x-admin.seo-preview
                            :title="$seoTitle ?: $title"
                            :description="$seoDescription"
                            :url="'pages/'.\Illuminate\Support\Str::slug($title ?: 'page')"
                        />
                        <x-ui.input wire:model.live.debounce.400ms="seoTitle" label="SEO title" hint="Best under 60 characters" />
                        <x-ui.textarea wire:model.live.debounce.400ms="seoDescription" label="Meta description" hint="Best under 155 characters" rows="3" />
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>

    {{-- Live preview drawer — reflects unsaved changes via the session draft (see
         PageBuilder::syncDraft + CMS StorefrontController preview). --}}
    @if ($pageId)
        <div x-show="showPreview" x-cloak x-transition
            class="fixed inset-y-0 right-0 z-40 flex w-full max-w-xl flex-col border-l border-line bg-surface shadow-2xl">
            <div class="flex items-center justify-between border-b border-line px-4 py-2.5">
                <span class="text-sm font-medium text-content">Live preview</span>
                <div class="flex items-center gap-2">
                    <a :href="'{{ url('/pages/'.$slug) }}?preview=1&v=' + v" target="_blank" class="text-xs text-content-muted hover:text-content">Open ↗</a>
                    <button type="button" @click="showPreview = false" class="grid h-7 w-7 place-items-center rounded text-content-muted hover:bg-surface-sunken" aria-label="Close preview">&times;</button>
                </div>
            </div>
            <iframe :src="'{{ url('/pages/'.$slug) }}?preview=1&v=' + v" class="w-full flex-1" title="Page preview"></iframe>
        </div>
    @endif
</div>
