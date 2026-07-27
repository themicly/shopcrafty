<div>
    <h1 class="mb-1 text-xl font-semibold text-content">Upgrade {{ config('app.name', 'Shopcrafty') }}</h1>
    <p class="mb-6 text-sm text-content-muted">
        You've uploaded new files. Run any pending database migrations to finish the upgrade — no shell required.
    </p>

    {{-- Version summary --}}
    <dl class="mb-4 divide-y divide-line rounded-lg border border-line text-sm">
        <div class="flex items-center justify-between px-4 py-2.5">
            <dt class="text-content-muted">Installed version</dt>
            <dd class="font-medium text-content">{{ $currentVersion ?? 'Unknown (legacy install)' }}</dd>
        </div>
        <div class="flex items-center justify-between px-4 py-2.5">
            <dt class="text-content-muted">Uploaded version</dt>
            <dd class="font-medium text-content">{{ $targetVersion }}</dd>
        </div>
        <div class="flex items-center justify-between px-4 py-2.5">
            <dt class="text-content-muted">Pending migrations</dt>
            <dd class="font-medium text-content">{{ $pending }}</dd>
        </div>
    </dl>

    @if ($done)
        {{-- Result of running the upgrade --}}
        <div class="mb-4 rounded-lg border px-4 py-3 text-sm {{ $success ? 'border-success/30 bg-success/5 text-content' : 'border-danger/30 bg-danger/5 text-content' }}">
            <span class="{{ $success ? 'text-success' : 'text-danger' }} font-medium">{{ $success ? '✓' : '✕' }}</span>
            {{ $result }}
        </div>

        @if ($success)
            <x-ui.button href="{{ route('login') }}" variant="secondary">Back to admin →</x-ui.button>
        @else
            <x-ui.button type="button" wire:click="upgrade" wire:loading.attr="disabled" wire:target="upgrade">Try again</x-ui.button>
        @endif
    @elseif (! $upgradeAvailable && $pending === 0)
        {{-- Nothing to do — idempotent no-op path. --}}
        <div class="mb-4 rounded-lg border border-success/30 bg-success/5 px-4 py-3 text-sm text-content">
            <span class="font-medium text-success">✓</span>
            You're already on the latest version. There's nothing to upgrade.
        </div>
        <div class="flex items-center gap-3">
            <x-ui.button href="{{ route('login') }}" variant="secondary">Back to admin →</x-ui.button>
            <button type="button" wire:click="upgrade" wire:loading.attr="disabled" wire:target="upgrade"
                class="text-sm text-content-muted hover:text-content">Re-run migrations anyway</button>
        </div>
    @else
        <p class="mb-5 text-sm text-content-muted">
            This runs <code>php artisan migrate --force</code> and clears caches. It won't touch your existing data or re-run setup.
            Back up your database first if you can.
        </p>
        <x-ui.button type="button" wire:click="upgrade" wire:loading.attr="disabled" wire:target="upgrade">
            <span wire:loading.remove wire:target="upgrade">Run upgrade →</span>
            <span wire:loading wire:target="upgrade">Upgrading…</span>
        </x-ui.button>
    @endif
</div>
