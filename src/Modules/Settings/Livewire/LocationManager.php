<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Modules\Orders\Models\Location;
use Themicly\Shopcrafty\Modules\Orders\Models\ShippingZone;
use Themicly\Shopcrafty\Modules\Orders\Services\LocationService;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

/**
 * Owner-managed location hierarchy (any country). Owners define the level labels
 * (e.g. Division / District / Area), build the tree by drilling in, map nodes to
 * shipping zones, and can bulk-upload a CSV. Checkout then renders a cascading
 * dropdown and derives the shipping rate from the chosen address.
 */
class LocationManager extends Component
{
    use WithFileUploads;

    /** Comma-separated level labels, top level first. */
    public string $levelsInput = '';

    /** Current drill-down parent (null = top level). */
    public ?int $parentId = null;

    /** @var array<int, array{id:int,name:string}> breadcrumb from root to current parent */
    public array $trail = [];

    public string $newName = '';

    public ?int $newZoneId = null;

    public ?int $editingId = null;

    public string $editName = '';

    public ?int $editZoneId = null;

    public $csv;

    /** @var array{created:int,errors:array<int,string>}|null */
    public ?array $importResult = null;

    public function mount(Settings $settings): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);
        $this->levelsInput = implode(', ', app(LocationService::class)->levels());
    }

    public function saveLevels(Settings $settings): void
    {
        $levels = array_values(array_filter(array_map('trim', explode(',', $this->levelsInput)), fn ($l) => $l !== ''));

        $settings->set('shipping.location_levels', $levels);
        $this->dispatch('toast', message: 'Location levels saved', type: 'success');
    }

    public function open(int $id): void
    {
        $node = Location::findOrFail($id);
        $this->trail[] = ['id' => $node->id, 'name' => $node->name];
        $this->parentId = $node->id;
        $this->reset('newName', 'newZoneId', 'editingId');
    }

    public function goTo(int $index): void
    {
        // -1 = back to root; otherwise trim the trail to that crumb.
        if ($index < 0) {
            $this->trail = [];
            $this->parentId = null;
        } else {
            $this->trail = array_slice($this->trail, 0, $index + 1);
            $this->parentId = $this->trail[$index]['id'] ?? null;
        }

        $this->reset('newName', 'newZoneId', 'editingId');
    }

    public function addNode(): void
    {
        $data = $this->validate([
            'newName' => ['required', 'string', 'max:120'],
            'newZoneId' => ['nullable', 'exists:shipping_zones,id'],
        ]);

        Location::create([
            'parent_id' => $this->parentId,
            'name' => $data['newName'],
            'level' => count($this->trail),
            'shipping_zone_id' => $data['newZoneId'] ?: null,
            'position' => (int) Location::where('parent_id', $this->parentId)->max('position') + 1,
            'is_active' => true,
        ]);

        $this->reset('newName', 'newZoneId');
        $this->dispatch('toast', message: 'Location added', type: 'success');
    }

    public function edit(int $id): void
    {
        $node = Location::where('parent_id', $this->parentId)->findOrFail($id);
        $this->editingId = $node->id;
        $this->editName = $node->name;
        $this->editZoneId = $node->shipping_zone_id;
    }

    public function saveEdit(): void
    {
        $data = $this->validate([
            'editName' => ['required', 'string', 'max:120'],
            'editZoneId' => ['nullable', 'exists:shipping_zones,id'],
        ]);

        Location::where('parent_id', $this->parentId)->whereKey($this->editingId)
            ->update(['name' => $data['editName'], 'shipping_zone_id' => $data['editZoneId'] ?: null]);

        $this->reset('editingId', 'editName', 'editZoneId');
        $this->dispatch('toast', message: 'Location updated', type: 'success');
    }

    public function toggle(int $id): void
    {
        $node = Location::where('parent_id', $this->parentId)->findOrFail($id);
        $node->update(['is_active' => ! $node->is_active]);
    }

    public function delete(int $id): void
    {
        Location::where('parent_id', $this->parentId)->whereKey($id)->delete();
        $this->dispatch('toast', message: 'Location deleted (with its sub-locations)', type: 'success');
    }

    public function import(LocationService $locations): void
    {
        $this->validate(['csv' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);

        $this->importResult = $locations->importCsv(file_get_contents($this->csv->getRealPath()));
        $this->reset('csv');
        $this->dispatch('toast', message: "Imported {$this->importResult['created']} new locations", type: 'success');
    }

    public function render()
    {
        $levels = app(LocationService::class)->levels();
        $depth = count($this->trail);

        return View::make('settings::livewire.location-manager', [
            'levels' => $levels,
            'currentLevelLabel' => $levels[$depth] ?? 'Location',
            'atMaxDepth' => $depth >= count($levels),
            'nodes' => Location::where('parent_id', $this->parentId)->orderBy('position')->orderBy('name')->get(),
            'zones' => ShippingZone::orderBy('position')->get(['id', 'name']),
        ]);
    }
}
