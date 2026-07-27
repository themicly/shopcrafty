<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Livewire;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Notifications\Models\NotificationLog;
use Themicly\Shopcrafty\Modules\Notifications\Services\ProviderRegistry;
use Themicly\Shopcrafty\Modules\Notifications\Support\OutgoingMessage;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

/**
 * Admin: per-channel provider cards (same journey as payment methods). Each
 * provider is a card with an enable toggle; turning one on reveals its
 * credential fields + test send and switches the others off (one active
 * gateway per channel), and turning the active one off disables the channel.
 * The registry (mirrors PaymentRegistry) supplies the providers; the choice
 * and config persist under the "notifications" settings group.
 */
class NotificationGateways extends Component
{
    /** @var array<string, string> channel => active gateway key */
    public array $active = [];

    /** @var array<string, bool> channel => channel switched on at all */
    public array $enabled = [];

    /** @var array<string, array<string, mixed>> channel => [field => value] */
    public array $config = [];

    /** @var array<string, string> channel => test recipient */
    public array $testTo = [];

    /** When set, the page shows only this channel's provider config. */
    public ?string $channel = null;

    public function mount(ProviderRegistry $registry, ?string $channel = null): void
    {
        $this->channel = $channel;

        foreach ($this->channels($registry) as $c) {
            $gateways = $registry->all($c);
            $chosen = (string) settings("notifications.{$c}.gateway");

            // Normalise a stale/unknown stored key (e.g. a provider no longer
            // offered, like wa_link) to a real one — same fallback order as
            // ProviderRegistry::for() — so the card toggles line up with what
            // actually sends.
            $this->active[$c] = $gateways->firstWhere(fn ($g) => $g->key() === $chosen)?->key()
                ?? $gateways->first(fn ($g) => $g->isConfigured())?->key()
                ?? $gateways->first()?->key()
                ?? '';

            // Missing setting = enabled, so pre-toggle installs keep sending.
            $this->enabled[$c] = (bool) (settings("notifications.{$c}.enabled") ?? true);

            // Load config, but NEVER hydrate secret values into a public property —
            // they'd serialize into the page HTML (NOT-02). Secrets stay server-side;
            // the input renders blank with a "saved" hint instead.
            $stored = settings("notifications.{$c}.config") ?? [];
            $secretKeys = $this->secretKeys($registry, $c);
            $this->config[$c] = collect($stored)
                ->reject(fn ($v, $k) => in_array($k, $secretKeys, true))
                ->all();
            $this->testTo[$c] = '';
        }
    }

    /** @return array<int, string> keys of the active gateway's fields marked secret. */
    protected function secretKeys(ProviderRegistry $registry, string $channel): array
    {
        return collect($this->activeGateway($registry, $channel)?->configFields() ?? [])
            ->filter(fn ($f) => $f['secret'] ?? false)
            ->pluck('key')->all();
    }

    protected function activeGateway(ProviderRegistry $registry, string $channel)
    {
        $gateways = $registry->all($channel);

        return $gateways->firstWhere(fn ($g) => $g->key() === ($this->active[$channel] ?? null))
            ?? $gateways->first();
    }

    /** @return array<int, string> the channels this page manages (one, or all as a fallback) */
    protected function channels(ProviderRegistry $registry): array
    {
        return $this->channel ? [$this->channel] : $registry->channels();
    }

    /**
     * Where to get credentials for each provider. Shown as a guide banner so an
     * owner isn't left guessing what to paste in.
     *
     * @return array<string, array{text: string, url: ?string, label: ?string}>
     */
    public function guides(): array
    {
        return [
            'smtp' => ['text' => "No setup needed — mail is sent with the server's own configuration. Set a custom sender below if you like.", 'url' => null, 'label' => null],
            'custom_smtp' => ['text' => 'Copy the SMTP host, port and login from your email provider. For Gmail or Outlook, create an app password to use here.', 'url' => null, 'label' => null],
            'log' => ['text' => 'Testing only — writes messages to the log instead of sending them.', 'url' => null, 'label' => null],
            'twilio' => ['text' => 'Create an account, buy a number, then copy your Account SID and Auth Token.', 'url' => 'https://console.twilio.com', 'label' => 'Open Twilio Console'],
        ];
    }

    /**
     * One-line card descriptions, keyed by gateway key.
     *
     * @return array<string, string>
     */
    public function descriptions(): array
    {
        return [
            'smtp' => "Use the server's mail configuration (.env) — works out of the box.",
            'custom_smtp' => 'Send through your own SMTP account (Gmail, Outlook, cPanel, or an email service).',
            'log' => 'Write messages to the application log instead of sending — for testing.',
            'twilio' => 'Global SMS through your Twilio account.',
        ];
    }

