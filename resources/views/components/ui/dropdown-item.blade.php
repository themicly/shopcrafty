@props(['href' => null])

@php
    $classes = 'block w-full px-3 py-2 text-left text-sm text-content-secondary hover:bg-surface-sunken hover:text-content';
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @else type="button" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    {{ $slot }}
</{{ $tag }}>
