<?php

namespace Themicly\Shopcrafty\Modules\Orders\Contracts;

use Illuminate\Http\Request;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

/**
 * Gateways that can verify a returning shopper's payment synchronously, by
 * asking the gateway's API about the session referenced in the return URL.
 * This is the fallback confirmation path for installs whose webhooks can't be
 * reached (localhost, misconfigured endpoint) — the webhook stays primary.
 */
interface ConfirmsReturnPayment
{
    /** True only when the gateway confirms this order's payment succeeded. */
    public function confirmReturn(Order $order, Request $request): bool;
}
