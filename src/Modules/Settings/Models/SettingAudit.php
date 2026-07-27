<?php

namespace Themicly\Shopcrafty\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

/**
 * Immutable record of a single config value change. Written by
 * {@see Settings::setMany()} whenever a setting's
 * stored value actually changes. `old_value`/`new_value` hold the JSON-encoded
 * representation of the value at the time of the change; `user_name` is a
 * snapshot so the log survives the user being renamed or removed.
 */
class SettingAudit extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['key', 'old_value', 'new_value', 'user_id', 'user_name', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }
}
