<?php

namespace Themicly\Shopcrafty\Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $name
 * @property int $position
 * @property bool $is_active
 */
class Location extends Model
{
    protected $table = 'locations';

    protected $fillable = ['parent_id', 'name', 'level', 'shipping_zone_id', 'position', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'level' => 'integer'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('name');
    }

    public function shippingZone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class);
    }

    /** The node's own zone, or the nearest ancestor's (a country/region default). */
    public function resolveZoneId(): ?int
    {
        $node = $this;

        while ($node) {
            if ($node->shipping_zone_id) {
                return (int) $node->shipping_zone_id;
            }
            $node = $node->parent;
        }

        return null;
    }
}
