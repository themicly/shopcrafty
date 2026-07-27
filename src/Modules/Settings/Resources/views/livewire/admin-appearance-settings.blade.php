@php
    // Guards against CSS injection if a value ever bypasses server validation
    // (e.g. set directly via tinker) — same convention as the storefront
    // theme layout's $cssToken guard.
    $cssToken = fn ($value, $fallback) => (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) ? $value : $fallback;
    $p = $cssToken($primary, \Themicly\Shopcrafty\Modules\Settings\Livewire\AdminAppearanceSettings::DEFAULT_PRIMARY);
    $pfg = $cssToken($primaryFg, \Themicly\Shopcrafty\Modules\Settings\Livewire\AdminAppearanceSettings::DEFAULT_PRIMARY_FG);
    $b2 = $cssToken($brand2, \Themicly\Shopcrafty\Modules\Settings\Livewire\AdminAppearanceSettings::DEFAULT_BRAND_2);
@endphp

<div>
    {{-- Live preview: this tag is part of the Livewire component's own render
         output, so Livewire morphs its content on every wire:model.live change
         — updating --bz-* globally (sidebar, buttons, badges) before Save is
         even clicked, the same trick the storefront customizer's iframe does
         with a full page reload, but here it's instant since it's just tokens. --}}
    <style>
        :root {
            --bz-primary: {{ $p }};
            --bz-primary-hover: color-mix(in srgb, {{ $p }} 85%, black);
            --bz-primary-soft: color-mix(in srgb, {{ $p }} 12%, white);
            --bz-primary-fg: {{ $pfg }};
            --bz-brand-2: {{ $b2 }};
        }
        [data-theme='dark'] {
            --bz-primary: {{ $p }};
            --bz-primary-hover: color-mix(in srgb, {{ $p }} 80%, white);
            --bz-primary-soft: color-mix(in srgb, {{ $p }} 18%, black);
            --bz-primary-fg: {{ $pfg }};
            --bz-brand-2: {{ $b2 }};
        }
    </style>

    <form wire:submit="save" class="max-w-2xl space-y-6">
        <x-ui.card title="Admin panel colors" subtitle="Recolors the admin sidebar, buttons and highlights — not your storefront theme.">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-content-secondary">Primary</label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live.debounce.150ms="primary"
                            class="h-9 w-12 shrink-0 cursor-pointer rounded-md border border-line bg-surface-raised p-1"
                            aria-label="Primary color">
                        <x-ui.input wire:model.live.debounce.300ms="primary" class="flex-1" :error="$errors->first('primary')" />
                    </div>
                    <p class="mt-1 text-xs text-content-muted">Buttons, links, active sidebar item.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-content-secondary">Text on primary</label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live.debounce.150ms="primaryFg"
                            class="h-9 w-12 shrink-0 cursor-pointer rounded-md border border-line bg-surface-raised p-1"
                            aria-label="Primary text color">
                        <x-ui.input wire:model.live.debounce.300ms="primaryFg" class="flex-1" :error="$errors->first('primaryFg')" />
                    </div>
                    <p class="mt-1 text-xs text-content-muted">Keep this high-contrast against Primary.</p>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-content-secondary">Secondary accent</label>
                    <div class="flex items-center gap-2">
                        <input type="color" wire:model.live.debounce.150ms="brand2"
                            class="h-9 w-12 shrink-0 cursor-pointer rounded-md border border-line bg-surface-raised p-1"
                            aria-label="Secondary accent color">
                        <x-ui.input wire:model.live.debounce.300ms="brand2" class="flex-1" :error="$errors->first('brand2')" />
                    </div>
                    <p class="mt-1 text-xs text-content-muted">Used in gradients, e.g. the sidebar brand mark.</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Preview">
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-semibold" style="background: var(--bz-primary); color: var(--bz-primary-fg)">
                    Primary button
                </span>
                <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold" style="background: var(--bz-primary-soft); color: var(--bz-primary)">
                    Soft badge
                </span>
                <span class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium" style="background: var(--bz-primary-soft); color: var(--bz-primary)">
                    <span class="h-2 w-2 rounded-full" style="background: var(--bz-primary)" aria-hidden="true"></span>
                    Active nav item
                </span>
                <span class="h-8 w-8 rounded-lg" style="background: linear-gradient(135deg, var(--bz-primary), var(--bz-brand-2))" aria-hidden="true"></span>
            </div>
        </x-ui.card>

        <x-admin.form-actions>
            <x-ui.save-button target="save" label="Save changes" />
            <button type="button" wire:click="resetToDefault" class="text-sm font-medium text-content-muted hover:text-content">
                Reset to default
            </button>
        </x-admin.form-actions>
    </form>
</div>
