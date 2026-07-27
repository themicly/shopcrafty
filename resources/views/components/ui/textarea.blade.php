@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
    'rows' => 4,
    'required' => false,
    'optional' => false,
])

<div class="space-y-1.5">
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="flex items-center gap-1 text-sm font-medium text-content-secondary">
            {{ $label }}
            @if ($required)
                <span class="text-danger" title="Required" aria-hidden="true">*</span>
            @elseif ($optional)
                <span class="text-xs font-normal text-content-muted">(optional)</span>
            @endif
        </label>
    @endif

    <textarea
        @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        @if ($required) aria-required="true" @endif
        rows="{{ $rows }}"
        {{ $attributes->merge([
            'class' => 'block w-full rounded-md border bg-surface-raised px-3 py-2 text-sm text-content '
                . 'placeholder:text-content-muted transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary '
                . ($error ? 'border-danger' : 'border-line'),
        ]) }}
    >{{ $slot }}</textarea>

    @if ($error)
        <p class="text-xs text-danger">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-content-muted">{{ $hint }}</p>
    @endif
</div>
