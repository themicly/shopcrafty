<?php

namespace Themicly\Shopcrafty\Modules\Orders\Gateways;

use Themicly\Shopcrafty\Modules\Orders\Contracts\PaymentMethod;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class CashOnDelivery implements PaymentMethod
{
    public function key(): string
    {
        return 'cod';
    }

    public function label(): string
    {
        return __('checkout.payment_cod');
    }

    public function instructions(): ?string
    {
        return __('checkout.cod_instructions');
    }

    public function isEnabled(): bool
    {
        return (bool) settings('payments.cod.enabled', true);
    }

    public function configFields(): array
    {
        return [];
    }

    public function process(Order $order): ?string
    {
        // COD stays unpaid and enters the verification queue.
        $order->update(['cod_verification_status' => 'unverified']);

        return null;
    }
}
