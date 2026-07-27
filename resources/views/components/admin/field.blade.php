@props([
    'field',
    'wireModel',
    'value' => null,
    'productQuery' => '',
    'productPath' => null,
    'productMatches' => [],
])

@php
    // Typed field renderer (TASK #29). `$wireModel` is the full dotted path to this
    // field's value (e.g. "form.heading" or "blocks.2.settings.items.0.question") and
    // doubles as the "path" passed to the component's typed-field actions.
    $type = $field['type'] ?? 'text';
    $path = $wireModel;
    $label = $field['label'] ?? \Illuminate\Support\Str::headline($field['key'] ?? '');
    $hint = $field['hint'] ?? null;
    // Required fields carry the red "*" marker (built into the x-ui controls) and,
    // for the custom-drawn controls below, the $requiredMark snippet. The dotted
    // path doubles as the validation key, so surface any error for it.
    $required = (bool) ($field['required'] ?? false);
    $error = $errors->first($path);
    // Forward picker context so product controls nested in repeaters still work.
    $ctx = ['productQuery' => $productQuery, 'productPath' => $productPath, 'productMatches' => $productMatches];
@endphp

@php
    // Reusable "*" marker for the controls that render their own <label> below.
    $requiredMark = '<span class="text-danger" title="Required" aria-hidden="true">*</span>';
@endphp

