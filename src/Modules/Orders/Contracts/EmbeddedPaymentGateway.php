<?php

namespace Themicly\Shopcrafty\Modules\Orders\Contracts;

/**
 * A gateway that collects payment details inline via its own JavaScript SDK
 * (hosted fields / embedded elements — e.g. Stripe Elements or Braintree Hosted
 * Fields) instead of redirecting the shopper off-site. The checkout renders a
 * `wire:ignore` mount node whose id is {@see elementId()}; the SDK owns that DOM
 * and tokenises the card client-side before the order is placed.
 *
 * No bundled gateway implements this yet — it is the extension point that lets a
 * future embedded driver light up the inline checkout UI without touching the
 * blade. Off-site gateways use {@see RedirectPaymentGateway} instead.
 */
interface EmbeddedPaymentGateway extends PaymentMethod
{
    /** DOM id of the wire:ignore node the gateway's SDK should mount into. */
    public function elementId(): string;
}
