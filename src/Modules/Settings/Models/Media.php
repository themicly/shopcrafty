<?php

namespace Themicly\Shopcrafty\Modules\Settings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $disk
 * @property string $path
 * @property string|null $alt
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['folder_id', 'name', 'disk', 'path', 'mime', 'size', 'width', 'height', 'alt'];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    /** URL for a rendition (thumb|medium|large) with fallback to the original. */
    public function url(?string $size = null): string
    {
        $disk = Storage::disk($this->disk);

        if ($size) {
            $rendition = $this->renditionPath($size);
            if ($disk->exists($rendition)) {
                return $disk->url($rendition);
            }
        }

        return $disk->url($this->path);
    }

    public function renditionPath(string $size): string
    {
        $ext = pathinfo($this->path, PATHINFO_EXTENSION);
        $withoutExt = substr($this->path, 0, -(strlen($ext) + 1));

        return "{$withoutExt}-{$size}.{$ext}";
    }
}
