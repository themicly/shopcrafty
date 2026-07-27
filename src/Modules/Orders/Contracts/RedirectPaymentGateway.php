<?php

namespace Themicly\Shopcrafty\Modules\Orders\Contracts;

use Illuminate\Http\Request;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

/**
 * An online payment method that sends the customer to a hosted checkout and
 * confirms via a return URL and/or webhook. Stripe, PayPal and redirect-based
 * mobile-money providers implement this on top of {@see PaymentMethod}.
 */
interface RedirectPaymentGateway extends PaymentMethod
{
    /** Create a payment session for the order and return the URL to redirect to. */
    public function pay(Order $order): string;

    /** Verify a gateway callback/webhook payload and return the referenced order (or null). */
    public function resolveWebhookOrder(Request $request): ?Order;
}
