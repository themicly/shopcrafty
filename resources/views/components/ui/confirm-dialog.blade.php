@props([
    'name',
    'title' => 'Are you sure?',
    'message' => null,
    'confirmLabel' => 'Confirm',
    'variant' => 'danger',
])

{{--
    Confirmation dialog. Put the actual action (a form or button) in the default
    slot — it renders in the footer next to Cancel. Open with:
    $dispatch('open-modal', '{{ $name }}')
--}}
<x-ui.modal :name="$name" :title="$title" max-width="sm">
    @if ($message)
        <p class="text-sm text-content-secondary">{{ $message }}</p>
    @endif

    <x-slot:footer>
        <x-ui.button variant="ghost" x-on:click="$dispatch('close-modal', '{{ $name }}')">Cancel</x-ui.button>
        {{ $slot }}
    </x-slot:footer>
</x-ui.modal>
