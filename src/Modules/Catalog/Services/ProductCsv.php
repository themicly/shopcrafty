<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Themicly\Shopcrafty\Modules\Catalog\Models\Brand;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;

/**
 * Import/export the catalogue as CSV. Rows match on SKU (update) or create new;
 * category/brand are resolved by name (created if missing) so a spreadsheet can
 * migrate a whole catalogue in.
 */
class ProductCsv
{
    public const COLUMNS = [
        'sku', 'name', 'description', 'category', 'brand',
        'price', 'compare_at_price', 'cost_price',
        'stock_qty', 'track_inventory', 'low_stock_threshold',
        'status', 'weight', 'seo_title', 'seo_description',
    ];

    protected function decimals(): int
    {
        return (int) settings('localization.currency_decimals', 2);
    }

    /**
     * Parse a money string to minor units. Tolerates thousands separators and a
     * leading currency symbol; returns null for blank OR non-numeric input so the
     * caller can flag the row instead of silently importing a corrupt price (CAT-06).
     */
    protected function toMinor(?string $value): ?int
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $clean = str_replace(',', '', preg_replace('/[^0-9.,\-]/', '', $value) ?? '');

        if ($clean === '' || ! is_numeric($clean)) {
            return null;
        }

        return (int) round(((float) $clean) * (10 ** $this->decimals()));
    }

    /** Neutralise CSV/formula injection: a cell that starts with a formula trigger is quoted (CAT-05). */
    protected function csvSafe(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    protected function toMajor(?int $minor): string
    {
        return $minor === null ? '' : number_format($minor / (10 ** $this->decimals()), $this->decimals(), '.', '');
    }

    public function export(): string
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, self::COLUMNS);

        Product::with(['category', 'brand'])->orderBy('id')->chunk(200, function ($products) use ($out) {
            foreach ($products as $p) {
                fputcsv($out, [
                    $this->csvSafe($p->sku), $this->csvSafe($p->name), $this->csvSafe($p->description),
                    $this->csvSafe($p->category?->name), $this->csvSafe($p->brand?->name),
                    $this->toMajor($p->price), $this->toMajor($p->compare_at_price), $this->toMajor($p->cost_price),
                    $p->stock_qty, $p->track_inventory ? 'yes' : 'no', $p->low_stock_threshold,
                    $p->status, $p->weight, $this->csvSafe($p->seo_title), $this->csvSafe($p->seo_description),
                ]);
            }
        });

        rewind($out);

        return (string) stream_get_contents($out);
    }

    /**
     * @return array{created:int, updated:int, errors:array<int, string>}
     */
    public function import(string $csv): array
    {
        $rows = array_map('str_getcsv', array_filter(explode("\n", str_replace("\r", '', $csv)), fn ($l) => trim($l) !== ''));

        if (empty($rows)) {
            return ['created' => 0, 'updated' => 0, 'errors' => ['The file is empty.']];
        }

        $header = array_map(fn ($h) => Str::snake(trim(strtolower($h))), array_shift($rows));
        $created = $updated = 0;
        $errors = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2; // +1 header, +1 for 1-based

            // Each row is isolated: a bad row is reported and skipped, never aborting
            // the batch or leaving a partial write behind (CAT-01).
            try {
                $data = array_combine($header, array_pad($row, count($header), null)) ?: [];
                $get = fn (string $key, string $default = '') => trim((string) ($data[$key] ?? $default));

                $name = $get('name');

                if ($name === '') {
                    $errors[] = "Row {$line}: missing name — skipped.";

                    continue;
                }

                if (mb_strlen($name) > 190) {
                    $errors[] = "Row {$line}: name is too long (max 190).";

                    continue;
                }

                $rawPrice = $get('price');
                $price = $this->toMinor($rawPrice);

                if ($rawPrice !== '' && $price === null) {
                    $errors[] = "Row {$line}: price is not a valid number.";

                    continue;
                }

                $price ??= 0;

                if ($price < 0) {
                    $errors[] = "Row {$line}: price cannot be negative.";

                    continue;
                }

                // A struck-through "compare at" price must be higher than the price;
                // otherwise drop it rather than store a nonsensical discount (CAT-04).
                $compare = $this->toMinor($get('compare_at_price'));
                if ($compare !== null && $compare <= $price) {
                    $compare = null;
                }

                $status = $get('status');

                $attributes = [
                    'name' => $name,
                    'description' => $get('description') ?: null,
                    'category_id' => $this->resolveId(Category::class, $get('category')),
                    'brand_id' => $this->resolveId(Brand::class, $get('brand')),
                    'price' => $price,
                    'compare_at_price' => $compare,
                    'cost_price' => $this->toMinor($get('cost_price')),
                    'stock_qty' => max(0, (int) $get('stock_qty', '0')),
                    'track_inventory' => $this->toBool($get('track_inventory', 'yes')),
                    'low_stock_threshold' => max(0, (int) $get('low_stock_threshold', '0')),
                    'status' => in_array($status, ['active', 'draft', 'archived'], true) ? $status : 'draft',
                    'weight' => $get('weight') !== '' ? max(0, (int) $get('weight')) : null,
                    'seo_title' => $get('seo_title') ?: null,
                    'seo_description' => $get('seo_description') ?: null,
                ];

                $sku = $get('sku');
                $existing = $sku !== '' ? Product::where('sku', $sku)->first() : null;

                DB::transaction(function () use ($existing, $attributes, $sku, &$created, &$updated) {
                    if ($existing) {
                        $existing->update($attributes);
                        $updated++;
                    } else {
                        $attributes['sku'] = $sku ?: null;
                        Product::create($attributes);
                        $created++;
                    }
                });
            } catch (\Throwable $e) {
                $errors[] = "Row {$line}: ".$e->getMessage();
            }
        }

        return ['created' => $created, 'updated' => $updated, 'errors' => $errors];
    }

    /** @param class-string<Model> $model */
    protected function resolveId(string $model, ?string $name): ?int
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        // Match on the (case-insensitive) name so "T Shirts" and "T-Shirts" don't
        // collapse onto the same slug and silently merge (CAT-19).
        $existing = $model::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();

        if ($existing) {
            return $existing->id;
        }

        return $model::create(['name' => $name, 'slug' => $this->uniqueSlug($model, $name)])->id;
    }

    /** @param class-string<Model> $model */
    protected function uniqueSlug(string $model, string $name): string
    {
        $base = Str::slug($name) ?: 'item';
        $slug = $base;
        $n = 1;

        while ($model::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    protected function toBool(?string $value): bool
    {
        return in_array(strtolower(trim((string) $value)), ['1', 'yes', 'true', 'y'], true);
    }
}
