<div>
    {{-- ── Inline field: label + thumbnail + affordances ─────────────── --}}
    @if ($label)
        <label class="mb-1.5 flex items-center gap-1 text-sm font-medium text-content-secondary">
            {{ $label }}
            @if ($required)
                <span class="text-danger" title="Required" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="flex items-center gap-3">
        @if (! empty($value))
            <img src="{{ $value }}" alt="" class="h-16 w-16 shrink-0 rounded-md object-cover ring-1 ring-black/10" />
        @else
            <div class="grid h-16 w-16 shrink-0 place-items-center rounded-md bg-surface-sunken text-content-muted">
                <x-ui.icon name="image" class="h-6 w-6" />
            </div>
        @endif

        <div class="min-w-0 flex-1 space-y-1">
            @if ($disabled)
                <p class="text-sm text-content-muted">{{ $disabledMessage }}</p>
            @else
                <div class="flex flex-wrap items-center gap-2">
                    <x-ui.button type="button" variant="secondary" size="sm" wire:click="openPicker">
                        <x-ui.icon name="photo" class="mr-1.5 h-4 w-4" />
                        {{ ! empty($value) ? 'Change image' : 'Choose image' }}
                    </x-ui.button>
                    @if (! empty($value))
                        <button type="button" wire:click="remove" class="text-xs font-medium text-danger hover:underline">Remove</button>
                    @endif
                </div>
                @if (! empty($value))
                    <p class="truncate text-xs text-content-muted" title="{{ $value }}">{{ $value }}</p>
                @endif
            @endif
        </div>
    </div>

    @if ($hint)<p class="mt-1 text-xs text-content-muted">{{ $hint }}</p>@endif

    {{-- ── Picker dialog (gallery · upload · URL) ─────────────────────── --}}
    @if ($open)
        <div
            x-data
            x-init="$nextTick(() => $refs.panel?.focus())"
            @keydown.escape.window="$wire.closePicker()"
            class="fixed inset-0 z-50 overflow-y-auto"
            role="dialog" aria-modal="true" aria-label="Choose an image"
        >
            <div class="fixed inset-0 bg-black/50" wire:click="closePicker" aria-hidden="true"></div>

            <div class="flex min-h-full items-start justify-center p-4 sm:p-6">
                <div x-ref="panel" tabindex="-1"
                    class="relative mt-[6vh] w-full max-w-2xl rounded-xl border border-line bg-surface-overlay shadow-lg focus:outline-none">

                    {{-- Header --}}
                    <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
                        <h3 class="text-sm font-semibold text-content">Choose an image</h3>
                        <button type="button" wire:click="closePicker" aria-label="Close"
                            class="grid h-8 w-8 place-items-center rounded-md text-content-muted transition-colors hover:bg-surface-sunken focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                            <span class="text-lg leading-none">&times;</span>
                        </button>
                    </div>

                    {{-- Mode tabs --}}
                    <div class="flex flex-nowrap gap-1 overflow-x-auto border-b border-line px-5" role="tablist">
                        @foreach (['gallery' => 'Gallery', 'upload' => 'Upload', 'url' => 'Path / URL'] as $key => $tabLabel)
                            <button type="button" wire:click="$set('mode', '{{ $key }}')"
                                role="tab" aria-selected="{{ $mode === $key ? 'true' : 'false' }}"
                                class="-mb-px shrink-0 whitespace-nowrap border-b-2 px-3 py-2.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary
                                    {{ $mode === $key ? 'border-primary text-content' : 'border-transparent text-content-muted hover:text-content' }}">
                                {{ $tabLabel }}
                            </button>
                        @endforeach
                    </div>

                    <div class="p-5">
                        {{-- Gallery --}}
                        @if ($mode === 'gallery')
                            <div class="space-y-4">
                                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search media by name…" />

                                @if ($items->isEmpty())
                                    <div class="grid place-items-center gap-2 rounded-xl bg-surface-sunken px-4 py-10 text-center">
                                        <x-ui.icon name="photo" class="h-8 w-8 text-content-muted" />
                                        <p class="text-sm text-content-muted">
                                            {{ $search !== '' ? 'No media matches your search.' : 'Your media library is empty. Upload an image to get started.' }}
                                        </p>
                                    </div>
                                @else
                                    <ul role="list" class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6">
                                        @foreach ($items as $item)
                                            <li wire:key="pick-{{ $item->id }}">
                                                <button type="button" wire:click="selectMedia({{ $item->id }})"
                                                    class="group block w-full overflow-hidden rounded-lg bg-surface-sunken ring-1 ring-line transition hover:ring-2 hover:ring-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary
                                                        {{ $value === $item->url($rendition) ? 'ring-2 ring-primary' : '' }}"
                                                    title="{{ $item->name }}">
                                                    <img src="{{ $item->url('thumb') }}" alt="{{ $item->alt ?: $item->name }}" loading="lazy"
                                                        class="aspect-square w-full object-cover" />
                                                </button>
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if ($galleryPage > 1 || $hasMore)
                                        <div class="flex items-center justify-between pt-1">
                                            <x-ui.button type="button" variant="ghost" size="sm" wire:click="prevPage" :disabled="$galleryPage <= 1">Previous</x-ui.button>
                                            <span class="text-xs text-content-muted">Page {{ $galleryPage }}</span>
                                            <x-ui.button type="button" variant="ghost" size="sm" wire:click="nextPage({{ $hasMore ? 'true' : 'false' }})" :disabled="! $hasMore">Next</x-ui.button>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif

                        {{-- Upload --}}
                        @if ($mode === 'upload')
                            <x-admin.image-uploader
                                label=""
                                wire:model="upload"
                                wireTarget="upload"
                                :error="$errors->first('upload')" />
                        @endif

                        {{-- Path / URL --}}
                        @if ($mode === 'url')
                            <div class="space-y-3">
                                <x-ui.input wire:model="urlInput" label="Image path or URL"
                                    placeholder="https://… or /storage/media/…"
                                    hint="Paste an external link or a path already on this site."
                                    :error="$errors->first('urlInput')" />
                                <div class="flex justify-end">
                                    <x-ui.button type="button" size="sm" wire:click="applyUrl">Use this URL</x-ui.button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
