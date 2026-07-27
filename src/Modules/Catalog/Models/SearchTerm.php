<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A normalized storefront search term with its running popularity counter.
 * Rows are written by SearchTermRecorder (upsert) and read by the admin
 * "Search terms" report.
 */
class SearchTerm extends Model
{
    protected $table = 'catalog_search_terms';

    protected $fillable = ['term', 'count', 'last_searched_at'];

    protected function casts(): array
    {
        return [
            'count' => 'integer',
            'last_searched_at' => 'datetime',
        ];
    }
}
