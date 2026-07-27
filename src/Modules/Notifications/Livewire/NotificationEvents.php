<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

/**
 * Admin: per-event channel toggles + template editor. Reads the config catalog
 * (config/notifications.php) as defaults and persists owner overrides through the
 * Settings service — the same keys SendNotification reads at delivery time.
 */
class NotificationEvents extends Component
{
    /** Event key currently open in the template editor, or null. */
    public ?string $editing = null;

    /** @var array<string, array{subject: ?string, body: string}> channel => template */
    public array $templates = [];

    /** @return array<int, array<string, mixed>> */
    public function getEventsProperty(): array
    {
        return collect(config('notifications.events', []))
            ->map(fn (array $cfg, string $key) => [
                'key' => $key,
                'label' => $cfg['label'] ?? $key,
                'recipients' => $cfg['recipients'] ?? [],
                'variables' => $cfg['variables'] ?? [],
                'available' => array_keys($cfg['templates'] ?? []),
                'enabled' => $this->enabledChannels($key, $cfg),
            ])
            ->values()
            ->all();
    }

    public function toggleChannel(string $key, string $channel): void
    {
        $cfg = $this->catalog($key);

        if (! $cfg || ! in_array($channel, array_keys($cfg['templates'] ?? []), true)) {
            return;
        }

        $enabled = $this->enabledChannels($key, $cfg);

        $enabled = in_array($channel, $enabled, true)
            ? array_values(array_diff($enabled, [$channel]))
            : [...$enabled, $channel];

        settings()->set("notifications.events.{$key}.channels", $enabled);

        $this->dispatch('toast', message: 'Channels updated', type: 'success');
    }

    public function edit(string $key): void
    {
        $cfg = $this->catalog($key);

        if (! $cfg) {
            return;
        }

        $this->editing = $key;
        $this->templates = [];

        foreach (array_keys($cfg['templates'] ?? []) as $channel) {
            $override = settings("notifications.templates.{$key}.{$channel}");
            $default = $cfg['templates'][$channel];

            $this->templates[$channel] = [
                'subject' => $override['subject'] ?? $default['subject'] ?? null,
                'body' => $override['body'] ?? $default['body'] ?? '',
            ];
        }
    }

    public function saveTemplates(Settings $settings): void
    {
        if (! $this->editing) {
            return;
        }

        $updates = [];

        foreach ($this->templates as $channel => $tpl) {
            $updates["notifications.templates.{$this->editing}.{$channel}"] = array_filter([
                'subject' => $tpl['subject'] ?? null,
                'body' => $tpl['body'] ?? '',
            ], fn ($v) => $v !== null);
        }

        $settings->setMany($updates);

        $this->editing = null;
        $this->templates = [];
        $this->dispatch('toast', message: 'Templates saved', type: 'success');
    }

    public function resetTemplates(Settings $settings): void
    {
        if (! $this->editing) {
            return;
        }

        $cfg = $this->catalog($this->editing);

        foreach (array_keys($cfg['templates'] ?? []) as $channel) {
            $settings->set("notifications.templates.{$this->editing}.{$channel}", null);
        }

        $this->edit($this->editing); // reload defaults into the form
        $this->dispatch('toast', message: 'Templates reset to defaults', type: 'success');
    }

    public function cancelEdit(): void
    {
        $this->editing = null;
        $this->templates = [];
    }

    /** @return array<string, mixed>|null */
    protected function catalog(string $key): ?array
    {
        return config('notifications.events', [])[$key] ?? null;
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @return array<int, string>
     */
    protected function enabledChannels(string $key, array $cfg): array
    {
        return settings("notifications.events.{$key}.channels") ?? ($cfg['channels'] ?? []);
    }

    public function render()
    {
        return View::make('notifications::livewire.notification-events');
    }
}
