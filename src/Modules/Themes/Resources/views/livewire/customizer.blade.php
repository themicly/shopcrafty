<div class="flex h-screen flex-col bg-surface" x-data="{ device: 'desktop', v: Date.now(), panel: 'settings' }" @preview-updated.window="v = Date.now()">
    {{-- Top bar --}}
    <div class="flex min-h-14 shrink-0 flex-wrap items-center justify-between gap-2 border-b border-line bg-surface-raised px-4 py-2">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.themes.index') }}" class="text-sm text-content-secondary hover:text-content">← Exit</a>
            <span class="text-sm font-semibold text-content">Customize theme</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1 rounded-md border border-line p-0.5">
                <button @click="device = 'mobile'" class="rounded px-2 py-1 text-xs" :class="device === 'mobile' ? 'bg-surface-sunken text-content' : 'text-content-muted'">Mobile</button>
                <button @click="device = 'desktop'" class="rounded px-2 py-1 text-xs" :class="device === 'desktop' ? 'bg-surface-sunken text-content' : 'text-content-muted'">Desktop</button>
            </div>
            <x-ui.button variant="ghost" size="sm" wire:click="resetToPublished">Reset</x-ui.button>
            <x-ui.button size="sm" wire:click="publish">Publish</x-ui.button>
        </div>
    </div>

    {{-- Settings/Preview switch — side-by-side panels don't fit below lg, so phones/tablets
         get an explicit toggle instead of a squeezed preview pane. --}}
    <div class="flex shrink-0 items-center gap-1 border-b border-line bg-surface-raised p-2 lg:hidden">
        <button @click="panel = 'settings'" class="flex-1 rounded px-3 py-1.5 text-sm font-medium" :class="panel === 'settings' ? 'bg-surface-sunken text-content' : 'text-content-muted'">Settings</button>
        <button @click="panel = 'preview'" class="flex-1 rounded px-3 py-1.5 text-sm font-medium" :class="panel === 'preview' ? 'bg-surface-sunken text-content' : 'text-content-muted'">Preview</button>
    </div>

    <div class="flex min-h-0 flex-1 flex-col lg:flex-row">
        {{-- Controls --}}
        <div class="w-full shrink-0 space-y-6 overflow-y-auto border-line bg-surface-raised p-5 lg:w-80 lg:border-r"
            :class="panel === 'settings' ? 'block' : 'hidden lg:block'">
            <div x-data="{
                    current: $wire.entangle('activeThemeId'),
                    pending: null,
                    pendingName: '',
                    open: false,
                    ask(e) {
                        const id = Number(e.target.value);
                        if (id === Number(this.current)) return;
                        this.pending = id;
                        this.pendingName = e.target.selectedOptions[0].text;
                        this.open = true;
                    },
                    cancel() { this.open = false; this.pending = null; $refs.picker.value = this.current; },
                    confirm() { this.open = false; $wire.switchTheme(this.pending); },
                }">
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-content-muted">Theme</h3>
                <x-ui.select x-ref="picker" x-on:change="ask($event)" label="Active theme" hint="Switching applies to your live storefront.">
                    @foreach ($themes as $theme)
                        <option value="{{ $theme->id }}" @selected($theme->id === $activeThemeId)>{{ $theme->name }}</option>
                    @endforeach
                </x-ui.select>
                <p wire:loading wire:target="switchTheme" class="mt-1.5 text-xs text-content-muted">Switching theme…</p>

                {{-- Confirm dialog. Self-contained (the shared x-ui.modal teleports to
                     <body>, out of this Alpine scope), so refs/state stay reachable. --}}
                <div x-show="open" x-cloak @keydown.escape.window="cancel()"
                    class="fixed inset-0 z-[70] flex items-center justify-center bg-black/50 p-4">
                    <div @click.outside="cancel()" class="w-full max-w-sm rounded-xl border border-line bg-surface-overlay p-5 shadow-lg">
                        <h4 class="text-sm font-semibold text-content">Switch theme?</h4>
                        <p class="mt-1.5 text-sm text-content-secondary">
                            Activating <span class="font-medium text-content" x-text="pendingName"></span> changes your live storefront immediately, and unpublished changes to the current theme will be discarded.
                        </p>
                        <div class="mt-4 flex justify-end gap-2">
                            <x-ui.button variant="ghost" size="sm" x-on:click="cancel()">Cancel</x-ui.button>
                            <x-ui.button size="sm" x-on:click="confirm()">Switch theme</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-content-muted">Colors</h3>
                <div class="space-y-3">
                    @foreach (['primary' => 'Primary', 'accent' => 'Accent', 'bg' => 'Background', 'ink' => 'Text', 'ink_soft' => 'Muted text', 'line' => 'Borders', 'surface' => 'Surface'] as $key => $label)
                        <label class="flex items-center justify-between gap-3">
                            <span class="text-sm text-content-secondary">{{ $label }}</span>
                            <input type="color" wire:model.live.debounce.300ms="settings.{{ $key }}" class="h-8 w-14 cursor-pointer rounded border border-line bg-surface-raised">
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-content-muted">Typography & shape</h3>
                <div class="space-y-3">
                    <x-ui.select wire:model.live="settings.display_font" label="Display font" required :error="$errors->first('settings.display_font')">
                        @foreach ($fonts as $value => $name)
                            <option value="{{ $value }}">{{ $name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model.live="settings.body_font" label="Body font" required :error="$errors->first('settings.body_font')">
                        @foreach ($fonts as $value => $name)
                            <option value="{{ $value }}">{{ $name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.input wire:model.live.debounce.400ms="settings.radius" label="Corner radius" required hint="e.g. 0px, 8px, 14px, 24px" :error="$errors->first('settings.radius')" />
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-content-muted">Content</h3>
                <div class="space-y-3">
                    <x-ui.toggle wire:model.live="settings.show_announcement" label="Show announcement bar" />
                    <x-ui.input wire:model.live.debounce.400ms="settings.announcement" label="Announcement text" />
                    <x-ui.input wire:model.live.debounce.400ms="settings.footer_text" label="Footer tagline" />
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-content-muted">Header</h3>
                <div class="space-y-3">
                    <x-ui.select wire:model.live="settings.header_layout" label="Layout" required :error="$errors->first('settings.header_layout')">
                        <option value="logo-left">Logo left, nav inline</option>
                        <option value="logo-center">Logo centered, nav below</option>
                    </x-ui.select>
                    <x-ui.toggle wire:model.live="settings.header_sticky" label="Sticky header" />
                    <x-ui.toggle wire:model.live="settings.header_transparent_home" label="Transparent over homepage hero" />
                </div>
            </div>

            <div>
                <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-content-muted">Footer</h3>
                <div class="space-y-3">
                    <x-ui.toggle wire:model.live="settings.footer_show_payment_icons" label="Show payment icons" />
                    <x-ui.textarea wire:model.live.debounce.400ms="settings.footer_payment_methods" label="Payment methods" rows="2" hint="Comma- or line-separated labels shown in the storefront footer." />
                    <x-ui.toggle wire:model.live="settings.footer_newsletter" label="Newsletter signup" />
                </div>
            </div>

            @if (! empty($textKeys))
                <div>
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-content-muted">Store text</h3>
                    <div class="space-y-3">
                        {{-- Every header_/footer_/text_ string the theme declares in its
                             manifest is editable copy — no code changes needed per theme. --}}
                        @foreach ($textKeys as $key)
                            <x-ui.input
                                wire:model.live.debounce.400ms="settings.{{ $key }}"
                                :label="ucfirst(str_replace('_', ' ', preg_replace('/^(header_|footer_|text_)/', '', $key))) . (str_starts_with($key, 'footer_') ? ' (footer)' : (str_starts_with($key, 'header_') ? ' (header)' : ''))"
                            />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Preview --}}
        <div class="min-h-0 min-w-0 flex-1 items-start justify-center overflow-auto bg-surface-sunken p-3 sm:p-6 lg:flex"
            :class="panel === 'preview' ? 'flex' : 'hidden lg:flex'">
            <div class="h-full w-full overflow-hidden rounded-xl border border-line bg-white shadow-sm transition-all"
                :class="device === 'mobile' ? 'max-w-[400px]' : 'max-w-full'">
                <iframe :src="'{{ url('/') }}?preview=1&v=' + v" class="h-full w-full" style="min-height: 100%"></iframe>
            </div>
        </div>
    </div>
</div>
