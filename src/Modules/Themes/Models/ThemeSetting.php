<?php

namespace Themicly\Shopcrafty\Modules\Themes\Models;

use Illuminate\Database\Eloquent\Model;

class ThemeSetting extends Model
{
    protected $table = 'theme_settings';

    protected $fillable = ['theme_id', 'key', 'value'];

    protected function casts(): array
    {
        return ['value' => 'json'];
    }
}
