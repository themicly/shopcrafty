<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Contracts;

use Themicly\Shopcrafty\Modules\Notifications\Support\DeliveryResult;
use Themicly\Shopcrafty\Modules\Notifications\Support\OutgoingMessage;

/**
 * A delivery provider for a single channel. Mirrors Orders\Contracts\PaymentMethod —
 * add a new provider by implementing this and registering it (see docs/03-modules-notifications.md).
 */
interface MessageGateway
{
    /** Stable machine key, e.g. 'smtp' or 'twilio'. */
    public function key(): string;

    /** Human label for the admin picker. */
    public function label(): string;

    /** Channel this gateway serves: email or sms. */
    public function channel(): string;

    /** Whether valid credentials/settings are present to actually send. */
    public function isConfigured(): bool;

    /**
     * Config schema that renders the admin form.
     *
     * @return array<int, array{key:string, label:string, type?:string, secret?:bool, help?:string}>
     */
    public function configFields(): array;

    public function send(OutgoingMessage $message): DeliveryResult;
}
