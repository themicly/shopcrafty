@props([
    'label' => 'Images',
    'accept' => 'image/*',
    'multiple' => false,
    'formats' => 'JPG, PNG, WebP or GIF',
    'maxSize' => '5 MB',
    'wireTarget' => null,          // wire:target for the "Uploading…" spinner
    'disabled' => false,
    'disabledMessage' => 'Save first to enable uploads.',
    'error' => null,
])

@php
    // One consistent, self-explaining dropzone used everywhere images are added.
    // The file input's wire:model / id come through $attributes from the caller.
@endphp

<div class="space-y-1.5">
    @if ($label)
        <span class="block text-sm font-medium text-content-secondary">{{ $label }}</span>
    @endif

    @if ($disabled)
        <div class="flex flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-line bg-surface px-4 py-8 text-center">
            <x-ui.icon name="photo" class="h-7 w-7 text-content-muted" />
            <p class="text-sm text-content-muted">{{ $disabledMessage }}</p>
        </div>
    @else
        <label class="group flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-line bg-surface px-4 py-8 text-center transition-colors hover:border-primary hover:bg-primary-soft">
            <input type="file" @if ($multiple) multiple @endif accept="{{ $accept }}"
                {{ $attributes->merge(['class' => 'sr-only']) }} />
            <x-ui.icon name="cloud-upload" class="h-8 w-8 text-content-muted transition-colors group-hover:text-primary" />
            <div>
                <p class="text-sm font-medium text-content">
                    Drop {{ $multiple ? 'images' : 'an image' }} here, or <span class="text-primary underline">browse</span>
                </p>
                <p class="mt-1 text-xs text-content-muted">{{ $formats }} · up to {{ $maxSize }}{{ $multiple ? ' each' : '' }}</p>
            </div>
            @if ($wireTarget)
                <p wire:loading wire:target="{{ $wireTarget }}" class="text-xs font-medium text-primary">Uploading…</p>
            @endif
        </label>
    @endif

    @if ($error)
        <p class="text-xs text-danger">{{ $error }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="pt-1">{{ $slot }}</div>
    @endif
</div>
