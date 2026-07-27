<?php

namespace Themicly\Shopcrafty\Modules\Themes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $image_large
 * @property string|null $image_small
 * @property string|null $link_url
 * @property string|null $link_label
 * @property string $placement
 * @property int $sort
 * @property bool $is_active
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 */
class Banner extends Model
{
    protected $table = 'banners';

    protected $fillable = [
        'title', 'subtitle', 'image_large', 'image_small',
        'link_url', 'link_label', 'placement', 'theme_id', 'sort', 'is_active',
        'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function scopeLive(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(fn ($w) => $w->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($w) => $w->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopePlacement(Builder $q, string $placement): Builder
    {
        return $q->where('placement', $placement);
    }

    public function mobileImage(): string
    {
        return $this->image_small ?: $this->image_large;
    }
}
