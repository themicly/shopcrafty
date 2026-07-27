<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Gateways\Concerns;

use Illuminate\Support\Facades\Crypt;

/**
 * Reads a single credential for a gateway from its channel config blob. The
 * admin stores the whole form as one array under "notifications.{channel}.config",
 * so a field is read by fetching that array and indexing it (not via a dotted
 * settings key, which would be a different, non-existent entry).
 *
 * Secret fields are stored encrypted (NOT-01); we transparently decrypt here and
 * fall back to the raw value for the non-secret fields (stored in the clear).
 */
trait ReadsGatewayConfig
{
    protected function config(string $key, mixed $default = null): mixed
    {
        $all = settings("notifications.{$this->channel()}.config");

        if (! is_array($all) || ! array_key_exists($key, $all)) {
            return $default;
        }

        $value = $all[$key];

        if (is_string($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Throwable) {
                return $value;
            }
        }

        return $value;
    }
}
