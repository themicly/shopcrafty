@php
    $selectedIds = array_map('intval', $selected);
    $pageIds = $items->pluck('id')->map(fn ($id) => (int) $id)->all();
    $allPageSelected = $pageIds !== [] && array_diff($pageIds, $selectedIds) === [];
@endphp

<div
    class="relative"
    x-data="{
        dragging: 0,
        copied: false,
        copyUrl(url) {
            const done = () => {
                this.copied = true;
                setTimeout(() => this.copied = false, 1500);
                this.$dispatch('toast', { message: 'URL copied to clipboard', type: 'success' });
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done, () => this.copyFallback(url) && done());
            } else if (this.copyFallback(url)) {
                done();
            }
        },
        copyFallback(url) {
            const el = document.createElement('textarea');
            el.value = url;
            el.setAttribute('readonly', '');
            el.style.position = 'fixed';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            let ok = false;
            try { ok = document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(el);
            return ok;
        },
        dropFiles(event) {
            this.dragging = 0;
            const files = Array.from(event.dataTransfer?.files || []).filter(f => f.type.startsWith('image/'));
            if (files.length) $wire.uploadMultiple('photos', files);
        },
    }"
    x-on:dragenter.prevent="dragging++"
    x-on:dragleave.prevent="dragging = Math.max(0, dragging - 1)"
    x-on:dragover.prevent
    x-on:drop.prevent="dropFiles($event)"
