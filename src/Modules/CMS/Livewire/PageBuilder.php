<?php

namespace Themicly\Shopcrafty\Modules\CMS\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Core\Support\InteractsWithTypedFields;
use Themicly\Shopcrafty\Modules\CMS\Models\Page;

class PageBuilder extends Component
{
    use InteractsWithTypedFields;

    public ?int $pageId = null;

    public string $title = '';

    public string $slug = '';

    public string $type = 'page';

    public string $status = 'draft';

    public string $template = 'default';

    public string $seoTitle = '';

    public string $seoDescription = '';

    /** @var array<int, array{type:string, settings:array}> */
    public array $blocks = [];

    /**
     * Block catalog: type => [label, default settings, typed field schema].
     * The `fields` drive the shared <x-admin.field> editor; each field's default
     * stays in sync with `defaults`. Complex controls (image/product/repeater)
     * read and write the canonical stored shape directly — no text-encoding hacks.
     */
    public const CATALOG = [
        'hero' => [
            'label' => 'Hero',
            'description' => 'Big heading with an optional button',
            'defaults' => ['heading' => 'Heading', 'subheading' => '', 'cta_label' => '', 'cta_url' => '/shop'],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                ['key' => 'subheading', 'type' => 'textarea', 'label' => 'Subheading', 'rows' => 2],
                ['key' => 'cta_label', 'type' => 'text', 'label' => 'Button label'],
                ['key' => 'cta_url', 'type' => 'url', 'label' => 'Button URL'],
            ],
        ],
        'text' => [
            'label' => 'Text',
            'description' => 'A paragraph of rich copy',
            'defaults' => ['body' => ''],
            'fields' => [
                ['key' => 'body', 'type' => 'textarea', 'label' => 'Text', 'rows' => 5],
            ],
        ],
        'image' => [
            'label' => 'Image',
            'description' => 'One image with alt text and caption',
            'defaults' => ['url' => '', 'alt' => '', 'caption' => ''],
            'fields' => [
                ['key' => 'url', 'type' => 'image', 'label' => 'Image'],
                ['key' => 'alt', 'type' => 'text', 'label' => 'Alt text'],
                ['key' => 'caption', 'type' => 'text', 'label' => 'Caption'],
            ],
        ],
        'gallery' => [
            'label' => 'Gallery',
            'description' => 'A grid of images',
            'defaults' => ['images' => []],
            'fields' => [
                ['key' => 'images', 'type' => 'repeater', 'label' => 'Images', 'itemType' => 'image'],
            ],
        ],
        'video' => [
            'label' => 'Video',
            'description' => 'Embedded YouTube, Vimeo or mp4',
            'defaults' => ['url' => ''],
            'fields' => [
                ['key' => 'url', 'type' => 'url', 'label' => 'YouTube / Vimeo URL'],
            ],
        ],
        'faq' => [
            'label' => 'FAQ',
            'description' => 'Expandable question & answer rows',
            'defaults' => ['items' => []],
            'fields' => [
                ['key' => 'items', 'type' => 'repeater', 'label' => 'FAQ items', 'subfields' => [
                    ['key' => 'question', 'type' => 'text', 'label' => 'Question'],
                    ['key' => 'answer', 'type' => 'textarea', 'label' => 'Answer', 'rows' => 2],
                ]],
            ],
        ],
        'cta' => [
            'label' => 'Call to action',
            'description' => 'A call-to-action band',
            'defaults' => ['heading' => '', 'button_label' => 'Shop now', 'button_url' => '/shop'],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                ['key' => 'button_label', 'type' => 'text', 'label' => 'Button label'],
                ['key' => 'button_url', 'type' => 'url', 'label' => 'Button URL'],
            ],
        ],
        'products' => [
            'label' => 'Products',
            'description' => 'A row of products from your catalog',
            'defaults' => ['heading' => 'Featured', 'product_ids' => []],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading'],
                ['key' => 'product_ids', 'type' => 'product', 'label' => 'Products'],
            ],
        ],
        'testimonials' => [
            'label' => 'Testimonials',
            'description' => 'Customer quotes',
            'defaults' => ['items' => []],
            'fields' => [
                ['key' => 'items', 'type' => 'repeater', 'label' => 'Testimonials', 'subfields' => [
                    ['key' => 'quote', 'type' => 'textarea', 'label' => 'Quote', 'rows' => 2],
                    ['key' => 'name', 'type' => 'text', 'label' => 'Name'],
                ]],
            ],
        ],
    ];

    public function mount(?int $page = null): void
    {
        if ($page) {
            $model = Page::findOrFail($page);
            $this->pageId = $model->id;
            $this->title = $model->title;
            $this->slug = (string) $model->slug;
            $this->type = $model->type;
            $this->status = $model->status;
            $this->template = $model->template;
            $this->seoTitle = (string) $model->seo_title;
            $this->seoDescription = (string) $model->seo_description;
            $this->blocks = array_values($model->blocks ?? []);
        }
    }

    public function addBlock(string $type): void
    {
        if (! isset(self::CATALOG[$type])) {
            return;
        }

        $this->blocks[] = ['type' => $type, 'settings' => self::CATALOG[$type]['defaults']];
        $this->syncDraft();
        $this->dispatch('toast', message: (self::CATALOG[$type]['label'] ?? 'Block').' added', type: 'success');
    }

    /**
     * Add a catalog block dropped from the sidebar palette onto an existing block:
     * it lands directly BEFORE that block's current index. Complements addBlock(),
     * which appends to the end (used when dropped on the trailing drop zone).
     */
    public function insertBefore(string $type, int $beforeIndex): void
    {
        if (! isset(self::CATALOG[$type]) || ! isset($this->blocks[$beforeIndex])) {
            return;
        }

        array_splice($this->blocks, $beforeIndex, 0, [
            ['type' => $type, 'settings' => self::CATALOG[$type]['defaults']],
        ]);
        $this->syncDraft();
        $this->dispatch('toast', message: (self::CATALOG[$type]['label'] ?? 'Block').' added', type: 'success');
    }

    public function removeBlock(int $index): void
    {
        unset($this->blocks[$index]);
        $this->blocks = array_values($this->blocks);
        $this->syncDraft();
    }

    public function moveBlock(int $index, string $dir): void
    {
        $swap = $dir === 'up' ? $index - 1 : $index + 1;
        if (isset($this->blocks[$swap])) {
            [$this->blocks[$index], $this->blocks[$swap]] = [$this->blocks[$swap], $this->blocks[$index]];
            $this->syncDraft();
        }
    }

    /** Field edits (wire:model) — keep the live preview in sync. */
    public function updated(): void
    {
        $this->syncDraft();
    }

    /**
     * Stash the current (unsaved) page into the session so the storefront preview
     * iframe can render it. Only meaningful once the page exists (has a URL).
     */
    protected function syncDraft(): void
    {
        if (! $this->pageId) {
            return;
        }

        session(['cms_page_draft.'.$this->pageId => [
            'blocks' => $this->cleanBlocks($this->blocks),
            'title' => $this->title,
        ]]);

        $this->dispatch('preview-updated');
    }

    /**
     * Reorder blocks from drag-and-drop. $order is the new sequence of the current
     * block indices.
     *
     * @param  array<int, int|string>  $order
     */
    public function reorderBlocks(array $order): void
    {
        $reordered = [];
        foreach ($order as $i) {
            if (isset($this->blocks[(int) $i])) {
                $reordered[] = $this->blocks[(int) $i];
            }
        }

        if (count($reordered) === count($this->blocks)) {
            $this->blocks = $reordered;
        }
    }

    protected function persist(): Page
    {
        $data = [
            'title' => $this->title,
            'type' => $this->type,
            'template' => $this->template,
            'seo_title' => $this->seoTitle ?: null,
            'seo_description' => $this->seoDescription ?: null,
            'blocks' => $this->cleanBlocks($this->blocks),
        ];

        if ($this->pageId) {
            $page = Page::findOrFail($this->pageId);
            $page->update($data);
        } else {
            $data['status'] = 'draft';
            $page = Page::create($data);
            $this->pageId = $page->id;
            $this->slug = (string) $page->slug;
        }

        // What's stored is now the source of truth — drop the preview draft.
        session()->forget('cms_page_draft.'.$page->id);

        return $page;
    }

    public function save(): void
    {
        $this->validate(['title' => ['required', 'string', 'max:190']]);
        $this->persist();
        $this->dispatch('toast', message: 'Page saved', type: 'success');
    }

    public function publish(): void
    {
        $this->validate(['title' => ['required', 'string', 'max:190']]);
        $page = $this->persist();
        $page->update(['status' => 'published', 'published_at' => $page->published_at ?? now()]);
        $this->status = 'published';
        $this->dispatch('toast', message: 'Page published', type: 'success');
    }

    /**
     * Tidy the canonical block settings before persisting: cast product ids to ints,
     * drop blank gallery images, and drop wholly-empty repeater rows. The stored
     * shape is unchanged from what the storefront blades already read — images as an
     * array of URL strings, product_ids as an array of ints, FAQ/testimonials as the
     * existing pairs shape.
     */
    protected function cleanBlocks(array $blocks): array
    {
        return array_map(function ($block) {
            $s = $block['settings'] ?? [];

            if (array_key_exists('product_ids', $s)) {
                $s['product_ids'] = array_values(array_map('intval', array_filter(
                    (array) $s['product_ids'],
                    fn ($v) => $v !== '' && $v !== null,
                )));
            }

            if (array_key_exists('images', $s)) {
                $s['images'] = array_values(array_filter(
                    array_map('trim', array_map('strval', (array) $s['images'])),
                    fn ($v) => $v !== '',
                ));
            }

            if (array_key_exists('items', $s)) {
                $s['items'] = array_values(array_filter(
                    (array) $s['items'],
                    fn ($it) => is_array($it) && trim(implode('', array_map('strval', $it))) !== '',
                ));
            }

            return ['type' => $block['type'], 'settings' => $s];
        }, array_values($blocks));
    }

    public function render()
    {
        return View::make('cms::livewire.page-builder', [
            'catalog' => self::CATALOG,
            'productMatches' => $this->typedProductMatches(),
        ]);
    }
}
