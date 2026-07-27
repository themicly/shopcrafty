@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'placeholder' => 'Select…',
    'value' => null,
])

@php
    // $options: array of ['value' => ..., 'label' => ...]
    $options = array_map(fn ($o) => ['value' => (string) $o['value'], 'label' => (string) $o['label']], $options);
@endphp

<div class="space-y-1.5">
    @if ($label)
        <label class="block text-sm font-medium text-content-secondary">{{ $label }}</label>
    @endif

    <div
        x-data="{
            open: false,
            query: '',
            selected: @js((string) $value),
            options: @js($options),
            get current() { return this.options.find(o => o.value === this.selected); },
            get filtered() {
                const q = this.query.toLowerCase();
                return this.options.filter(o => o.label.toLowerCase().includes(q));
            },
            choose(o) { this.selected = o.value; this.open = false; this.query = ''; },
        }"
        @keydown.escape="open = false"
        class="relative"
    >
        <input type="hidden" @if ($name) name="{{ $name }}" @endif :value="selected">

        <button
            type="button"
            @click="open = !open; $nextTick(() => open && $refs.q.focus())"
            class="flex h-9 w-full items-center justify-between gap-2 rounded-md border border-line bg-surface-raised px-3 text-sm text-content focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary"
        >
            <span x-text="current ? current.label : @js($placeholder)" :class="!current && 'text-content-muted'"></span>
            <x-ui.icon name="chevron-left" class="h-4 w-4 -rotate-90 text-content-muted" />
        </button>

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
                    <button type="button" @click="choose(o)" class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-left text-sm text-content-secondary hover:bg-surface-sunken" :class="o.value === selected && 'text-primary'">
                        <span x-text="o.label"></span>
                    </button>
                </template>
                <p x-show="filtered.length === 0" class="px-2 py-3 text-center text-xs text-content-muted">No matches.</p>
            </div>
        </div>
    </div>
</div>
