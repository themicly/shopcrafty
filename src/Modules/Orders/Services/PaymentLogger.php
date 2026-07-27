<?php

namespace Themicly\Shopcrafty\Modules\Orders\Services;

use Illuminate\Support\Str;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Models\PaymentLog;

/**
 * Records every payment-gateway interaction to the `payment_logs` table.
 *
 * Two hard guarantees:
 *  1. It NEVER throws — logging can never break checkout (the insert is wrapped
 *     in rescue(report:false)), so a logging failure is swallowed silently.
 *  2. It NEVER stores secrets — only a whitelist of safe fields reaches
 *     `context`, and any string value is scrubbed of known key/token patterns
 *     (sk_/rk_/whsec_/Bearer …) before it lands in the database.
 */
class PaymentLogger
{
    /** Only these keys survive into the stored `context` JSON. */
    protected const SAFE_KEYS = [
        'session_id', 'approval_id', 'paypal_order_id', 'amount', 'currency',
        'order_number', 'error_code', 'error_type', 'decline_code', 'status',
        'verification_status', 'event_type', 'resolved', 'matched', 'reason',
        'idempotent', 'applied', 'body',
    ];

    /**
     * @param  array{order?: ?Order, order_id?: ?int, order_number?: ?string, http_status?: ?int, message?: ?string, context?: array}  $opts
     */
    public function record(string $gateway, string $action, bool $success, array $opts = []): void
    {
        rescue(function () use ($gateway, $action, $success, $opts) {
            $order = $opts['order'] ?? null;

            PaymentLog::create([
                'order_id' => $order?->id ?? ($opts['order_id'] ?? null),
                'order_number' => $order?->number ?? ($opts['order_number'] ?? null),
                'gateway' => Str::limit($gateway, 60, ''),
                'action' => Str::limit($action, 60, ''),
                'success' => $success,
                'http_status' => $opts['http_status'] ?? null,
                'message' => isset($opts['message']) ? Str::limit((string) $opts['message'], 1000) : null,
                'context' => $this->sanitize($opts['context'] ?? []),
            ]);
        }, report: false);
    }

    /**
     * Whitelist safe keys and scrub secret patterns from any string value; the
     * gateway response body is additionally truncated. Anything not on the
     * whitelist (headers, api keys, card data, raw auth) is dropped entirely.
     */
    protected function sanitize(array $context): ?array
    {
        $clean = [];

        foreach ($context as $key => $value) {
            if (! in_array($key, static::SAFE_KEYS, true)) {
                continue;
            }

            if (is_string($value)) {
                $value = $this->redact($value);

                if ($key === 'body') {
                    $value = Str::limit($value, 800);
                }
            }

            if ($value === null || $value === '') {
                continue;
            }

            $clean[$key] = $value;
        }

        return $clean ?: null;
    }

    /** Strip known gateway secret/token shapes so they can never be persisted. */
    protected function redact(string $value): string
    {
        $patterns = [
            '/\b(?:sk|rk|pk|whsec|pi|seti)_[A-Za-z0-9_]+/',   // Stripe keys/ids that may be secret
            '/Bearer\s+[A-Za-z0-9._\-]+/i',                   // Authorization: Bearer …
            '/Basic\s+[A-Za-z0-9+\/=]+/i',                    // Authorization: Basic …
            '/"?access_token"?\s*[:=]\s*"?[A-Za-z0-9._\-]+"?/i',
        ];

        // Keep pk_ (publishable) but redact secret sk_/rk_/whsec_ specifically —
        // simplest safe default is to redact the whole family; publishable keys
        // aren't sensitive but also aren't useful in a log, so dropping is fine.
        return (string) preg_replace($patterns, '[redacted]', $value);
    }
}
