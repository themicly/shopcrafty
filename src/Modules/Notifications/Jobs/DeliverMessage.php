<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Themicly\Shopcrafty\Modules\Notifications\Models\NotificationLog;
use Themicly\Shopcrafty\Modules\Notifications\Services\ProviderRegistry;
use Themicly\Shopcrafty\Modules\Notifications\Support\OutgoingMessage;

/**
 * Delivers one message through the active gateway for its channel, then records
 * a notification_logs row. Queued (database driver on shared hosting); an
 * unconfigured channel is logged as "skipped" and never blocks an order.
 */
class DeliverMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** Retry transient infrastructure failures with growing backoff (NOT-04). */
    public int $tries = 3;

    /** @return array<int, int> seconds between attempts */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function __construct(
        public string $eventKey,
        public string $channel,
        public string $to,
        public ?string $subject,
        public string $body,
        public string $recipientType,
    ) {}

    public function handle(ProviderRegistry $registry): void
    {
        $gateway = $registry->for($this->channel);

        if (! $gateway || ! $gateway->isConfigured()) {
            $this->log(null, 'skipped', 'No configured gateway for this channel.');

            return;
        }

        $result = $gateway->send(new OutgoingMessage(
            channel: $this->channel,
            to: $this->to,
            subject: $this->subject,
            body: $this->body,
            meta: ['event' => $this->eventKey, 'recipient' => $this->recipientType],
        ));

        $this->log($gateway->key(), $result->ok ? 'sent' : 'failed', $result->error);
    }

    /** If the job dies (uncaught exception after retries), leave an audit row. */
    public function failed(\Throwable $e): void
    {
        $this->log(null, 'failed', $e->getMessage());
    }

    protected function log(?string $gateway, string $status, ?string $error = null): void
    {
        NotificationLog::create([
            'event_key' => $this->eventKey,
            'channel' => $this->channel,
            'gateway' => $gateway,
            'recipient' => $this->to,
            'recipient_type' => $this->recipientType,
            'status' => $status,
            'error' => $error,
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }
}
