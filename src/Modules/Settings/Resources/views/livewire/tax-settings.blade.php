<div>
    <form wire:submit="save" class="max-w-2xl space-y-6">
        <x-ui.card title="Tax / VAT" subtitle="Charge tax on orders. Turn off if your prices are tax-free.">
            <div class="space-y-5">
                <x-ui.toggle wire:model="enabled" label="Charge tax on orders" />

                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input wire:model="label" label="Tax label" hint="Shown to customers, e.g. VAT, GST, Sales tax" :error="$errors->first('label')" />
                    <x-ui.input wire:model="rate" type="number" step="0.01" label="Rate (%)" hint="e.g. 15 for 15%" :error="$errors->first('rate')" />
                </div>

                <div>
                    <x-ui.toggle wire:model="inclusive" label="Prices already include tax" />
                    <p class="mt-1.5 text-xs text-content-muted">
                        On = product prices are tax-inclusive (tax is shown for information, not added).
                        Off = tax is added on top at checkout.
                    </p>
                </div>
            </div>
        </x-ui.card>

        <x-admin.form-actions>
            <x-ui.save-button target="save" label="Save changes" />
        </x-admin.form-actions>
    </form>
</div>
