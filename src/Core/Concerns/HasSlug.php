<?php

namespace Themicly\Shopcrafty\Core\Concerns;

use Illuminate\Support\Str;

/**
 * Auto-generates a unique slug from a source attribute (default: name) when the
 * slug is empty. Set $sluggableFrom on the model to change the source column.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            if (blank($model->slug)) {
                $model->slug = $model->generateUniqueSlug((string) $model->{$model->slugSource()});
            }
        });
    }

    protected function slugSource(): string
    {
        return property_exists($this, 'sluggableFrom') ? $this->sluggableFrom : 'name';
    }

    public function generateUniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->when($this->exists, fn ($q) => $q->whereKeyNot($this->getKey()))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
