<?php

namespace Themicly\Shopcrafty\Modules\CMS\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\CMS\Models\Menu;
use Themicly\Shopcrafty\Modules\CMS\Models\MenuItem;
use Themicly\Shopcrafty\Modules\CMS\Models\Page;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;

/**
 * Builds the header/footer navigation menus. Items link to a page, a category,
 * or a custom URL; the storefront renders whichever menu matches its location.
 */
class MenuBuilder extends Component
{
    use WithFileUploads;

    public string $location = 'header';

    public string $label = '';

    public string $linkType = 'custom';

    public string $url = '';

    public ?int $targetId = null;

    /** Optional parent for the new item — enables one level of dropdown/mega submenus. */
    public ?int $parentId = null;

    /** Optional tile image (mega-menu). Stored as a media rendition URL. */
    public string $image = '';

    /** Transient upload for the new item's tile image. */
    public $upload = null;

    protected function menu(): Menu
    {
        return Menu::firstOrCreate(['location' => $this->location], ['name' => ucfirst($this->location).' menu']);
    }

    public function updatedLocation(): void
    {
        $this->reset('label', 'linkType', 'url', 'targetId', 'parentId', 'image', 'upload');
    }

    /** Upload and store a tile image, keeping only its rendition URL. */
    public function updatedUpload(MediaService $media): void
    {
        $this->validate(['upload' => MediaService::imageRules()]);
        $this->image = $media->store($this->upload)->url('medium');
        $this->reset('upload');
    }

    public function clearImage(): void
    {
        $this->reset('image', 'upload');
    }

    public function addItem(): void
    {
        $data = $this->validate([
            'linkType' => ['in:custom,page,category'],
            'label' => ['nullable', 'string', 'max:80'],
            'url' => ['nullable', 'string', 'max:255', 'required_if:linkType,custom'],
            'targetId' => ['nullable', 'integer', 'required_if:linkType,page', 'required_if:linkType,category'],
            'parentId' => ['nullable', 'integer'],
            'image' => ['nullable', 'string', 'max:255'],
        ]);

        [$url, $label] = match ($data['linkType']) {
            'page' => $this->fromPage((int) $data['targetId']),
            'category' => $this->fromCategory((int) $data['targetId']),
            default => [$this->safeUrl((string) $data['url']), $data['label'] ?: $data['url']],
        };

        // Only allow nesting one level deep (a top-level item of this menu).
        $parentId = null;
        if ($data['parentId']) {
            $parentId = MenuItem::where('menu_id', $this->menu()->id)
                ->whereNull('parent_id')->whereKey($data['parentId'])->value('id');
        }

        $this->menu()->items()->create([
            'parent_id' => $parentId,
            'label' => $data['label'] ?: $label,
            'url' => $url ?: '#',
            'image' => $data['image'] ?: null,
            'position' => (int) MenuItem::where('menu_id', $this->menu()->id)
                ->where('parent_id', $parentId)->max('position') + 1,
        ]);

        $this->reset('label', 'url', 'targetId', 'image', 'upload');
    }

    /** Allow only safe link schemes; anything else (e.g. javascript:) becomes "#" (CMS-02). */
    protected function safeUrl(string $url): string
    {
        $url = trim($url);

        return preg_match('#^(https?://|/|\#|mailto:|tel:)#i', $url) ? $url : '#';
    }

    public function removeItem(int $id): void
    {
        // Removing a parent takes its submenu with it.
        MenuItem::where('menu_id', $this->menu()->id)->where('parent_id', $id)->delete();
        MenuItem::where('menu_id', $this->menu()->id)->whereKey($id)->delete();
    }

    public function move(int $id, string $dir): void
    {
        $item = MenuItem::where('menu_id', $this->menu()->id)->findOrFail($id);

        // Reorder only among siblings (items sharing the same parent).
        $swap = MenuItem::where('menu_id', $item->menu_id)
            ->where('parent_id', $item->parent_id)
            ->when($dir === 'up',
                fn ($q) => $q->where('position', '<', $item->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $item->position)->orderBy('position'),
            )->first();

        if ($swap) {
            [$item->position, $swap->position] = [$swap->position, $item->position];
            $item->save();
            $swap->save();
        }
    }

    /** @return array{0:string,1:string} */
    protected function fromPage(int $id): array
    {
        $page = Page::find($id);

        return $page ? [url('/pages/'.$page->slug), $page->title] : ['#', 'Page'];
    }

    /** @return array{0:string,1:string} */
    protected function fromCategory(int $id): array
    {
        $category = Category::find($id);

        return $category ? [url('/category/'.$category->slug), $category->name] : ['#', 'Category'];
    }

    public function render()
    {
        $menu = $this->menu();

        $items = $menu->items()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('position')
            ->get();

        return View::make('cms::livewire.menu-builder', [
            'items' => $items,
            'parents' => $items, // top-level items eligible to hold a submenu
            'pages' => Page::where('type', 'page')->orderBy('title')->get(['id', 'title']),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
