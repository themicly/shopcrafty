<?php

namespace Themicly\Shopcrafty\Modules\Settings\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Themicly\Shopcrafty\Core\Support\DemoMode;
use Themicly\Shopcrafty\Core\Support\DemoModeException;
use Themicly\Shopcrafty\Modules\Settings\Models\Media;

class MediaService
{
    public const RENDITIONS = ['thumb' => 300, 'medium' => 800, 'large' => 1600];

    /** Raster formats we accept — deliberately excludes SVG (script-injection vector). */
    public const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /** Canonical extension per accepted MIME — the ONLY source of the on-disk extension. */
    public const MIME_EXT = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    public const MAX_KB = 8192;

    /**
     * Validation rules for an uploaded image. Centralized so every uploader
     * enforces the same allowlist (no SVG) and size cap.
     *
     * @return array<int, string>
     */
    public static function imageRules(): array
    {
        return [
            'image',
            'mimetypes:'.implode(',', self::ALLOWED_MIMES),
            'max:'.self::MAX_KB,
            // Caps decode/re-encode memory use — a small file can still claim huge pixel dimensions.
            'dimensions:max_width=8000,max_height=8000',
        ];
    }

    /**
     * Store an uploaded file and generate resized renditions (storefront never
     * serves originals). Returns the created Media record.
     */
    public function store(UploadedFile $file, ?int $folderId = null): Media
    {
        // The global demo-mode guard only blocks the Media::create() row below —
        // by then the original + 3 resized renditions are already written to the
        // public disk. Fail before any of that so a public demo can't be used to
        // fill disk space for free.
        if (DemoMode::blocksAction()) {
            throw new DemoModeException('Uploading media is disabled in this demo.');
        }

        // Defense in depth: never trust the caller's validation alone. The on-disk
        // extension is derived ONLY from the sniffed MIME — never the client-supplied
        // name — so a `shell.php` polyglot can't land as executable (SET-01).
        $mime = (string) $file->getMimeType();
        if (! isset(self::MIME_EXT[$mime])) {
            throw new \InvalidArgumentException('Unsupported image type.');
        }

        $disk = 'public';
        $dir = 'media/'.now()->format('Y/m');
        $ext = self::MIME_EXT[$mime];
        $base = (string) Str::ulid();

        $path = $file->storeAs($dir, "{$base}.{$ext}", $disk);
        $full = Storage::disk($disk)->path($path);

        $manager = new ImageManager(new Driver);

        try {
            // Re-encode the stored original from decoded pixels — strips any embedded
            // scripts, EXIF, or trailing payload smuggled past the mime check.
            $original = $manager->decodePath($full);
            $original->save($full);

            $width = $original->width();
            $height = $original->height();

            foreach (self::RENDITIONS as $name => $maxWidth) {
                $rendition = $manager->decodePath($full)->scaleDown(width: $maxWidth);
                $rendition->save(Storage::disk($disk)->path("{$dir}/{$base}-{$name}.{$ext}"));
            }
        } catch (\Throwable $e) {
            // A file that can't be decoded/re-encoded isn't a real image — remove
            // every artefact so no raw payload is left publicly served.
            $disk = Storage::disk($disk);
            $disk->delete($path);
            foreach (array_keys(self::RENDITIONS) as $name) {
                $disk->delete("{$dir}/{$base}-{$name}.{$ext}");
            }
            throw new \InvalidArgumentException('That image could not be processed.');
        }

        return Media::create([
            'folder_id' => $folderId,
            'name' => $file->getClientOriginalName(),
            'disk' => $disk,
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
        ]);
    }

    public function delete(Media $media): void
    {
        $disk = Storage::disk($media->disk);

        foreach (array_keys(self::RENDITIONS) as $size) {
            $disk->delete($media->renditionPath($size));
        }

        $disk->delete($media->path);
        $media->delete();
    }
}
