<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Ai\AiNotConfiguredException;
use Themicly\Shopcrafty\Ai\AiRequestException;
use Themicly\Shopcrafty\Ai\AiService;
use Themicly\Shopcrafty\Ai\Generators\CategoryContentGenerator;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;

class CategoryManager extends Component
{
    use WithFileUploads;

    public string $search = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public ?int $parent_id = null;

    public string $description = '';

    public string $icon = '';

    public string $image_path = '';

    public $upload = null;

    public bool $is_active = true;

    public string $seo_title = '';

    public string $seo_description = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:catalog_categories,id'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:32'],
            'image_path' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'parent_id', 'description', 'icon', 'image_path', 'upload', 'seo_title', 'seo_description');
        $this->is_active = true;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->parent_id = $category->parent_id;
        $this->description = (string) $category->description;
        $this->icon = (string) $category->icon;
        $this->image_path = (string) $category->image_path;
        $this->reset('upload');
        $this->is_active = (bool) $category->is_active;
        $this->seo_title = (string) $category->seo_title;
        $this->seo_description = (string) $category->seo_description;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function updatedUpload(MediaService $m): void
    {
        $this->validate(['upload' => MediaService::imageRules()]);
        $media = $m->store($this->upload);
        $this->image_path = $media->url('medium');
        $this->reset('upload');
    }

    public function removeImage(): void
    {
        $this->image_path = '';
        $this->reset('upload');
    }

    public function getAiEnabledProperty(): bool
    {
        return app(AddonRegistry::class)->installed('ai') && app(AiService::class)->featureEnabled('category_copy');
    }

    /**
     * Draft the description and SEO fields with AI, grounded on the category
     * name plus a sample of product names already filed under it.
     */
    public function generateWithAi(): void
    {
        if (! app(AddonRegistry::class)->installed('ai')) {
            abort(404);
        }

        $generator = app(CategoryContentGenerator::class);

        if (trim($this->name) === '') {
            $this->dispatch('toast', message: 'Enter a category name first', type: 'danger');

            return;
        }

        try {
            $productNames = $this->editingId
                ? Product::where('category_id', $this->editingId)->latest()->limit(10)->pluck('name')->all()
                : [];

            $result = $generator->generate(trim($this->name), $productNames);
        } catch (AiNotConfiguredException) {
            $this->dispatch('toast', message: 'Turn on AI in Settings → AI first', type: 'danger');

            return;
        } catch (AiRequestException $e) {
            $this->dispatch('toast', message: 'AI generation failed: '.$e->getMessage(), type: 'danger');

            return;
        } catch (\Throwable) {
            $this->dispatch('toast', message: 'AI generation failed. Please try again.', type: 'danger');

            return;
        }

        $items = [];

        if ($result['description'] !== '') {
            $items[] = ['key' => 'description', 'label' => 'Description', 'before' => $this->description, 'after' => $result['description']];
        }
        if ($result['seo_title'] !== '') {
            $items[] = ['key' => 'seo_title', 'label' => 'SEO title', 'before' => $this->seo_title, 'after' => $result['seo_title']];
        }
        if ($result['seo_description'] !== '') {
            $items[] = ['key' => 'seo_description', 'label' => 'SEO description', 'before' => $this->seo_description, 'after' => $result['seo_description']];
        }

        if ($items === []) {
            $this->dispatch('toast', message: 'AI had nothing new to suggest', type: 'danger');

            return;
        }

        $this->openAiReview($items);
    }

    /** Apply whichever AI-suggested fields the owner left checked in the review modal. */
    public function applyAiReview(): void
    {
        foreach ($this->aiReview as $item) {
            if (! $item['selected']) {
                continue;
            }

            match ($item['key']) {
                'description' => $this->description = $item['value'],
                'seo_title' => $this->seo_title = $item['value'],
                'seo_description' => $this->seo_description = $item['value'],
                default => null,
            };
        }

        $this->discardAiReview();
        $this->dispatch('toast', message: 'AI copy applied — review and edit before saving', type: 'success');
    }

    public function save(): void
    {
        $data = $this->validate();

        // Prevent a cycle: the chosen parent must not be the category itself or any
        // of its own descendants (walk the ancestor chain, not just the direct parent — CAT-02).
        if ($this->editingId && $data['parent_id'] && $this->createsCycle($this->editingId, (int) $data['parent_id'])) {
            $this->addError('parent_id', 'A category cannot be moved under itself or one of its subcategories.');

            return;
        }

        if ($this->editingId) {
            Category::findOrFail($this->editingId)->update($data);
            $message = 'Category updated';
        } else {
            $data['position'] = (int) Category::where('parent_id', $data['parent_id'])->max('position') + 1;
            Category::create($data);
            $message = 'Category created';
        }

        $this->showForm = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    /** True if setting $parentId as the parent of $categoryId would form a cycle. */
    protected function createsCycle(int $categoryId, int $parentId): bool
    {
        $ancestor = $parentId;
        $guard = 0;

        while ($ancestor && $guard++ < 1000) {
            if ($ancestor === $categoryId) {
                return true;
            }

            $ancestor = (int) Category::whereKey($ancestor)->value('parent_id');
        }

        return false;
    }

    public function delete(int $id): void
    {
        Category::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Category deleted', type: 'success');
    }

    public function move(int $id, string $direction): void
    {
        $category = Category::findOrFail($id);

        $swap = Category::where('parent_id', $category->parent_id)
            ->when($direction === 'up',
                fn ($q) => $q->where('position', '<', $category->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $category->position)->orderBy('position'),
            )
            ->first();

        if ($swap) {
            [$category->position, $swap->position] = [$swap->position, $category->position];
            $category->save();
            $swap->save();
        }
    }

    public function render(CategoryTree $tree)
    {
        $searching = trim($this->search) !== '';
        // Searching flattens the tree to name matches; children are emptied so
        // the recursive row partial doesn't drag in non-matching descendants.
        $roots = $searching
            ? Category::where('name', 'like', '%'.str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], trim($this->search)).'%')
                ->orderBy('name')->get()
                ->each(fn (Category $c) => $c->setRelation('children', collect()))
            : $tree->roots();

        return View::make('catalog::livewire.category-manager', [
            'roots' => $roots,
            'searching' => $searching,
            'categoryCount' => $searching ? $roots->count() : Category::count(),
            'parentOptions' => $tree->flat(),
        ]);
    }
}
