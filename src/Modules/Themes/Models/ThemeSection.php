<?php

namespace Themicly\Shopcrafty\Modules\Themes\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $theme_id
 * @property string $page
 * @property string $section_key
 * @property int $position
 * @property bool $is_enabled
 * @property array|null $settings
 */
class ThemeSection extends Model
{
    protected $table = 'theme_sections';

    protected $fillable = ['theme_id', 'page', 'section_key', 'position', 'is_enabled', 'settings'];

    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'settings' => 'array'];
    }
}
