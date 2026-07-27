<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Gateways;

use Illuminate\Support\Facades\Http;
use Themicly\Shopcrafty\Modules\Notifications\Contracts\MessageGateway;
use Themicly\Shopcrafty\Modules\Notifications\Gateways\Concerns\ReadsGatewayConfig;
use Themicly\Shopcrafty\Modules\Notifications\Support\DeliveryResult;
use Themicly\Shopcrafty\Modules\Notifications\Support\OutgoingMessage;

/** SMS via Twilio's REST API. */
class TwilioSms implements MessageGateway
{
    use ReadsGatewayConfig;

    public function key(): string
    {
        return 'twilio';
    }

    public function label(): string
    {
        return 'SMS — Twilio';
    }

    public function channel(): string
    {
        return 'sms';
    }

    public function isConfigured(): bool
    {
        return filled($this->config('account_sid'))
            && filled($this->config('auth_token'))
            && filled($this->config('from'));
    }

    public function configFields(): array
    {
        return [
            ['key' => 'account_sid', 'label' => 'Account SID', 'help' => 'Twilio Console → Account Info'],
            ['key' => 'auth_token', 'label' => 'Auth token', 'secret' => true, 'help' => 'Twilio Console → Account Info (keep secret)'],
            ['key' => 'from', 'label' => 'From number', 'help' => 'A Twilio number you own, e.g. +1234567890'],
        ];
    }

    public function send(OutgoingMessage $message): DeliveryResult
    {
        try {
            $sid = (string) $this->config('account_sid');

            $response = Http::withBasicAuth($sid, (string) $this->config('auth_token'))
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $this->config('from'),
                    'To' => $message->to,
                    'Body' => $message->body,
                ]);

            if ($response->successful()) {
                return DeliveryResult::ok($this->key(), $response->json('sid'));
            }

            return DeliveryResult::fail($this->key(), $response->json('message') ?? 'HTTP '.$response->status());
        } catch (\Throwable $e) {
            return DeliveryResult::fail($this->key(), $e->getMessage());
        }
    }
}
