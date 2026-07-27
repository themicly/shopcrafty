<?php

namespace Themicly\Shopcrafty\Modules\Themes\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $slug
 * @property string $name
 * @property bool $is_active
 */
class Theme extends Model
{
    protected $table = 'themes';

    protected $fillable = ['slug', 'name', 'version', 'author', 'is_active', 'installed_at'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'installed_at' => 'datetime'];
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ThemeSetting::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(ThemeSection::class)->orderBy('position');
    }
}
