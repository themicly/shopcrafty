@php
    // Featured image drives the live preview thumbnail; falls back to the first image.
    $previewImage = $media->firstWhere('is_featured', true)?->path ?? $media->first()?->path;

    // Per-tab validation flags so a hidden tab still signals its errors with a dot.
    // Pricing lives in the Details tab, so its errors roll up into $errDetails.
    $errDetails = $errors->hasAny(['name', 'type', 'categoryId', 'brandId', 'weight', 'price', 'compareAtPrice', 'costPrice']);
    $errInventory = $errors->hasAny(['sku', 'barcode', 'stockQty', 'lowStockThreshold']);
    $errMedia = $errors->has('photos.*');
    $errSeo = $errors->hasAny(['seoTitle', 'seoDescription']);

    // Tab a fresh page should open on (details), and the landing tab for each type
    // switch so the shopper never lands on a panel that isn't rendered.
    $tabClasses = 'relative flex items-center gap-1.5 whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition';
@endphp

<div x-data="{
        tab: 'details',
        pvName: @js($name),
        pvPrice: @js($price),
        pvCompare: @js($compareAtPrice),
        sym: @js($currencySymbol),
        money(v) {
            const n = parseFloat(v);
            return isNaN(n) ? '' : this.sym + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }">
    {{-- Sticky action bar --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.catalog.products.index') }}" class="grid h-9 w-9 place-items-center rounded-md text-content-secondary hover:bg-surface-sunken" title="Back">
                <x-ui.icon name="chevron-left" class="h-5 w-5" />
            </a>
            <div>
                <h2 class="text-lg font-semibold text-content">{{ $name !== '' ? $name : 'New product' }}</h2>
                <div class="mt-0.5 flex items-center gap-2 text-xs">
                    @if ($status === 'active')
                        <x-ui.badge variant="success">Active</x-ui.badge>
                    @elseif ($status === 'archived')
                        <x-ui.badge>Archived</x-ui.badge>
                    @else
                        <x-ui.badge variant="warning">Draft</x-ui.badge>
                    @endif
                    @if ($savedAt)
                        <span class="text-content-muted" wire:loading.remove wire:target="save,publish">Saved {{ $savedAt }}</span>
                    @endif
                    <span class="text-content-muted" wire:loading wire:target="save,publish">Saving…</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if ($status === 'active')
                <x-ui.button variant="secondary" wire:click="unpublish">Unpublish</x-ui.button>
            @endif
            <x-ui.button variant="secondary" wire:click="save">
                {{ $productId ? 'Update product' : 'Save draft' }}
            </x-ui.button>
            @if ($status !== 'active')
                <x-ui.button wire:click="publish">Publish</x-ui.button>
            @endif
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        {{-- Main column: tabbed editor --}}
        <div>
            {{-- Tab bar. Only the tabs relevant to the current product type are shown;
                 a red dot marks a tab that holds a validation error on a hidden panel. --}}
            <div class="mb-5 flex flex-wrap items-center gap-1 border-b border-line" role="tablist">
                <button type="button" role="tab" @click="tab = 'details'"
                    :class="tab === 'details' ? 'border-primary text-content' : 'border-transparent text-content-secondary hover:text-content'"
                    class="{{ $tabClasses }}">
                    Details
                    @if ($errDetails)<span class="h-1.5 w-1.5 rounded-full bg-danger" aria-hidden="true"></span>@endif
                </button>

                @if ($type === 'simple')
                    <button type="button" role="tab" @click="tab = 'inventory'"
                        :class="tab === 'inventory' ? 'border-primary text-content' : 'border-transparent text-content-secondary hover:text-content'"
                        class="{{ $tabClasses }}">
                        Inventory
                        @if ($errInventory)<span class="h-1.5 w-1.5 rounded-full bg-danger" aria-hidden="true"></span>@endif
                    </button>
                @endif

                @if ($type === 'variable')
                    <button type="button" role="tab" @click="tab = 'variants'"
                        :class="tab === 'variants' ? 'border-primary text-content' : 'border-transparent text-content-secondary hover:text-content'"
                        class="{{ $tabClasses }}">
                        Variants
                    </button>
                @endif

                <button type="button" role="tab" @click="tab = 'media'"
                    :class="tab === 'media' ? 'border-primary text-content' : 'border-transparent text-content-secondary hover:text-content'"
                    class="{{ $tabClasses }}">
                    Media
                    @if ($errMedia)<span class="h-1.5 w-1.5 rounded-full bg-danger" aria-hidden="true"></span>@endif
                </button>

                @if ($type === 'digital')
                    <button type="button" role="tab" @click="tab = 'files'"
                        :class="tab === 'files' ? 'border-primary text-content' : 'border-transparent text-content-secondary hover:text-content'"
                        class="{{ $tabClasses }}">
                        Files
                    </button>
                @endif

                <button type="button" role="tab" @click="tab = 'preview'"
                    :class="tab === 'preview' ? 'border-primary text-content' : 'border-transparent text-content-secondary hover:text-content'"
                    class="{{ $tabClasses }}">
                    Preview
                </button>

                <button type="button" role="tab" @click="tab = 'seo'"
                    :class="tab === 'seo' ? 'border-primary text-content' : 'border-transparent text-content-secondary hover:text-content'"
                    class="{{ $tabClasses }}">
                    SEO
                    @if ($errSeo)<span class="h-1.5 w-1.5 rounded-full bg-danger" aria-hidden="true"></span>@endif
                </button>
            </div>

            <p class="mb-4 text-xs text-content-muted">Fields marked <span class="text-danger">*</span> are required. Your work autosaves once the product is created.</p>

            {{-- ============================ DETAILS ============================ --}}
            <div x-show="tab === 'details'" class="space-y-6">
                <x-ui.card title="General" subtitle="What the product is called and how it's described to shoppers.">
                    @if ($this->aiEnabled)
                        <x-slot:actions>
                            <x-ui.button type="button" size="sm" variant="secondary" wire:click="generateWithAi" wire:target="generateWithAi" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="generateWithAi">✨ Draft with AI</span>
                                <span wire:loading wire:target="generateWithAi">Writing…</span>
                            </x-ui.button>
                        </x-slot:actions>
                    @endif
                    <div class="space-y-5">
                        <x-ui.input wire:model.blur="name" x-on:input="pvName = $event.target.value" label="Product name" required :error="$errors->first('name')" hint="Keep it short and clear — this is the first thing shoppers see." />
                        <x-ui.textarea wire:model.live="description" label="Description" optional rows="5" hint="{{ $this->aiEnabled ? 'Tip: enter a name, then “Draft with AI” to auto-fill description, SEO & category.' : 'Describe the key features and benefits.' }}" />
                    </div>
                </x-ui.card>

                <x-ui.card title="Pricing" subtitle="Set what shoppers pay and, optionally, your costs.">
                    @if ($type === 'variable')
                        <x-admin.note variant="info" class="mb-5">Every variant sells at this price — there's no per-variant pricing. Manage each option's stock in the <button type="button" class="font-medium underline" @click="tab = 'variants'">Variants</button> tab.</x-admin.note>
                    @endif
                    <div class="grid gap-5 sm:grid-cols-3">
                        <x-ui.money-input wire:model.blur="price" x-on:input="pvPrice = $event.target.value" :symbol="$currencySymbol" label="Price" required :error="$errors->first('price')" />
                        <x-ui.money-input wire:model.blur="compareAtPrice" x-on:input="pvCompare = $event.target.value" :symbol="$currencySymbol" label="Original price" optional :error="$errors->first('compareAtPrice')" hint="Set this if the item is discounted — shown crossed out beside the sale price." />
                        <x-ui.money-input wire:model.blur="costPrice" :symbol="$currencySymbol" label="Cost per item" optional :error="$errors->first('costPrice')" hint="Only you see this. Used for profit reports." />
                    </div>
                </x-ui.card>
            </div>

            {{-- ======================= INVENTORY (simple) ======================= --}}
            @if ($type === 'simple')
                <div x-show="tab === 'inventory'" x-cloak class="space-y-6">
                    <x-ui.card title="Inventory" subtitle="Track how many you have in stock.">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-ui.input wire:model.blur="sku" label="SKU" optional :error="$errors->first('sku')" hint="Your own product code, e.g. TSHIRT-RED-M." />
                            <x-ui.input wire:model.blur="barcode" label="Barcode" optional />
                            <x-ui.input wire:model.blur="stockQty" type="number" min="0" label="Stock quantity" required :error="$errors->first('stockQty')" />
                            <x-ui.input wire:model.blur="lowStockThreshold" type="number" min="0" label="Low-stock threshold" required :error="$errors->first('lowStockThreshold')" hint="Get warned when stock drops to this number." />
                        </div>
                        <div class="mt-5">
                            <x-ui.toggle wire:model.blur="trackInventory" label="Track inventory" />
                        </div>
                    </x-ui.card>
                </div>
            @endif

            {{-- ======================= VARIANTS (variable) ====================== --}}
            @if ($type === 'variable')
                <div x-show="tab === 'variants'" x-cloak class="space-y-6">
                    <x-ui.card title="Variants" subtitle="Build combinations from attributes like size and colour — each keeps its own stock.">
                        <div class="mb-5 rounded-lg border border-line bg-surface-sunken p-4">
                            <x-ui.toggle wire:model.blur="trackInventory" label="Block ordering when out of stock" />
                            <p class="mt-1.5 text-xs text-content-muted">When on, shoppers can't select or buy an option whose stock has run out. Turn off to keep selling regardless of stock.</p>
                        </div>
                        @if ($productId)
                            <livewire:catalog.variant-manager :product="$productId" :key="'variants-' . $productId" />
                        @else
                            <x-admin.note variant="info">Save the product first — then you can generate variants and set their stock here.</x-admin.note>
                        @endif
                    </x-ui.card>
                </div>
            @endif

            {{-- ============================= MEDIA ============================= --}}
            <div x-show="tab === 'media'" x-cloak class="space-y-6">
                <x-ui.card title="Media" subtitle="Clear photos help products sell. The first image becomes the featured one.">
                    @if (! $productId)
                        <x-admin.note variant="info">Save the product first — then you can add and reorder photos here.</x-admin.note>
                    @else
                        <x-admin.image-uploader
                            wire:model="photos"
                            label="Product photos"
                            :multiple="true"
                            wireTarget="photos"
                            formats="JPG, PNG, WebP or GIF"
                            maxSize="5 MB"
                            :error="$errors->first('photos.*')"
                        />

                        <div class="mt-4 flex items-end gap-3">
                            <div class="flex-1">
                                <x-ui.input wire:model="mediaUrl" label="…or add by image URL" optional placeholder="https://…" wire:keydown.enter.prevent="addMedia" :error="$errors->first('mediaUrl')" />
                            </div>
                            <x-ui.button type="button" wire:click="addMedia">Add</x-ui.button>
                        </div>

                        @if ($media->isNotEmpty())
                            <p class="mt-5 text-xs text-content-muted">Hover a photo to set it as featured or remove it.</p>
                            <div class="mt-2 grid grid-cols-3 gap-3 sm:grid-cols-4">
                                @foreach ($media as $item)
                                    <div class="group relative overflow-hidden rounded-lg border border-line bg-surface" wire:key="media-{{ $item->id }}">
                                        <img src="{{ $item->path }}" alt="" class="aspect-square w-full object-cover">
                                        @if ($item->is_featured)
                                            <span class="absolute left-1.5 top-1.5"><x-ui.badge variant="primary">Featured</x-ui.badge></span>
                                        @endif
                                        <div class="absolute inset-x-0 bottom-0 flex justify-between gap-1 bg-black/50 p-1.5 opacity-0 transition group-hover:opacity-100">
                                            <button type="button" wire:click="setFeatured({{ $item->id }})" class="text-xs text-white hover:underline">Feature</button>
                                            <button type="button" wire:click="removeMedia({{ $item->id }})" class="text-xs text-white hover:underline">Remove</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </x-ui.card>
            </div>

            {{-- ========================= FILES (digital) ======================= --}}
            @if ($type === 'digital')
                <div x-show="tab === 'files'" x-cloak class="space-y-6">
                    <x-ui.card title="Digital files" subtitle="Stored in private storage and delivered to the buyer after payment.">
                        @if (! $productId)
                            <x-admin.note variant="info">Save the product first to add downloadable files.</x-admin.note>
                        @else
                            <label class="mb-3 flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-line px-4 py-6 text-center">
                                <input type="file" wire:model="digitalUploads" multiple class="hidden">
                                <span class="text-sm font-medium text-content">Click to upload downloadable files</span>
                                <span class="text-xs text-content-muted">ZIP, PDF, audio, video, images… up to 100 MB each</span>
                                <span wire:loading wire:target="digitalUploads" class="mt-1 text-xs text-primary">Uploading…</span>
                            </label>
                            @error('digitalUploads.*')<p class="mb-3 text-sm text-danger">{{ $message }}</p>@enderror

                            @if ($digitalFiles->isNotEmpty())
                                <ul class="divide-y divide-line rounded-lg border border-line">
                                    @foreach ($digitalFiles as $file)
                                        <li class="flex items-center justify-between gap-3 px-3 py-2" wire:key="file-{{ $file->id }}">
                                            <span class="min-w-0 flex-1 truncate text-sm text-content">{{ $file->name }}</span>
                                            <span class="shrink-0 text-xs text-content-muted">{{ $file->humanSize() }}</span>
                                            <button type="button" wire:click="removeDigitalFile({{ $file->id }})" class="shrink-0 text-xs text-danger hover:underline">Remove</button>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <x-admin.note variant="warning">Buyers need at least one file to download.</x-admin.note>
                            @endif
                        @endif
                    </x-ui.card>
                </div>
            @endif

            {{-- ============================ PREVIEW ============================ --}}
            <div x-show="tab === 'preview'" x-cloak class="space-y-6">
                <x-ui.card title="Live preview" subtitle="How this product's card will look to shoppers.">
                    <div class="mx-auto w-full max-w-xs overflow-hidden rounded-xl border border-line bg-surface">
                        <div class="aspect-square w-full bg-surface-sunken">
                            @if ($previewImage)
                                <img src="{{ $previewImage }}" alt="" class="h-full w-full object-cover">
                            @else
                                <div class="grid h-full w-full place-items-center text-content-muted">
                                    <div class="text-center">
                                        <x-ui.icon name="photo" class="mx-auto h-8 w-8" />
                                        <p class="mt-1 text-xs">Add a photo</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="space-y-1 p-3">
                            <p class="truncate text-sm font-medium text-content" x-text="pvName || 'Product name'"></p>
                            <div class="flex items-baseline gap-2">
                                <span class="text-sm font-semibold text-content" x-text="money(pvPrice) || '{{ $currencySymbol }}0.00'"></span>
                                <span class="text-xs text-content-muted line-through"
                                    x-show="pvCompare && parseFloat(pvCompare) > parseFloat(pvPrice)"
                                    x-text="money(pvCompare)"></span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            {{-- ============================== SEO ============================== --}}
            <div x-show="tab === 'seo'" x-cloak class="space-y-6">
                <x-ui.card title="Search engine (SEO)" subtitle="How this product appears in Google results.">
                    <div class="space-y-4">
                        <x-admin.seo-preview
                            :title="$seoTitle ?: $name"
                            :description="$seoDescription"
                            :image="$previewImage"
                            :url="'product/'.($slug ?: \Illuminate\Support\Str::slug($name ?: 'product'))"
                        />
                        <x-ui.input wire:model.live.debounce.400ms="seoTitle" label="SEO title" optional hint="Best under 60 characters" :error="$errors->first('seoTitle')" />
                        <x-ui.textarea wire:model.live.debounce.400ms="seoDescription" label="Meta description" optional hint="Best under 155 characters" rows="3" />
                    </div>
                </x-ui.card>
            </div>
        </div>

        {{-- Side column: always-on context (persists across tabs) --}}
        <div class="space-y-6">
            {{-- Product type + organization. Type is a prominent segmented control so
                 the variant workflow is discoverable; picking "Variable" jumps the
                 editor to the Variants tab. --}}
            <x-ui.card title="Organization" subtitle="Group the product so shoppers can find it.">
                <div class="space-y-5">
                    <div>
                        <span class="mb-1.5 block text-sm font-medium text-content">Product type <span class="text-danger">*</span></span>
                        <div class="grid grid-cols-3 gap-1 rounded-lg bg-surface-sunken p-1">
                            <button type="button" wire:click="$set('type', 'simple')" @click="if (['variants', 'files'].includes(tab)) tab = 'inventory'"
                                @class([
                                    'rounded-md px-2 py-1.5 text-xs font-medium transition',
                                    'bg-surface text-content shadow-sm' => $type === 'simple',
                                    'text-content-secondary hover:text-content' => $type !== 'simple',
                                ])>Simple</button>
                            <button type="button" wire:click="$set('type', 'variable')" @click="tab = 'variants'"
                                @class([
                                    'rounded-md px-2 py-1.5 text-xs font-medium transition',
                                    'bg-surface text-content shadow-sm' => $type === 'variable',
                                    'text-content-secondary hover:text-content' => $type !== 'variable',
                                ])>Variable</button>
                            <button type="button" wire:click="$set('type', 'digital')" @click="tab = 'files'"
                                @class([
                                    'rounded-md px-2 py-1.5 text-xs font-medium transition',
                                    'bg-surface text-content shadow-sm' => $type === 'digital',
                                    'text-content-secondary hover:text-content' => $type !== 'digital',
                                ])>Digital</button>
                        </div>
                        <p class="mt-1.5 text-xs text-content-muted">
                            @if ($type === 'variable')
                                Sells in options (size, colour…). Manage them in the Variants tab.
                            @elseif ($type === 'digital')
                                Delivered as a download — no shipping or stock tracking.
                            @else
                                A single product with one price and stock level.
                            @endif
                        </p>
                        @error('type')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror
                    </div>

                    <x-ui.select wire:model.blur="categoryId" label="Category" optional>
                        <option value="">— None —</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </x-ui.select>
                    <x-ui.select wire:model.blur="brandId" label="Brand" optional>
                        <option value="">— None —</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </x-ui.card>

            @if ($type !== 'digital')
                <x-ui.card title="Shipping" subtitle="Physical products always ship.">
                    <x-ui.input wire:model.blur="weight" type="number" min="0" label="Weight (g)" optional hint="Used to calculate shipping rates at checkout." />
                </x-ui.card>
            @endif

            <x-ui.card title="Recommended products" subtitle="Shown as “bought together”. Overrides the automatic suggestions.">
                @if (! $productId)
                    <x-admin.note variant="info">Save the product first.</x-admin.note>
                @else
                    @if ($relatedSelected->isNotEmpty())
                        <ul class="mb-3 space-y-1.5">
                            @foreach ($relatedSelected as $rel)
                                <li class="flex items-center justify-between gap-2 rounded-md bg-surface px-2.5 py-1.5 text-sm" wire:key="rel-{{ $rel->id }}">
                                    <span class="min-w-0 flex-1 truncate text-content">{{ $rel->name }}</span>
                                    <button type="button" wire:click="detachRelated({{ $rel->id }})" class="shrink-0 text-xs text-danger hover:underline">Remove</button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    <x-ui.input type="search" wire:model.live.debounce.300ms="relatedSearch" placeholder="Search products to add…" />
                    @if ($relatedResults->isNotEmpty())
                        <ul class="mt-2 divide-y divide-line rounded-md border border-line">
                            @foreach ($relatedResults as $result)
                                <li>
                                    <button type="button" wire:click="attachRelated({{ $result->id }})"
                                        class="block w-full px-2.5 py-1.5 text-left text-sm text-content-secondary hover:bg-primary-soft hover:text-primary">
                                        + {{ $result->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                @endif
            </x-ui.card>
        </div>
    </div>

    @if (app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->installed('ai') && isset($aiReview))
        <x-admin.ai-review-modal :items="$aiReview" />
    @endif
</div>
