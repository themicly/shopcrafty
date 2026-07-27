<?php

namespace Themicly\Shopcrafty\Modules\Themes\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;
use Themicly\Shopcrafty\Modules\Themes\Models\Banner;

class BannerManager extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $title = '';

    public string $subtitle = '';

    public string $imageLarge = '';

    public string $imageSmall = '';

    public string $linkUrl = '';

    public string $linkLabel = '';

    public string $placement = 'home_slider';

    public string $sort = '0';

    public bool $isActive = true;

    public string $startsAt = '';

    public string $endsAt = '';

    public $uploadLarge = null;

    public $uploadSmall = null;

    protected function rules(): array
    {
        return [
            'imageLarge' => ['required', 'string'],
            'imageSmall' => ['nullable', 'string'],
            'title' => ['nullable', 'string'],
            'subtitle' => ['nullable', 'string'],
            'linkUrl' => ['nullable', 'string'],
            'linkLabel' => ['nullable', 'string'],
            'placement' => ['required', 'in:home_slider,promo_strip'],
            'sort' => ['nullable', 'string'],
            'startsAt' => ['nullable', 'date'],
            'endsAt' => ['nullable', 'date', 'after_or_equal:startsAt'], // THM-06
        ];
    }

    public function create(): void
    {
        $this->reset('editingId', 'title', 'subtitle', 'imageLarge', 'imageSmall', 'linkUrl', 'linkLabel', 'sort', 'startsAt', 'endsAt', 'uploadLarge', 'uploadSmall');
        $this->isActive = true;
        $this->placement = 'home_slider';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $banner = Banner::findOrFail($id);
        $this->editingId = $banner->id;
        $this->title = (string) $banner->title;
        $this->subtitle = (string) $banner->subtitle;
        $this->imageLarge = (string) $banner->image_large;
        $this->imageSmall = (string) $banner->image_small;
        $this->linkUrl = (string) $banner->link_url;
        $this->linkLabel = (string) $banner->link_label;
        $this->placement = $banner->placement;
        $this->sort = (string) $banner->sort;
        $this->isActive = (bool) $banner->is_active;
        $this->startsAt = $banner->starts_at ? $banner->starts_at->format('Y-m-d\TH:i') : '';
        $this->endsAt = $banner->ends_at ? $banner->ends_at->format('Y-m-d\TH:i') : '';
        $this->reset('uploadLarge', 'uploadSmall');
        $this->resetValidation();
        $this->showForm = true;
    }

    public function updatedUploadLarge(MediaService $m): void
    {
        $this->validate(['uploadLarge' => MediaService::imageRules()]);

        try {
            $media = $m->store($this->uploadLarge);
        } catch (\InvalidArgumentException $e) {
            $this->addError('uploadLarge', $e->getMessage());
            $this->reset('uploadLarge');

            return;
        }

        $this->imageLarge = $media->url('large');
        $this->reset('uploadLarge');
    }

    public function updatedUploadSmall(MediaService $m): void
    {
        $this->validate(['uploadSmall' => MediaService::imageRules()]);

        try {
            $media = $m->store($this->uploadSmall);
        } catch (\InvalidArgumentException $e) {
            $this->addError('uploadSmall', $e->getMessage());
            $this->reset('uploadSmall');

            return;
        }

        $this->imageSmall = $media->url('large');
        $this->reset('uploadSmall');
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title ?: null,
            'subtitle' => $this->subtitle ?: null,
            'image_large' => $this->imageLarge,
            'image_small' => $this->imageSmall ?: null,
            'link_url' => $this->linkUrl ?: null,
            'link_label' => $this->linkLabel ?: null,
            'placement' => $this->placement,
            'sort' => (int) $this->sort,
            'is_active' => $this->isActive,
            'starts_at' => $this->startsAt ?: null,
            'ends_at' => $this->endsAt ?: null,
        ];

        if ($this->editingId) {
            Banner::findOrFail($this->editingId)->update($data);
            $message = 'Banner updated';
        } else {
            Banner::create($data);
            $message = 'Banner created';
        }

        $this->showForm = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function delete(int $id): void
    {
        Banner::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Banner deleted', type: 'success');
    }

    public function toggle(int $id): void
    {
        $banner = Banner::findOrFail($id);
        $banner->is_active = ! $banner->is_active;
        $banner->save();
        $this->dispatch('toast', message: 'Banner updated', type: 'success');
    }

    public function move(int $id, string $dir): void
    {
        $banner = Banner::findOrFail($id);

        $swap = Banner::where('placement', $banner->placement)
            ->when($dir === 'up',
                fn ($q) => $q->where('sort', '<', $banner->sort)->orderByDesc('sort'),
                fn ($q) => $q->where('sort', '>', $banner->sort)->orderBy('sort'),
            )
            ->first();

        if ($swap) {
            [$banner->sort, $swap->sort] = [$swap->sort, $banner->sort];
            $banner->save();
            $swap->save();
        }
    }

    public function render()
    {
        return View::make('themes::livewire.banner-manager', [
            'banners' => Banner::orderBy('placement')->orderBy('sort')->get(),
        ]);
    }
}
