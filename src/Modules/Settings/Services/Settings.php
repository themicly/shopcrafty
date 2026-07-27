<?php

namespace Themicly\Shopcrafty\Modules\Settings\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Themicly\Shopcrafty\Modules\Settings\Events\SettingsUpdated;
use Themicly\Shopcrafty\Modules\Settings\Models\Setting;
use Themicly\Shopcrafty\Modules\Settings\Models\SettingAudit;

/**
 * Central settings store. All settings load once into a single cached blob
 * (shared-hosting friendly — one query, file cache by default) and invalidate
 * on write. Keys are dotted "group.key"; a bare key defaults to the "general" group.
 */
class Settings
{
    protected const CACHE_KEY = 'shopcrafty.settings';

    protected ?array $items = null;

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return $this->items ??= Cache::rememberForever(self::CACHE_KEY, function () {
            $out = [];

            foreach (Setting::all() as $setting) {
                $out[$setting->group][$setting->key] = $setting->value;
            }

            return $out;
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        [$group, $name] = $this->split($key);

        // Direct access: the key portion is a literal (it may itself contain
        // dots, e.g. "notifications.events.order.placed.channels").
        return $this->all()[$group][$name] ?? $default;
    }

    /** @return array<string, mixed> */
    public function group(string $group): array
    {
        return $this->all()[$group] ?? [];
    }

    public function set(string $key, mixed $value): void
    {
        $this->setMany([$key => $value]);
    }

    /** @param array<string, mixed> $values keyed by dotted "group.key" */
    public function setMany(array $values): void
    {
        $groups = [];
        $audits = [];

        foreach ($values as $key => $value) {
            [$group, $name] = $this->split($key);

            // Compare against the currently stored value; skip no-op writes so the
            // audit log only records real changes. json_encode normalises type
            // quirks (e.g. 0 vs 0.0) to the shape the value is actually stored in.
            $old = $this->all()[$group][$name] ?? null;

            if (json_encode($old) === json_encode($value)) {
                continue;
            }

            Setting::updateOrCreate(['group' => $group, 'key' => $name], ['value' => $value]);
            $groups[$group] = true;
            $audits[] = [$group.'.'.$name, $old, $value];
        }

        // Nothing actually changed — don't flush the cache or emit events.
        if (empty($audits)) {
            return;
        }

        $this->recordAudits($audits);
        $this->flush();

        event(new SettingsUpdated(count($groups) === 1 ? array_key_first($groups) : null));
    }

    /**
     * Persist one audit row per changed key. Resilient to unauthenticated
     * contexts (seeders, console) where there is no acting user.
     *
     * @param  array<int, array{0: string, 1: mixed, 2: mixed}>  $audits
     */
    protected function recordAudits(array $audits): void
    {
        $user = Auth::user();
        $now = now();

        $rows = [];

        foreach ($audits as [$key, $old, $new]) {
            $rows[] = [
                'key' => $key,
                'old_value' => $old === null ? null : json_encode($old),
                'new_value' => $new === null ? null : json_encode($new),
                'user_id' => $user?->getKey(),
                'user_name' => $user?->name,
                'created_at' => $now,
            ];
        }

        SettingAudit::insert($rows);
    }

    public function flush(): void
    {
        $this->items = null;
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array{0: string, 1: string} */
    protected function split(string $key): array
    {
        return str_contains($key, '.')
            ? explode('.', $key, 2)
            : ['general', $key];
    }
}
