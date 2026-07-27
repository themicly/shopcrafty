<?php

namespace Themicly\Shopcrafty\Modules\Orders\Services;

use Illuminate\Support\Collection;
use Themicly\Shopcrafty\Modules\Orders\Contracts\PaymentMethod;
use Themicly\Shopcrafty\Modules\Orders\Gateways\BankTransfer;
use Themicly\Shopcrafty\Modules\Orders\Gateways\CashOnDelivery;

/**
 * Central registry of core payment methods. Gateway packages register their
 * own drivers through this registry when installed.
 */
class PaymentRegistry
{
    /** @var array<int, class-string<PaymentMethod>> */
    protected array $methods = [
        CashOnDelivery::class,
        BankTransfer::class,
    ];

    public function register(string $methodClass): void
    {
        if (! in_array($methodClass, $this->methods, true)) {
            $this->methods[] = $methodClass;
        }
    }

    /** All methods, ordered by the owner-configured position (registration order as fallback). */
    public function all(): Collection
    {
        return collect($this->methods)
            ->map(fn (string $class) => app($class))
            ->sortBy(fn (PaymentMethod $m, int $i) => (int) settings("payments.{$m->key()}.position", $i))
            ->values()
            ->keyBy(fn (PaymentMethod $m) => $m->key());
    }

    /** @return Collection<string, PaymentMethod> */
    public function enabled(): Collection
    {
        return $this->all()->filter(fn (PaymentMethod $m) => $m->isEnabled());
    }

    public function find(string $key): ?PaymentMethod
    {
        return $this->all()->get($key);
    }
}
