<div>
    <form wire:submit="save" class="max-w-2xl space-y-6">
        <x-admin.note>
            Editing text for <strong>{{ $theme?->name ?? 'the active theme' }}</strong>. Switching themes changes
            which fields appear here — each theme declares its own copy. For colors, fonts and layout, use
            <a href="{{ route('admin.themes.customize') }}" class="font-medium text-primary">the visual customizer</a> instead.
        </x-admin.note>

        <x-ui.card title="Announcement bar" subtitle="The slim banner at the very top of the storefront.">
            <div class="space-y-4">
                <x-ui.toggle wire:model="showAnnouncement" label="Show announcement bar" />
                <x-ui.input wire:model="announcement" label="Announcement text" placeholder="Free shipping on orders over $50 · Shop now" />
            </div>
        </x-ui.card>

        <x-ui.card title="Footer" subtitle="Shown under your logo in the storefront footer.">
            <x-ui.textarea wire:model="footerText" label="Footer tagline" rows="2" />
        </x-ui.card>

        @if (! empty($text))
            <x-ui.card title="Page copy" subtitle="Labels and prompts declared by this theme's header and footer.">
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($text as $key => $value)
                        <x-ui.input wire:model="text.{{ $key }}" :label="$labels[$key] ?? $key" />
                    @endforeach
                </div>
            </x-ui.card>
        @endif

        <div class="flex items-center gap-3">
            <x-ui.save-button target="save" label="Save changes" />
        </div>
    </form>
</div>
