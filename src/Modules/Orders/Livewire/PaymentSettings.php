<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Orders\Contracts\PaymentMethod;
use Themicly\Shopcrafty\Modules\Orders\Contracts\RedirectPaymentGateway;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentRegistry;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

/**
 * Payment-method manager: enable/disable, reorder, set test/live mode, and edit
 * per-method credentials. Secret fields are stored encrypted.
 */
class PaymentSettings extends Component
{
    /** @var array<string, bool> */
    public array $enabled = [];

    /** @var array<string, string> */
    public array $mode = [];

    /** @var array<string, array<string, mixed>> */
    public array $config = [];

    /** @var array<int, string> display order of method keys */
    public array $order = [];

    public function mount(PaymentRegistry $registry): void
    {
        $methods = $registry->all();
        $this->order = $methods->keys()->all();

        foreach ($methods as $key => $method) {
            $this->enabled[$key] = $method->isEnabled();
            $this->mode[$key] = (string) settings("payments.{$key}.mode", 'test');
            $this->config[$key] = $this->loadConfig($key, $method);
        }
    }

    /** @return array<string, mixed> */
    protected function loadConfig(string $key, PaymentMethod $method): array
    {
        $stored = settings("payments.{$key}.config") ?? [];

        // Load config, but NEVER hydrate secret values into a public property —
        // they'd serialize into the page HTML. Secrets stay server-side; the
        // input renders blank with a "saved" hint instead (mirrors NotificationGateways).
        $out = [];
        foreach ($method->configFields() as $field) {
            $fk = $field['key'];
            $out[$fk] = ($field['secret'] ?? false) ? '' : ($stored[$fk] ?? '');
        }

        return $out;
    }

    public function moveUp(string $key): void
    {
        $i = array_search($key, $this->order, true);
        if ($i > 0) {
            [$this->order[$i - 1], $this->order[$i]] = [$this->order[$i], $this->order[$i - 1]];
        }
    }

    public function moveDown(string $key): void
    {
        $i = array_search($key, $this->order, true);
        if ($i !== false && $i < count($this->order) - 1) {
            [$this->order[$i + 1], $this->order[$i]] = [$this->order[$i], $this->order[$i + 1]];
        }
    }

    public function save(Settings $settings, PaymentRegistry $registry): void
    {
        $updates = [];

        foreach ($this->order as $position => $key) {
            $method = $registry->find($key);
            if (! $method) {
                continue;
            }

            $updates["payments.{$key}.enabled"] = (bool) ($this->enabled[$key] ?? false);
            $updates["payments.{$key}.position"] = $position;

            if ($method->configFields()) {
                $existing = settings("payments.{$key}.config") ?? [];
                $config = [];

                // Rebuild from the method's schema so stale fields drop out.
                foreach ($method->configFields() as $field) {
                    $fk = $field['key'];
                    $value = $this->config[$key][$fk] ?? '';

                    if ($field['secret'] ?? false) {
                        // Encrypt new secrets; keep the stored one when left blank.
                        if ($value !== null && $value !== '') {
                            $config[$fk] = Crypt::encryptString((string) $value);
                        } elseif (isset($existing[$fk])) {
                            $config[$fk] = $existing[$fk];
                        }
                    } elseif ($value !== null && $value !== '') {
                        $config[$fk] = $value;
                    }
                }

                $updates["payments.{$key}.config"] = $config;
            }

            if ($method instanceof RedirectPaymentGateway) {
                $updates["payments.{$key}.mode"] = in_array($this->mode[$key] ?? 'test', ['test', 'live'], true)
                    ? $this->mode[$key]
                    : 'test';
            }
        }

        $settings->setMany($updates);
        $this->dispatch('toast', message: 'Payment methods saved', type: 'success');
    }

    /** One-line description shown under the method name in its card header. */
    protected function describe(PaymentMethod $method): string
    {
        return match ($method->key()) {
            'cod' => 'Collect payment in cash when the order is delivered.',
            'bank_transfer' => 'The customer pays by manual bank transfer following your instructions.',
            default => $method instanceof RedirectPaymentGateway
                ? 'Online gateway — the customer is redirected to complete payment.'
                : 'Offline payment method.',
        };
    }

    public function render()
    {
        $registry = app(PaymentRegistry::class);

        $methods = collect($this->order)
            ->map(fn (string $key) => $registry->find($key))
            ->filter()
            ->map(function (PaymentMethod $m) {
                $stored = settings("payments.{$m->key()}.config") ?? [];

                return [
                    'key' => $m->key(),
                    'label' => $m->label(),
                    'description' => $this->describe($m),
                    // Flag secret fields that already have a stored value so the
                    // blade can show a "saved" placeholder instead of the secret.
                    'fields' => collect($m->configFields())
                        ->map(fn (array $f) => $f + ['saved' => ($f['secret'] ?? false) && isset($stored[$f['key']])])
                        ->all(),
                    'isGateway' => $m instanceof RedirectPaymentGateway,
                ];
            });

        return View::make('orders::livewire.payment-settings', ['methods' => $methods]);
    }
}
