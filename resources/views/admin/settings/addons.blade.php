@php($addons = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class))

<x-layouts.admin title="Add-ons">
    <x-admin.settings-shell>
        <div class="space-y-6">
            <div>
                <h1 class="text-xl font-semibold text-content">Installed add-ons</h1>
                <p class="mt-1 text-sm text-content-muted">Optional Shopcrafty packages extend your storefront and admin panel when installed.</p>
            </div>

            @forelse ($addons->all() as $key => $addon)
                <div class="rounded-xl border border-line bg-surface-raised p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="font-medium text-content">{{ $addon['name'] ?? str($key)->headline() }}</h2>
                            <p class="mt-1 text-xs uppercase tracking-wide text-content-muted">{{ $key }}</p>
                        </div>
                        <span class="rounded-full bg-primary-soft px-2.5 py-1 text-xs font-medium text-primary">Installed</span>
                    </div>
                    @if (! empty($addon['description']))
                        <p class="mt-3 text-sm text-content-muted">{{ $addon['description'] }}</p>
                    @endif
                    @if ($schema = $addons->settingsSchemas()[$key] ?? null)
                        <p class="mt-3 text-xs text-content-muted">Configuration fields: {{ implode(', ', $schema['fields'] ?? []) }}</p>
                    @endif
                    @if (! empty($addon['settings_route']) && Route::has($addon['settings_route']))
                        <a href="{{ route($addon['settings_route']) }}" class="mt-4 inline-flex text-sm font-medium text-primary hover:underline">Configure add-on →</a>
                    @endif
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-line p-8 text-center">
                    <p class="font-medium text-content">No optional add-ons installed</p>
                    <p class="mt-1 text-sm text-content-muted">Install a Shopcrafty package with Composer and it will appear here automatically.</p>
                </div>
            @endforelse
        </div>
    </x-admin.settings-shell>
</x-layouts.admin>
