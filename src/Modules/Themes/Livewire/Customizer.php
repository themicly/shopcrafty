<?php

namespace Themicly\Shopcrafty\Modules\Themes\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Themes\Models\Theme;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

class Customizer extends Component
{
    /** @var array<string, mixed> */
    public array $settings = [];

    /** The active theme, switchable from the customizer's theme picker. */
    public ?int $activeThemeId = null;

    private const KEYS = [
        'primary', 'primary_ink', 'accent', 'bg', 'surface', 'ink', 'ink_soft', 'line',
        'radius', 'display_font', 'body_font', 'announcement', 'show_announcement', 'footer_text',
        'header_layout', 'header_sticky', 'header_transparent_home',
        'footer_show_payment_icons', 'footer_payment_methods', 'footer_newsletter',
    ];

    /**
     * Theme-declared editable copy. Any string setting in the active theme's
     * manifest named header_* / footer_* / text_* (beyond the fixed controls
     * above) is store text the owner can edit — themes opt strings in simply
     * by declaring them in theme.json.
     *
     * @return array<int, string>
     */
    protected function textKeys(ThemeService $themes): array
    {
        $manifest = $themes->metadata($themes->activeSlug())['settings'] ?? [];

        return collect($manifest)
            ->filter(fn ($value, $key) => is_string($value)
                && preg_match('/^(header_|footer_|text_)/', $key)
                && ! in_array($key, self::KEYS, true))
            ->keys()->values()->all();
    }

    /** @return array<int, string> */
    protected function allowedKeys(ThemeService $themes): array
    {
        return array_merge(self::KEYS, $this->textKeys($themes));
    }

    public function mount(ThemeService $themes): void
    {
        $this->activeThemeId = $themes->active()?->id;
        $this->settings = collect($themes->settings())->only($this->allowedKeys($themes))->all();
        $this->syncDraft();
    }

    /**
     * Switch the active theme from within the customizer (guarded by a confirm
     * dialog, since it applies to the live storefront), then reload the picker with
     * the newly active theme's own settings and refresh the live preview.
     */
    public function switchTheme(int $id, ThemeService $themes): void
    {
        $theme = Theme::find($id);

        if (! $theme || $theme->is_active) {
            return;
        }

        $themes->activate($theme);

        // Drop the previous theme's draft and load the new theme's published look.
        session()->forget('theme_draft');
        $this->activeThemeId = $theme->id;
        $this->settings = collect($themes->settings())->only($this->allowedKeys($themes))->all();
        $this->syncDraft();

        $this->dispatch('preview-updated');
        $this->dispatch('toast', message: "Switched to {$theme->name}", type: 'success');
    }

    public function updated(string $property): void
    {
        if (str_starts_with($property, 'settings')) {
            $this->syncDraft();
            $this->dispatch('preview-updated');
        }
    }

    protected function syncDraft(): void
    {
        session(['theme_draft' => $this->settings]);
    }

    public function publish(ThemeService $themes): void
    {
        // The structural tokens must carry a value or the storefront renders with
        // broken type/shape — block publish (and mark the field) when one is blank.
        $this->validate([
            'settings.display_font' => ['required', 'string'],
            'settings.body_font' => ['required', 'string'],
            'settings.radius' => ['required', 'string'],
            'settings.header_layout' => ['required', 'string'],
        ], [], [
            'settings.display_font' => 'Display font',
            'settings.body_font' => 'Body font',
            'settings.radius' => 'Corner radius',
            'settings.header_layout' => 'Layout',
        ]);

        $themes->publishSettings($this->settings);
        $this->dispatch('toast', message: 'Theme published', type: 'success');
    }

    public function resetToPublished(ThemeService $themes): void
    {
        session()->forget('theme_draft');
        $this->settings = collect($themes->settings())->only($this->allowedKeys($themes))->all();
        $this->syncDraft();
        $this->dispatch('preview-updated');
    }

    public function render()
    {
        $fonts = [
            "'Fraunces Variable', Georgia, serif" => 'Fraunces (serif)',
            "'Inter Variable', ui-sans-serif, system-ui, sans-serif" => 'Inter (sans)',
            'Georgia, Cambria, "Times New Roman", serif' => 'Georgia (serif)',
            'ui-sans-serif, system-ui, -apple-system, sans-serif' => 'System (sans)',
        ];

        return View::make('themes::livewire.customizer', [
            'fonts' => $fonts,
            'themes' => Theme::orderBy('name')->get(['id', 'name', 'slug']),
            'textKeys' => $this->textKeys(app(ThemeService::class)),
        ]);
    }
}
