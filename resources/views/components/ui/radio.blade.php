@props([
    'label' => null,
    'name' => null,
])

<label class="flex items-center gap-2.5 text-sm text-content-secondary">
    <input
        type="radio"
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'h-4 w-4 border-line text-primary focus:ring-primary focus:ring-offset-0',
        ]) }}
    >
    @if ($label)<span>{{ $label }}</span>@endif
    {{ $slot }}
</label>
