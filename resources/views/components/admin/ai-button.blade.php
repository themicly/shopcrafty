@props([
    'action',              // wire method to call, e.g. "generateTitle"
    'label' => 'Generate with AI',
    'target' => null,      // wire:loading target (defaults to the action)
    'size' => 'sm',        // sm (inline beside an input) | md
])

@php
    // Standard AI affordance: sparkles + label, busy state while generating.
    // Render it only where the matching AI feature is enabled — callers gate
    // with AiService::featureEnabled('…').
    $busyTarget = $target ?? $action;
    $sizes = ['sm' => 'px-2 py-1 text-[11px]', 'md' => 'px-3 py-1.5 text-xs'];
@endphp

<button
    type="button"
    wire:click="{{ $action }}"
    wire:loading.attr="disabled"
    wire:target="{{ $busyTarget }}"
    {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center gap-1 rounded-md border border-primary/30 bg-primary-soft font-medium text-primary transition-colors hover:border-primary/60 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary disabled:opacity-60 '.($sizes[$size] ?? $sizes['sm'])]) }}
    title="{{ $label }}"
>
    <x-ui.icon name="sparkles" class="h-3.5 w-3.5" />
    <span wire:loading.remove wire:target="{{ $busyTarget }}">{{ $label }}</span>
    <span wire:loading wire:target="{{ $busyTarget }}" class="inline-flex items-center gap-1">
        <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/></svg>
        Generating…
    </span>
</button>
