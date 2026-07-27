<?php

namespace Themicly\Shopcrafty\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Themicly\Shopcrafty\Core\Concerns\HasSlug;

/**
 * @property string $title
 * @property string $slug
 * @property string $type
 * @property array $blocks
 * @property string $status
 * @property string $template
 * @property string|null $seo_title
 * @property string|null $seo_description
 */
class Page extends Model
{
    use HasSlug;

    protected $table = 'cms_pages';

    protected $fillable = [
        'title', 'slug', 'type', 'blocks', 'status', 'template',
        'seo_title', 'seo_description', 'published_at',
    ];

    protected $sluggableFrom = 'title';

    protected function casts(): array
    {
        return ['blocks' => 'array', 'published_at' => 'datetime'];
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published');
    }
}
