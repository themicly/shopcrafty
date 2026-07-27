<?php

namespace Themicly\Shopcrafty\Modules\Catalog\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Catalog\Models\Attribute;
use Themicly\Shopcrafty\Modules\Catalog\Models\AttributeValue;

class AttributeManager extends Component
{
    public ?int $selectedId = null;

    public string $newName = '';

    public string $newType = 'select';

    public string $newValue = '';

    public string $newColor = '#000000';

    public function addAttribute(): void
    {
        $data = $this->validate([
            'newName' => ['required', 'string', 'max:120'],
            'newType' => ['required', 'in:select,color,button'],
        ]);

        $attribute = Attribute::create(['name' => $data['newName'], 'type' => $data['newType']]);

        $this->reset('newName', 'newType');
        $this->newType = 'select';
        $this->selectedId = $attribute->id;
        $this->dispatch('toast', message: 'Attribute created', type: 'success');
    }

    public function select(int $id): void
    {
        $this->selectedId = $id;
    }

    public function deleteAttribute(int $id): void
    {
        Attribute::findOrFail($id)->delete();
        if ($this->selectedId === $id) {
            $this->selectedId = null;
        }
        $this->dispatch('toast', message: 'Attribute deleted', type: 'success');
    }

    public function addValue(): void
    {
        if (! $this->selectedId) {
            return;
        }

        $attribute = Attribute::findOrFail($this->selectedId);

        $data = $this->validate([
            'newValue' => ['required', 'string', 'max:120'],
            'newColor' => ['nullable', 'string', 'max:20'],
        ]);

        $attribute->values()->create([
            'value' => $data['newValue'],
            'color_code' => $attribute->type === 'color' ? $data['newColor'] : null,
            'position' => (int) $attribute->values()->max('position') + 1,
        ]);

        $this->reset('newValue');
        $this->newColor = '#000000';
    }

    public function deleteValue(int $id): void
    {
        AttributeValue::findOrFail($id)->delete();
    }

    public function render()
    {
        return View::make('catalog::livewire.attribute-manager', [
            // Not `attributes`: inside a <livewire:…>-mounted component that name is
            // shadowed by the (empty) attribute bag, hiding the whole list (CAT-16).
            'attributeList' => Attribute::withCount('values')->orderBy('name')->get(),
            'selected' => $this->selectedId ? Attribute::with('values')->find($this->selectedId) : null,
        ]);
    }
}
