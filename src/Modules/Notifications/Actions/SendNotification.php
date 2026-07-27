<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Actions;

use Illuminate\Support\Arr;
use Themicly\Shopcrafty\Modules\Notifications\Jobs\DeliverMessage;
use Themicly\Shopcrafty\Modules\Notifications\Services\TemplateRenderer;

/**
 * Entry point for the pipeline: given a notification event key + context, fan
 * out to every enabled channel × recipient and queue a DeliverMessage each.
 */
class SendNotification
{
    public function __construct(protected TemplateRenderer $renderer) {}

    /**
     * @param  array<string, mixed>  $context  e.g. ['order' => [...], 'customer' => ['name','email','phone']]
     * @param  array<int, string>|null  $recipientsOverride
     */
    public function handle(string $eventKey, array $context, ?array $recipientsOverride = null): void
    {
        // Index by literal key — config('notifications.events.order.placed') would
        // mis-read the dot in "order.placed" as nesting.
        $catalog = (config('notifications.events') ?? [])[$eventKey] ?? null;

        if (! $catalog) {
            return;
        }

        // `??` (not `?:`) so an explicitly-emptied channel list stays empty — turning
        // off the last channel must actually silence the event (NOT-03).
        $channels = settings("notifications.events.{$eventKey}.channels") ?? ($catalog['channels'] ?? []);
        $recipients = $recipientsOverride ?? ($catalog['recipients'] ?? ['customer']);
        $context = array_merge($this->baseContext(), $context);

        foreach ($channels as $channel) {
            foreach ($recipients as $recipient) {
                $to = $this->resolveTo($recipient, $channel, $context);

                if (blank($to)) {
                    continue;
                }

                $rendered = $this->render($eventKey, $channel, $catalog, $context);

                if ($rendered === null || $rendered['body'] === '') {
                    continue;
                }

                DeliverMessage::dispatch($eventKey, $channel, (string) $to, $rendered['subject'], $rendered['body'], $recipient);
            }
        }
    }

    protected function baseContext(): array
    {
        return [
            'store' => [
                'name' => settings('general.store_name', config('app.name')),
                'email' => settings('general.store_email'),
                'phone' => settings('general.store_phone'),
                'url' => url('/'),
            ],
        ];
    }

    protected function resolveTo(string $recipient, string $channel, array $context): ?string
    {
        if ($recipient === 'owner') {
            return $channel === 'email'
                ? settings('general.store_email')
                : settings('general.store_phone');
        }

        // customer
        return $channel === 'email'
            ? Arr::get($context, 'customer.email')
            : Arr::get($context, 'customer.phone');
    }

    /** @return array{subject: ?string, body: string}|null */
    protected function render(string $eventKey, string $channel, array $catalog, array $context): ?array
    {
        $template = settings("notifications.templates.{$eventKey}.{$channel}")
            ?? Arr::get($catalog, "templates.{$channel}");

        if (! $template) {
            return null;
        }

        // Email bodies are rendered as HTML, so escape substituted values there.
        $escapeBody = $channel === 'email';

        return [
            'subject' => isset($template['subject']) ? $this->renderer->render($template['subject'], $context) : null,
            'body' => $this->renderer->render($template['body'] ?? '', $context, $escapeBody),
        ];
    }
}
