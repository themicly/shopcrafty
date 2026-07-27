<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Gateways;

use Illuminate\Support\Facades\Mail;
use Themicly\Shopcrafty\Modules\Notifications\Contracts\MessageGateway;
use Themicly\Shopcrafty\Modules\Notifications\Gateways\Concerns\ReadsGatewayConfig;
use Themicly\Shopcrafty\Modules\Notifications\Support\DeliveryResult;
use Themicly\Shopcrafty\Modules\Notifications\Support\OutgoingMessage;

/**
 * Email via the app's configured mail transport (.env: smtp/mailgun/ses/log).
 * This is the zero-setup default; owners who want their own SMTP account use
 * the SmtpMail provider instead.
 */
class MailGateway implements MessageGateway
{
    use ReadsGatewayConfig;

    public function key(): string
    {
        // Historical key: existing installs saved "smtp" for this app-default
        // gateway, so it must stay. The real SMTP provider is 'custom_smtp'
        // (SmtpMail) — do not reuse this key.
        return 'smtp';
    }

    public function label(): string
    {
        return 'App default';
    }

    public function channel(): string
    {
        return 'email';
    }

    public function isConfigured(): bool
    {
        // The app mailer is always configured (Mailpit in dev); a real store
        // sets SMTP credentials on the Emails settings page.
        return true;
    }

    public function configFields(): array
    {
        // Mail routes through the app's configured transport (.env), so we only
        // collect the sender identity here. Owners who want to enter their own
        // SMTP credentials switch to the SmtpMail provider card (NOT-05).
        return [
            ['key' => 'from_name', 'label' => 'From name'],
            ['key' => 'from_email', 'label' => 'From email', 'type' => 'email'],
        ];
    }

    public function send(OutgoingMessage $message): DeliveryResult
    {
        try {
            $fromEmail = $this->config('from_email') ?: settings('general.store_email');
            $fromName = $this->config('from_name') ?: settings('general.store_name', config('app.name'));

            Mail::html($message->body, function ($mail) use ($message, $fromEmail, $fromName) {
                $mail->to($message->to)->subject($message->subject ?? '');
                if ($fromEmail) {
                    $mail->from($fromEmail, $fromName);
                }
            });

            return DeliveryResult::ok($this->key());
        } catch (\Throwable $e) {
            return DeliveryResult::fail($this->key(), $e->getMessage());
        }
    }
}
