<?php

namespace Themicly\Shopcrafty\Core\Support;

use Illuminate\Support\Arr;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;

/**
 * Shared editing behaviour for the typed field schema (TASK #29/#30). Powers both
 * the homepage SectionBuilder and the CMS PageBuilder so the `<x-admin.field>`
 * renderer has one consistent backend for image uploads, repeater rows and the
 * product picker — regardless of whether values live under `form.*` (sections)
 * or `blocks.N.settings.*` (blocks).
 *
 * A "path" is a dotted string rooted at a public Livewire property, e.g.
 * `form.image` or `blocks.2.settings.items.0.question`.
 */
trait InteractsWithTypedFields
{
    use WithFileUploads;

    /** @var array<string, mixed> Temp file uploads keyed by encoded path (dots → "__"). */
    public array $fieldUploads = [];

    /** Current search term for the product picker (one picker interacted with at a time). */
    public string $productQuery = '';

    /** Path of the product field whose picker is currently open. */
    public ?string $productPath = null;

    /** An uploaded image is stored via MediaService and its URL written back to the field path. */
    public function updatedFieldUploads(mixed $value, string $key): void
    {
        $file = $this->fieldUploads[$key] ?? null;

        if (! $file) {
            return;
        }

        $this->validate(['fieldUploads.'.$key => MediaService::imageRules()]);

        $url = app(MediaService::class)->store($file)->url('large');
        $this->setFieldValue($this->decodePath($key), $url);

        unset($this->fieldUploads[$key]);
    }

    /** Clear a single scalar field (e.g. remove an uploaded image). */
    public function clearField(string $path): void
    {
        $this->setFieldValue($path, '');
    }

    /** Append a new row to a repeater array. Flat repeaters use a scalar template (''). */
    public function addFieldRow(string $path, mixed $template = ''): void
    {
        $rows = (array) $this->getFieldValue($path, []);
        $rows[] = $template;
        $this->setFieldValue($path, array_values($rows));
    }

    /** Remove the row at $index from a repeater array. */
    public function removeFieldRow(string $path, int $index): void
    {
        $rows = (array) $this->getFieldValue($path, []);
        unset($rows[$index]);
        $this->setFieldValue($path, array_values($rows));
    }

    /** Open (focus) the product picker for a given field path. */
    public function openProductPicker(string $path): void
    {
        $this->productPath = $path;
    }

    /** Add a product id to a product field (stored as a de-duplicated array of ints). */
    public function pickProduct(string $path, int $id): void
    {
        $ids = array_map('intval', (array) $this->getFieldValue($path, []));

        if (! in_array($id, $ids, true)) {
            $ids[] = $id;
        }

        $this->setFieldValue($path, array_values($ids));
    }

    /** Remove a product id from a product field. */
    public function unpickProduct(string $path, int $id): void
    {
        $ids = array_values(array_filter(
            array_map('intval', (array) $this->getFieldValue($path, [])),
            fn ($existing) => $existing !== $id,
        ));

        $this->setFieldValue($path, $ids);
    }

    /**
     * Product search matches for the currently open picker.
     *
     * @return array<int, array{id:int, name:string}>
     */
    public function typedProductMatches(): array
    {
        if ($this->productPath === null || trim($this->productQuery) === '') {
            return [];
        }

        return Product::query()
            ->where('name', 'like', '%'.trim($this->productQuery).'%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name'])
            ->map(fn (Product $p) => ['id' => (int) $p->id, 'name' => (string) $p->name])
            ->all();
    }

    /** Read a value at a dotted path rooted on a public property. */
    protected function getFieldValue(string $path, mixed $default = null): mixed
    {
        [$prop, $rest] = array_pad(explode('.', $path, 2), 2, null);

        if ($rest === null) {
            return $this->{$prop} ?? $default;
        }

        return Arr::get($this->{$prop}, $rest, $default);
    }

    /** Write a value at a dotted path rooted on a public property. */
    protected function setFieldValue(string $path, mixed $value): void
    {
        [$prop, $rest] = array_pad(explode('.', $path, 2), 2, null);

        if ($rest === null) {
            $this->{$prop} = $value;

            return;
        }

        $target = $this->{$prop};
        Arr::set($target, $rest, $value);
        $this->{$prop} = $target;
    }

    /** Decode an upload key ("form__image") back into a field path ("form.image"). */
    protected function decodePath(string $key): string
    {
        return str_replace('__', '.', $key);
    }
}
