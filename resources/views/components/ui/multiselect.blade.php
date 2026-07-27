@props([
    'label' => null,
    'wireModel',       // dotted Livewire path this syncs to, e.g. "selectedValues.5"
    'options' => [],   // [['value' => ..., 'label' => ..., 'color' => ?string], ...]
    'value' => [],     // currently selected values
    'placeholder' => 'Add…',
    'hint' => null,
])

@php
    // Chip-based multiselect: selected options render as removable chips, the
    // rest live in a searchable dropdown — keeps a long value list (e.g. 20
    // colors) compact instead of showing every checkbox expanded at once.
    $options = array_map(fn ($o) => ['value' => (string) $o['value'], 'label' => (string) $o['label'], 'color' => $o['color'] ?? null], $options);
    $value = array_map('strval', $value);
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label class="block text-sm font-medium text-content-secondary">{{ $label }}</label>
    @endif

    <div
        x-data="{
            open: false,
            query: '',
            selected: @js($value),
            options: @js($options),
            get chosen() { return this.options.filter(o => this.selected.includes(o.value)); },
            get filtered() {
                const q = this.query.toLowerCase();
                return this.options.filter(o => !this.selected.includes(o.value) && o.label.toLowerCase().includes(q));
            },
            toggle(v) {
                this.selected = this.selected.includes(v) ? this.selected.filter(x => x !== v) : [...this.selected, v];
                $wire.set('{{ $wireModel }}', this.selected);
            },
        }"
        @keydown.escape="open = false"
        class="relative"
    >
        <div class="flex min-h-9 flex-wrap items-center gap-1.5 rounded-md border border-line bg-surface-raised px-2 py-1.5">
            <template x-for="o in chosen" :key="o.value">
                <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2 py-0.5 text-xs font-medium text-primary">
                    <span x-show="o.color" :style="{ background: o.color }" class="h-2 w-2 rounded-full border border-line" aria-hidden="true"></span>
                    <span x-text="o.label"></span>
                    <button type="button" @click="toggle(o.value)" class="text-primary/70 hover:text-primary" aria-label="Remove">&times;</button>
                </span>
            </template>
            <template x-if="chosen.length === 0">
                <span class="px-1 text-sm text-content-muted" x-text="@js($placeholder)"></span>
            </template>
            <button type="button" @click="open = !open; $nextTick(() => open && $refs.q.focus())"
                class="ml-auto inline-flex shrink-0 items-center gap-1 rounded-md px-1.5 py-0.5 text-xs font-medium text-content-muted hover:bg-surface-sunken hover:text-primary">
                <x-ui.icon name="plus" class="h-3.5 w-3.5" />
                Add
            </button>
        </div>

        <div
            x-show="open"
            x-transition
            @click.outside="open = false"
            x-cloak
            class="absolute z-30 mt-1 w-full overflow-hidden rounded-lg border border-line bg-surface-overlay shadow-lg"
            style="display: none;"
        >
            <div class="border-b border-line p-2">
                <input x-ref="q" x-model="query" type="text" placeholder="Search…" class="h-8 w-full rounded-md bg-surface px-2 text-sm text-content placeholder:text-content-muted focus:outline-none">
            </div>
            <div class="max-h-56 overflow-y-auto p-1">
                <template x-for="o in filtered" :key="o.value">
                    <button type="button" @click="toggle(o.value)" class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-sm text-content-secondary hover:bg-surface-sunken">
                        <span x-show="o.color" :style="{ background: o.color }" class="h-3 w-3 shrink-0 rounded-full border border-line" aria-hidden="true"></span>
                        <span x-text="o.label"></span>
                    </button>
                </template>
                <p x-show="filtered.length === 0" class="px-2 py-3 text-center text-xs text-content-muted">No matches.</p>
            </div>
        </div>
    </div>
    @if ($hint)
        <p class="text-xs text-content-muted">{{ $hint }}</p>
    @endif
</div>
