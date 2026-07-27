<?php

namespace Themicly\Shopcrafty\Modules\Themes\Livewire;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

/**
 * A focused, non-technical editor for the active theme's copy — the same
 * header-, footer- and text-prefixed fields the live Customizer
 * auto-discovers into its "Store text" panel (see Customizer::textKeys()),
 * surfaced here as their own page under Website for owners who just want to
 * tweak wording without opening the full visual customizer. Saves straight
 * to the published settings (no draft/preview), matching GeneralSettings /
 * StorefrontSettings — both share ThemeService::publishSettings() as the
 * underlying writer, so a change here shows up immediately either way.
 */
class StoreText extends Component
{
    public bool $showAnnouncement = true;

    public string $announcement = '';

    public string $footerText = '';

    /** @var array<string, string> dynamic header-/footer-/text-prefixed keys => value */
    public array $text = [];

    public function mount(ThemeService $themes): void
    {
        abort_unless(Gate::allows('manage-content'), 403);

        $settings = $themes->settings();

        $this->showAnnouncement = (bool) ($settings['show_announcement'] ?? true);
        $this->announcement = (string) ($settings['announcement'] ?? '');
        $this->footerText = (string) ($settings['footer_text'] ?? '');
        $this->text = collect($settings)->only($this->dynamicKeys($themes))->all();
    }

    /**
     * Same discovery rule as Customizer::textKeys(): any string setting in the
     * active theme's manifest whose key starts with header_, footer_ or text_,
     * excluding the fixed structural controls (layout selects, sticky/
     * transparent toggles) that happen to share the prefix but aren't free text.
     *
     * @return array<int, string>
     */
    protected function dynamicKeys(ThemeService $themes): array
    {
        $manifest = $themes->metadata($themes->activeSlug())['settings'] ?? [];
        // Excluded either because they're structural (selects/toggles that
        // happen to share the prefix) or because they're already bound to
        // their own explicit property above — without this exclusion they'd
        // be edited in two places at once, and whichever saves last wins.
        $excluded = [
            'header_layout', 'header_sticky', 'header_transparent_home',
            'footer_show_payment_icons', 'footer_payment_methods', 'footer_newsletter',
            'announcement', 'show_announcement', 'footer_text',
        ];

        return collect($manifest)
            ->filter(fn ($value, $key) => is_string($value)
                && preg_match('/^(header_|footer_|text_)/', $key)
                && ! in_array($key, $excluded, true))
            ->keys()->values()->all();
    }

    public function save(ThemeService $themes): void
    {
        abort_unless(Gate::allows('manage-content'), 403);

        $themes->publishSettings(array_merge($this->text, [
            'show_announcement' => $this->showAnnouncement,
            'announcement' => $this->announcement,
            'footer_text' => $this->footerText,
        ]));

        $this->dispatch('toast', message: 'Store text saved', type: 'success');
    }

    public function render(ThemeService $themes)
    {
        return View::make('themes::livewire.store-text', [
            'theme' => $themes->active(),
            'labels' => collect($this->text)->keys()->mapWithKeys(fn ($k) => [$k => Str::headline(str_replace(['header_', 'footer_', 'text_'], '', $k))])->all(),
        ]);
    }
}
