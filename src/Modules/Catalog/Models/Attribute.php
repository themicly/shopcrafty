<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Themicly\Shopcrafty\Core\Concerns\HasSlug;

/**
 * @property string $name
 * @property string $type
 */
class Attribute extends Model
{
    use HasSlug;

    protected $table = 'catalog_attributes';

    protected $fillable = ['name', 'slug', 'type', 'position'];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class)->orderBy('position');
    }
}
