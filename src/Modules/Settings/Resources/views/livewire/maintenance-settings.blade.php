<div class="max-w-2xl space-y-6">
    <x-ui.card title="Storefront maintenance mode" subtitle="Temporarily show a maintenance page to shoppers. You and other admins stay unaffected.">
        <form wire:submit="saveMaintenance" class="space-y-5">
            <x-ui.toggle wire:model="maintenanceEnabled" label="Enable maintenance mode" />
            <x-ui.textarea wire:model="maintenanceMessage" label="Message shown to visitors" rows="3" />
            <x-ui.input wire:model="maintenancePasscode" label="Preview passcode (optional)" hint="Share ?preview=YOURCODE to let others view the live store while it's closed." />
            <x-ui.save-button target="saveMaintenance" label="Save" />
        </form>
    </x-ui.card>

    <x-ui.card title="Caches" subtitle="Clear application caches after configuration changes.">
        <div class="flex flex-wrap items-center gap-4">
            <x-ui.button type="button" variant="secondary" wire:click="clearCache">
                <span wire:loading.remove wire:target="clearCache">Clear all caches</span>
                <span wire:loading wire:target="clearCache">Clearing…</span>
            </x-ui.button>
            <div class="text-xs text-content-muted">
                Cache driver: <span class="font-medium text-content-secondary">{{ $cacheDriver }}</span>
                &middot; Queue: <span class="font-medium text-content-secondary">{{ $queueDriver }}</span>
            </div>
        </div>
    </x-ui.card>
</div>