    /** @return array<int, array<string, mixed>> */
    public function getSectionsProperty(): array
    {
        $registry = app(ProviderRegistry::class);
        $guides = $this->guides();
        $descriptions = $this->descriptions();
        $labels = ['email' => 'Email', 'sms' => 'SMS'];

        return collect($this->channels($registry))->map(function (string $channel) use ($registry, $guides, $descriptions, $labels) {
            $gateways = $registry->all($channel);
            $activeKey = $this->active[$channel] ?? $gateways->first()?->key();
            $activeGateway = $gateways->firstWhere(fn ($g) => $g->key() === $activeKey) ?? $gateways->first();
            $enabled = (bool) ($this->enabled[$channel] ?? true);

            return [
                'channel' => $channel,
                'label' => $labels[$channel] ?? ucfirst($channel),
                'enabled' => $enabled,
                'activeKey' => $activeGateway?->key(),
                'gateways' => $gateways->map(fn ($g) => [
                    'key' => $g->key(),
                    'label' => $g->label(),
                    'description' => $descriptions[$g->key()] ?? '',
                    'configured' => $g->isConfigured(),
                    // The card expands only when it is the active provider AND
                    // the channel itself is switched on.
                    'on' => $enabled && $g->key() === $activeGateway?->key(),
                ])->all(),
                // Fields/guide belong to the active gateway — only its card renders them.
                'fields' => collect($activeGateway?->configFields() ?? [])->map(function ($f) use ($channel) {
                    $stored = settings("notifications.{$channel}.config") ?? [];

                    return $f + ['saved' => ($f['secret'] ?? false) && isset($stored[$f['key']])];
                })->all(),
                'configured' => $activeGateway?->isConfigured() ?? false,
                'guide' => $guides[$activeGateway?->key()] ?? null,
            ];
        })->all();
    }

    /**
     * Card toggles behave like radios: turning a provider on makes it the
     * channel's single active gateway (the others switch off); turning the
     * active one off disables the whole channel.
     */
    public function toggleProvider(string $channel, string $key): void
    {
        if (($this->enabled[$channel] ?? true) && ($this->active[$channel] ?? null) === $key) {
            $this->enabled[$channel] = false;

            return;
        }

        $this->active[$channel] = $key;
        $this->enabled[$channel] = true;
    }

    /** Choose a provider and switch it on (also the entry point used by tests). */
    public function selectGateway(string $channel, string $key): void
    {
        $this->active[$channel] = $key;
        $this->enabled[$channel] = true;
    }

    public function save(Settings $settings): void
    {
        $registry = app(ProviderRegistry::class);
        $updates = [];

        foreach ($this->active as $channel => $key) {
            $updates["notifications.{$channel}.gateway"] = $key;
            $updates["notifications.{$channel}.enabled"] = (bool) ($this->enabled[$channel] ?? true);

            $existing = settings("notifications.{$channel}.config") ?? [];
            $fields = $this->activeGateway($registry, $channel)?->configFields() ?? [];
            $config = [];

            // Rebuild from the active gateway's schema so stale fields drop out.
            foreach ($fields as $field) {
                $fk = $field['key'];
                $submitted = $this->config[$channel][$fk] ?? '';

                if ($field['secret'] ?? false) {
                    // Encrypt new secrets; keep the stored one when left blank.
                    if ($submitted !== null && $submitted !== '') {
                        $config[$fk] = Crypt::encryptString((string) $submitted);
                    } elseif (isset($existing[$fk])) {
                        $config[$fk] = $existing[$fk];
                    }
                } elseif ($submitted !== null && $submitted !== '') {
                    $config[$fk] = $submitted;
                }
            }

            $updates["notifications.{$channel}.config"] = $config;
        }

        $settings->setMany($updates);
        $this->dispatch('toast', message: 'Gateways saved', type: 'success');
    }

    public function sendTest(string $channel): void
    {
        // Persist first so the gateway sees the credentials currently on the
        // form — the UI says so next to the button, so it isn't a silent save.
        $this->save(app(Settings::class));

        $to = trim($this->testTo[$channel] ?? '');

        if ($to === '') {
            $this->dispatch('toast', message: 'Enter a test recipient first', type: 'danger');

            return;
        }

        $gateway = app(ProviderRegistry::class)->for($channel);

        if (! $gateway || ! $gateway->isConfigured()) {
            $this->dispatch('toast', message: 'No configured gateway for this channel', type: 'danger');

            return;
        }

        $result = $gateway->send(new OutgoingMessage(
            channel: $channel,
            to: $to,
            subject: 'Test message from '.settings('general.store_name', config('app.name')),
            body: 'This is a test '.$channel.' notification from your store. If you received it, the gateway works.',
            meta: ['event' => 'test'],
        ));

        NotificationLog::create([
            'event_key' => 'test', 'channel' => $channel, 'gateway' => $gateway->key(),
            'recipient' => $to, 'recipient_type' => 'test',
            'status' => $result->ok ? 'sent' : 'failed', 'error' => $result->error,
            'sent_at' => $result->ok ? now() : null,
        ]);

        $this->dispatch('toast',
            message: $result->ok ? "Test sent to {$to}" : "Test failed: {$result->error}",
            type: $result->ok ? 'success' : 'error',
        );
    }

    public function render()
    {
        return View::make('notifications::livewire.notification-gateways');
    }
}
