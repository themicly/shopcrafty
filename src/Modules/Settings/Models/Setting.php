<?php

namespace Themicly\Shopcrafty\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $group
 * @property string $key
 * @property mixed $value
 */
class Setting extends Model
{
    protected $fillable = ['group', 'key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }
}
