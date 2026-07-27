<div>
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-content">Demo content</h2>
        <p class="mt-1 text-sm text-content-muted">
            Import a ready-made, theme-matched storefront to explore — or as a starting point you edit.
            Importing is additive and safe to run more than once.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($packs as $key => $pack)
            <div class="flex flex-col justify-between rounded-xl border border-line bg-surface p-5">
                <div>
                    <h3 class="text-sm font-semibold text-content">{{ $pack['label'] }}</h3>
                    <p class="mt-1 text-xs text-content-muted">{{ $pack['description'] }}</p>
                    <p class="mt-3 text-[11px] uppercase tracking-wide text-content-muted">Theme: {{ ucfirst($pack['theme']) }}</p>
                </div>
                <div class="mt-4">
                    <x-ui.button type="button" size="sm" variant="secondary"
                        wire:click="import('{{ $key }}')"
                        wire:target="import('{{ $key }}')"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="import('{{ $key }}')">Import {{ $pack['label'] }}</span>
                        <span wire:loading wire:target="import('{{ $key }}')">Importing…</span>
                    </x-ui.button>
                </div>
            </div>
        @endforeach
    </div>
</div>
