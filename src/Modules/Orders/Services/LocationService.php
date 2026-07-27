<?php

namespace Themicly\Shopcrafty\Modules\Orders\Services;

use Illuminate\Support\Collection;
use Themicly\Shopcrafty\Modules\Orders\Models\Location;
use Themicly\Shopcrafty\Modules\Orders\Models\ShippingZone;

/**
 * Admin-managed, any-country location hierarchy that powers the cascading
 * shipping-address dropdown and derives the shipping zone from the address.
 */
class LocationService
{
    /** @return array<int, string> the level labels, e.g. ['Division','District','Area'] */
    public function levels(): array
    {
        $levels = settings('shipping.location_levels', ['Division', 'District', 'Area']);

        return array_values(array_filter(array_map('trim', (array) $levels), fn ($l) => $l !== ''));
    }

    public function enabled(): bool
    {
        return ! empty($this->levels()) && Location::query()->exists();
    }

    /** Active children of a parent (or roots when $parentId is null). */
    public function options(?int $parentId): Collection
    {
        return Location::query()
            ->where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('position')->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Validate a chosen path (one id per level) and resolve it.
     *
     * @param  array<int, int|string|null>  $path
     * @return array{valid:bool, names:array<int,string>, zone_id:?int, leaf:?Location}
     */
    public function resolve(array $path): array
    {
        $levels = $this->levels();
        $names = [];
        $parentId = null;
        $leaf = null;

        foreach ($levels as $i => $label) {
            $id = (int) ($path[$i] ?? 0);
            $node = $id ? Location::where('id', $id)->where('parent_id', $parentId)->where('is_active', true)->first() : null;

            if (! $node) {
                return ['valid' => false, 'names' => $names, 'zone_id' => $leaf?->resolveZoneId(), 'leaf' => $leaf];
            }

            $names[$i] = $node->name;
            $parentId = $node->id;
            $leaf = $node;
        }

        return ['valid' => true, 'names' => $names, 'zone_id' => $leaf?->resolveZoneId(), 'leaf' => $leaf];
    }

    /**
     * Bulk import from CSV. Each row is one full path: a column per level, plus an
     * optional trailing "Zone" column mapping the leaf to a shipping zone by name.
     * Idempotent — existing nodes are reused, not duplicated.
     *
     * @return array{created:int, errors:array<int,string>}
     */
    public function importCsv(string $csv): array
    {
        $levels = $this->levels();
        $zones = ShippingZone::pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [mb_strtolower((string) $name) => $id]);

        $rows = array_filter(array_map('str_getcsv', preg_split('/\r\n|\r|\n/', trim($csv)) ?: []), fn ($r) => array_filter($r ?? [], fn ($c) => trim((string) $c) !== ''));
        $created = 0;
        $errors = [];

        // Skip a header row if its first cell matches the first level label.
        if (! empty($rows) && isset($levels[0]) && mb_strtolower(trim((string) ($rows[0][0] ?? ''))) === mb_strtolower($levels[0])) {
            array_shift($rows);
        }

        foreach ($rows as $i => $row) {
            $parentId = null;
            $leaf = null;

            foreach ($levels as $level => $label) {
                $name = trim((string) ($row[$level] ?? ''));

                if ($name === '') {
                    $errors[] = 'Row '.($i + 1).": missing {$label}.";
                    $leaf = null;
                    break;
                }

                $node = Location::firstOrNew(['parent_id' => $parentId, 'name' => $name, 'level' => $level]);

                if (! $node->exists) {
                    $node->is_active = true;
                    $node->position = (int) Location::where('parent_id', $parentId)->max('position') + 1;
                    $node->save();
                    $created++;
                }

                $parentId = $node->id;
                $leaf = $node;
            }

            // Optional trailing zone column maps the leaf to a shipping zone.
            $zoneName = trim((string) ($row[count($levels)] ?? ''));
            if ($leaf && $zoneName !== '' && isset($zones[mb_strtolower($zoneName)])) {
                $leaf->update(['shipping_zone_id' => $zones[mb_strtolower($zoneName)]]);
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }
}
