<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Modules\Catalog\Models\Brand;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;

class BrandManager extends Component
{
    use WithFileUploads;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public bool $is_active = true;

    public string $logoPath = '';

    public $uploadLogo = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'is_active' => ['boolean'],
            'logoPath' => ['nullable', 'string'],
        ];
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'logoPath', 'uploadLogo');
        $this->is_active = true;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $brand = Brand::findOrFail($id);
        $this->editingId = $brand->id;
        $this->name = $brand->name;
        $this->is_active = (bool) $brand->is_active;
        $this->logoPath = (string) $brand->logo_path;
        $this->reset('uploadLogo');
        $this->resetValidation();
        $this->showForm = true;
    }

    public function updatedUploadLogo(MediaService $m): void
    {
        $this->validate(['uploadLogo' => MediaService::imageRules()]);
        $media = $m->store($this->uploadLogo);
        $this->logoPath = $media->url('medium');
        $this->reset('uploadLogo');
    }

    public function removeLogo(): void
    {
        $this->logoPath = '';
        $this->reset('uploadLogo');
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'is_active' => $this->is_active,
            'logo_path' => $this->logoPath ?: null,
        ];

        if ($this->editingId) {
            Brand::findOrFail($this->editingId)->update($data);
            $message = 'Brand updated';
        } else {
            Brand::create($data);
            $message = 'Brand created';
        }

        $this->showForm = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function delete(int $id): void
    {
        Brand::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Brand deleted', type: 'success');
    }

    public function render()
    {
        return View::make('catalog::livewire.brand-manager', [
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }
}
