@props([
    'label' => 'Save changes',
    'loadingLabel' => 'Saving…',
    'target' => 'save',     // wire method(s) to show the loading state for
    'type' => 'submit',
])

{{-- Standard admin save/submit button: primary, with a built-in loading swap
     tied to a Livewire target. Use for every form save/submit action. --}}
<x-ui.button :type="$type" variant="primary" wire:target="{{ $target }}" wire:loading.attr="disabled" {{ $attributes }}>
    <span wire:loading.remove wire:target="{{ $target }}">{{ $label }}</span>
    <span wire:loading wire:target="{{ $target }}" class="inline-flex items-center gap-2">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path></svg>
        {{ $loadingLabel }}
    </span>
</x-ui.button>
