@props([
    'items' => [],
    'apply' => 'applyAiReview',
    'discard' => 'discardAiReview',
])

{{-- Paired with the ReviewsAiDrafts trait: renders whatever the owning Livewire
     component staged in $aiReview, one before/after row per proposed field.
     wire:model targets `aiReview.N.selected` directly since that property name
     is the trait's contract, not something passed through as a prop.

     x-ui.modal opens/closes on a bare-string event ($dispatch('open-modal', name)),
     which is Alpine's own dispatch shape — Livewire's server-side dispatch()
     always wraps params in an array/object, so it can't drive that listener.
     Watching $wire.aiReview instead reacts correctly regardless of which side
     changed it. --}}
<div x-data x-init="$watch('$wire.aiReview', (value) => $dispatch(value.length > 0 ? 'open-modal' : 'close-modal', 'ai-review'))">
    <x-ui.modal name="ai-review" title="Review AI draft" max-width="2xl">
        <div class="max-h-[60vh] space-y-3 overflow-y-auto">
            @forelse ($items as $i => $item)
                <label class="flex items-start gap-3 rounded-lg border border-line p-3">
                    <input type="checkbox" wire:model="aiReview.{{ $i }}.selected" class="mt-1 h-4 w-4 shrink-0 rounded border-line text-primary focus:ring-primary">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wide text-content-muted">{{ $item['label'] }}</p>
                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                            <div>
                                <p class="mb-1 text-[11px] font-medium uppercase text-content-muted">Current</p>
                                <div class="max-h-40 overflow-y-auto whitespace-pre-line rounded-md border border-line bg-surface-sunken p-2 text-sm text-content-muted">{{ $item['before'] !== '' ? $item['before'] : '— empty —' }}</div>
                            </div>
                            <div>
                                <p class="mb-1 text-[11px] font-medium uppercase text-content-muted">AI suggestion</p>
                                <div class="max-h-40 overflow-y-auto whitespace-pre-line rounded-md border border-line bg-surface-overlay p-2 text-sm text-content">{{ $item['after'] }}</div>
                            </div>
                        </div>
                    </div>
                </label>
            @empty
                <p class="text-sm text-content-muted">Nothing to review.</p>
            @endforelse
        </div>

        <x-slot:footer>
            <x-ui.button type="button" variant="ghost" wire:click="{{ $discard }}">Keep current</x-ui.button>
            <x-ui.button type="button" variant="primary" wire:click="{{ $apply }}">Replace selected</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
</div>
