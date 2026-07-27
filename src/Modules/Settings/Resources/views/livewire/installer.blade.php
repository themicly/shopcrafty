<div>
    {{-- Step indicator --}}
    @php
        $steps = ['Requirements', 'Database', 'Admin', 'Store', 'Demo', 'Done'];
    @endphp
    <div class="mb-8 flex items-center justify-between">
        @foreach ($steps as $i => $label)
            @php $n = $i + 1; $state = $step > $n ? 'done' : ($step === $n ? 'current' : 'todo'); @endphp
            <div class="flex flex-1 flex-col items-center gap-1">
                <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold
                    {{ $state === 'current' ? 'bg-primary text-white' : ($state === 'done' ? 'bg-primary-soft text-primary' : 'bg-surface text-content-muted') }}">
                    {{ $state === 'done' ? '✓' : $n }}
                </span>
                <span class="hidden text-[11px] {{ $state === 'todo' ? 'text-content-muted' : 'text-content-secondary' }} sm:block">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    {{-- Step 1: Requirements --}}
    @if ($step === 1)
        <h1 class="mb-1 text-xl font-semibold text-content">Server requirements</h1>
        <p class="mb-5 text-sm text-content-muted">We checked your hosting environment.</p>
        <ul class="mb-4 divide-y divide-line rounded-lg border border-line">
            @foreach ($requirements as $row)
                <li class="flex items-center justify-between gap-3 px-4 py-2.5 text-sm">
                    <span class="text-content">{{ $row['label'] }}</span>
                    <span class="flex items-center gap-2">
                        <span class="text-xs text-content-muted">{{ $row['hint'] }}</span>
                        <span class="{{ $row['ok'] ? 'text-success' : 'text-danger' }}">{{ $row['ok'] ? '✓' : '✕' }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
        @error('requirements')<p class="mb-3 text-sm text-danger">{{ $message }}</p>@enderror

    {{-- Step 2: Database --}}
    @elseif ($step === 2)
        <h1 class="mb-1 text-xl font-semibold text-content">Database</h1>
        @if ($dbConnected)
            <div class="mb-4 rounded-lg border border-success/30 bg-success/5 px-4 py-3 text-sm text-content">
                ✓ Connected using your current configuration. No changes needed.
            </div>
        @else
            <p class="mb-5 text-sm text-content-muted">Enter your database details, then test the connection.</p>
            <div class="grid gap-3 sm:grid-cols-2">
                <x-ui.input wire:model="dbHost" label="Host" />
                <x-ui.input wire:model="dbPort" label="Port" />
                <x-ui.input wire:model="dbName" label="Database name" class="sm:col-span-2" />
                <x-ui.input wire:model="dbUser" label="Username" />
                <x-ui.input wire:model="dbPass" type="password" label="Password" />
            </div>
            @if ($dbError)<p class="mt-3 text-sm text-danger">{{ $dbError }}</p>@endif
            <x-ui.button type="button" wire:click="testDatabase" variant="secondary" class="mt-4">Test connection</x-ui.button>
        @endif
        @error('db')<p class="mt-3 text-sm text-danger">{{ $message }}</p>@enderror

    {{-- Step 3: Admin --}}
    @elseif ($step === 3)
        <h1 class="mb-1 text-xl font-semibold text-content">Admin account</h1>
        <p class="mb-5 text-sm text-content-muted">Create the store owner login.</p>
        <div class="space-y-3">
            <x-ui.input wire:model="adminName" label="Your name" :error="$errors->first('adminName')" />
            <x-ui.input wire:model="adminEmail" type="email" label="Email" :error="$errors->first('adminEmail')" />
            <x-ui.input wire:model="adminPassword" type="password" label="Password" hint="At least 8 characters." :error="$errors->first('adminPassword')" />
        </div>

    {{-- Step 4: Store --}}
    @elseif ($step === 4)
        <h1 class="mb-1 text-xl font-semibold text-content">Your store</h1>
        <p class="mb-5 text-sm text-content-muted">Name your store and pick a regional preset (currency, timezone, formats).</p>
        <div class="space-y-3">
            <x-ui.input wire:model="storeName" label="Store name" :error="$errors->first('storeName')" />
            <x-ui.select wire:model="country" label="Country / region preset" :error="$errors->first('country')">
                @foreach ($presets as $key => $preset)
                    <option value="{{ $key }}">{{ $preset['name'] }} — {{ $preset['currency_code'] }}</option>
                @endforeach
            </x-ui.select>
        </div>

    {{-- Step 5: Demo --}}
    @elseif ($step === 5)
        <h1 class="mb-1 text-xl font-semibold text-content">Demo content</h1>
        <p class="mb-5 text-sm text-content-muted">Start with a ready-made store you can edit — or skip and start empty.</p>
        <div class="space-y-2">
            <label class="flex cursor-pointer items-start gap-3 rounded-lg border px-4 py-3 {{ $demoPack === '' ? 'border-primary' : 'border-line' }}">
                <input type="radio" wire:model.live="demoPack" value="" class="mt-1">
                <span><span class="block text-sm font-medium text-content">Start empty</span><span class="block text-xs text-content-muted">No sample products — I'll add my own.</span></span>
            </label>
            @foreach ($packs as $key => $pack)
                <label class="flex cursor-pointer items-start gap-3 rounded-lg border px-4 py-3 {{ $demoPack === $key ? 'border-primary' : 'border-line' }}">
                    <input type="radio" wire:model.live="demoPack" value="{{ $key }}" class="mt-1">
                    <span><span class="block text-sm font-medium text-content">{{ $pack['label'] }}</span><span class="block text-xs text-content-muted">{{ $pack['description'] }}</span></span>
                </label>
            @endforeach
        </div>
        <p wire:loading wire:target="next" class="mt-3 text-sm text-primary">Importing demo content…</p>

    {{-- Step 6: Done --}}
    @elseif ($step === 6)
        <div class="py-6 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-primary-soft text-2xl text-primary">✓</div>
            <h1 class="mb-1 text-xl font-semibold text-content">You're all set</h1>
            <p class="mb-6 text-sm text-content-muted">Your store is installed and ready. Sign in to start selling.</p>
            <x-ui.button type="button" wire:click="finish">Go to admin login →</x-ui.button>
        </div>
    @endif

    {{-- Nav --}}
    @if ($step < 6)
        <div class="mt-8 flex items-center justify-between">
            <button type="button" wire:click="back" @class(['text-sm text-content-muted hover:text-content', 'invisible' => $step === 1]) @if($step === 1) disabled @endif>← Back</button>
            <x-ui.button type="button" wire:click="next" wire:loading.attr="disabled" wire:target="next">
                {{ $step === 5 ? 'Finish setup' : 'Continue' }} →
            </x-ui.button>
        </div>
    @endif
</div>
