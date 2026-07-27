<div>
    <form wire:submit="create" class="max-w-xl space-y-6">
        <x-admin.note variant="tip" title="Adding a customer yourself?">
            Use this for shoppers you serve over phone or WhatsApp. Add at least a name — an email or mobile lets you reach them and reuse their details at checkout.
        </x-admin.note>

        <x-ui.card title="New customer" subtitle="For phone or WhatsApp shoppers you add yourself.">
            <div class="space-y-4">
                <x-ui.input wire:model="name" label="Name" required :error="$errors->first('name')" hint="The shopper's full name." />
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input wire:model="email" type="email" label="Email" optional :error="$errors->first('email')" />
                    <x-ui.input wire:model="mobile" label="Mobile" optional hint="Include country code" :error="$errors->first('mobile')" />
                </div>
                <x-ui.select wire:model="status" label="Status" class="max-w-xs">
                    <option value="active">Active</option>
                    <option value="blocked">Blocked</option>
                </x-ui.select>
            </div>
        </x-ui.card>

        <p class="text-xs text-content-muted">Fields marked <span class="text-danger">*</span> are required.</p>

        <x-ui.save-button target="create" label="Create customer" loadingLabel="Creating…" />
    </form>
</div>
