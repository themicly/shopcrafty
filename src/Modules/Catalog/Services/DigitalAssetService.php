<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Catalog\Models\ProductFile;

/**
 * Stores and removes downloadable product assets on a PRIVATE disk
 * (storage/app/private) so they are never web-reachable — buyers only reach
 * them through order-scoped download grants (see DigitalFulfillmentService).
 */
class DigitalAssetService
{
    /** Private disk that holds digital assets (not the public media disk). */
    public const DISK = 'local';

    /** Allowed extensions for a downloadable good. */
    public const ALLOWED = [
        'zip', 'rar', '7z', 'pdf', 'epub', 'mobi', 'mp3', 'wav', 'mp4', 'mov',
        'png', 'jpg', 'jpeg', 'gif', 'svg', 'psd', 'ai', 'csv', 'xlsx', 'docx',
        'pptx', 'txt', 'json', 'ttf', 'otf', 'woff', 'woff2',
    ];

    /** Max upload size in kilobytes (100 MB). */
    public const MAX_KB = 102400;

    /**
     * Content types that must never pass regardless of file extension — the
     * `extensions:` rule below only checks the client-supplied filename, so a
     * script renamed to something innocuous (e.g. a .txt) would otherwise pass.
     */
    public const BLOCKED_CONTENT_TYPES = [
        'text/html', 'application/xhtml+xml',
        'text/x-php', 'application/x-php', 'application/x-httpd-php',
        'text/x-shellscript', 'application/x-sh',
        'application/x-executable', 'application/x-msdownload', 'application/x-dosexec',
    ];

    /** Livewire validation rules for a pending digital upload. */
    public static function rules(): array
    {
        return [
            'file',
            'max:'.self::MAX_KB,
            'extensions:'.implode(',', self::ALLOWED),
            function ($attribute, $value, $fail) {
                if ($value instanceof UploadedFile && in_array($value->getMimeType(), self::BLOCKED_CONTENT_TYPES, true)) {
                    $fail("That file's content doesn't match an allowed type.");
                }
            },
        ];
    }

    public function store(Product $product, UploadedFile $file): ProductFile
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs(
            'digital-products/'.$product->id,
            Str::random(40).($ext ? '.'.$ext : ''),
            self::DISK,
        );

        return $product->files()->create([
            'name' => $file->getClientOriginalName(),
            'disk' => self::DISK,
            'path' => $path,
            'size' => $file->getSize(),
            'sort' => (int) $product->files()->max('sort') + 1,
        ]);
    }

    public function delete(ProductFile $file): void
    {
        Storage::disk($file->disk)->delete($file->path);
        $file->delete();
    }
}
