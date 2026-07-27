@props([
    'label' => null,
    'hint' => null,
    'error' => null,
    'name' => null,
    'symbol' => '$',
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

    <div @class([
        'flex items-center rounded-md border bg-surface-raised transition-colors focus-within:ring-2 focus-within:ring-primary',
        'border-danger' => $error,
        'border-line' => ! $error,
    ])>
        <span class="grid h-9 place-items-center px-3 text-sm text-content-muted">{{ $symbol }}</span>
        <input
            type="number" step="0.01" min="0" inputmode="decimal"
            @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
            @if ($required) aria-required="true" @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-r-md border-0 bg-transparent px-0 pr-3 h-9 text-sm text-content placeholder:text-content-muted focus:outline-none focus:ring-0',
            ]) }}
        >
    </div>

    @if ($error)
        <p class="text-xs text-danger">{{ $error }}</p>
    @elseif ($hint)
        <p class="text-xs text-content-muted">{{ $hint }}</p>
    @endif
</div>
