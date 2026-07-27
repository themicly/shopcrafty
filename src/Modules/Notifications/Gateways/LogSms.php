<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Gateways;

use Illuminate\Support\Facades\Log;
use Themicly\Shopcrafty\Modules\Notifications\Contracts\MessageGateway;
use Themicly\Shopcrafty\Modules\Notifications\Support\DeliveryResult;
use Themicly\Shopcrafty\Modules\Notifications\Support\OutgoingMessage;

/** Dev SMS gateway — writes to the log instead of sending. Always available. */
class LogSms implements MessageGateway
{
    public function key(): string
    {
        return 'log';
    }

    public function label(): string
    {
        return 'SMS — log only (testing)';
    }

    public function channel(): string
    {
        return 'sms';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function configFields(): array
    {
        return [];
    }

    public function send(OutgoingMessage $message): DeliveryResult
    {
        Log::info("[SMS→{$message->to}] {$message->body}");

        return DeliveryResult::ok($this->key());
    }
}
