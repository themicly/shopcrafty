<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Livewire;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Marketing\Models\Coupon;

class CouponBuilder extends Component
{
    public ?int $couponId = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'percentage';

    // Major-unit string for fixed; plain integer 0-100 for percentage.
    public string $value = '';

    // Major-unit string, converted to minor on save.
    public string $minPurchase = '';

    public string $usageLimit = '';

    public string $perCustomerLimit = '';

    public string $startsAt = '';

    public string $endsAt = '';

    public bool $isEnabled = true;

    // BOGO
    public string $buyQty = '1';

    public string $getQty = '1';

    // Scope
    public string $scopeType = 'all';

    /** @var array<int, int> */
    public array $scopeIds = [];

    // Filters the product picker when scope is "product".
    public string $productSearch = '';

    public function mount(?int $coupon = null): void
    {
        if ($coupon) {
            $this->loadCoupon(Coupon::findOrFail($coupon));
        }
    }

    protected function loadCoupon(Coupon $coupon): void
    {
        $this->couponId = $coupon->id;
        $this->code = $coupon->code;
        $this->name = (string) $coupon->name;
        $this->type = $coupon->type;
        $this->value = $coupon->type === 'free_shipping'
            ? ''
            : ($coupon->type === 'percentage' ? (string) $coupon->value : $this->toMajor($coupon->value));
        $this->minPurchase = $coupon->min_purchase !== null ? $this->toMajor($coupon->min_purchase) : '';
        $this->usageLimit = $coupon->usage_limit !== null ? (string) $coupon->usage_limit : '';
        $this->perCustomerLimit = $coupon->per_customer_limit !== null ? (string) $coupon->per_customer_limit : '';
        $this->startsAt = $coupon->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->endsAt = $coupon->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->isEnabled = (bool) $coupon->is_enabled;
        $this->buyQty = (string) ($coupon->buy_qty ?: 1);
        $this->getQty = (string) ($coupon->get_qty ?: 1);
        $this->scopeType = $coupon->scope_type ?: 'all';
        $this->scopeIds = array_map('intval', $coupon->scope_ids ?? []);
    }

    protected function decimals(): int
    {
        return (int) settings('localization.currency_decimals', 2);
    }

    protected function toMinor(string $value): int
    {
        return (int) round(((float) $value) * (10 ** $this->decimals()));
    }

    protected function toMajor(int $minor): string
    {
        return number_format($minor / (10 ** $this->decimals()), $this->decimals(), '.', '');
    }

    protected function rules(): array
    {
        return [
            // $this->code is uppercased in save() before validation, so this
            // catches "summer25" colliding with an existing "SUMMER25".
            'code' => ['required', 'string', 'max:40', Rule::unique((new Coupon)->getTable(), 'code')->ignore($this->couponId)],
            'name' => ['nullable', 'string', 'max:120'],
            'type' => ['required', 'in:percentage,fixed,free_shipping,bogo'],
            // A percentage can't exceed 100% (MKT-09); other types are plain amounts.
            'value' => ['nullable', 'numeric', 'min:0', 'required_if:type,percentage', 'required_if:type,fixed',
                // A percentage is stored as a whole number — reject decimals up
                // front rather than silently truncating 12.5% to 12%.
                ...($this->type === 'percentage' ? ['integer', 'max:100'] : [])],
            'buyQty' => ['nullable', 'integer', 'min:1', 'required_if:type,bogo'],
            'getQty' => ['nullable', 'integer', 'min:1', 'required_if:type,bogo'],
            'scopeType' => ['in:all,category,product'],
            'scopeIds' => ['array'],
            'minPurchase' => ['nullable', 'numeric', 'min:0'],
            'usageLimit' => ['nullable', 'integer', 'min:0'],
            'perCustomerLimit' => ['nullable', 'integer', 'min:0'],
            'startsAt' => ['nullable', 'date'],
            'endsAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
            'isEnabled' => ['boolean'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'minPurchase' => 'minimum purchase',
            'usageLimit' => 'usage limit',
            'perCustomerLimit' => 'per-customer limit',
            'startsAt' => 'start date',
            'endsAt' => 'end date',
        ];
    }

    public function summary(): string
    {
        $symbol = settings('localization.currency_symbol', '$');

        $base = match ($this->type) {
            'percentage' => ($this->value === '' ? '0' : (string) (int) $this->value).'% off',
            'fixed' => format_money($this->value === '' ? 0 : $this->toMinor($this->value)).' off',
            'free_shipping' => 'Free shipping',
            'bogo' => "Buy {$this->buyQty} get {$this->getQty} free",
            default => '',
        };

        $parts = $base;

        if ($this->minPurchase !== '') {
            $parts .= ' on orders over '.format_money($this->toMinor($this->minPurchase));
        }

        if ($this->usageLimit !== '') {
            $parts .= ', limited to '.(int) $this->usageLimit.' uses';
        }

        if ($this->endsAt !== '') {
            $parts .= ', until '.Carbon::parse($this->endsAt)->format('M j, Y');
        }

        return $parts;
    }

    public function save(): void
    {
        // Normalise to the stored form up front so the unique rule compares
        // against what's persisted regardless of the DB's collation (SQLite
        // compares case-sensitively; MySQL usually doesn't).
        $this->code = strtoupper(trim($this->code));

        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name ?: null,
            'type' => $this->type,
            'value' => match ($this->type) {
                'percentage' => (int) $this->value,
                'fixed' => $this->toMinor($this->value),
                default => 0,
            },
            'buy_qty' => $this->type === 'bogo' ? (int) $this->buyQty : null,
            'get_qty' => $this->type === 'bogo' ? (int) $this->getQty : null,
            'scope_type' => $this->scopeType,
            'scope_ids' => $this->scopeType === 'all' ? null : array_values(array_map('intval', $this->scopeIds)),
            'min_purchase' => $this->minPurchase !== '' ? $this->toMinor($this->minPurchase) : null,
            'usage_limit' => $this->usageLimit !== '' ? (int) $this->usageLimit : null,
            'per_customer_limit' => $this->perCustomerLimit !== '' ? (int) $this->perCustomerLimit : null,
            'starts_at' => $this->startsAt !== '' ? $this->startsAt : null,
            'ends_at' => $this->endsAt !== '' ? $this->endsAt : null,
            'is_enabled' => $this->isEnabled,
        ];

        if ($this->couponId) {
            Coupon::findOrFail($this->couponId)->update($data);
            $message = 'Coupon updated';
        } else {
            Coupon::create($data);
            $message = 'Coupon created';
        }

        $this->dispatch('toast', message: $message, type: 'success');
        $this->redirectRoute('admin.marketing.coupons.index', navigate: true);
    }

    public function render()
    {
        return View::make('marketing::livewire.coupon-builder', [
            'summary' => $this->summary(),
            'currencySymbol' => settings('localization.currency_symbol', '$'),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            // Product picker (searchable, capped) + always-visible chips for
            // already-selected products even when filtered out of the list.
            'products' => $this->scopeType === 'product'
                ? Product::query()
                    ->when($this->productSearch !== '', fn ($q) => $q->where('name', 'like', '%'.$this->productSearch.'%'))
                    ->orderBy('name')->limit(50)->get(['id', 'name'])
                : collect(),
            'selectedProducts' => $this->scopeType === 'product' && $this->scopeIds !== []
                ? Product::whereIn('id', $this->scopeIds)->orderBy('name')->get(['id', 'name'])
                : collect(),
        ]);
    }
}
