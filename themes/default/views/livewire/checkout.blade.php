@php
    $inputCls = 'w-full border px-4 py-3 text-sm outline-none';
    $inputStyle = 'border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
    // Error state: same field, accent border (the error hue in every theme).
    $errorStyle = 'border-color: var(--st-accent); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)';
    $styleFor = fn (string $field) => $errors->has($field) ? $errorStyle : $inputStyle;
    // Required-field marker. These inputs use placeholders in place of labels, so the
    // "*" is positioned in the field's top-right corner (its wrapper is `relative`).
    $reqMark = '<span class="pointer-events-none absolute end-3 top-2.5 text-xs leading-none" style="color: var(--st-accent)" aria-hidden="true" title="Required">*</span>';
@endphp

<div class="grid gap-10 lg:grid-cols-[1fr_400px] lg:gap-16">
    {{-- Form --}}
    <form wire:submit="placeOrder" class="min-w-0 space-y-8">
        <h1 class="st-display text-2xl font-semibold" style="color: var(--st-ink)">{{ __('checkout.checkout') }}</h1>

        <div>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('checkout.contact') }}</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                <div class="relative sm:col-span-2">
                    <input wire:model.blur="name" aria-label="{{ __('storefront.full_name') }}" placeholder="{{ __('storefront.full_name') }}" class="{{ $inputCls }}" style="{{ $styleFor('name') }}" @error('name') aria-invalid="true" @enderror>
                    {!! $reqMark !!}
                    @error('name')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <div class="relative">
                    <input wire:model.blur="phone" aria-label="{{ __('checkout.phone_number') }}" placeholder="{{ __('checkout.phone_number') }}" class="{{ $inputCls }}" style="{{ $styleFor('phone') }}" @error('phone') aria-invalid="true" @enderror>
                    {!! $reqMark !!}
                    @error('phone')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
                <div class="relative">
                    <input wire:model.blur="email" type="email" aria-label="{{ $requiresShipping ? __('checkout.email_optional') : __('checkout.email_for_delivery') }}" placeholder="{{ $requiresShipping ? __('checkout.email_optional') : __('checkout.email_for_delivery') }}" class="{{ $inputCls }}" style="{{ $styleFor('email') }}" @error('email') aria-invalid="true" @enderror>
                    @unless ($requiresShipping){!! $reqMark !!}@endunless
                    @error('email')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        @if ($requiresShipping)
        <div>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.shipping_address') }}</h2>
            <div class="grid gap-3 sm:grid-cols-2">
                @if ($savedAddresses->isNotEmpty())
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium" style="color: var(--st-ink-soft)">{{ __('checkout.use_saved_address') }}</label>
                        <select wire:model.live="selectedAddressId" aria-label="{{ __('checkout.saved_address') }}" class="{{ $inputCls }}" style="{{ $inputStyle }}">
                            <option value="">{{ __('checkout.enter_new_address') }}</option>
                            @foreach ($savedAddresses as $addr)
                                <option value="{{ $addr->id }}">{{ $addr->label ? $addr->label.' — ' : '' }}{{ $addr->address }}{{ $addr->city ? ', '.$addr->city : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="relative sm:col-span-2">
                    <input wire:model.blur="address" aria-label="{{ __('checkout.street_address') }}" placeholder="{{ __('checkout.street_address') }}" class="{{ $inputCls }}" style="{{ $styleFor('address') }}" @error('address') aria-invalid="true" @enderror>
                    {!! $reqMark !!}
                    @error('address')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                </div>

                @if ($locationsEnabled)
                    {{-- Cascading, admin-managed location dropdown (any country) --}}
                    @foreach ($locationLevels as $i => $label)
                        @php $opts = $locationOptions[$i] ?? null; @endphp
                        @if ($opts)
                            <div>
                                <select wire:model.live="locationPath.{{ $i }}" aria-label="{{ $label }}" class="{{ $inputCls }}" style="{{ $styleFor('locationPath') }}" @error('locationPath') aria-invalid="true" @enderror>
                                    <option value="">{{ __('checkout.select_placeholder', ['label' => $label]) }}</option>
                                    @foreach ($opts as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                    @endforeach
                    @error('locationPath')<p class="mt-1 text-xs sm:col-span-2" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                @else
                    <div class="relative">
                        <input wire:model.blur="city" aria-label="{{ __('storefront.city') }}" placeholder="{{ __('storefront.city') }}" class="{{ $inputCls }}" style="{{ $styleFor('city') }}" @error('city') aria-invalid="true" @enderror>
                        {!! $reqMark !!}
                        @error('city')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <input wire:model.blur="region" aria-label="{{ __('checkout.state_region') }}" placeholder="{{ __('checkout.state_region') }}" class="{{ $inputCls }}" style="{{ $styleFor('region') }}" @error('region') aria-invalid="true" @enderror>
                        @error('region')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
                    </div>
                @endif
            </div>
        </div>

        <div>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('checkout.shipping_method') }}</h2>
            @if ($locationsEnabled)
                {{-- Rate is derived from the selected delivery area. --}}
                <div class="border px-4 py-3 text-sm" style="border-color: var(--st-line); border-radius: var(--st-radius-sm); color: var(--st-ink)">
                    @php $selectedZone = $shippingZoneId ? $zones->firstWhere('id', $shippingZoneId) : null; @endphp
                    @if ($selectedZone)
                        <div class="flex items-center justify-between">
                            <span>{{ $selectedZone->name }}</span>
                            <span class="font-medium">{{ $shipping > 0 ? format_money($shipping) : __('storefront.free') }}</span>
                        </div>
                    @else
                        <span style="color: var(--st-ink-soft)">{{ __('checkout.select_delivery_area') }}</span>
                    @endif
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($zones as $zone)
                        <label class="flex cursor-pointer items-center justify-between border px-4 py-3" style="border-color: {{ $errors->has('shippingZoneId') ? 'var(--st-accent)' : ($shippingZoneId === $zone->id ? 'var(--st-ink)' : 'var(--st-line)') }}; border-radius: var(--st-radius-sm)">
                            <span class="flex items-center gap-3 text-sm" style="color: var(--st-ink)">
                                <input type="radio" wire:model.live="shippingZoneId" value="{{ $zone->id }}" class="accent-current" @error('shippingZoneId') aria-invalid="true" @enderror>
                                {{ $zone->name }}
                            </span>
                            <span class="text-sm font-medium" style="color: var(--st-ink)">{{ $zone->rate > 0 ? format_money($zone->rate) : __('storefront.free') }}</span>
                        </label>
                    @endforeach
                </div>
                @error('shippingZoneId')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
            @endif
        </div>

        @else
        <div>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('checkout.delivery') }}</h2>
            <div class="border px-4 py-3 text-sm" style="border-color: var(--st-line); border-radius: var(--st-radius-sm); color: var(--st-ink-soft)">
                {{ __('checkout.digital_delivery_note') }}
            </div>
        </div>
        @endif

        <div>
            <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('storefront.payment') }}<span style="color: var(--st-accent)" title="Required" aria-hidden="true"> *</span></h2>

            {{-- Express / wallet checkout — one-tap accelerated pay (Apple Pay & Google
                 Pay via Stripe, PayPal, Shop Pay). Shown here, with the payment options,
                 only when a redirect gateway is enabled — no gateways, no dead buttons. --}}
            @if ($redirectGateways->isNotEmpty())
                <div class="mb-4">
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('checkout.express_checkout') }}</p>
                    <div class="space-y-2">
                        @foreach ($redirectGateways as $key => $method)
                            <button type="button" wire:click="expressCheckout('{{ $key }}')" wire:target="expressCheckout('{{ $key }}')" wire:loading.attr="disabled"
                                class="flex w-full items-center justify-center gap-2 py-3 text-sm font-semibold"
                                style="background: var(--st-ink); color: var(--st-bg); border-radius: var(--st-radius-sm)">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" /></svg>
                                <span wire:loading.remove wire:target="expressCheckout('{{ $key }}')">{{ __('checkout.express_pay_with', ['method' => $method->label()]) }}</span>
                                <span wire:loading wire:target="expressCheckout('{{ $key }}')">{{ __('checkout.starting') }}</span>
                            </button>
                        @endforeach
                    </div>
                    @if (! empty($wallets))
                        <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                            @foreach ($wallets as $w)
                                <button type="button" wire:click="expressCheckout('{{ $w['gateway'] }}')" wire:target="expressCheckout('{{ $w['gateway'] }}')" wire:loading.attr="disabled"
                                    aria-label="{{ __('checkout.pay_with', ['label' => $w['label']]) }}"
                                    class="flex items-center justify-center gap-1.5 border py-2.5 text-xs font-semibold"
                                    style="border-color: var(--st-line); color: var(--st-ink); border-radius: var(--st-radius-sm); background: var(--st-surface)">
                                    {{ $w['label'] }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                    <div class="mt-4 flex items-center gap-3">
                        <span class="h-px flex-1" style="background: var(--st-line)"></span>
                        <span class="text-xs uppercase tracking-wide" style="color: var(--st-ink-soft)">{{ __('checkout.or_pay_another_way') }}</span>
                        <span class="h-px flex-1" style="background: var(--st-line)"></span>
                    </div>
                </div>
            @endif

            <div class="space-y-2">
                @foreach ($methods as $key => $method)
                    <label class="flex cursor-pointer items-center justify-between gap-3 border px-4 py-3" style="border-color: {{ $errors->has('paymentMethod') ? 'var(--st-accent)' : ($paymentMethod === $key ? 'var(--st-ink)' : 'var(--st-line)') }}; border-radius: var(--st-radius-sm)">
                        <span class="flex items-center gap-3 text-sm font-medium" style="color: var(--st-ink)">
                            <input type="radio" wire:model.live="paymentMethod" value="{{ $key }}" class="accent-current" @error('paymentMethod') aria-invalid="true" @enderror>
                            {{ $method->label() }}
                        </span>
                        <span class="shrink-0 rounded border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide" style="border-color: var(--st-line); color: var(--st-ink-soft)">
                            @if ($method instanceof \Themicly\Shopcrafty\Modules\Orders\Contracts\EmbeddedPaymentGateway)
                                {{ __('checkout.badge_card') }}
                            @elseif ($method instanceof \Themicly\Shopcrafty\Modules\Orders\Contracts\RedirectPaymentGateway)
                                {{ __('checkout.badge_online') }}
                            @else
                                {{ __('checkout.badge_offline') }}
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
            @error('paymentMethod')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror

            {{-- Contextual panel for the selected method. --}}
            @php $selected = $methods->get($paymentMethod); @endphp
            @if ($selected)
                <div class="mt-3 border px-4 py-3" style="border-color: var(--st-line); border-radius: var(--st-radius-sm); background: var(--st-surface)">
                    @if ($selected instanceof \Themicly\Shopcrafty\Modules\Orders\Contracts\EmbeddedPaymentGateway)
                        {{-- Embedded / hosted-fields gateway. Its JS SDK mounts a
                             secure, PCI-compliant iframe (e.g. Stripe Elements) into
                             this node. It's wire:ignore so Livewire never re-renders
                             over the SDK's DOM. A real integration would initialise
                             the SDK against this element id when the method is picked
                             and tokenise the card before placeOrder() runs. --}}
                        <div wire:ignore id="{{ $selected->elementId() }}" data-payment-embed="{{ $selected->key() }}" class="grid min-h-[52px] place-items-center text-sm" style="color: var(--st-ink-soft)">
                            <span>{{ __('checkout.loading_payment_form') }}</span>
                        </div>
                        @if ($selected->instructions())
                            <p class="mt-2 text-xs" style="color: var(--st-ink-soft)">{{ $selected->instructions() }}</p>
                        @endif
                    @elseif ($selected instanceof \Themicly\Shopcrafty\Modules\Orders\Contracts\RedirectPaymentGateway)
                        <p class="text-sm font-medium" style="color: var(--st-ink)">{{ __('checkout.complete_payment_on', ['method' => $selected->label()]) }}</p>
                        <p class="mt-1 text-xs" style="color: var(--st-ink-soft)">{{ __('checkout.redirect_note', ['method' => $selected->label()]) }}</p>
                    @elseif ($selected->instructions())
                        <p class="text-sm" style="color: var(--st-ink-soft)">{{ $selected->instructions() }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <textarea wire:model="notes" rows="2" aria-label="{{ __('checkout.order_notes_optional') }}" placeholder="{{ __('checkout.order_notes_optional') }}" class="{{ $inputCls }}" style="{{ $styleFor('notes') }}" @error('notes') aria-invalid="true" @enderror></textarea>
            @error('notes')<p class="mt-1 text-xs" style="color: var(--st-accent)">{{ $message }}</p>@enderror
        </div>
    </form>

    {{-- Summary rail. On mobile it sits at the TOP (order-first) so shoppers see
         what they're paying for before the form; on desktop (lg) the order resets
         and the grid floats it to the sticky right rail. --}}
    <div class="min-w-0 order-first lg:order-none">
        <div class="lg:sticky lg:top-8">
            <div class="border p-5" style="border-color: var(--st-line); border-radius: var(--st-radius); background: var(--st-surface)">
                <h2 class="st-display mb-4 text-lg font-semibold" style="color: var(--st-ink)">{{ __('storefront.order_summary') }}</h2>

                <div class="space-y-3">
                    @foreach ($items as $item)
                        <div class="flex items-center gap-3" wire:key="co-{{ $item->id }}">
                            {{-- overflow-hidden clips only the image wrapper, not the
                                 positioned badge that overhangs the thumbnail corner. --}}
                            <div class="relative h-14 w-12 shrink-0">
                                <div class="h-full w-full overflow-hidden" style="border-radius: var(--st-radius-sm); background: var(--st-bg)">
                                    @if ($item->product?->featuredImage())
                                        <img src="{{ $item->product->featuredImage()->path }}" alt="" class="h-full w-full object-cover">
                                    @endif
                                </div>
                                <span class="absolute -right-1.5 -top-1.5 grid h-5 w-5 place-items-center rounded-full text-[11px] font-bold" style="background: var(--st-ink); color: var(--st-bg); box-shadow: 0 0 0 2px var(--st-surface)">{{ $item->qty }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm" style="color: var(--st-ink)">{{ $item->product?->name }}</p>
                                @if ($item->variant)<p class="text-xs" style="color: var(--st-ink-soft)">{{ implode(' / ', array_values($item->variant->options)) }}</p>@endif
                                <p class="text-xs" style="color: var(--st-ink-soft)">{{ __('checkout.qty_label') }} {{ $item->qty }}</p>
                            </div>
                            <span class="text-sm font-medium" style="color: var(--st-ink)">{{ format_money($item->lineTotal()) }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Coupon --}}
                <div class="mt-4 border-t pt-4" style="border-color: var(--st-line)">
                    <div class="flex gap-2">
                        <input wire:model="couponCode" wire:keydown.enter.prevent="applyCoupon" aria-label="{{ __('storefront.coupon_code') }}" placeholder="{{ __('storefront.coupon_code') }}" class="h-10 flex-1 border px-3 text-sm outline-none" style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                        <button type="button" wire:click="applyCoupon" class="h-10 px-4 text-sm font-semibold" style="border: 1px solid var(--st-line); color: var(--st-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.apply') }}</button>
                    </div>
                    @if ($couponMessage)<p class="mt-1.5 text-xs" style="color: var(--st-accent)">{{ $couponMessage }}</p>@endif
                    @if ($appliedCode)<p class="mt-1.5 text-xs" style="color: var(--st-ink-soft)">{{ __('checkout.coupon_word') }} <strong style="color: var(--st-ink)">{{ $appliedCode }}</strong> {{ __('checkout.applied_word') }}</p>@endif
                </div>

                <div class="mt-4 space-y-1.5 border-t pt-4 text-sm" style="border-color: var(--st-line)">
                    <div class="flex justify-between" style="color: var(--st-ink-soft)"><span>{{ __('storefront.subtotal') }}</span><span>{{ format_money($subtotal) }}</span></div>
                    @if ($discount > 0)
                        <div class="flex justify-between" style="color: var(--st-accent)"><span>{{ __('storefront.discount') }}</span><span>−{{ format_money($discount) }}</span></div>
                    @endif
                    @if ($requiresShipping)
                        <div class="flex justify-between" style="color: var(--st-ink-soft)"><span>{{ __('storefront.shipping') }}</span><span>{{ $shipping > 0 ? format_money($shipping) : __('storefront.free') }}</span></div>
                    @endif
                    @if ($taxEnabled && ! $taxInclusive)
                        <div class="flex justify-between" style="color: var(--st-ink-soft)"><span>{{ $taxLabel }}</span><span>{{ format_money($tax) }}</span></div>
                    @endif
                    <div class="flex justify-between pt-1.5 text-base font-semibold" style="color: var(--st-ink)"><span>{{ __('storefront.total') }}</span><span>{{ format_money($total) }}</span></div>
                    @if ($taxEnabled && $taxInclusive)
                        <div class="flex justify-between text-xs" style="color: var(--st-ink-soft)"><span>{{ __('checkout.includes') }} {{ $taxLabel }}</span><span>{{ format_money($tax) }}</span></div>
                    @endif
                </div>

                @if (settings('privacy.checkout_consent_enabled'))
                    @php
                        $consentText = (string) settings('privacy.checkout_consent_text', __('storefront.checkout_consent_default'));
                        $policy = (string) settings('privacy.privacy_policy_page', '');
                        $policyUrl = $policy !== ''
                            ? (str_starts_with($policy, 'http') ? $policy : route('storefront.page', $policy))
                            : null;
                    @endphp
                    <div class="mt-5">
                        <label class="flex items-start gap-2 text-xs" style="color: var(--st-ink-soft)">
                            <input type="checkbox" wire:model="consent" class="mt-0.5 h-4 w-4 shrink-0" aria-describedby="consent-error" style="accent-color: var(--st-primary)">
                            <span>
                                {{ $consentText }}
                                @if ($policyUrl)
                                    <a href="{{ $policyUrl }}" target="_blank" rel="noopener" class="underline" style="color: var(--st-ink)">{{ __('storefront.learn_more') }}</a>
                                @endif
                            </span>
                        </label>
                        @error('consent')
                            <p id="consent-error" class="mt-1.5 text-xs" style="color: var(--st-accent)">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                {{-- Desktop places the order from the summary; on mobile the sticky
                     bottom bar (below) carries the button so it's always reachable. --}}
                <button wire:click="placeOrder" class="mt-5 hidden w-full py-3.5 text-sm font-semibold lg:block" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">
                    <span wire:loading.remove wire:target="placeOrder">{{ __('storefront.place_order') }} · {{ format_money($total) }}</span>
                    <span wire:loading wire:target="placeOrder">{{ __('checkout.placing_order') }}</span>
                </button>

                {{-- Trust signals at the point of payment --}}
                <div class="mt-3 flex items-center justify-center gap-1.5 text-xs" style="color: var(--st-ink-soft)">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                    {{ __('checkout.secure_encrypted_checkout') }}
                </div>
                @if ($methods->isNotEmpty())
                    <div class="mt-2 flex flex-wrap items-center justify-center gap-1.5">
                        @foreach ($methods as $key => $method)
                            <span class="rounded border px-2 py-1 text-[11px] font-medium" style="border-color: var(--st-line); color: var(--st-ink-soft)">{{ $method->label() }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Spacer + sticky mobile place-order bar so the CTA is always reachable on
         phones without scrolling the whole summary into view. --}}
    <div class="h-20 lg:hidden" aria-hidden="true"></div>
    <div class="fixed inset-x-0 bottom-0 z-30 border-t p-3 lg:hidden" style="border-color: var(--st-line); background: var(--st-bg)">
        <button wire:click="placeOrder" wire:target="placeOrder" wire:loading.attr="disabled" class="w-full py-3.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">
            <span wire:loading.remove wire:target="placeOrder">{{ __('storefront.place_order') }} · {{ format_money($total) }}</span>
            <span wire:loading wire:target="placeOrder">{{ __('checkout.placing_order') }}</span>
        </button>
    </div>
</div>
