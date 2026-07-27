<div>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($themes as $theme)
            @php
                $meta = $metadata($theme->slug);
                $s = $meta['settings'] ?? [];
                $primary = $s['primary'] ?? '#2563eb';
                $accent = $s['accent'] ?? '#f43f5e';
                $bg = $s['bg'] ?? '#ffffff';
                $surface = $s['surface'] ?? '#f4f4f5';
                $ink = $s['ink'] ?? '#111111';
                $inkSoft = $s['ink_soft'] ?? '#9ca3af';
                $line = $s['line'] ?? '#e5e7eb';
                $radius = $s['radius'] ?? '10px';
                $displayFont = $s['display_font'] ?? 'inherit';
                // Live preview: a signed URL renders the real homepage under this
                // theme for one request (ApplyThemePreview middleware).
                $livePreviewUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'shopcrafty.storefront', now()->addHour(), ['_theme' => $theme->slug],
                );
            @endphp

            <div @class([
                    'overflow-hidden rounded-xl border bg-surface-raised transition-shadow',
                    'border-primary ring-2 ring-primary shadow-md' => $theme->is_active,
                    'border-line hover:shadow-sm' => ! $theme->is_active,
                ]) wire:key="theme-{{ $theme->id }}">

                {{-- Live preview: the real storefront homepage rendered under this
                     theme, scaled down (iframe spans 500% and shrinks to 20%). --}}
                <div class="relative aspect-video overflow-hidden" style="background: {{ $bg }}">
                    <iframe
                        src="{{ $livePreviewUrl }}"
                        title="{{ $theme->name }} live preview"
                        loading="lazy"
                        scrolling="no"
                        tabindex="-1"
                        aria-hidden="true"
                        class="pointer-events-none absolute left-0 top-0"
                        style="width: 500%; height: 500%; transform: scale(0.2); transform-origin: top left; border: 0"
                    ></iframe>

                    @if ($theme->is_active)
                        <span class="absolute right-2 top-2 inline-flex items-center gap-1 rounded-full bg-primary px-2 py-0.5 text-[10px] font-semibold text-primary-fg shadow-sm">
                            <span class="h-1.5 w-1.5 rounded-full bg-primary-fg"></span> Active
                        </span>
                    @endif
                </div>

                <div class="p-4">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-content">{{ $theme->name }}</h3>
                        <div class="flex items-center gap-1.5">
                            @foreach (['primary', 'accent', 'ink'] as $tok)
                                @if (! empty($s[$tok]))
                                    <span class="h-3.5 w-3.5 rounded-full ring-1 ring-black/10" style="background: {{ $s[$tok] }}"></span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <p class="mt-0.5 text-xs text-content-muted">by {{ $theme->author ?? 'Unknown' }} · v{{ $theme->version }}</p>
                    @if (! empty($meta['shop_type']))
                        <p class="mt-2 text-xs font-medium text-content-secondary">{{ $meta['shop_type'] }}</p>
                    @endif
                    @if (! empty($meta['description']))
                        <p class="mt-1 line-clamp-2 text-xs text-content-muted">{{ $meta['description'] }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-2">
                        @if ($theme->is_active)
                            <x-ui.button size="sm" :href="route('admin.themes.customize')">Customize</x-ui.button>
                            <x-ui.button size="sm" variant="secondary" :href="route('admin.themes.sections')">Sections</x-ui.button>
                        @else
                            <x-ui.button size="sm" variant="secondary"
                                x-on:click="$dispatch('confirm', { title: 'Activate {{ $theme->name }}?', message: 'This changes the live storefront theme customers see immediately.', confirmLabel: 'Activate', variant: 'primary', onConfirm: () => $wire.activate({{ $theme->id }}) })">Activate</x-ui.button>
                            <x-ui.button size="sm" variant="ghost" :href="$livePreviewUrl" target="_blank" rel="noopener">Preview</x-ui.button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
