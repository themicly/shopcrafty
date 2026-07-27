<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Themicly\Shopcrafty\Core\Concerns\HasSlug;

/**
 * @property string $name
 * @property string|null $logo_path
 * @property bool $is_active
 */
class Brand extends Model
{
    use HasSlug;

    protected $table = 'catalog_brands';

    protected $fillable = ['name', 'slug', 'logo', 'logo_path', 'is_active', 'position'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
