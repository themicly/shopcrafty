{{--
    One category row, recursing into children with a per-depth indent.
    Inherits $searching from the parent view — searching renders a flat match
    list, so the reorder
    arrows (which swap positions among siblings) are hidden there.
--}}
@php
    $thumb = $category->image_path ?: $category->image;
@endphp

<div>
    <div class="flex items-center gap-3 border-t border-line px-4 py-3 first:border-t-0" style="padding-left: {{ 1 + $depth * 1.5 }}rem">
        {{-- Feature image thumbnail (ring, not border — single-frame rule) --}}
        @if ($thumb)
            <img src="{{ $thumb }}" alt="" class="h-10 w-10 shrink-0 rounded-md object-cover ring-1 ring-black/10">
        @else
            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-md bg-surface-sunken text-content-muted" aria-hidden="true">
                <x-ui.icon name="image" class="h-4 w-4" />
            </div>
        @endif

        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                @if (trim((string) $category->icon) !== '')
                    <span class="shrink-0 text-base leading-none" aria-hidden="true">{{ $category->icon }}</span>
                @endif
                <span class="truncate text-sm font-medium text-content">{{ $category->name }}</span>
                @unless ($category->is_active)
                    <x-ui.badge>Hidden</x-ui.badge>
                @endunless
                @if ($category->children->isNotEmpty())
                    <span class="shrink-0 text-xs text-content-muted">{{ $category->children->count() }} {{ \Illuminate\Support\Str::plural('subcategory', $category->children->count()) }}</span>
                @endif
            </div>
            <p class="mt-0.5 truncate text-xs text-content-muted">
                @if (filled($category->description))
                    {{ \Illuminate\Support\Str::limit($category->description, 80) }}
                @else
                    &mdash;
                @endif
            </p>
        </div>

        <div class="flex shrink-0 items-center gap-1">
            @unless ($searching ?? false)
                <button type="button" wire:click="move({{ $category->id }}, 'up')" class="grid h-7 w-7 place-items-center rounded text-content-muted hover:bg-surface-sunken" title="Move up" aria-label="Move {{ $category->name }} up">↑</button>
                <button type="button" wire:click="move({{ $category->id }}, 'down')" class="grid h-7 w-7 place-items-center rounded text-content-muted hover:bg-surface-sunken" title="Move down" aria-label="Move {{ $category->name }} down">↓</button>
            @endunless
            <x-ui.icon-button icon="pencil" variant="ghost" label="Edit" type="button" wire:click="edit({{ $category->id }})" />
            <x-ui.icon-button icon="trash" variant="danger" label="Delete" type="button" x-on:click="$dispatch('confirm', { title: 'Delete category?', message: 'This permanently deletes the category.', confirmLabel: 'Delete', onConfirm: () => $wire.delete({{ $category->id }}) })" />
        </div>
    </div>

    @foreach ($category->children as $child)
        @include('catalog::livewire.partials.category-row', ['category' => $child, 'depth' => $depth + 1])
    @endforeach
</div>
