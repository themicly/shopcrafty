<?php

namespace Themicly\Shopcrafty\Modules\Orders\Contracts;

use Themicly\Shopcrafty\Modules\Orders\Models\Order;

/**
 * A payment method. COD and bank transfer are functional in v1.0; online
 * gateways (Stripe, PayPal, bKash…) implement this same contract — this is the
 * extension point for the payment-driver plugins in later sessions.
 */
interface PaymentMethod
{
    public function key(): string;

    public function label(): string;

    public function instructions(): ?string;

    public function isEnabled(): bool;

    /**
     * Config schema for the payment-method manager (credentials, instructions).
     * Offline methods return an empty array; gateways describe their fields.
     *
     * @return array<int, array{key:string, label:string, type?:string, secret?:bool, help?:string}>
     */
    public function configFields(): array;

    /**
     * Handle payment for a freshly placed order. Offline methods (COD, bank
     * transfer) simply leave it unpaid; redirect gateways return a URL to send
     * the customer to (see RedirectPaymentGateway), otherwise null.
     */
    public function process(Order $order): ?string;
}
