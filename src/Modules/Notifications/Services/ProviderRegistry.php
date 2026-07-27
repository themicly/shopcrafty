<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Services;

use Illuminate\Support\Collection;
use Themicly\Shopcrafty\Modules\Notifications\Contracts\MessageGateway;

/**
 * Registry of message gateways. Mirrors Orders\Services\PaymentRegistry:
 * modules (and future plugins) register providers; the admin picks the active
 * one per channel via settings.
 */
class ProviderRegistry
{
    /** @var array<int, class-string<MessageGateway>> */
    protected array $gateways = [];

    public function register(string $gatewayClass): void
    {
        if (! in_array($gatewayClass, $this->gateways, true)) {
            $this->gateways[] = $gatewayClass;
        }
    }

    /** @return Collection<int, MessageGateway> */
    public function all(?string $channel = null): Collection
    {
        return collect($this->gateways)
            ->map(fn (string $class) => app($class))
            ->when($channel, fn (Collection $c) => $c->filter(fn (MessageGateway $g) => $g->channel() === $channel))
            ->values();
    }

    /**
     * Whether the channel is switched on at all (the admin's card toggles).
     * A missing setting counts as enabled so installs that predate the toggle
     * keep sending without a resave.
     */
    public function enabled(string $channel): bool
    {
        return (bool) (settings("notifications.{$channel}.enabled") ?? true);
    }

    /** The active gateway for a channel: the configured choice, else first configured, else first available. */
    public function for(string $channel): ?MessageGateway
    {
        // Channel switched off by the admin — nothing sends (DeliverMessage
        // logs the message as "skipped").
        if (! $this->enabled($channel)) {
            return null;
        }

        $all = $this->all($channel);

        if ($all->isEmpty()) {
            return null;
        }

        $chosen = settings("notifications.{$channel}.gateway");

        if ($chosen) {
            $match = $all->firstWhere(fn (MessageGateway $g) => $g->key() === $chosen);
            if ($match) {
                return $match;
            }
        }

        return $all->first(fn (MessageGateway $g) => $g->isConfigured()) ?? $all->first();
    }

    /** @return array<int, string> channels that have at least one registered gateway */
    public function channels(): array
    {
        return $this->all()->map(fn (MessageGateway $g) => $g->channel())->unique()->values()->all();
    }
}
