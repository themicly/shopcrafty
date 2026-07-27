<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Themicly\Shopcrafty\Core\Concerns\HasSlug;

/**
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $icon
 * @property string|null $image_path
 * @property int|null $parent_id
 * @property int $position
 * @property bool $is_active
 * @property string|null $seo_title
 * @property string|null $seo_description
 */
class Category extends Model
{
    use HasSlug;

    protected $table = 'catalog_categories';

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'icon', 'image', 'image_path',
        'position', 'is_active', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('name');
    }
}
