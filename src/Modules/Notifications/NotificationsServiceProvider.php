<?php

namespace Themicly\Shopcrafty\Modules\Notifications;

use Illuminate\Support\Facades\Event;
use Themicly\Shopcrafty\Core\Module\ModuleServiceProvider;
use Themicly\Shopcrafty\Modules\Customers\Events\CustomerRegistered;
use Themicly\Shopcrafty\Modules\Notifications\Gateways\LogSms;
use Themicly\Shopcrafty\Modules\Notifications\Gateways\MailGateway;
use Themicly\Shopcrafty\Modules\Notifications\Gateways\SmtpMail;
use Themicly\Shopcrafty\Modules\Notifications\Gateways\TwilioSms;
use Themicly\Shopcrafty\Modules\Notifications\Listeners\CustomerNotifications;
use Themicly\Shopcrafty\Modules\Notifications\Listeners\OrderNotifications;
use Themicly\Shopcrafty\Modules\Notifications\Services\ProviderRegistry;
use Themicly\Shopcrafty\Modules\Orders\Events\DigitalOrderReady;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderPlaced;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderStatusChanged;

class NotificationsServiceProvider extends ModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Notifications';
    }

    protected function modulePath(): string
    {
        return __DIR__;
    }

    protected function registerModule(): void
    {
        $this->app->singleton(ProviderRegistry::class);
    }

    protected function bootModule(): void
    {
        // Bundled gateways. New gateways (Twilio, plugins)
        // register the same way — one class + one register() call, no core change.
        $registry = $this->app->make(ProviderRegistry::class);
        $registry->register(MailGateway::class);
        $registry->register(SmtpMail::class);
        $registry->register(LogSms::class);
        $registry->register(TwilioSms::class);
        $this->applyEmailOverride();

        // Domain events → notification event keys. Adding a new trigger is a
        // listener + a catalog entry, nothing in the emitting module changes.
        Event::listen(OrderPlaced::class, [OrderNotifications::class, 'placed']);
        Event::listen(OrderStatusChanged::class, [OrderNotifications::class, 'statusChanged']);
        Event::listen(DigitalOrderReady::class, [OrderNotifications::class, 'digitalReady']);
        Event::listen(CustomerRegistered::class, [CustomerNotifications::class, 'welcome']);
    }

    /**
     * When the owner picked the SMTP provider on the Email gateway page, apply
     * those settings to Laravel's runtime mail config so every outgoing email
     * (order notifications, password resets, ...) actually sends through it —
     * no .env editing. Guarded twice: only when that provider is the saved
     * choice, and applyToMailConfig() itself no-ops without a saved host +
     * from address, so a half-filled form never breaks app mail.
     */
    public function applyEmailOverride(): void
    {
        try {
            if (settings('notifications.email.gateway') === 'custom_smtp') {
                $this->app->make(SmtpMail::class)->applyToMailConfig();
            }
        } catch (\Throwable) {
            // Settings storage unavailable (installer, pre-migration artisan
            // runs) — keep the .env mail configuration.
        }
    }
}
