@props([
    'name' => null,
    'value' => 1,
    'min' => 0,
    'max' => null,
])

<div
    x-data="{
        count: {{ (int) $value }},
        min: {{ (int) $min }},
        max: {{ $max === null ? 'null' : (int) $max }},
        dec() { if (this.count > this.min) this.count--; },
        inc() { if (this.max === null || this.count < this.max) this.count++; },
    }"
    class="inline-flex items-center rounded-md border border-line bg-surface-raised"
>
    <button type="button" @click="dec()" class="grid h-9 w-9 place-items-center text-content-secondary hover:bg-surface-sunken disabled:opacity-40" :disabled="count <= min" aria-label="Decrease">
        <span class="text-lg leading-none">&minus;</span>
    </button>
    <input
        type="number"
        x-model.number="count"
        @if ($name) name="{{ $name }}" @endif
        :min="min" :max="max"
        class="h-9 w-12 border-x border-line bg-transparent text-center text-sm text-content focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
    >
    <button type="button" @click="inc()" class="grid h-9 w-9 place-items-center text-content-secondary hover:bg-surface-sunken disabled:opacity-40" :disabled="max !== null && count >= max" aria-label="Increase">
        <span class="text-lg leading-none">+</span>
    </button>
</div>
