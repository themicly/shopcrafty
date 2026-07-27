<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Customers\Models\CustomerAddress;
use Themicly\Shopcrafty\Modules\Marketing\Contracts\CouponValidator;
use Themicly\Shopcrafty\Modules\Orders\Actions\PlaceOrder;
use Themicly\Shopcrafty\Modules\Orders\Contracts\RedirectPaymentGateway;
use Themicly\Shopcrafty\Modules\Orders\Exceptions\InsufficientStockException;
use Themicly\Shopcrafty\Modules\Orders\Models\Location;
use Themicly\Shopcrafty\Modules\Orders\Models\ShippingZone;
use Themicly\Shopcrafty\Modules\Orders\Services\CartService;
use Themicly\Shopcrafty\Modules\Orders\Services\LocationService;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentRegistry;
use Themicly\Shopcrafty\Modules\Orders\Services\TaxService;

class Checkout extends Component
{
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $city = '';

    public string $region = '';

    public ?int $shippingZoneId = null;

    /** Saved address chosen by a logged-in customer (empty = enter a new one). */
    public ?int $selectedAddressId = null;

    /** Selected location id per level (cascading address dropdown). */
    public array $locationPath = [];

    public string $paymentMethod = '';

    public string $notes = '';

    /** Terms/privacy consent — only required when the privacy toggle is on. */
    public bool $consent = false;

    public string $couponCode = '';

    // These three are derived server-side from a validated coupon; they must never
    // be hydrated from the browser payload (price tampering — see PlaceOrder).
    #[Locked]
    public int $discount = 0;

    #[Locked]
    public bool $freeShipping = false;

    public ?string $couponMessage = null;

    #[Locked]
    public ?string $appliedCode = null;

    public function applyCoupon(CartService $cart): void
    {
        $code = trim($this->couponCode);

        if ($code === '') {
            $this->reset('discount', 'freeShipping', 'appliedCode', 'couponMessage');

            return;
        }

        // Stop coupon-code enumeration: cap attempts per visitor.
        $key = 'coupon-apply:'.(auth('customer')->id() ?? request()->ip());

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $this->reset('discount', 'freeShipping', 'appliedCode');
            $this->couponMessage = 'Too many attempts. Please try again in a minute.';

            return;
        }

        RateLimiter::hit($key, 60);

        $items = $cart->items()->map(fn ($item) => [
            'product_id' => (int) $item->product_id,
            'category_id' => $item->product?->category_id,
            'price' => $item->unitPrice(),
            'qty' => (int) $item->qty,
        ])->all();

        $result = app(CouponValidator::class)
            ->validate($code, $cart->subtotal(), auth('customer')->id(), $items);