@switch($type)
    @case('textarea')
        <x-ui.textarea wire:model.blur="{{ $path }}" :label="$label" :hint="$hint" :required="$required" :error="$error" rows="{{ $field['rows'] ?? 4 }}" />
        @break

    @case('number')
        <x-ui.input type="number" wire:model.blur="{{ $path }}" :label="$label" :hint="$hint" :required="$required" :error="$error" />
        @break

    @case('toggle')
        <x-ui.toggle wire:model.live="{{ $path }}" :label="$label" />
        @break

    @case('select')
        <x-ui.select wire:model.blur="{{ $path }}" :label="$label" :hint="$hint" :required="$required" :error="$error">
            @foreach (($field['options'] ?? []) as $optValue => $optLabel)
                <option value="{{ is_int($optValue) ? $optLabel : $optValue }}">{{ $optLabel }}</option>
            @endforeach
        </x-ui.select>
        @break

    @case('color')
        <div class="space-y-1.5">
            <label class="flex items-center gap-1 text-sm font-medium text-content-secondary">{{ $label }} @if ($required){!! $requiredMark !!}@endif</label>
            <div class="flex items-center gap-2">
                <input type="color" wire:model.blur="{{ $path }}"
                    class="h-9 w-12 shrink-0 cursor-pointer rounded-md border border-line bg-surface-raised p-1" />
                <x-ui.input wire:model.blur="{{ $path }}" class="flex-1" :error="$error" />
            </div>
            @if ($error)<p class="text-xs text-danger">{{ $error }}</p>@elseif ($hint)<p class="text-xs text-content-muted">{{ $hint }}</p>@endif
        </div>
        @break

    @case('image')
        @php $encoded = str_replace('.', '__', $path); @endphp
        {{-- Unified image control: gallery · upload · paste URL. Binds the same
             dotted field path ($path), so section/page-builder logic is unchanged. --}}
        <div wire:key="img-{{ $encoded }}">
            <livewire:settings.image-picker wire:model="{{ $path }}" :label="$label" :hint="$hint"
                :required="$required" rendition="large" :key="'picker-'.$encoded" />
            @if ($error)<p class="mt-1 text-xs text-danger">{{ $error }}</p>@endif
        </div>
        @break

    @case('product')
        @php
            $selected = array_values(array_map('intval', (array) ($value ?? [])));
            $selectedNames = $selected
                ? \Themicly\Shopcrafty\Modules\Catalog\Models\Product::whereIn('id', $selected)->pluck('name', 'id')
                : collect();
        @endphp
        <div class="space-y-1.5" wire:key="prod-{{ $path }}">
            @if ($label)<label class="flex items-center gap-1 text-sm font-medium text-content-secondary">{{ $label }} @if ($required){!! $requiredMark !!}@endif</label>@endif
            @if ($selected)
                <div class="flex flex-wrap gap-2">
                    @foreach ($selected as $id)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-surface-sunken px-3 py-1 text-xs text-content">
                            {{ $selectedNames[$id] ?? ('#'.$id) }}
                            <button type="button" wire:click="unpickProduct('{{ $path }}', {{ $id }})"
                                class="text-content-muted hover:text-danger" aria-label="Remove product">&times;</button>
                        </span>
                    @endforeach
                </div>
            @endif
            <div class="relative">
                <x-ui.input wire:model.live.debounce.300ms="productQuery"
                    wire:focus="openProductPicker('{{ $path }}')"
                    placeholder="Search products by name…" />
                @if ($productPath === $path && count($productMatches))
                    <div class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md border border-line bg-surface-overlay shadow-lg">
                        @foreach ($productMatches as $match)
                            <button type="button" wire:click="pickProduct('{{ $path }}', {{ $match['id'] }})"
                                class="block w-full truncate px-3 py-2 text-left text-sm text-content hover:bg-surface-sunken
                                    {{ in_array($match['id'], $selected, true) ? 'opacity-40' : '' }}">
                                {{ $match['name'] }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            @if ($hint)<p class="text-xs text-content-muted">{{ $hint }}</p>@endif
        </div>
        @break

    @case('repeater')
        @php
            $rows = (array) ($value ?? []);
            $isFlat = isset($field['itemType']);
            $rowTemplate = [];
            if (! $isFlat) {
                foreach (($field['subfields'] ?? []) as $sf) {
                    $rowTemplate[$sf['key']] = '';
                }
            }
        @endphp
        <div class="space-y-2" wire:key="rep-{{ $path }}">
            @if ($label)<label class="flex items-center gap-1 text-sm font-medium text-content-secondary">{{ $label }} @if ($required){!! $requiredMark !!}@endif</label>@endif
            @if ($error)<p class="text-xs text-danger">{{ $error }}</p>@endif
            <div class="space-y-2">
                @foreach ($rows as $ri => $row)
                    <div wire:key="rep-{{ str_replace('.', '-', $path) }}-{{ $ri }}"
                        class="space-y-2 rounded-md bg-surface-sunken/50 p-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-medium text-content-muted">#{{ $ri + 1 }}</span>
                            <button type="button" wire:click="removeFieldRow('{{ $path }}', {{ $ri }})"
                                class="text-xs text-danger hover:underline">Remove</button>
                        </div>
                        @if ($isFlat)
                            <x-admin.field
                                :field="['key' => $path.'.'.$ri, 'type' => $field['itemType'], 'label' => '']"
                                :wireModel="$path.'.'.$ri" :value="$row"
                                :productQuery="$ctx['productQuery']" :productPath="$ctx['productPath']" :productMatches="$ctx['productMatches']" />
                        @else
                            @foreach (($field['subfields'] ?? []) as $sf)
                                <x-admin.field :field="$sf"
                                    :wireModel="$path.'.'.$ri.'.'.$sf['key']"
                                    :value="$row[$sf['key']] ?? null"
                                    :productQuery="$ctx['productQuery']" :productPath="$ctx['productPath']" :productMatches="$ctx['productMatches']" />
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
            <button type="button"
                wire:click="addFieldRow('{{ $path }}'{{ $isFlat ? '' : ', '.\Illuminate\Support\Js::from($rowTemplate) }})"
                class="text-sm font-medium text-primary hover:underline">+ Add {{ \Illuminate\Support\Str::singular(strtolower($label ?: 'item')) }}</button>
        </div>
        @break

    @case('url')
    @case('text')
    @default
        <x-ui.input wire:model.blur="{{ $path }}" :label="$label" :hint="$hint" :required="$required" :error="$error" />
@endswitch
