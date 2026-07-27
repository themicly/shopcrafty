<?php

namespace Themicly\Shopcrafty\Modules\Themes\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Core\Support\InteractsWithTypedFields;
use Themicly\Shopcrafty\Modules\Themes\Models\ThemeSection;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

class SectionBuilder extends Component
{
    use InteractsWithTypedFields;

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $editingKey = '';

    public string $editingLabel = '';

    /** @var array<string, mixed> */
    public array $form = [];

    public function toggle(int $id): void
    {
        $section = ThemeSection::findOrFail($id);
        $section->update(['is_enabled' => ! $section->is_enabled]);
        $this->previewChanged();
    }

    /** Add a new instance of a catalog section to the end of the homepage. */
    public function add(string $sectionKey): void
    {
        if (! isset(ThemeService::SECTIONS[$sectionKey])) {
            return;
        }

        $theme = app(ThemeService::class)->active();

        if (! $theme) {
            return;
        }

        $theme->sections()->create([
            'page' => 'home',
            'section_key' => $sectionKey,
            'position' => (int) $theme->sections()->where('page', 'home')->max('position') + 1,
            'is_enabled' => true,
        ]);

        $this->dispatch('toast', message: 'Section added', type: 'success');
        $this->previewChanged();
    }

    /**
     * Add a catalog section dropped at a specific spot: it lands directly
     * BEFORE the section it was dropped on. Complements add(), which appends.
     */
    public function insertBefore(string $sectionKey, int $beforeId): void
    {
        if (! isset(ThemeService::SECTIONS[$sectionKey])) {
            return;
        }

        $theme = app(ThemeService::class)->active();
        $target = $theme?->sections()->where('page', 'home')->whereKey($beforeId)->first();

        if (! $theme || ! $target) {
            return;
        }

        ThemeSection::where('theme_id', $theme->id)
            ->where('page', 'home')
            ->where('position', '>=', $target->position)
            ->increment('position');

        $theme->sections()->create([
            'page' => 'home',
            'section_key' => $sectionKey,
            'position' => $target->position,
            'is_enabled' => true,
        ]);

        $this->dispatch('toast', message: 'Section added', type: 'success');
        $this->previewChanged();
    }

    /** Duplicate a section (same type + settings) directly after the original. */
    public function duplicate(int $id): void
    {
        $section = ThemeSection::findOrFail($id);

        // Make room right after the original so the copy lands next to it.
        ThemeSection::where('theme_id', $section->theme_id)
            ->where('page', $section->page)
            ->where('position', '>', $section->position)
            ->increment('position');

        ThemeSection::create([
            'theme_id' => $section->theme_id,
            'page' => $section->page,
            'section_key' => $section->section_key,
            'position' => $section->position + 1,
            'is_enabled' => $section->is_enabled,
            'settings' => $section->settings,
        ]);

        $this->dispatch('toast', message: 'Section duplicated', type: 'success');
        $this->previewChanged();
    }

    public function delete(int $id): void
    {
        ThemeSection::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Section removed', type: 'success');
        $this->previewChanged();
    }

    /** Tell the live-preview iframe to refresh. */
    protected function previewChanged(): void
    {
        $this->dispatch('preview-updated');
    }

    public function move(int $id, string $direction): void
    {
        $section = ThemeSection::findOrFail($id);

        $swap = ThemeSection::where('theme_id', $section->theme_id)
            ->where('page', $section->page)
            ->when($direction === 'up',
                fn ($q) => $q->where('position', '<', $section->position)->orderByDesc('position'),
                fn ($q) => $q->where('position', '>', $section->position)->orderBy('position'),
            )
            ->first();

        if ($swap) {
            [$section->position, $swap->position] = [$swap->position, $section->position];
            $section->save();
            $swap->save();
            $this->previewChanged();
        }
    }

    /**
     * Persist a new order from drag-and-drop. Positions are assigned by the
     * ordered id list, scoped to the active theme's home page.
     *
     * @param  array<int, int|string>  $orderedIds
     */
    public function reorder(array $orderedIds): void
    {
        $theme = app(ThemeService::class)->active();

        if (! $theme) {
            return;
        }

        foreach (array_values($orderedIds) as $index => $id) {
            ThemeSection::where('theme_id', $theme->id)
                ->where('page', 'home')
                ->whereKey((int) $id)
                ->update(['position' => $index + 1]);
        }

        $this->previewChanged();
    }

    public function edit(int $id): void
    {
        $section = ThemeSection::findOrFail($id);
        $defaults = ThemeService::SECTIONS[$section->section_key]['defaults'] ?? [];

        $this->editingId = $section->id;
        $this->editingKey = $section->section_key;
        $this->editingLabel = ThemeService::SECTIONS[$section->section_key]['label'] ?? $section->section_key;
        $this->form = array_merge($defaults, $section->settings ?? []);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        if (! $this->editingId) {
            return;
        }

        // Block the save when a field the section marks required is left blank,
        // surfacing the error inline on the matching x-admin.field control.
        $this->validate(...$this->requiredValidation());

        ThemeSection::whereKey($this->editingId)->update(['settings' => $this->form]);
        $this->showForm = false;
        $this->dispatch('toast', message: 'Section updated', type: 'success');
        $this->previewChanged();
    }

    /**
     * Validation for the fields the active section marks 'required' => true in its
     * ThemeService schema. Number fields must be a positive integer; everything
     * else must be a non-empty string. Keyed by the dotted "form.<key>" path so
     * the error lands on the matching control.
     *
     * @return array{0: array<string, array<int, string>>, 1: array<string, string>, 2: array<string, string>}
     */
    protected function requiredValidation(): array
    {
        $rules = [];
        $attributes = [];

        foreach (ThemeService::SECTIONS[$this->editingKey]['fields'] ?? [] as $field) {
            if (empty($field['required'])) {
                continue;
            }

            $path = 'form.'.$field['key'];
            $rules[$path] = ($field['type'] ?? 'text') === 'number'
                ? ['required', 'integer', 'min:1']
                : ['required', 'string'];
            $attributes[$path] = $field['label'] ?? $field['key'];
        }

        return [$rules, [], $attributes];
    }

    public function render(ThemeService $themes)
    {
        $theme = $themes->active();

        return View::make('themes::livewire.section-builder', [
            'sections' => $theme
                ? $theme->sections()->where('page', 'home')->orderBy('position')->get()
                : collect(),
            'catalog' => ThemeService::SECTIONS,
            'activeTheme' => $theme,
            'productMatches' => $this->typedProductMatches(),
        ]);
    }
}
