<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Support;

final class DeliveryResult
{
    public function __construct(
        public readonly bool $ok,
        public readonly string $gateway,
        public readonly ?string $error = null,
        public readonly ?string $reference = null,
    ) {}

    public static function ok(string $gateway, ?string $reference = null): self
    {
        return new self(true, $gateway, null, $reference);
    }

    public static function fail(string $gateway, string $error): self
    {
        return new self(false, $gateway, $error);
    }
}
