<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $attribute_id
 * @property string $value
 * @property string|null $color_code
 * @property int $position
 */
class AttributeValue extends Model
{
    protected $table = 'catalog_attribute_values';

    protected $fillable = ['attribute_id', 'value', 'color_code', 'position'];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }
}
