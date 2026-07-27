@php($addons = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class))

<div class="max-w-2xl">
    <form wire:submit="save" class="space-y-6">
        <x-ui.card title="Shopper features" subtitle="Turn storefront modules on or off — links, buttons and pages hide instantly when a feature is off.">
            <div class="space-y-4">
                @if ($addons->installed('reviews'))
                    <div>
                        <x-ui.toggle wire:model="reviewsEnabled" label="Product reviews & star ratings" />
                        <p class="mt-1 text-xs text-content-muted">When off, star ratings are hidden across the storefront and the rating filter is removed.</p>
                    </div>
                @endif
                @if ($addons->installed('wishlist'))
                    <div>
                        <x-ui.toggle wire:model="wishlistEnabled" label="Wishlist" />
                        <p class="mt-1 text-xs text-content-muted">When off, wishlist hearts and the wishlist page are hidden. Saved lists are kept.</p>
                    </div>
                @endif
                @if ($addons->installed('compare'))
                    <div>
                        <x-ui.toggle wire:model="compareEnabled" label="Product compare" />
                        <p class="mt-1 text-xs text-content-muted">When off, compare buttons and the compare page are hidden.</p>
                    </div>
                @endif
                <div>
                    <x-ui.toggle wire:model="newsletterEnabled" label="Newsletter signup" />
                    <p class="mt-1 text-xs text-content-muted">When off, signup forms disappear from homepage sections and footers. Existing subscribers are kept.</p>
                </div>
            </div>
        </x-ui.card>

        @if ($addons->installed('popular-search'))
        <x-ui.card title="Popular search terms" subtitle="Shown as clickable suggestion chips under the header search bar when a shopper hasn't typed anything yet. Up to 10.">
            <div class="space-y-3">
                @if (count($popularSearchTerms))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($popularSearchTerms as $index => $term)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-line bg-surface px-3 py-1 text-sm text-content">
                                {{ $term }}
                                <button type="button" wire:click="removePopularSearchTerm({{ $index }})" class="text-content-muted hover:text-danger" aria-label="Remove {{ $term }}">✕</button>
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-content-muted">No popular terms configured yet — the suggestion row stays hidden on the storefront until you add one.</p>
                @endif

                @if (count($popularSearchTerms) < \Themicly\Shopcrafty\Modules\Themes\Livewire\StorefrontSettings::MAX_POPULAR_TERMS)
                    {{-- Not a <form> — this already lives inside the page's outer <form>,
                         and a nested <form> is invalid HTML (the browser drops the inner
                         tag and its </form> closes the outer one early, breaking both
                         "Add" and the real Save button). Enter-to-submit + button click
                         both call addPopularSearchTerm() directly instead. --}}
                    <div class="flex items-center gap-2" wire:keydown.enter.prevent="addPopularSearchTerm">
                        <input type="text" wire:model="newPopularSearchTerm" maxlength="60" placeholder="e.g. wireless headphones" class="h-10 flex-1 rounded-lg border border-line bg-surface px-3 text-sm focus:border-primary focus:outline-none">
                        <button type="button" wire:click="addPopularSearchTerm" class="h-10 shrink-0 rounded-lg border border-line px-4 text-sm font-medium text-content hover:bg-surface-raised">Add</button>
                    </div>
                @else
                    <p class="text-xs text-content-muted">Limit reached — remove a term to add another.</p>
                @endif
            </div>
        </x-ui.card>
        @endif

        <x-admin.note>
            Store name, contact details, branding and social links live in
            <a href="{{ route('admin.settings.index') }}" class="font-medium text-primary">Settings → General</a>;
            colors, fonts and store text in the
            <a href="{{ route('admin.themes.customize') }}" class="font-medium text-primary">Customizer</a>.
        </x-admin.note>

        <x-admin.form-actions>
            <x-ui.save-button target="save" label="Save changes" />
        </x-admin.form-actions>
    </form>
</div>
