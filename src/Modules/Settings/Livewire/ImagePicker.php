<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Modules\Settings\Models\Media;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;

/**
 * Unified image control (media gallery · upload · paste URL/path).
 *
 * Binds a single URL/path STRING to the parent via `wire:model` (Modelable), so a
 * caller adopts it with no component-logic change — the parent property still holds
 * a URL/path exactly as the old "upload + paste URL" fields did.
 *
 *   <livewire:settings.image-picker wire:model="image_path" label="Feature image" />
 *
 * Three modes live inside a self-contained dialog opened from the inline "Choose
 * image" / "Change" affordance:
 *   1. Gallery — paginated, searchable grid of existing Media; click selects url().
 *   2. Upload  — dropzone (WithFileUploads + MediaService::store), stores + selects.
 *   3. Path/URL — paste an external URL or path.
 */
class ImagePicker extends Component
{
    use WithFileUploads;

    private const PER_PAGE = 18;

    /** The bound value — a URL or path string shared two-way with the parent. */
    #[Modelable]
    public string $value = '';

    public ?string $label = null;

    public ?string $hint = null;

    public bool $required = false;

    public bool $disabled = false;

    public string $disabledMessage = 'Save first to enable images.';

    /** Rendition whose URL is stored when a gallery item is picked or a file uploaded. */
    public string $rendition = 'medium';

    /** Optional media folder new uploads land in. */
    public ?int $folderId = null;

    /* ── Dialog state ─────────────────────────────────────────────── */

    public bool $open = false;

    /** Active mode: gallery | upload | url. */
    public string $mode = 'gallery';

    public string $search = '';

    public int $galleryPage = 1;

    /** Draft text for the "Path / URL" mode (applied on confirm, not keystroke). */
    public string $urlInput = '';

    /** A pending file selected in the upload dropzone. */
    public $upload = null;

    public function openPicker(): void
    {
        if ($this->disabled) {
            return;
        }

        $this->urlInput = $this->value;
        $this->search = '';
        $this->galleryPage = 1;
        $this->mode = 'gallery';
        $this->resetErrorBag();
        $this->open = true;
    }

    public function closePicker(): void
    {
        $this->open = false;
        $this->upload = null;
        $this->resetErrorBag();
    }

    public function updatedSearch(): void
    {
        $this->galleryPage = 1;
    }

    public function nextPage(bool $hasMore): void
    {
        if ($hasMore) {
            $this->galleryPage++;
        }
    }

    public function prevPage(): void
    {
        $this->galleryPage = max(1, $this->galleryPage - 1);
    }

    /** Pick an existing media item — store its rendition URL and close. */
    public function selectMedia(int $id): void
    {
        $media = Media::find($id);

        if ($media) {
            $this->value = $media->url($this->rendition);
            $this->closePicker();
        }
    }

    /** Store the dropped/browsed file via MediaService, then select it. */
    public function updatedUpload(MediaService $media): void
    {
        $this->validate(['upload' => MediaService::imageRules()]);

        try {
            $stored = $media->store($this->upload, $this->folderId);
        } catch (\InvalidArgumentException $e) {
            $this->upload = null;
            $this->addError('upload', $e->getMessage());

            return;
        }

        $this->value = $stored->url($this->rendition);
        $this->closePicker();
    }

    /** Apply the pasted path/URL. */
    public function applyUrl(): void
    {
        $this->validate(['urlInput' => ['nullable', 'string', 'max:2048']]);

        $this->value = trim($this->urlInput);
        $this->closePicker();
    }

    /** Clear the bound value (does not delete the underlying media). */
    public function remove(): void
    {
        $this->value = '';
    }

    public function render(): View
    {
        $items = collect();
        $hasMore = false;

        if ($this->open && $this->mode === 'gallery') {
            $query = Media::query()
                ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
                ->latest();

            $items = $query->forPage($this->galleryPage, self::PER_PAGE)->get();
            $hasMore = $items->count() === self::PER_PAGE
                && $query->count() > $this->galleryPage * self::PER_PAGE;
        }

        return \Illuminate\Support\Facades\View::make('settings::livewire.image-picker', [
            'items' => $items,
            'hasMore' => $hasMore,
        ]);
    }
}
