<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Ai\AiNotConfiguredException;
use Themicly\Shopcrafty\Ai\AiRequestException;
use Themicly\Shopcrafty\Ai\AiService;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Modules\Settings\Models\Media;
use Themicly\Shopcrafty\Modules\Settings\Models\MediaFolder;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;

class MediaManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public const PER_PAGE = 24;

    public ?int $currentFolder = null;

    public string $search = '';

    /** Toggles the inline "new folder" form in the toolbar. */
    public bool $creatingFolder = false;

    public string $newFolderName = '';

    /** Folder currently being renamed inline (null = none). */
    public ?int $renamingFolderId = null;

    public string $renameFolderName = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $photos = [];

    /** @var array<int, int|string> */
    public array $selected = [];

    /** Media item shown in the details slide-over (null = closed). */
    public ?int $detailId = null;

    public string $altText = '';

    /** Route middleware doesn't re-run on Livewire action requests — check per mutating method too. */
    protected function guard(): void
    {
        abort_unless(Auth::user()?->can('manage-content') ?? false, 403);
    }

    public function updatedPhotos(MediaService $media): void
    {
        $this->guard();

        $this->validate([
            'photos.*' => MediaService::imageRules(),
        ]);

        foreach ($this->photos as $photo) {
            $media->store($photo, $this->currentFolder);
        }

        $count = count($this->photos);
        $this->photos = [];
        $this->dispatch('toast', message: $count.' '.($count === 1 ? 'image' : 'images').' uploaded', type: 'success');
    }

    /* ── Folders ─────────────────────────────────────────────────── */

    public function createFolder(): void
    {
        $this->guard();

        $this->validate(['newFolderName' => ['required', 'string', 'max:120']]);

        MediaFolder::create([
            'parent_id' => $this->currentFolder,
            'name' => $this->newFolderName,
        ]);

        $this->newFolderName = '';
        $this->creatingFolder = false;
        $this->dispatch('toast', message: 'Folder created', type: 'success');
    }

    public function cancelCreateFolder(): void
    {
        $this->creatingFolder = false;
        $this->newFolderName = '';
        $this->resetErrorBag('newFolderName');
    }

    public function startRename(int $id): void
    {
        $folder = MediaFolder::findOrFail($id);
        $this->renamingFolderId = $folder->id;
        $this->renameFolderName = $folder->name;
        $this->resetErrorBag('renameFolderName');
    }

    public function renameFolder(): void
    {
        $this->guard();

        if (! $this->renamingFolderId) {
            return;
        }

        $this->validate(['renameFolderName' => ['required', 'string', 'max:120']]);

        MediaFolder::whereKey($this->renamingFolderId)->update(['name' => $this->renameFolderName]);

        $this->renamingFolderId = null;
        $this->renameFolderName = '';
        $this->dispatch('toast', message: 'Folder renamed', type: 'success');
    }

    public function cancelRename(): void
    {
        $this->renamingFolderId = null;
        $this->renameFolderName = '';
        $this->resetErrorBag('renameFolderName');
    }

    /**
     * Delete a folder. Its files and subfolders are NOT deleted — they move to
     * the Library root (same as the schema's nullOnDelete, made explicit here).
     */
    public function deleteFolder(int $id): void
    {
        $this->guard();

        $folder = MediaFolder::findOrFail($id);

        Media::where('folder_id', $folder->id)->update(['folder_id' => null]);
        MediaFolder::where('parent_id', $folder->id)->update(['parent_id' => null]);
        $folder->delete();

        if ($this->currentFolder === $folder->id) {
            $this->openFolder(null);
        }
        if ($this->renamingFolderId === $folder->id) {
            $this->cancelRename();
        }

        $this->dispatch('toast', message: 'Folder deleted — its contents moved to the Library root', type: 'success');
    }

    public function openFolder(?int $id): void
    {
        $this->currentFolder = $id;
        $this->selected = [];
        $this->detailId = null;
        $this->search = '';
        $this->cancelRename();
        $this->resetPage();
    }

    /* ── Selection & bulk actions ────────────────────────────────── */

    /** Select every file on the current page — or clear them if all are already selected. */
    public function toggleSelectPage(): void
    {
        $pageIds = $this->mediaQuery()->paginate(self::PER_PAGE)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $selected = array_map('intval', $this->selected);

        if ($pageIds !== [] && array_diff($pageIds, $selected) === []) {
            $this->selected = array_values(array_diff($selected, $pageIds));
        } else {
            $this->selected = array_values(array_unique(array_merge($selected, $pageIds)));
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function deleteSelected(MediaService $service): void
    {
        $this->guard();

        $ids = array_map('intval', $this->selected);

        Media::whereIn('id', $ids)->get()->each(fn ($m) => $service->delete($m));

        if ($this->detailId !== null && in_array($this->detailId, $ids, true)) {
            $this->closeDetails();
        }

        $count = count($ids);
        $this->selected = [];
        $this->dispatch('toast', message: "{$count} ".($count === 1 ? 'file' : 'files').' deleted', type: 'success');
    }

    /** Move every selected file into a folder (null = Library root). */
    public function moveSelected(?int $folderId = null): void
    {
        $this->guard();

        if ($folderId !== null && ! MediaFolder::whereKey($folderId)->exists()) {
            return;
        }

        $ids = array_map('intval', $this->selected);
        $count = Media::whereIn('id', $ids)->update(['folder_id' => $folderId]);

        $this->selected = [];
        $destination = $folderId ? MediaFolder::find($folderId)?->name : 'the Library root';
        $this->dispatch('toast', message: "Moved {$count} ".($count === 1 ? 'file' : 'files')." to {$destination}", type: 'success');
    }

    /* ── Details panel ───────────────────────────────────────────── */

    public function showDetails(int $id): void
    {
        $media = Media::findOrFail($id);
        $this->detailId = $media->id;
        $this->altText = (string) $media->alt;
    }

    public function closeDetails(): void
    {
        $this->detailId = null;
        $this->altText = '';
    }

    public function saveAlt(): void
    {
        $this->guard();

        if (! $this->detailId) {
            return;
        }

        $this->validate(['altText' => ['nullable', 'string', 'max:255']]);

        Media::whereKey($this->detailId)->update(['alt' => $this->altText]);
        $this->dispatch('toast', message: 'Alt text saved', type: 'success');
    }

    /** Move the file open in the details panel ('' = Library root). */
    public function moveDetail(string $folderId): void
    {
        $this->guard();

        if (! $this->detailId) {
            return;
        }

        $target = $folderId === '' ? null : (int) $folderId;

        if ($target !== null && ! MediaFolder::whereKey($target)->exists()) {
            return;
        }

        Media::whereKey($this->detailId)->update(['folder_id' => $target]);
        $destination = $target ? MediaFolder::find($target)?->name : 'the Library root';
        $this->dispatch('toast', message: "Moved to {$destination}", type: 'success');
    }

    public function deleteMedia(int $id, MediaService $service): void
    {
        $this->guard();

        $media = Media::findOrFail($id);
        $service->delete($media);

        $this->selected = array_values(array_filter(
            $this->selected,
            fn ($selectedId) => (int) $selectedId !== $id,
        ));

        if ($this->detailId === $id) {
            $this->closeDetails();
        }

        $this->dispatch('toast', message: 'File deleted', type: 'success');
    }

    public function getAiEnabledProperty(): bool
    {
        // Show the button only when the alt-text AI feature itself is on.
        return app(AddonRegistry::class)->installed('ai') && app(AiService::class)->featureEnabled('alt_text');
    }

    /** Describe the image with AI and fill the alt-text field for review. */
    public function generateAlt(): void
    {
        $this->guard();

        if (! app(AddonRegistry::class)->installed('ai')) {
            abort(404);
        }

        $ai = app(AiService::class);

        if (! $this->detailId) {
            return;
        }

        $media = Media::findOrFail($this->detailId);

        try {
            $alt = $ai->describeImage(
                $media->url('medium'),
                'Write concise, descriptive alt text for this product image for accessibility and SEO. Max 120 characters. Return only the alt text, with no surrounding quotes.',
            );
            $this->altText = mb_substr(trim($alt, " \"'"), 0, 200);
            $this->dispatch('toast', message: 'Alt text drafted — review and save', type: 'success');
        } catch (AiNotConfiguredException $e) {
            $this->dispatch('toast', message: 'Turn on AI in Settings → AI first', type: 'danger');
        } catch (AiRequestException $e) {
            $this->dispatch('toast', message: 'Could not generate alt text: '.$e->getMessage(), type: 'danger');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Could not generate alt text', type: 'danger');
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /* ── Queries & render ────────────────────────────────────────── */

    protected function mediaQuery(): Builder
    {
        return Media::where('folder_id', $this->currentFolder)
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->latest();
    }

    /**
     * Every folder flattened depth-first, for "Move to…" pickers.
     *
     * @return array<int, array{id: int, name: string, depth: int}>
     */
    protected function folderOptions(): array
    {
        $byParent = MediaFolder::orderBy('name')
            ->get(['id', 'parent_id', 'name'])
            ->groupBy(fn ($f) => $f->parent_id ?? 0);

        $options = [];
        $walk = function (int $parentId, int $depth) use (&$walk, $byParent, &$options) {
            foreach ($byParent->get($parentId, collect()) as $f) {
                $options[] = ['id' => $f->id, 'name' => $f->name, 'depth' => $depth];
                if ($depth < 10) {
                    $walk($f->id, $depth + 1);
                }
            }
        };
        $walk(0, 0);

        return $options;
    }

    /** @return array<int, MediaFolder> the current folder's ancestor chain, root-first (including itself). */
    protected function breadcrumbs(?MediaFolder $folder): array
    {
        $crumbs = [];
        $guard = 0;

        while ($folder && $guard++ < 20) {
            array_unshift($crumbs, $folder);
            $folder = $folder->parent;
        }

        return $crumbs;
    }

    public function render()
    {
        $folder = $this->currentFolder ? MediaFolder::find($this->currentFolder) : null;

        return View::make('settings::livewire.media-manager', [
            'folder' => $folder,
            'breadcrumbs' => $this->breadcrumbs($folder),
            'folders' => MediaFolder::where('parent_id', $this->currentFolder)->withCount('media')->orderBy('name')->get(),
            'folderOptions' => $this->folderOptions(),
            'items' => $this->mediaQuery()->paginate(self::PER_PAGE),
            'detail' => $this->detailId ? Media::find($this->detailId) : null,
        ]);
    }
}
