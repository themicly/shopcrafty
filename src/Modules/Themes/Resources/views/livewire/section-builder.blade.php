<div x-data="{
        dragId: null,   // an existing section being reordered
        dragKey: null,  // a catalog section being dragged in from the palette
        drop(id) {
            if (this.dragKey !== null) {
                $wire.insertBefore(this.dragKey, id);
                this.dragKey = null;
                return;
            }
            if (this.dragId === null || this.dragId === id) { this.dragId = null; return; }
            const ids = [...$el.querySelectorAll('[data-sid]')].map(n => Number(n.dataset.sid));
            const from = ids.indexOf(this.dragId), to = ids.indexOf(id);
            if (from < 0 || to < 0) { this.dragId = null; return; }
            ids.splice(to, 0, ids.splice(from, 1)[0]);
            $wire.reorder(ids);
            this.dragId = null;
        },
        dropEnd() {
            if (this.dragKey !== null) {
                $wire.add(this.dragKey);
                this.dragKey = null;
                return;
            }
            if (this.dragId === null) return;
            const ids = [...$el.querySelectorAll('[data-sid]')].map(n => Number(n.dataset.sid));
            const from = ids.indexOf(this.dragId);
            if (from < 0) { this.dragId = null; return; }
            ids.push(ids.splice(from, 1)[0]);
            $wire.reorder(ids);
            this.dragId = null;
        },
    }">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-3">
            @if ($activeTheme)
                {{-- Sections belong to the active theme — make that visible. --}}
                @php $themeAccent = app(\Themicly\Shopcrafty\Modules\Themes\Services\ThemeService::class)->metadata($activeTheme->slug)['settings']['accent'] ?? '#6d28d9'; @endphp
                <a href="{{ route('admin.themes.index') }}" title="Change theme"
                    class="inline-flex items-center gap-2 rounded-full border border-line bg-surface-raised px-3 py-1.5 text-xs font-medium text-content transition-colors hover:border-primary/50">
                    <span class="h-2.5 w-2.5 rounded-full ring-1 ring-black/10" style="background: {{ $themeAccent }}"></span>
                    {{ $activeTheme->name }} theme
                    <span class="text-content-muted">· change</span>
                </a>
            @endif
            <p class="text-sm text-content-muted">Drag sections from the palette onto your homepage, or drag rows to reorder.</p>
        </div>
        <x-ui.button variant="secondary" :href="url('/')" target="_blank" rel="noopener">
            Open website preview
            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="ml-1.5 h-3.5 w-3.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
        </x-ui.button>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px]">

        {{-- Homepage — current sections --}}
        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-content-muted">Your homepage</h3>
            <div class="divide-y divide-line rounded-lg border border-line bg-surface-raised"
                :class="dragKey !== null && 'ring-2 ring-primary/40'">
                @forelse ($sections as $section)
                    <div data-sid="{{ $section->id }}"
                        @dragover.prevent @drop.prevent="drop({{ $section->id }})"
                        :class="dragId === {{ $section->id }} && 'opacity-40'"
                        class="flex items-center gap-3 px-3 py-2.5 {{ $section->is_enabled ? '' : 'opacity-60' }}" wire:key="section-{{ $section->id }}">
                        <button type="button" draggable="true" @dragstart="dragId = {{ $section->id }}" @dragend="dragId = null"
                            class="cursor-grab text-content-muted hover:text-content active:cursor-grabbing" aria-label="Drag to reorder">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><circle cx="9" cy="6" r="1.4"/><circle cx="15" cy="6" r="1.4"/><circle cx="9" cy="12" r="1.4"/><circle cx="15" cy="12" r="1.4"/><circle cx="9" cy="18" r="1.4"/><circle cx="15" cy="18" r="1.4"/></svg>
                        </button>
                        <x-admin.section-preview :type="$section->section_key" />
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-content">{{ $catalog[$section->section_key]['label'] ?? $section->section_key }}</p>
                            @unless ($section->is_enabled)<span class="text-xs text-content-muted">Hidden</span>@endunless
                        </div>
                        <x-ui.toggle
                            :checked="$section->is_enabled"
                            wire:click="toggle({{ $section->id }})"
                            aria-label="{{ $section->is_enabled ? 'Hide section' : 'Show section' }}"
                            title="{{ $section->is_enabled ? 'Shown on the homepage' : 'Hidden' }}"
                        />
                        <x-ui.icon-button icon="pencil" label="Edit section" wire:click="edit({{ $section->id }})" />
                        <x-ui.icon-button icon="copy" label="Duplicate section" wire:click="duplicate({{ $section->id }})" />
                        <x-ui.icon-button icon="trash" variant="danger" label="Remove section"
                            x-on:click="$dispatch('confirm', { title: 'Remove section?', message: 'This removes the section from your homepage.', confirmLabel: 'Remove', variant: 'danger', onConfirm: () => $wire.delete({{ $section->id }}) })" />
                    </div>
                @empty
                    <div @dragover.prevent @drop.prevent="dropEnd()"
                        class="px-4 py-10 text-center text-sm text-content-muted">
                        No sections yet — drag one in from the palette, or use its “+ Add” button.
                    </div>
                @endforelse

                @if ($sections->isNotEmpty())
                    {{-- Tail drop zone: drop here to add/move to the end. --}}
                    <div @dragover.prevent @drop.prevent="dropEnd()"
                        x-show="dragId !== null || dragKey !== null" x-cloak
                        class="border-2 border-dashed border-line px-3 py-3 text-center text-xs text-content-muted">
                        Drop here to place at the end
                    </div>
                @endif
            </div>
        </div>

        {{-- Palette — available sections with sample previews --}}
        <div>
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-content-muted">Available sections</h3>
            {{-- One frame per item: the container is unframed and each card carries
                 the single border (its inner preview is flat). --}}
            <div class="max-h-[calc(100vh-11rem)] overflow-y-auto lg:sticky lg:top-4">
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($catalog as $key => $meta)
                        <div draggable="true" @dragstart="dragKey = '{{ $key }}'" @dragend="dragKey = null"
                            :class="dragKey === '{{ $key }}' && 'opacity-40'"
                            class="relative flex cursor-grab flex-col rounded-lg border border-line bg-surface-raised p-2 transition-colors hover:border-primary/40 active:cursor-grabbing"
                            wire:key="palette-{{ $key }}">
                            {{-- Square sample preview on top --}}
                            <div class="aspect-square w-full">
                                <x-admin.section-preview :type="$key" fill />
                            </div>
                            {{-- Name + subtitle at the bottom --}}
                            <div class="mt-2 min-w-0">
                                <p class="truncate text-sm font-medium text-content">{{ $meta['label'] ?? $key }}</p>
                                <p class="line-clamp-2 text-[11px] leading-snug text-content-muted">{{ $meta['description'] ?? 'Drag onto the page' }}</p>
                            </div>
                            <x-ui.icon-button icon="plus" size="sm" variant="primary" label="Add {{ $meta['label'] ?? $key }} to the homepage"
                                wire:click="add('{{ $key }}')"
                                class="absolute right-1.5 top-1.5 border border-line bg-surface shadow-sm" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-50">
            <div class="fixed inset-0 bg-black/50" wire:click="$set('showForm', false)"></div>
            <div class="fixed inset-y-0 right-0 flex w-full max-w-md flex-col border-l border-line bg-surface-overlay shadow-lg">
                <div class="flex items-center justify-between border-b border-line px-5 py-4">
                    <h3 class="text-sm font-semibold text-content">Edit {{ $editingLabel ?: 'section' }}</h3>
                    <button wire:click="$set('showForm', false)" class="grid h-8 w-8 place-items-center rounded-md text-content-muted hover:bg-surface-sunken" aria-label="Close">&times;</button>
                </div>
                <form wire:submit="save" class="flex flex-1 flex-col overflow-y-auto">
                    <div class="flex-1 space-y-5 p-5">
                        <div>
                            <p class="mb-1.5 text-xs font-medium text-content-secondary">Preview on the page</p>
                            <x-admin.section-preview :type="$editingKey" large />
                        </div>
                        @php $fields = $catalog[$editingKey]['fields'] ?? null; @endphp
                        @if ($fields)
                            @foreach ($fields as $field)
                                <x-admin.field :field="$field" :wireModel="'form.'.$field['key']"
                                    :value="data_get($form, $field['key'])"
                                    :productQuery="$productQuery" :productPath="$productPath" :productMatches="$productMatches" />
                            @endforeach
                        @else
                            @foreach ($form as $key => $value)
                                @if (is_bool($value))
                                    <x-ui.toggle wire:model="form.{{ $key }}" label="{{ ucwords(str_replace('_', ' ', $key)) }}" />
                                @else
                                    <x-ui.input wire:model="form.{{ $key }}" label="{{ ucwords(str_replace('_', ' ', $key)) }}" />
                                @endif
                            @endforeach
                        @endif
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
