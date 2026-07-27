<div>
    <form wire:submit="save" class="max-w-2xl space-y-6">
        <x-ui.card title="Country preset" subtitle="Sets currency, timezone, phone format, and suggested payments in one step.">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-56 flex-1">
                    <x-ui.select wire:model="country" label="Country">
                        @foreach ($countries as $key => $preset)
                            <option value="{{ $key }}">{{ $preset['name'] }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
                <x-ui.button type="button" variant="secondary" wire:click="applyPreset">Apply preset defaults</x-ui.button>
            </div>
            <p class="mt-3 text-xs text-content-muted">Applying a preset overwrites the fields below with that country's defaults. Review, then save.</p>
        </x-ui.card>

        <x-ui.card title="Currency">
            <div class="grid gap-5 sm:grid-cols-3">
                <x-ui.input wire:model="currencyCode" label="Code (ISO)" hint="e.g. USD, BDT, EUR — used to charge gateways" :error="$errors->first('currencyCode')" />
                <x-ui.input wire:model="currencySymbol" label="Symbol" :error="$errors->first('currencySymbol')" />
                <x-ui.select wire:model="currencyPosition" label="Position">
                    <option value="before">Before ($100)</option>
                    <option value="after">After (100$)</option>
                </x-ui.select>
                <x-ui.input wire:model="currencyDecimals" type="number" min="0" max="4" label="Decimals" :error="$errors->first('currencyDecimals')" />
            </div>

            {{-- Additional display currencies (storefront switcher). Money is still
                 charged in the base currency above; these convert prices for display. --}}
            <div class="mt-6 border-t border-line pt-5">
                <div class="mb-2 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-content">Display currencies</p>
                        <p class="text-xs text-content-muted">Let shoppers view prices in other currencies. Rate = value of 1 base unit.</p>
                    </div>
                    <x-ui.button type="button" size="sm" variant="secondary" wire:click="addCurrency">+ Add</x-ui.button>
                </div>

                @forelse ($currencies as $i => $c)
                    <div class="mb-2 grid grid-cols-[1fr_1fr_1.2fr_auto] items-end gap-2" wire:key="cur-{{ $i }}">
                        <x-ui.input wire:model="currencies.{{ $i }}.code" label="Code" placeholder="EUR" :error="$errors->first('currencies.'.$i.'.code')" />
                        <x-ui.input wire:model="currencies.{{ $i }}.symbol" label="Symbol" placeholder="€" :error="$errors->first('currencies.'.$i.'.symbol')" />
                        <x-ui.input wire:model="currencies.{{ $i }}.rate" label="Rate" placeholder="0.92" :error="$errors->first('currencies.'.$i.'.rate')" />
                        <x-ui.icon-button icon="trash" variant="danger" label="Remove" wire:click="removeCurrency({{ $i }})" class="mb-0.5" />
                    </div>
                @empty
                    <p class="text-xs text-content-muted">No extra currencies — the storefront shows the base currency only.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="Regional">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.select wire:model="timezone" label="Timezone">
                    @foreach ($timezones as $tz)
                        <option value="{{ $tz }}">{{ $tz }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input wire:model="dateFormat" label="Date format" hint="PHP date() format, e.g. M j, Y" :error="$errors->first('dateFormat')" />
                <x-ui.select wire:model="weightUnit" label="Weight unit">
                    <option value="kg">Kilogram (kg)</option>
                    <option value="g">Gram (g)</option>
                    <option value="lb">Pound (lb)</option>
                </x-ui.select>
                <x-ui.select wire:model="language" label="Storefront language" hint="The whole storefront renders in this one language — shoppers don't get a switcher." :error="$errors->first('language')">
                    @foreach ($languages as $code => $label)
                        <option value="{{ $code }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model="textDirection" label="Text direction" hint="A manual choice, independent of the language above — set it yourself for right-to-left languages like Arabic." :error="$errors->first('textDirection')">
                    <option value="ltr">Left to right (LTR)</option>
                    <option value="rtl">Right to left (RTL)</option>
                </x-ui.select>
            </div>
        </x-ui.card>

        <x-admin.form-actions>
            <x-ui.save-button target="save" label="Save changes" />
        </x-admin.form-actions>
    </form>
</div>
