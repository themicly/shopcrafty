<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Gateways;

use Illuminate\Support\Facades\Mail;
use Themicly\Shopcrafty\Modules\Notifications\Contracts\MessageGateway;
use Themicly\Shopcrafty\Modules\Notifications\Gateways\Concerns\ReadsGatewayConfig;
use Themicly\Shopcrafty\Modules\Notifications\Support\DeliveryResult;
use Themicly\Shopcrafty\Modules\Notifications\Support\OutgoingMessage;

/**
 * Email through the store's own SMTP account, entered on the Email gateway
 * page — no .env editing (NOT-05). When this provider is the active email
 * gateway, applyToMailConfig() rewrites Laravel's mail config at runtime
 * (hooked in NotificationsServiceProvider::applyEmailOverride) so ALL app
 * mail — notifications, password resets, receipts — goes through it.
 *
 * Key is 'custom_smtp', not 'smtp': the historical 'smtp' key already means
 * "app default mail" (MailGateway) on existing installs, and reusing it would
 * silently re-point saved installs at an unconfigured provider.
 */
class SmtpMail implements MessageGateway
{
    use ReadsGatewayConfig;

    public function key(): string
    {
        return 'custom_smtp';
    }

    public function label(): string
    {
        return 'SMTP';
    }

    public function channel(): string
    {
        return 'email';
    }

    public function isConfigured(): bool
    {
        return filled($this->config('host')) && filled($this->config('from_email'));
    }

    public function configFields(): array
    {
        return [
            ['key' => 'host', 'label' => 'SMTP host', 'help' => 'e.g. smtp.gmail.com'],
            ['key' => 'port', 'label' => 'Port', 'help' => '587 for TLS (default), 465 for SSL'],
            ['key' => 'username', 'label' => 'Username', 'help' => 'Usually the full email address'],
            ['key' => 'password', 'label' => 'Password', 'secret' => true, 'help' => 'For Gmail/Outlook, create an app password'],
            ['key' => 'encryption', 'label' => 'Encryption', 'type' => 'select', 'options' => [
                'tls' => 'TLS / STARTTLS (port 587)',
                'ssl' => 'SSL / SMTPS (port 465)',
                'none' => 'None (not recommended)',
            ]],
            ['key' => 'from_name', 'label' => 'From name', 'help' => 'Defaults to the store name'],
            ['key' => 'from_email', 'label' => 'From address', 'type' => 'email', 'help' => 'Usually must match the SMTP account'],
        ];
    }

    /**
     * Push the saved SMTP settings into Laravel's runtime mail config and make
     * "smtp" the default mailer. Guarded: does nothing unless a host and from
     * address are saved, so a half-filled form can never break app mail.
     */
    public function applyToMailConfig(): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $encryption = strtolower((string) ($this->config('encryption') ?: 'tls'));
        $port = (int) ($this->config('port') ?: ($encryption === 'ssl' ? 465 : 587));

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $this->config('host'),
            'mail.mailers.smtp.port' => $port,
            'mail.mailers.smtp.username' => $this->config('username') ?: null,
            'mail.mailers.smtp.password' => $this->config('password') ?: null,
            // 'smtps' = implicit TLS (SSL). For 'tls'/'none', leave the scheme
            // unset: Symfony negotiates STARTTLS automatically when offered.
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : null,
            'mail.from.address' => $this->config('from_email'),
            'mail.from.name' => $this->fromName(),
        ]);

        // Drop any mailer already built from the old config so the next send rebuilds.
        Mail::purge('smtp');

        return true;
    }

    public function send(OutgoingMessage $message): DeliveryResult
    {
        if (! $this->applyToMailConfig()) {
            return DeliveryResult::fail($this->key(), 'SMTP is not configured — save a host and from address first.');
        }

        try {
            Mail::mailer('smtp')->html($message->body, function ($mail) use ($message) {
                $mail->to($message->to)
                    ->subject($message->subject ?? '')
                    ->from((string) $this->config('from_email'), $this->fromName());
            });

            return DeliveryResult::ok($this->key());
        } catch (\Throwable $e) {
            return DeliveryResult::fail($this->key(), $e->getMessage());
        }
    }

    protected function fromName(): string
    {
        return (string) ($this->config('from_name') ?: settings('general.store_name', config('app.name')));
    }
}
