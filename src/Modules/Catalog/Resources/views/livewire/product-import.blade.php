<div class="space-y-5">
    @php
        $guide = [
            'sku' => 'Unique code. Rows with a matching SKU update that product; blank/new SKUs create one.',
            'name' => 'Required.',
            'price' => 'Major units, e.g. 19.99.',
            'category' => 'Matched by name; created if new.',
            'brand' => 'Matched by name; created if new.',
            'track_inventory' => 'yes or no.',
            'status' => 'active, draft, or archived.',
        ];
    @endphp

    <x-ui.card title="Import products" subtitle="Upload a CSV to create new products or update existing ones (matched by SKU).">
        <x-slot:actions>
            <x-ui.button size="sm" variant="secondary" :href="route('admin.catalog.products.import.sample')">Download sample</x-ui.button>
        </x-slot:actions>

        <form wire:submit="import" class="space-y-4">
            <label class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-line bg-surface px-4 py-8 text-center transition-colors hover:border-primary/50">
                <x-ui.icon name="content" class="h-6 w-6 text-content-muted" />
                <span class="text-sm font-medium text-content">Choose a CSV file</span>
                <span class="text-xs text-content-muted">or drag it here · up to 5 MB</span>
                <input type="file" wire:model="file" accept=".csv,text/csv" class="sr-only">
            </label>
            <div wire:loading wire:target="file" class="text-xs text-content-muted">Reading file…</div>
            @if ($file)<p class="text-xs text-success">Ready: {{ $file->getClientOriginalName() }}</p>@endif
            @error('file')<p class="text-xs text-danger">{{ $message }}</p>@enderror

            <x-ui.save-button target="import" label="Import CSV" loadingLabel="Importing…" />
        </form>
    </x-ui.card>

    <x-ui.card title="Columns">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <tbody class="[&_td]:border-t [&_td]:border-line [&_td]:py-2 [&_td]:pr-4">
                    @foreach ($columns as $col)
                        <tr>
                            <td class="whitespace-nowrap font-mono text-xs text-content">{{ $col }}</td>
                            <td class="text-content-muted">{{ $guide[$col] ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-ui.card>

    @if ($result)
        <x-ui.card title="Import summary">
            <div class="flex gap-6 text-sm">
                <div><span class="text-2xl font-semibold text-success">{{ $result['created'] }}</span><p class="text-content-muted">created</p></div>
                <div><span class="text-2xl font-semibold text-info">{{ $result['updated'] }}</span><p class="text-content-muted">updated</p></div>
                <div><span class="text-2xl font-semibold text-warning">{{ count($result['errors']) }}</span><p class="text-content-muted">skipped</p></div>
            </div>
            @if (! empty($result['errors']))
                <ul class="mt-4 space-y-1 text-xs text-content-muted">
                    @foreach ($result['errors'] as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            @endif
        </x-ui.card>
    @endif
</div>