>
    {{-- Drag-over overlay: whole library is a dropzone --}}
    <div x-show="dragging > 0" x-cloak
        class="pointer-events-none absolute -inset-2 z-30 flex items-center justify-center rounded-xl border-2 border-dashed border-primary bg-primary-soft/90">
        <div class="flex items-center gap-2 text-sm font-semibold text-primary">
            <x-ui.icon name="cloud-upload" class="h-6 w-6" />
            Drop images to upload{{ $folder ? ' into "'.$folder->name.'"' : '' }}
        </div>
    </div>

    {{-- Toolbar: breadcrumb path + search + new folder + upload --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <nav aria-label="Folder path" class="flex min-w-0 items-center gap-1 text-sm">
            @if ($breadcrumbs === [])
                <span class="px-1 font-semibold text-content" aria-current="page">Library</span>
            @else
                <button type="button" wire:click="openFolder(null)"
                    class="rounded px-1 py-0.5 text-content-secondary transition-colors hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    Library
                </button>
            @endif
            @foreach ($breadcrumbs as $crumb)
                <span class="text-content-muted" aria-hidden="true">/</span>
                @if ($loop->last)
                    <span class="max-w-48 truncate px-1 font-semibold text-content" aria-current="page">{{ $crumb->name }}</span>
                @else
                    <button type="button" wire:click="openFolder({{ $crumb->id }})"
                        class="max-w-40 truncate rounded px-1 py-0.5 text-content-secondary transition-colors hover:text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                        {{ $crumb->name }}
                    </button>
                @endif
            @endforeach
        </nav>

        <div class="ml-auto flex flex-wrap items-center gap-2">
            <div class="w-52">
                <x-ui.input type="search" wire:model.live.debounce.400ms="search"
                    placeholder="Search this folder…" aria-label="Search files in this folder" />
            </div>

            <x-ui.button type="button" variant="secondary" size="sm" wire:click="$set('creatingFolder', true)">
                <x-ui.icon name="plus" class="h-4 w-4" />
                New folder
            </x-ui.button>

            {{-- Upload button (label wraps the file input; ring via focus-within for keyboard) --}}
            <label class="inline-flex h-8 cursor-pointer select-none items-center justify-center gap-2 rounded-md bg-gradient-to-r from-primary to-brand-2 px-3 text-sm font-medium text-primary-fg shadow-sm transition-colors focus-within:ring-2 focus-within:ring-primary focus-within:ring-offset-2 focus-within:ring-offset-surface hover:opacity-95">
                <input type="file" wire:model="photos" multiple accept="image/*" class="sr-only">
                <x-ui.icon name="upload" class="h-4 w-4" />
                Upload
            </label>
        </div>
    </div>

    {{-- Inline new-folder form --}}
    @if ($creatingFolder)
        <form wire:submit="createFolder" class="mb-4 flex flex-wrap items-start gap-2 rounded-lg border border-line bg-surface-raised p-3">
            <div class="w-full max-w-xs">
                <x-ui.input wire:model="newFolderName" aria-label="New folder name" placeholder="Folder name"
                    :error="$errors->first('newFolderName')" x-init="$el.focus()" />
            </div>
            <x-ui.button type="submit" size="sm">Create{{ $folder ? ' in "'.$folder->name.'"' : '' }}</x-ui.button>
            <x-ui.button type="button" variant="ghost" size="sm" wire:click="cancelCreateFolder">Cancel</x-ui.button>
        </form>
    @endif

    {{-- Upload progress --}}
    <div wire:loading.flex wire:target="photos" class="mb-4 items-center gap-3 rounded-lg border border-primary/30 bg-primary-soft px-4 py-2.5" role="status">
        <span class="relative flex h-2.5 w-2.5">
            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-60"></span>
            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-primary"></span>
        </span>
        <p class="text-sm font-medium text-primary">Uploading images…</p>
    </div>

    @error('photos.*')<p class="mb-3 text-sm text-danger">{{ $message }}</p>@enderror

    {{-- Folders --}}
    @if ($folders->isNotEmpty())
        <section class="mb-6" aria-label="Folders">
            <h3 class="mb-2 text-xs font-semibold uppercase tracking-wide text-content-muted">Folders</h3>
            <ul role="list" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                @foreach ($folders as $f)
                    <li wire:key="folder-{{ $f->id }}" class="rounded-lg border border-line bg-surface-raised">
                        @if ($renamingFolderId === $f->id)
                            <form wire:submit="renameFolder" class="p-2">
                                <div class="flex items-center gap-1">
                                    <x-ui.input wire:model="renameFolderName" aria-label="Rename folder {{ $f->name }}"
                                        x-init="$el.focus(); $el.select()" class="h-8" />
                                    <x-ui.icon-button type="submit" icon="check" variant="success" label="Save name" />
                                    <x-ui.icon-button type="button" icon="x-mark" label="Cancel rename" wire:click="cancelRename" />
                                </div>
                                @error('renameFolderName')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                            </form>
                        @else
                            <div class="flex items-center gap-0.5 p-1.5">
                                <button type="button" wire:click="openFolder({{ $f->id }})"
                                    class="flex min-w-0 flex-1 items-center gap-2.5 rounded-md px-1.5 py-1 text-left transition-colors hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                    {{-- Folder icon (heroicon outline, inline — not in the shared icon set) --}}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" class="h-6 w-6 shrink-0 text-content-muted">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                    </svg>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-medium text-content">{{ $f->name }}</span>
                                        <span class="block text-xs text-content-muted">{{ $f->media_count }} {{ $f->media_count === 1 ? 'file' : 'files' }}</span>
                                    </span>
                                </button>

                                <x-ui.dropdown width="w-44">
                                    <x-slot:trigger>
                                        <x-ui.icon-button type="button" icon="ellipsis" label="Actions for folder {{ $f->name }}" />
                                    </x-slot:trigger>
                                    <x-ui.dropdown-item wire:click="startRename({{ $f->id }})">Rename</x-ui.dropdown-item>
                                    <x-ui.dropdown-item
                                        class="text-danger hover:bg-danger-soft hover:text-danger"
                                        x-on:click="$dispatch('confirm', {
                                            title: 'Delete this folder?',
                                            message: {{ \Illuminate\Support\Js::from('“'.$f->name.'” will be deleted. The files and subfolders inside are kept and move to the Library root.') }},
                                            confirmLabel: 'Delete folder',
                                            onConfirm: () => $wire.deleteFolder({{ $f->id }})
                                        })"
                                    >Delete folder</x-ui.dropdown-item>
                                </x-ui.dropdown>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Files --}}
    <section aria-label="Files">
        @if ($items->isEmpty())
            @if ($search !== '')
                <div class="rounded-lg border border-line bg-surface-raised">
                    <x-ui.empty-state icon="search" title="No files match “{{ $search }}”"
                        description="Check the spelling or search inside a different folder.">
                        <x-slot:action>
                            <x-ui.button type="button" variant="secondary" size="sm" wire:click="$set('search', '')">Clear search</x-ui.button>
                        </x-slot:action>
                    </x-ui.empty-state>
                </div>
            @else
                {{-- Empty folder: the empty state itself is an upload target --}}
                <label class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-line bg-surface-raised px-4 py-14 text-center transition-colors focus-within:border-primary hover:border-primary hover:bg-primary-soft">
                    <input type="file" wire:model="photos" multiple accept="image/*" class="sr-only">
                    <x-ui.icon name="cloud-upload" class="h-9 w-9 text-content-muted transition-colors group-hover:text-primary" />
                    <p class="text-sm font-medium text-content">
                        {{ $folder ? 'This folder is empty' : 'Your library is empty' }}
                    </p>
                    <p class="text-xs text-content-muted">Drop images here or <span class="text-primary underline">click to upload</span> — JPG, PNG, WebP or GIF, up to 8 MB each</p>
                </label>
            @endif
        @else
            <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
                <label class="flex cursor-pointer select-none items-center gap-2 text-sm text-content-secondary">
                    <input type="checkbox" wire:click="toggleSelectPage" @checked($allPageSelected)
                        class="h-4 w-4 rounded border-line text-primary focus:ring-primary">
                    Select all on this page
                </label>
                <span class="text-xs text-content-muted">{{ $items->total() }} {{ $items->total() === 1 ? 'file' : 'files' }}{{ $search !== '' ? ' matching “'.$search.'”' : '' }}</span>
            </div>

            <ul role="list" class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                @foreach ($items as $item)
                    @php $isChecked = in_array((int) $item->id, $selectedIds, true); @endphp
                    <li wire:key="media-{{ $item->id }}"
                        class="relative overflow-hidden rounded-lg border bg-surface transition-colors {{ $isChecked ? 'border-primary ring-1 ring-primary' : 'border-line hover:border-line-strong' }}">
                        {{-- Tile opens the details panel --}}
                        <button type="button" wire:click="showDetails({{ $item->id }})"
                            class="block w-full text-left focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-primary"
                            aria-label="Details for {{ $item->name }}">
                            <img src="{{ $item->url('thumb') }}" alt="{{ $item->alt }}" loading="lazy" class="aspect-square w-full bg-surface-sunken object-cover">
                            <span class="block truncate border-t border-line px-2 py-1.5 text-xs text-content-secondary">{{ $item->name }}</span>
                        </button>

                        {{-- Always-visible selection checkbox (works on touch, no hover needed) --}}
                        <label class="absolute left-1.5 top-1.5 z-10 grid h-6 w-6 cursor-pointer place-items-center rounded-md bg-surface/90 shadow-sm ring-1 ring-line">
                            <span class="sr-only">Select {{ $item->name }}</span>
                            <input type="checkbox" value="{{ $item->id }}" wire:model.live="selected"
                                class="h-4 w-4 rounded border-line text-primary focus:ring-primary">
                        </label>
                    </li>
                @endforeach
            </ul>

            <div class="mt-4">{{ $items->links() }}</div>
        @endif
    </section>

    {{-- Floating bulk actions --}}
    @if (count($selected) > 0)
        <x-ui.bulk-action-bar :count="count($selected)">
            <div class="relative" x-data="{ open: false }" x-on:keydown.escape.stop="open = false">
                <button type="button" x-on:click="open = !open" :aria-expanded="open" aria-haspopup="true"
                    class="rounded-lg px-2.5 py-1 text-sm font-medium text-surface/90 transition-colors hover:bg-surface/15">
                    Move to…
                </button>
                <div x-show="open" x-cloak x-transition x-on:click.outside="open = false"
                    class="absolute bottom-full left-0 mb-2 max-h-64 w-56 overflow-y-auto rounded-lg border border-line bg-surface-overlay py-1 shadow-lg">
                    <x-ui.dropdown-item wire:click="moveSelected(null)" x-on:click="open = false">Library (root)</x-ui.dropdown-item>
                    @foreach ($folderOptions as $opt)
                        <x-ui.dropdown-item wire:click="moveSelected({{ $opt['id'] }})" x-on:click="open = false">
                            <span class="block truncate">{{ str_repeat('— ', $opt['depth']) }}{{ $opt['name'] }}</span>
                        </x-ui.dropdown-item>
                    @endforeach
                </div>
            </div>
            <button type="button"
                x-on:click="$dispatch('confirm', { title: 'Delete selected files?', message: 'This permanently deletes {{ count($selected) }} file(s) and their resized copies. Anywhere they are used will lose the image.', confirmLabel: 'Delete', onConfirm: () => $wire.deleteSelected() })"
                class="rounded-lg px-2.5 py-1 text-sm font-medium text-danger transition-colors hover:bg-danger/20">
                Delete
            </button>
            <button type="button" wire:click="clearSelection" class="rounded-lg px-2 py-1 text-surface/60 transition-colors hover:bg-surface/15" aria-label="Clear selection">&times;</button>
        </x-ui.bulk-action-bar>
    @endif

    {{-- Details slide-over --}}
    @if ($detail)
        @php
            $kb = $detail->size / 1024;
            $sizeLabel = $kb >= 1024 ? number_format($kb / 1024, 1).' MB' : number_format(max($kb, 1), 0).' KB';
        @endphp
        <div class="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label="File details: {{ $detail->name }}"
            x-data x-init="$nextTick(() => $refs.closeDetails?.focus())"
            x-on:keydown.escape.window="$wire.closeDetails()">
            <div class="fixed inset-0 bg-black/50" wire:click="closeDetails" aria-hidden="true"></div>

            <div class="fixed inset-y-0 right-0 flex w-full max-w-md flex-col border-l border-line bg-surface-overlay shadow-lg">
                <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
                    <h3 class="min-w-0 truncate text-sm font-semibold text-content" title="{{ $detail->name }}">{{ $detail->name }}</h3>
                    <button type="button" x-ref="closeDetails" wire:click="closeDetails" aria-label="Close details"
                        class="grid h-8 w-8 shrink-0 place-items-center rounded-md text-content-muted transition-colors hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                        <span class="text-lg leading-none" aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="flex-1 space-y-5 overflow-y-auto p-5">
                    <img src="{{ $detail->url('medium') }}" alt="{{ $detail->alt ?: $detail->name }}"
                        class="max-h-64 w-full rounded-lg border border-line bg-surface-sunken object-contain">

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        @if ($detail->width && $detail->height)
                            <div>
                                <dt class="text-xs text-content-muted">Dimensions</dt>
                                <dd class="text-content">{{ $detail->width }} × {{ $detail->height }} px</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs text-content-muted">Size</dt>
                            <dd class="text-content">{{ $sizeLabel }}</dd>
                        </div>
                        @if ($detail->mime)
                            <div>
                                <dt class="text-xs text-content-muted">Type</dt>
                                <dd class="text-content">{{ $detail->mime }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-xs text-content-muted">Uploaded</dt>
                            <dd class="text-content">{{ $detail->created_at?->format('M j, Y') }}</dd>
                        </div>
                    </dl>

                    {{-- Alt text --}}
                    <div>
                        <x-ui.input label="Alt text" wire:model="altText" placeholder="Describe this image"
                            :error="$errors->first('altText')" />
                        <div class="mt-2 flex items-center justify-between gap-2">
                            @if ($this->aiEnabled)
                                <x-ui.button type="button" variant="secondary" size="sm" wire:click="generateAlt" wire:loading.attr="disabled" wire:target="generateAlt">
                                    <span wire:loading.remove wire:target="generateAlt">✨ Generate</span>
                                    <span wire:loading wire:target="generateAlt">Looking…</span>
                                </x-ui.button>
                            @else
                                <span></span>
                            @endif
                            <x-ui.save-button type="button" target="saveAlt" label="Save alt text" wire:click="saveAlt" />
                        </div>
                    </div>

                    {{-- URL + copy --}}
                    <div class="space-y-1.5">
                        <label for="media-detail-url" class="block text-sm font-medium text-content-secondary">File URL</label>
                        <div class="flex items-center gap-2">
                            <input id="media-detail-url" type="text" readonly value="{{ $detail->url() }}"
                                x-on:focus="$event.target.select()"
                                class="block h-9 w-full min-w-0 flex-1 truncate rounded-md border border-line bg-surface-sunken px-3 text-sm text-content-secondary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            {{-- @js() doesn't expand inside an <x-component> tag attribute (the tag
                                 compiler freezes it as a literal string first) — pre-render with Js::from(). --}}
                            <x-ui.button type="button" variant="secondary" size="sm" x-on:click="copyUrl({{ \Illuminate\Support\Js::from($detail->url()) }})">
                                <x-ui.icon name="copy" class="h-4 w-4" />
                                <span x-show="!copied">Copy URL</span>
                                <span x-show="copied" x-cloak class="text-success">Copied!</span>
                            </x-ui.button>
                        </div>
                    </div>

                    {{-- Move to folder --}}
                    <x-ui.select label="Folder" wire:change="moveDetail($event.target.value)" aria-label="Move file to folder">
                        <option value="" @selected($detail->folder_id === null)>Library (root)</option>
                        @foreach ($folderOptions as $opt)
                            <option value="{{ $opt['id'] }}" @selected($detail->folder_id === $opt['id'])>{{ str_repeat('— ', $opt['depth']) }}{{ $opt['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div class="flex items-center justify-between gap-2 border-t border-line px-5 py-4">
                    <x-ui.button type="button" variant="ghost" size="sm" wire:click="closeDetails">Close</x-ui.button>
                    <x-ui.button type="button" variant="danger" size="sm"
                        x-on:click="$dispatch('confirm', { title: 'Delete this file?', message: 'This permanently deletes the file and its resized copies. Anywhere it is used will lose the image.', confirmLabel: 'Delete', onConfirm: () => $wire.deleteMedia({{ $detail->id }}) })">
                        Delete file
                    </x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
