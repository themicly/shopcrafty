@props([
    'label' => null,
    'name' => null,
])

{{-- The knob is a grandchild of the checkbox, so Tailwind's `peer-checked:` (a
     sibling combinator) can never move it — these rules do the sliding instead. --}}
@once
<style>
    .ui-toggle-knob { transition: transform .18s ease; }
    .ui-toggle input:checked + .ui-toggle-track .ui-toggle-knob { transform: translateX(1rem); }
    @media (prefers-reduced-motion: reduce) { .ui-toggle-knob { transition: none; } }
</style>
@endonce

<label class="ui-toggle inline-flex cursor-pointer items-center gap-3">
    <input
        type="checkbox"
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->merge(['class' => 'peer sr-only']) }}
    >
    <span class="ui-toggle-track relative h-5 w-9 rounded-full bg-line transition-colors peer-checked:bg-primary peer-focus-visible:ring-2 peer-focus-visible:ring-primary peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-surface">
        <span class="ui-toggle-knob absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white"></span>
    </span>
    @if ($label)<span class="text-sm text-content-secondary">{{ $label }}</span>@endif
</label>
