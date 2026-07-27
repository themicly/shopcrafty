<div>
    <form wire:submit="save" class="space-y-6">

        {{-- Header --}}
        <x-admin.steps
            :current="1"
            :steps="[
                ['title' => 'Code', 'description' => 'What shoppers type'],
                ['title' => 'Discount', 'description' => 'What they get'],
                ['title' => 'Limits', 'description' => 'When & how often'],
            ]"
        />

        <div class="grid gap-8 lg:grid-cols-12">

            {{-- LEFT CONTENT --}}
            <div class="lg:col-span-8">

                <div class="grid gap-6 lg:grid-cols-2">

                    {{-- Coupon --}}
                    <x-ui.card
                        title="Coupon"
                        subtitle="The code shoppers enter at checkout."
                    >
                        <div class="space-y-5">

                            <x-ui.input
                                wire:model="code"
                                label="Code"
                                required
                                placeholder="SUMMER25"
                                class="font-mono uppercase"
                                :error="$errors->first('code')"
                                hint="Shown to customers at checkout. Saved in uppercase."
                            />

                            <x-ui.input
                                wire:model="name"
                                label="Internal name"
                                optional
                                placeholder="Summer sale"
                                hint="Only you see this."
                                :error="$errors->first('name')"
                            />

                        </div>
                    </x-ui.card>

                    {{-- Discount --}}
                    <x-ui.card
                        title="Discount"
                        subtitle="Reward configuration"
                    >

                        <div class="space-y-5">

                            <x-ui.select
                                wire:model.live="type"
                                label="Type"
                                required
                                :error="$errors->first('type')"
                            >
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed amount</option>
                                <option value="free_shipping">Free shipping</option>
                                <option value="bogo">Buy X Get Y</option>
                            </x-ui.select>

                            @if ($type === 'percentage')

                                <x-ui.input
                                    type="number"
                                    wire:model.live="value"
                                    label="Percentage"
                                    min="0"
                                    max="100"
                                />

                            @elseif ($type === 'fixed')

                                <x-ui.money-input
                                    wire:model.live="value"
                                    label="Amount"
                                    :symbol="$currencySymbol"
                                />

                            @elseif ($type === 'bogo')

                                <div class="grid grid-cols-2 gap-4">

                                    <x-ui.input
                                        type="number"
                                        wire:model.live="buyQty"
                                        label="Buy"
                                    />

                                    <x-ui.input
                                        type="number"
                                        wire:model.live="getQty"
                                        label="Free"
                                    />

                                </div>

                            @endif

                            <x-ui.select
                                wire:model.live="scopeType"
                                label="Applies to"
                            >
                                <option value="all">Entire cart</option>
                                <option value="category">Category</option>
                                <option value="product">Product</option>
                            </x-ui.select>

                            @if ($scopeType === 'category')
                                <div class="rounded-lg border border-line p-3">
                                    <p class="mb-2 text-xs font-medium text-content-secondary">Eligible categories</p>
                                    <div class="grid gap-1.5 sm:grid-cols-2">
                                        @foreach ($categories as $category)
                                            <label class="flex items-center gap-2 text-sm text-content-secondary">
                                                <input type="checkbox" wire:model="scopeIds" value="{{ $category->id }}"> {{ $category->name }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if ($scopeType === 'product')
                                <div class="rounded-lg border border-line p-3">
                                    <p class="mb-2 text-xs font-medium text-content-secondary">Eligible products</p>
                                    @if ($selectedProducts->isNotEmpty())
                                        <div class="mb-3 flex flex-wrap gap-1.5">
                                            @foreach ($selectedProducts as $sp)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-primary-soft px-2 py-0.5 text-xs text-primary">
                                                    {{ $sp->name }}
                                                    <button type="button" wire:click="$set('scopeIds', {{ json_encode(array_values(array_diff($scopeIds, [$sp->id]))) }})" aria-label="Remove">&times;</button>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                    <x-ui.input type="search" wire:model.live.debounce.300ms="productSearch" placeholder="Search products…" class="mb-2" />
                                    <div class="grid max-h-56 gap-1.5 overflow-y-auto sm:grid-cols-2">
                                        @forelse ($products as $product)
                                            <label class="flex items-center gap-2 text-sm text-content-secondary">
                                                <input type="checkbox" wire:model.live="scopeIds" value="{{ $product->id }}"> {{ $product->name }}
                                            </label>
                                        @empty
                                            <p class="text-sm text-content-muted">No products match.</p>
                                        @endforelse
                                    </div>
                                </div>
                            @endif

                            <x-ui.money-input
                                wire:model.live="minPurchase"
                                label="Minimum purchase"
                                :symbol="$currencySymbol"
                            />

                        </div>

                    </x-ui.card>

                    {{-- Limits --}}
                    <x-ui.card
                        class="lg:col-span-2"
                        title="Limits & Schedule"
                        subtitle="Usage restrictions and active dates."
                    >

                        <div class="space-y-6">

                            <div class="grid gap-5 md:grid-cols-2">

                                <x-ui.input
                                    type="number"
                                    wire:model.live="usageLimit"
                                    label="Usage limit"
                                    optional
                                />

                                <x-ui.input
                                    type="number"
                                    wire:model="perCustomerLimit"
                                    label="Per customer"
                                    optional
                                />

                            </div>

                            <div class="grid gap-5 md:grid-cols-2">

                                <x-ui.input
                                    type="datetime-local"
                                    wire:model.live="startsAt"
                                    label="Starts"
                                />

                                <x-ui.input
                                    type="datetime-local"
                                    wire:model.live="endsAt"
                                    label="Ends"
                                />

                            </div>

                        </div>

                    </x-ui.card>

                </div>

            </div>

            {{-- RIGHT SIDEBAR --}}
            <div class="lg:col-span-4">

                <div class="sticky top-6 space-y-6">

                    {{-- Summary --}}
                    <x-ui.card title="Summary">

                        <div class="rounded-lg bg-primary-soft p-4">

                            <p class="text-xs uppercase tracking-wide text-primary/70">
                                This coupon gives
                            </p>

                            <p class="mt-2 text-lg font-semibold text-primary">
                                {{ $summary }}
                            </p>

                        </div>

                    </x-ui.card>

                    {{-- Status --}}
                    <x-ui.card title="Status">

                        <div class="space-y-5">

                            <x-ui.toggle
                                wire:model="isEnabled"
                                label="Enabled"
                            />

                            <div class="border-t pt-4 text-sm space-y-3">

                                <div class="flex justify-between">
                                    <span>Type</span>
                                    <span>{{ ucfirst(str_replace('_',' ', $type)) }}</span>
                                </div>

                                <div class="flex justify-between">
                                    <span>Scope</span>
                                    <span>{{ ucfirst($scopeType) }}</span>
                                </div>

                                @if($minPurchase)
                                    <div class="flex justify-between">
                                        <span>Minimum</span>
                                        <span>{{ $currencySymbol }}{{ $minPurchase }}</span>
                                    </div>
                                @endif

                            </div>

                        </div>

                    </x-ui.card>

                    {{-- Actions --}}
                    <x-ui.card>

                        <div class="space-y-3">

                            <x-ui.save-button
                                target="save"
                                class="w-full"
                                :label="$couponId ? 'Save coupon' : 'Create coupon'"
                            />

                            <x-ui.button
                                type="button"
                                variant="ghost"
                                class="w-full"
                                :href="route('admin.marketing.coupons.index')"
                            >
                                Cancel
                            </x-ui.button>

                        </div>

                    </x-ui.card>

                </div>

            </div>

        </div>

    </form>
</div>