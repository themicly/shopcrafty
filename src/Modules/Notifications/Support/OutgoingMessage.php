<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Support;

/** An immutable, channel-agnostic message ready for a gateway to deliver. */
final class OutgoingMessage
{
    public function __construct(
        public readonly string $channel,   // email | sms
        public readonly string $to,
        public readonly ?string $subject,
        public readonly string $body,
        public readonly array $meta = [],  // event key, recipient type, media, …
    ) {}
}