        if ($result['ok']) {
            $this->discount = $result['discount'];
            $this->freeShipping = $result['free_shipping'];
            $this->appliedCode = $result['code'];
            $this->couponMessage = null;
        } else {
            $this->reset('discount', 'freeShipping', 'appliedCode');
            $this->couponMessage = $result['message'];
        }
    }

    public function mount(CartService $cart)
    {
        if ($cart->isEmpty()) {
            return redirect()->route('storefront.shop');
        }

        // Prefill for a logged-in customer, including their default address.
        if ($customer = auth('customer')->user()) {
            $this->name = (string) $customer->name;
            $this->phone = (string) $customer->mobile;
            $this->email = (string) $customer->email;

            if ($default = $customer->addresses()->orderByDesc('is_default')->first()) {
                $this->selectedAddressId = $default->id;
                $this->fillFromAddress($default);
            }
        }

        // A digital-only cart skips shipping entirely — no zone, and COD (a
        // pay-on-delivery method) is not offered.
        $requiresShipping = $cart->requiresShipping();

        if ($requiresShipping) {
            $this->shippingZoneId = ShippingZone::where('is_active', true)->orderBy('position')->value('id');
        }

        $this->paymentMethod = (string) $this->availableMethods($requiresShipping)->keys()->first();

        // Carry a coupon applied in the cart drawer into checkout, re-validating
        // server-side so the discount is authoritative (never trust the client).
        if ($code = session('cart_coupon')) {
            $this->couponCode = (string) $code;
            $this->applyCoupon($cart);
        }
    }

    /** Inline (on-blur) validation so shoppers see field errors before submitting. */
    public function updated(string $property): void
    {
        if (in_array($property, ['name', 'phone', 'email', 'address', 'city', 'region'], true)) {
            $this->validateOnly($property);
        }
    }

    /** Prefill the address form when a logged-in customer picks a saved address. */
    public function updatedSelectedAddressId(): void
    {
        if (! $this->selectedAddressId) {
            return;
        }

        if ($address = auth('customer')->user()?->addresses()->find($this->selectedAddressId)) {
            $this->fillFromAddress($address);
        }
    }

    protected function fillFromAddress(CustomerAddress $address): void
    {
        $this->name = $address->name ?: $this->name;
        $this->phone = $address->phone ?: $this->phone;
        $this->address = (string) $address->address;

        // City/region are derived from the cascading dropdown when locations are on,
        // so only copy the free-text fields when that mode is disabled.
        if (! app(LocationService::class)->enabled()) {
            $this->city = (string) $address->city;
            $this->region = (string) $address->region;
        }
    }

    /**
     * Save the address used to place this order to the customer's address book,
     * unless it already matches one they've saved (picking an existing saved
     * address, or re-ordering to the same place, should never create a duplicate).
     *
     * @param  array{name:string, phone:?string, address:?string, city:?string, region:?string}  $data
     */
    protected function rememberAddress(Customer $customer, array $data): void
    {
        $address = trim((string) ($data['address'] ?? ''));

        // Digital-only checkout never collects a shipping address.
        if ($address === '') {
            return;
        }

        $city = $data['city'] ? trim((string) $data['city']) : null;

        $duplicate = $customer->addresses()
            ->whereRaw('LOWER(TRIM(address)) = ?', [mb_strtolower($address)])
            ->when($city, fn ($q) => $q->whereRaw('LOWER(TRIM(city)) = ?', [mb_strtolower($city)]))
            ->exists();

        if ($duplicate) {
            return;
        }

        $customer->addresses()->create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'address' => $address,
            'city' => $city,
            'region' => $data['region'] ?: null,
            // First saved address becomes the default automatically.
            'is_default' => ! $customer->addresses()->exists(),
        ]);
    }

    /**
     * When the shopper changes any level of the cascading location dropdown, drop
     * any now-invalid deeper selections and re-derive the shipping zone + address
     * text from the chosen path.
     */
    public function updatedLocationPath(): void
    {
        $service = app(LocationService::class);
        $levels = $service->levels();

        // Truncate selections below the first empty/invalid level.
        $parentId = null;
        foreach ($levels as $i => $label) {
            $id = (int) ($this->locationPath[$i] ?? 0);
            $valid = $id && Location::where('id', $id)->where('parent_id', $parentId)->where('is_active', true)->exists();

            if (! $valid) {
                foreach ($levels as $j => $l) {
                    if ($j >= $i) {
                        unset($this->locationPath[$j]);
                    }
                }
                break;
            }
            $parentId = $id;
        }

        $result = $service->resolve($this->locationPath);

        if ($result['zone_id']) {
            $this->shippingZoneId = $result['zone_id'];
        }
    }

    /** Enabled payment methods for this cart — COD is dropped for digital-only. */
    protected function availableMethods(bool $requiresShipping)
    {
        $methods = app(PaymentRegistry::class)->enabled();

        return $requiresShipping ? $methods : $methods->reject(fn ($m, $key) => $key === 'cod');
    }

    protected function rules(): array
    {
        $requiresShipping = app(CartService::class)->requiresShipping();

        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+()\-\s]{6,}$/'],
            // A digital order is delivered by email, so an address is required.
            'email' => [$requiresShipping ? 'nullable' : 'required', 'email', 'max:190'],
            'address' => [$requiresShipping ? 'required' : 'nullable', 'string', 'max:255'],
            'city' => [$requiresShipping ? 'required' : 'nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'shippingZoneId' => ['nullable', Rule::exists('shipping_zones', 'id')->where('is_active', true)],
            'paymentMethod' => ['required', 'string', Rule::in($this->availableMethods($requiresShipping)->keys()->all())],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        // Terms/privacy consent is only enforced when the owner has enabled it in
        // Settings → Privacy; otherwise the checkbox never renders and no rule applies.
        if (settings('privacy.checkout_consent_enabled')) {
            $rules['consent'] = ['accepted'];
        }

        return $rules;
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'consent.accepted' => __('storefront.consent_required'),
        ];
    }

    /**
     * Translated field names for validation messages (e.g. "The :attribute field
     * is required.") — without these the placeholder falls back to the raw
     * camelCase property name in every locale, English included.
     *
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('storefront.full_name'),
            'phone' => __('checkout.phone_number'),
            'email' => __('storefront.email'),
            'address' => __('checkout.street_address'),
            'city' => __('storefront.city'),
            'region' => __('checkout.state_region'),
            'shippingZoneId' => __('checkout.shipping_method'),
            'paymentMethod' => __('storefront.payment_method'),
            'notes' => __('checkout.order_notes_optional'),
        ];
    }

    /**
     * One-tap "express" / wallet checkout: pre-select an enabled redirect gateway
     * and run the normal placeOrder() flow. We deliberately reuse placeOrder()'s
     * full validation, so a shopper whose required shipping fields aren't known
     * yet (e.g. a guest with an empty form) gets field errors instead of an
     * invalid order; a logged-in customer whose saved default address prefilled
     * the form gets a genuine one-tap checkout.
     */
    public function expressCheckout(string $key, PlaceOrder $action)
    {
        $method = app(PaymentRegistry::class)->find($key);

        // Never fire for a method that can't actually take an online payment —
        // the UI only renders buttons for enabled redirect gateways, but guard
        // the server side too so a crafted request can't select a dead method.
        if (! $method instanceof RedirectPaymentGateway || ! $method->isEnabled()) {
            $this->dispatch('toast', message: __('checkout.express_unavailable'), type: 'danger');

            return null;
        }

        $this->paymentMethod = $key;

        return $this->placeOrder($action);
    }

    /**
     * Failed-submit feedback: an error toast plus a browser event the checkout
     * layout listens for to scroll the first invalid field into view.
     */
    protected function notifyInvalidFields(): void
    {
        $this->dispatch('toast', message: __('checkout.check_highlighted_fields'), type: 'danger');
        $this->dispatch('checkout-scroll-to-error');
    }

    public function placeOrder(PlaceOrder $action)
    {
        // Throttle order submission to curb spam/duplicate orders.
        $key = 'checkout:'.(auth('customer')->id() ?? request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->dispatch('toast', message: __('checkout.too_many_attempts'), type: 'danger');

            return null;
        }

        RateLimiter::hit($key, 60);

        // When an admin has configured a location hierarchy, the address area and
        // its shipping zone come from the cascading dropdown, not free-text/manual
        // pick — resolve and stamp them before validation.
        $locationId = null;
        $service = app(LocationService::class);

        if ($service->enabled() && app(CartService::class)->requiresShipping()) {
            $result = $service->resolve($this->locationPath);

            if (! $result['valid']) {
                $this->addError('locationPath', __('checkout.select_full_delivery_area'));
                $this->notifyInvalidFields();

                return null;
            }

            $names = $result['names'];
            $this->region = $names[0] ?? '';
            $this->city = end($names) ?: '';
            $this->shippingZoneId = $result['zone_id'];
            $locationId = $result['leaf']?->id;
        }

        try {
            $data = $this->validate();
        } catch (ValidationException $e) {
            // Livewire fills the error bag from this exception as usual; also
            // surface a toast and scroll the shopper to the first highlighted
            // field so a failed submit is never silent (the "Place order"
            // button in the summary rail sits far from the form fields).
            $this->notifyInvalidFields();

            throw $e;
        }

        try {
            $order = $action->handle([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?: null,
                'address' => $data['address'],
                'city' => $data['city'],
                'region' => $data['region'] ?: null,
                'location_id' => $locationId,
                'shipping_zone_id' => $data['shippingZoneId'],
                'payment_method' => $data['paymentMethod'],
                'notes' => $data['notes'] ?: null,
                'customer_id' => auth('customer')->id(),
                'discount' => $this->discount,
                'free_shipping' => $this->freeShipping,
                'coupon_code' => $this->appliedCode,
            ]);
        } catch (InsufficientStockException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger');

            return null;
        } catch (\RuntimeException $e) {
            // PlaceOrder's RuntimeException messages are shopper-safe ("Your
            // cart is empty.", "That payment method is not available."), so
            // flash them for the shop page's flash-toast hook instead of
            // bouncing the shopper there with no explanation.
            session()->flash('flash_toast', $e->getMessage() ?: __('storefront.could_not_place_order'));
            session()->flash('flash_toast_type', 'danger');

            return redirect()->route('storefront.shop');
        }

        // A logged-in shopper who typed a new (or edited) delivery address gets it
        // saved to their address book, so it's there to pick from next time — same
        // as if they'd added it from the account page.
        if ($customer = auth('customer')->user()) {
            $this->rememberAddress($customer, $data);
        }

        // Online gateways send the customer to a hosted checkout; confirmation
        // comes back via webhook. If the session can't be created, the order
        // still exists (unpaid) and we fall through to the thank-you page.
        $method = app(PaymentRegistry::class)->find($data['paymentMethod']);

        if ($method instanceof RedirectPaymentGateway) {
            try {
                return redirect()->away($method->pay($order));
            } catch (\Throwable $e) {
                // The gateway now throws a clean, shopper-readable reason (parsed
                // from its structured error — no keys/stack traces), so surface it.
                // Stay on checkout (don't redirect to the thank-you page, which
                // would swallow the toast) so the shopper sees why and can retry
                // or pick another method — their order already exists as unpaid.
                $reason = trim($e->getMessage());
                $prefix = $reason !== ''
                    ? __('checkout.payment_start_failed', ['reason' => $reason]).' '
                    : __('checkout.payment_start_failed_generic').' ';

                $this->dispatch('toast', message: $prefix.__('checkout.order_saved_unpaid'), type: 'danger');

                return null;
            }
        }

        return redirect()->route('storefront.thankyou', $order->number);
    }

    public function render()
    {
        $cart = app(CartService::class);
        $items = $cart->items();
        $subtotal = $cart->subtotal();
        $requiresShipping = $cart->requiresShipping();
        $zone = ($requiresShipping && $this->shippingZoneId) ? ShippingZone::find($this->shippingZoneId) : null;
        $shipping = ($this->freeShipping || ! $requiresShipping) ? 0 : ($zone ? $zone->costFor($subtotal) : 0);

        $tax = app(TaxService::class);
        $taxBase = max(0, $subtotal - $this->discount);
        $taxAmount = $tax->taxFor($taxBase);
        $total = $taxBase + $tax->addedTaxFor($taxBase) + $shipping;

        // Build the cascading location dropdown options (each level = children of the
        // previously selected node).
        $service = app(LocationService::class);
        $locationsEnabled = $service->enabled();
        $locationLevels = $locationsEnabled ? $service->levels() : [];
        $locationOptions = [];

        if ($locationsEnabled) {
            $parentId = null;
            foreach ($locationLevels as $i => $label) {
                $locationOptions[$i] = $service->options($parentId);
                $selected = (int) ($this->locationPath[$i] ?? 0);
                if (! $selected) {
                    break;
                }
                $parentId = $selected;
            }
        }

        // Express checkout: only genuine, enabled redirect gateways get a one-tap
        // button. If none are enabled the whole express area is hidden (no dead
        // buttons).
        $methods = $this->availableMethods($requiresShipping);
        $redirectGateways = $methods->filter(fn ($m) => $m instanceof RedirectPaymentGateway);

        // Wallet-specific buttons belong to their gateway add-ons. Core only
        // exposes the generic redirect-gateway slot.
        $wallets = [];

        return View::make('theme::livewire.checkout', [
            'redirectGateways' => $redirectGateways,
            'wallets' => $wallets,
            'locationsEnabled' => $locationsEnabled,
            'locationLevels' => $locationLevels,
            'locationOptions' => $locationOptions,
            'items' => $items,
            'requiresShipping' => $requiresShipping,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'discount' => $this->discount,
            'tax' => $taxAmount,
            'taxLabel' => $tax->label(),
            'taxInclusive' => $tax->inclusive(),
            'taxEnabled' => $tax->enabled() && $taxAmount > 0,
            'total' => $total,
            'zones' => ShippingZone::where('is_active', true)->orderBy('position')->get(),
            'methods' => $methods,
            'savedAddresses' => auth('customer')->user()?->addresses()->orderByDesc('is_default')->get() ?? collect(),
        ]);
    }
}
