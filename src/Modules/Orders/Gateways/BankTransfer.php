<?php

namespace Themicly\Shopcrafty\Modules\Orders\Gateways;

use Themicly\Shopcrafty\Modules\Orders\Contracts\PaymentMethod;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class BankTransfer implements PaymentMethod
{
    public function key(): string
    {
        return 'bank_transfer';
    }

    public function label(): string
    {
        return __('checkout.payment_bank_transfer');
    }

    public function instructions(): ?string
    {
        $all = settings('payments.bank_transfer.config');
        $custom = is_array($all) ? ($all['instructions'] ?? null) : null;

        return (string) ($custom ?: __('checkout.bank_transfer_instructions_default'));
    }

    public function isEnabled(): bool
    {
        return (bool) settings('payments.bank_transfer.enabled', true);
    }

    public function configFields(): array
    {
        return [
            ['key' => 'instructions', 'label' => 'Payment instructions', 'type' => 'textarea', 'help' => 'Shown to the customer at checkout (bank details, reference, etc.)'],
        ];
    }

    public function process(Order $order): ?string
    {
        // Awaiting manual confirmation of the transfer; order stays unpaid.
        return null;
    }
}
