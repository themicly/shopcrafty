<div>
    <form wire:submit="save" class="max-w-2xl space-y-6">
        <x-ui.card title="Store details" subtitle="Shown across your storefront and emails.">
            <div class="space-y-5">
                <x-ui.input wire:model="storeName" label="Store name" :error="$errors->first('storeName')" />
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.input wire:model="storeEmail" type="email" label="Contact email" :error="$errors->first('storeEmail')" />
                    <x-ui.input wire:model="storePhone" label="Contact phone" :error="$errors->first('storePhone')" />
                </div>
                <x-ui.input wire:model="orderNumberPrefix" label="Order number prefix" hint="e.g. SC-9F3K2LQXAB" class="max-w-[12rem]" :error="$errors->first('orderNumberPrefix')" />
            </div>
        </x-ui.card>

        <x-ui.card title="Branding" subtitle="Your logo shows in the storefront header; the favicon in browser tabs.">
            <div class="space-y-8">
                {{-- Logo: previewed inside mock storefront header bars, since themes
                     render it on both light and dark surfaces. --}}
                <div>
                    <p class="mb-2 text-sm font-medium text-content-secondary">Logo</p>
                    <div class="space-y-2">
                        @foreach ([['bg' => '#ffffff', 'ink' => '#111827', 'line' => '#e5e7eb', 'label' => 'Light header'], ['bg' => '#191512', 'ink' => '#f5f1ea', 'line' => '#3a332c', 'label' => 'Dark header']] as $mock)
                            <div class="flex items-center gap-4 rounded-lg px-4 py-3" style="background: {{ $mock['bg'] }}; border: 1px solid {{ $mock['line'] }}" aria-hidden="true">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="" class="h-8 w-auto max-w-[10rem] object-contain">
                                @else
                                    <span class="text-base font-semibold tracking-tight" style="color: {{ $mock['ink'] }}">{{ $storeName ?: 'Your store' }}</span>
                                @endif
                                {{-- Faux nav + cart to suggest the storefront header --}}
                                <span class="ml-auto hidden items-center gap-3 sm:flex">
                                    <span class="h-1.5 w-8 rounded-full" style="background: {{ $mock['ink'] }}; opacity: .25"></span>
                                    <span class="h-1.5 w-6 rounded-full" style="background: {{ $mock['ink'] }}; opacity: .25"></span>
                                    <span class="h-5 w-5 rounded-full" style="border: 1.5px solid {{ $mock['ink'] }}; opacity: .35"></span>
                                </span>
                                <span class="text-[10px] uppercase tracking-wide" style="color: {{ $mock['ink'] }}; opacity: .45">{{ $mock['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-2 flex items-center gap-3">
                        <label class="cursor-pointer text-sm font-medium text-primary">
                            {{ $logoUrl ? 'Replace' : 'Upload' }}
                            <input type="file" wire:model="logoUpload" accept="image/*" class="sr-only">
                        </label>
                        @if ($logoUrl)<button type="button" wire:click="removeLogo" class="text-sm text-content-muted hover:text-danger">Remove</button>@endif
                        <span wire:loading wire:target="logoUpload" class="text-xs text-content-muted">Uploading…</span>
                        @unless ($logoUrl)<span class="text-xs text-content-muted">No logo yet — the store name is shown as a wordmark.</span>@endunless
                    </div>
                    @error('logoUpload')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>

                {{-- Favicon: previewed inside a mock browser tab plus at real sizes. --}}
                <div>
                    <p class="mb-2 text-sm font-medium text-content-secondary">Favicon</p>
                    <div class="flex flex-wrap items-end gap-6">
                        <div aria-hidden="true">
                            <div class="flex items-end" style="background: #cfd4dc; border-radius: 10px 10px 0 0; padding: 6px 6px 0">
                                <div class="flex w-52 items-center gap-2 rounded-t-lg bg-surface-raised px-3 py-2" style="border: 1px solid #b8bec8; border-bottom: none">
                                    @if ($faviconUrl)
                                        <img src="{{ $faviconUrl }}" alt="" class="h-4 w-4 shrink-0 object-contain">
                                    @else
                                        <span class="grid h-4 w-4 shrink-0 place-items-center rounded-sm bg-primary text-[9px] font-bold text-white">{{ mb_strtoupper(mb_substr($storeName ?: 'S', 0, 1)) }}</span>
                                    @endif
                                    <span class="truncate text-xs text-content-secondary">{{ $storeName ?: 'Your store' }}</span>
                                    <span class="ml-auto text-xs text-content-muted">&times;</span>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] uppercase tracking-wide text-content-muted">Browser tab</p>
                        </div>
                        @if ($faviconUrl)
                            <div class="flex items-end gap-4" aria-hidden="true">
                                @foreach ([16, 32, 48] as $px)
                                    <div class="text-center">
                                        <img src="{{ $faviconUrl }}" alt="" style="width: {{ $px }}px; height: {{ $px }}px" class="mx-auto object-contain">
                                        <p class="mt-1 text-[10px] text-content-muted">{{ $px }}px</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="mt-2 flex items-center gap-3">
                        <label class="cursor-pointer text-sm font-medium text-primary">
                            {{ $faviconUrl ? 'Replace' : 'Upload' }}
                            <input type="file" wire:model="faviconUpload" accept="image/*" class="sr-only">
                        </label>
                        @if ($faviconUrl)<button type="button" wire:click="removeFavicon" class="text-sm text-content-muted hover:text-danger">Remove</button>@endif
                        <span wire:loading wire:target="faviconUpload" class="text-xs text-content-muted">Uploading…</span>
                    </div>
                    <p class="mt-1 text-xs text-content-muted">Square images work best — at least 48×48px.</p>
                    @error('faviconUpload')<p class="mt-1 text-xs text-danger">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Social & messaging" subtitle="Used for WhatsApp commerce and storefront links.">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input wire:model="whatsapp" label="WhatsApp number" hint="Include country code, e.g. +8801XXXXXXXXX" :error="$errors->first('whatsapp')" />
                <x-ui.input wire:model="facebook" label="Facebook URL" :error="$errors->first('facebook')" />
                <x-ui.input wire:model="instagram" label="Instagram URL" :error="$errors->first('instagram')" />
            </div>

            <div class="mt-6 border-t border-line pt-5">
                <p class="text-sm font-medium text-content">WhatsApp buttons on the product page</p>
                <p class="mt-1 text-xs text-content-muted">Shown only when a WhatsApp number is set above.</p>
                <div class="mt-4 space-y-3">
                    <x-ui.toggle wire:model="whatsappBuy" label="Buy via WhatsApp" />
                    <x-ui.toggle wire:model="whatsappInquiry" label="Ask a question" />
                    <x-ui.toggle wire:model="whatsappShare" label="Share" />
                </div>
            </div>
        </x-ui.card>

        <x-admin.form-actions>
            <x-ui.save-button target="save" label="Save changes" />
        </x-admin.form-actions>
    </form>
</div>
