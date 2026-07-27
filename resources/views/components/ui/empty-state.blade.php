@props([
    'icon' => null,
    'title' => 'Nothing here yet',
    'description' => null,
    'tone' => 'neutral',
])

@php
    $toneClasses = [
        'neutral' => 'bg-surface-sunken text-content-muted',
        'success' => 'bg-success-soft text-success',
        'primary' => 'bg-primary-soft text-primary',
    ][$tone] ?? 'bg-surface-sunken text-content-muted';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-10 text-center']) }}>
    @if ($icon)
        <div class="grid h-12 w-12 place-items-center rounded-full {{ $toneClasses }}">
            <x-ui.icon :name="$icon" class="h-6 w-6" />
        </div>
    @endif
    <p class="mt-3 text-sm font-medium text-content">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 text-xs text-content-muted">{{ $description }}</p>
    @endif
    @isset($action)
        <div class="mt-4">{{ $action }}</div>
    @endisset
</div>
