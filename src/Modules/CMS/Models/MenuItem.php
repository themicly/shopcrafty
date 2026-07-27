<?php

namespace Themicly\Shopcrafty\Modules\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $menu_id
 * @property int|null $parent_id
 * @property string $label
 * @property int $position
 */
class MenuItem extends Model
{
    protected $table = 'cms_menu_items';

    protected $fillable = ['menu_id', 'parent_id', 'label', 'url', 'image', 'position'];

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('position');
    }
}
