@props([
    'icon' => 'plus',
    'href' => null,
])

@php $tag = $href ? 'a' : 'button'; @endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="button" @endif
    {{ $attributes->merge(['class' => 'inline-flex h-9 items-center gap-1.5 rounded-lg bg-primary px-4 text-sm font-semibold text-primary-fg shadow-sm transition-colors hover:bg-primary-hover']) }}
>
    @if ($icon)
        <x-ui.icon :name="$icon" class="h-4 w-4" />
    @endif
    {{ $slot }}
</{{ $tag }}>
