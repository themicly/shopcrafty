<?php

namespace Themicly\Shopcrafty\Modules\Themes\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Themicly\Shopcrafty\Modules\Themes\Models\Theme;
use Themicly\Shopcrafty\Modules\Themes\Models\ThemeSection;

class ThemeService
{
    /** Themes shipped directly with the core package. */
    public const OPEN_SOURCE_THEMES = ['market', 'boutique'];

    /**
     * Section catalog for the homepage builder. Each maps to a
     * `theme::sections.{key}` Blade partial.
     */
    public const SECTIONS = [
        'banners' => [
            'label' => 'Banner slider',
            'description' => 'Rotating full-width promo slides',
            'defaults' => ['autoplay' => true, 'align' => 'left', 'height' => 'standard'],
            'fields' => [
                ['key' => 'autoplay', 'type' => 'toggle', 'label' => 'Autoplay slider'],
                ['key' => 'align', 'type' => 'select', 'label' => 'Text position', 'options' => ['left' => 'Left', 'center' => 'Center']],
                ['key' => 'height', 'type' => 'select', 'label' => 'Slide height', 'options' => ['compact' => 'Compact', 'standard' => 'Standard', 'tall' => 'Tall']],
            ],
        ],
        'hero' => [
            'label' => 'Hero',
            'description' => 'Big opening statement with a call to action',
            'defaults' => ['layout' => 'text', 'eyebrow' => '', 'heading' => 'Welcome to our store', 'subheading' => 'Quality products, delivered.', 'cta_label' => 'Shop now', 'cta_url' => '/shop', 'cta2_label' => '', 'cta2_url' => '', 'coupon' => '', 'image' => '', 'image2' => '', 'image3' => '', 'badge' => '', 'video_url' => ''],
            'fields' => [
                ['key' => 'layout', 'type' => 'select', 'label' => 'Layout', 'options' => ['text' => 'Text only', 'image' => 'Image', 'video' => 'Video']],
                ['key' => 'eyebrow', 'type' => 'text', 'label' => 'Eyebrow', 'hint' => 'Small line above the heading'],
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => true],
                ['key' => 'subheading', 'type' => 'textarea', 'label' => 'Subheading', 'rows' => 2],
                ['key' => 'cta_label', 'type' => 'text', 'label' => 'Primary button label'],
                ['key' => 'cta_url', 'type' => 'url', 'label' => 'Primary button URL'],
                ['key' => 'cta2_label', 'type' => 'text', 'label' => 'Secondary button label'],
                ['key' => 'cta2_url', 'type' => 'url', 'label' => 'Secondary button URL'],
                ['key' => 'coupon', 'type' => 'text', 'label' => 'Coupon code', 'hint' => 'Shown beside the button (e.g. SAVE10)'],
                ['key' => 'image', 'type' => 'image', 'label' => 'Image', 'hint' => 'Used by the Image layout / Volt & Bloom heroes'],
                ['key' => 'image2', 'type' => 'image', 'label' => 'Image — slide 2', 'hint' => 'Optional. Themes with a sliding hero (Noir) crossfade through the extra images'],
                ['key' => 'image3', 'type' => 'image', 'label' => 'Image — slide 3', 'hint' => 'Optional. Themes with a sliding hero (Noir) crossfade through the extra images'],
                ['key' => 'badge', 'type' => 'text', 'label' => 'Image badge', 'hint' => 'e.g. 30% OFF — shown on the hero image (Volt)'],
                ['key' => 'video_url', 'type' => 'url', 'label' => 'Video URL', 'hint' => 'YouTube, Vimeo or mp4/webm'],
            ],
        ],
        'promo_pair' => [
            'label' => 'Promo pair',
            'description' => 'Two side-by-side promo tiles',
            'defaults' => [
                'a_eyebrow' => 'Deal of the day!', 'a_heading' => 'Save big — up to 30% off', 'a_link_label' => 'Shop now', 'a_link' => '/shop', 'a_image' => '', 'a_bg' => '#c7ede7', 'a_countdown' => '',
                'b_eyebrow' => 'New arrivals', 'b_heading' => 'Designed for the everyday', 'b_link_label' => 'Shop now', 'b_link' => '/shop', 'b_image' => '', 'b_bg' => '#f3eccf',
            ],
            'fields' => [
                ['key' => 'a_eyebrow', 'type' => 'text', 'label' => 'Card A — eyebrow'],
                ['key' => 'a_heading', 'type' => 'text', 'label' => 'Card A — heading', 'required' => true],
                ['key' => 'a_countdown', 'type' => 'text', 'label' => 'Card A — countdown until', 'hint' => 'YYYY-MM-DD HH:MM — leave blank for no timer'],
                ['key' => 'a_link_label', 'type' => 'text', 'label' => 'Card A — button label'],
                ['key' => 'a_link', 'type' => 'url', 'label' => 'Card A — button URL'],
                ['key' => 'a_image', 'type' => 'image', 'label' => 'Card A — image'],
                ['key' => 'a_bg', 'type' => 'color', 'label' => 'Card A — background'],
                ['key' => 'b_eyebrow', 'type' => 'text', 'label' => 'Card B — eyebrow'],
                ['key' => 'b_heading', 'type' => 'text', 'label' => 'Card B — heading', 'required' => true],
                ['key' => 'b_link_label', 'type' => 'text', 'label' => 'Card B — button label'],
                ['key' => 'b_link', 'type' => 'url', 'label' => 'Card B — button URL'],
                ['key' => 'b_image', 'type' => 'image', 'label' => 'Card B — image'],
                ['key' => 'b_bg', 'type' => 'color', 'label' => 'Card B — background'],
            ],
        ],
        'categories' => [
            'label' => 'Category tiles',
            'description' => 'A grid of category tiles',
            'defaults' => ['heading' => 'Shop by category', 'limit' => 6],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => true],
                ['key' => 'limit', 'type' => 'number', 'label' => 'Number of categories', 'required' => true],
            ],
        ],
        'usp' => [
            'label' => 'Service highlights',
            'description' => 'Service highlights strip',
            'defaults' => ['item1' => 'Free shipping', 'item2' => 'Easy 30-day returns', 'item3' => 'Secure checkout', 'item4' => '24/7 customer support', 'icon1' => '', 'icon2' => '', 'icon3' => '', 'icon4' => ''],
            'fields' => [
                ['key' => 'item1', 'type' => 'text', 'label' => 'Highlight 1', 'required' => true],
                ['key' => 'icon1', 'type' => 'image', 'label' => 'Highlight 1 — icon', 'hint' => 'Optional image; replaces the built-in icon'],
                ['key' => 'item2', 'type' => 'text', 'label' => 'Highlight 2'],
                ['key' => 'icon2', 'type' => 'image', 'label' => 'Highlight 2 — icon', 'hint' => 'Optional image; replaces the built-in icon'],
                ['key' => 'item3', 'type' => 'text', 'label' => 'Highlight 3'],
                ['key' => 'icon3', 'type' => 'image', 'label' => 'Highlight 3 — icon', 'hint' => 'Optional image; replaces the built-in icon'],
                ['key' => 'item4', 'type' => 'text', 'label' => 'Highlight 4'],
                ['key' => 'icon4', 'type' => 'image', 'label' => 'Highlight 4 — icon', 'hint' => 'Optional image; replaces the built-in icon'],
            ],
        ],
        'featured_products' => [
            'label' => 'Featured Products',
            'description' => 'A curated product grid',
            'defaults' => ['heading' => 'Featured', 'limit' => 8],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => true],
                ['key' => 'limit', 'type' => 'number', 'label' => 'Number of products', 'required' => true],
            ],
        ],
        'feature' => [
            'label' => 'Feature showcase',
            'description' => 'Split story: image beside text',
            'defaults' => ['heading' => 'Crafted with care', 'subheading' => 'Every piece is chosen for quality and made to last.', 'bullets' => 'Premium materials|Considered design|Free returns', 'cta_label' => 'Learn more', 'cta_url' => '/about', 'image' => '/images/section-defaults/feature.svg'],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => true],
                ['key' => 'subheading', 'type' => 'textarea', 'label' => 'Subheading', 'rows' => 2],
                ['key' => 'bullets', 'type' => 'text', 'label' => 'Bullets', 'hint' => 'Separate each benefit with a | character'],
                ['key' => 'cta_label', 'type' => 'text', 'label' => 'Button label'],
                ['key' => 'cta_url', 'type' => 'url', 'label' => 'Button URL'],
                ['key' => 'image', 'type' => 'image', 'label' => 'Image'],
            ],
        ],
        'flash_sale' => [
            'label' => 'Flash Sale',
            'description' => 'Countdown deal with products',
            'defaults' => ['heading' => 'Flash Sale', 'limit' => 4, 'ends_at' => ''],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => true],
                ['key' => 'limit', 'type' => 'number', 'label' => 'Number of products', 'required' => true],
                ['key' => 'ends_at', 'type' => 'text', 'label' => 'Countdown ends at', 'hint' => 'e.g. 2026-12-31 23:59'],
            ],
        ],
        'brands' => [
            'label' => 'Brands',
            'description' => 'Brand logo wall',
            'defaults' => ['heading' => 'Shop by brand'],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => true],
            ],
        ],
        'instagram' => [
            'label' => 'Social gallery',
            'description' => 'Social-style photo gallery',
            'defaults' => ['heading' => 'Shop by gram', 'limit' => 6],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => true],
                ['key' => 'limit', 'type' => 'number', 'label' => 'Number of tiles', 'required' => true],
            ],
        ],
        'newsletter' => [
            'label' => 'Newsletter',
            'description' => 'Email signup band',
            'defaults' => ['heading' => 'Join our newsletter', 'subheading' => 'Get the latest offers in your inbox.'],
            'fields' => [
                ['key' => 'heading', 'type' => 'text', 'label' => 'Heading', 'required' => true],
                ['key' => 'subheading', 'type' => 'textarea', 'label' => 'Subheading', 'rows' => 2],
            ],
        ],
    ];

    /**
     * Default enabled homepage sections, in order, per theme slug. Each theme composes
     * a different subset of the catalog to suit its shop type (electronics vs grocery
     * vs luxury). Sections omitted here are created disabled and can be switched on in
     * the Section builder. Unknown slugs fall back to the full catalog order.
     */
    public const HOMEPAGE_LAYOUTS = [
        // Aurora — balanced fashion/general storefront.
        'default' => ['banners', 'hero', 'categories', 'featured_products', 'feature', 'flash_sale', 'brands', 'newsletter'],
        // Marketplace — the banner slider leads with a left category sidebar built in,
        // then category tiles, product grid, promo, trust, brands.
        'market' => ['banners', 'categories', 'featured_products', 'promo_pair', 'usp', 'brands', 'newsletter'],
        // Bloom — grocery: round crate-circle category tiles, then fresh picks.
        'fresh' => ['hero', 'usp', 'categories', 'featured_products', 'promo_pair', 'feature', 'newsletter'],
        // Noir — fashion boutique: hero, categories, products, story and brand wall.
        'boutique' => ['banners', 'hero', 'categories', 'featured_products', 'feature', 'brands', 'newsletter'],
        // Studio — classic fashion store: sage hero band, plaque category trio,
        // featured grid, saving-zone promos, split story banner, subscribe.
        'studio' => ['hero', 'categories', 'featured_products', 'promo_pair', 'feature', 'newsletter'],
        // Haven — premium interiors: drifting room-scene hero, promo banner slider,
        // values marquee, room categories, gallery product grid, offset story
        // composition, espresso promo duo and espresso subscribe closer.
        'haven' => ['banners', 'hero', 'usp', 'categories', 'featured_products', 'feature', 'promo_pair', 'newsletter'],
    ];

    protected ?Theme $active = null;

    /** Per-request preview override (signed admin preview of a non-active theme). */
    protected ?string $previewSlug = null;

    /** @var array<string, array<string, mixed>> Per-request memo of resolved theme.json metadata. */
    protected array $metaCache = [];

    /** @var array<string, mixed>|null Per-request memo of resolved settings (rebuilt on activate). */
    protected ?array $settingsCache = null;

    /**
     * Render this request under a different theme without touching the DB —
     * used by the admin theme-switcher's live preview thumbnails.
     */
    public function setPreviewSlug(?string $slug): void
    {
        $this->previewSlug = $slug;
        $this->active = null;
        $this->settingsCache = null;
    }

    public function previewSlug(): ?string
    {
        return $this->previewSlug;
    }

    public function active(): ?Theme
    {
        if ($this->active) {
            return $this->active;
        }

        try {
            if ($this->previewSlug !== null) {
                $preview = Theme::where('slug', $this->previewSlug)->first();

                if ($preview) {
                    return $this->active = $preview;
                }
            }

            return $this->active = Theme::where('is_active', true)->first()
                ?? Theme::orderBy('id')->first();
        } catch (\Throwable $e) {
            return null; // table not migrated yet (install time)
        }
    }

    public function activeSlug(): string
    {
        return $this->active()?->slug ?? 'default';
    }

    /** @return array<string, mixed> */
    public function settings(): array
    {
        if ($this->settingsCache !== null) {
            return $this->settingsCache;
        }

        $theme = $this->active();

        // Resolution order (later wins): hardcoded defaults → the theme package's
        // theme.json "settings" (its intended out-of-the-box look) → buyer overrides
        // saved by the customizer. This is what lets activating a theme swap the palette.
        $settings = $this->defaultSettings();

        if ($theme) {
            $packageSettings = $this->metadata($theme->slug)['settings'] ?? [];
            $settings = array_merge($settings, $packageSettings, $theme->settings()->pluck('value', 'key')->all());
        }

        // Live customizer preview: an authenticated admin viewing ?preview=1 sees
        // the unpublished draft stored in their session.
        if (request()->boolean('preview') && auth('web')->check() && ($draft = session('theme_draft'))) {
            $settings = array_merge($settings, $draft);
        }

        return $this->settingsCache = $settings;
    }

    /** Persist a draft settings map to the active theme (publish). */
    public function publishSettings(array $settings): void
    {
        $theme = $this->active();

        if (! $theme) {
            return;
        }

        foreach ($settings as $key => $value) {
            $theme->settings()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->active = null; // refresh cache
        $this->settingsCache = null;
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return $this->settings()[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function defaultSettings(): array
    {
        return [
            // Storefront design tokens (customizer-controlled; see docs/08).
            'primary' => '#6d28d9',
            'primary_ink' => '#ffffff',
            'accent' => '#f43f5e',
            'bg' => '#ffffff',
            'surface' => '#f6f4ff',
            'ink' => '#1b1233',
            'ink_soft' => '#6b6688',
            'line' => '#ece8f7',
            'radius' => '16px',
            'display_font' => "'Fraunces Variable', Georgia, serif",
            'body_font' => "'Inter Variable', ui-sans-serif, system-ui, sans-serif",
            'announcement' => 'Free shipping on orders over $50 · Shop now',
            'show_announcement' => true,
            'footer_text' => 'Quality products, delivered.',
            // Header/footer builder (customizer-controlled). See TASK #31.
            'header_layout' => 'logo-left', // logo-left · logo-center
            'header_sticky' => true, // keep the header pinned while scrolling
            'header_transparent_home' => false, // overlay the homepage hero, solid on scroll
            'footer_show_payment_icons' => true,
            'footer_payment_methods' => 'Visa, Mastercard, Amex, PayPal, Apple Pay, Google Pay',
            'footer_newsletter' => false,
            // Product-card variant, resolved at runtime by the shared card component.
            // overlay (default) · bordered · basket · editorial · caption.
            'card_style' => 'overlay',
        ];
    }

    /** Enabled sections for a page, in order, with defaults merged in. */
    public function sections(string $page = 'home'): Collection
    {
        $theme = $this->active();

        if (! $theme) {
            return collect();
        }

        $rows = $theme->sections()
            ->where('page', $page)
            ->where('is_enabled', true)
            ->orderBy('position')
            ->get()
            ->map(function (ThemeSection $section) {
                $defaults = self::SECTIONS[$section->section_key]['defaults'] ?? [];
                $section->resolved_settings = array_merge($defaults, $section->settings ?? []);

                return $section;
            });

        // Previewing a theme that was never configured: synthesize its default
        // homepage from HOMEPAGE_LAYOUTS so the preview shows a real page, not
        // an empty storefront. Never persisted; normal rendering is unchanged.
        if ($rows->isEmpty() && $this->previewSlug !== null && $page === 'home') {
            $keys = self::HOMEPAGE_LAYOUTS[$theme->slug] ?? array_keys(self::SECTIONS);

            return collect(array_values($keys))->map(function (string $key, int $i) {
                $section = new ThemeSection([
                    'page' => 'home',
                    'section_key' => $key,
                    'position' => $i + 1,
                    'is_enabled' => true,
                ]);
                $section->resolved_settings = self::SECTIONS[$key]['defaults'] ?? [];

                return $section;
            });
        }

        return $rows;
    }

    /** Theme package metadata from themes/{slug}/theme.json. */
    public function metadata(?string $slug = null): array
    {
        $slug ??= $this->activeSlug();

        if (array_key_exists($slug, $this->metaCache)) {
            return $this->metaCache[$slug];
        }

        $file = $this->themePath($slug).'/theme.json';
        $meta = is_file($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];

        return $this->metaCache[$slug] = $meta;
    }

    /**
     * Register every theme package found in the host and package theme roots.
     *
     * Roots are searched in priority order: host themes/, core package
     * themes/, then package vendor themes. A host theme with the same slug
     * therefore replaces the bundled version without changing its database
     * customizations.
     *
     * @return int number of theme packages registered
     */
    public function syncFromDisk(): int
    {
        $count = 0;
        $manifests = [];

        foreach ($this->themeRoots() as $root) {
            foreach (glob($root.'/*/theme.json') ?: [] as $file) {
                $meta = json_decode(file_get_contents($file), true);

                if (! is_array($meta)) {
                    continue;
                }

                $slug = (string) ($meta['slug'] ?? basename(dirname($file)));

                if (! $this->validSlug($slug) || isset($manifests[$slug])) {
                    continue;
                }

                // First root wins: host themes override package themes.
                $manifests[$slug] = [$file, $meta];
            }
        }

        foreach ($manifests as $slug => [$file, $meta]) {
            $theme = Theme::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $meta['name'] ?? ucfirst($slug),
                    'author' => $meta['author'] ?? null,
                    'version' => $meta['version'] ?? '1.0.0',
                    'installed_at' => Theme::where('slug', $slug)->value('installed_at') ?? now(),
                ],
            );

            $this->seedDefaultSections($theme);

            $count++;
        }

        // Remove database entries whose theme files were removed. This prevents
        // stale themes from remaining selectable after an upgrade.
        if ($manifests !== []) {
            Theme::whereNotIn('slug', array_keys($manifests))->delete();
        }

        // Never leave the storefront without an active theme (THM-05).
        if (! Theme::where('is_active', true)->exists()) {
            $fallback = Theme::where('slug', 'default')->first() ?? Theme::orderBy('id')->first();
            $fallback?->update(['is_active' => true]);
        }

        $this->active = null;
        $this->settingsCache = null;

        return $count;
    }

    /** Seed the initial homepage composition once, without overwriting edits on reinstall. */
    protected function seedDefaultSections(Theme $theme): void
    {
        $keys = self::HOMEPAGE_LAYOUTS[$theme->slug] ?? [];
        $existingKeys = $theme->sections()->where('page', 'home')->pluck('section_key')->all();
        $position = (int) $theme->sections()->where('page', 'home')->max('position');

        foreach ($keys as $key) {
            if (! isset(self::SECTIONS[$key]) || in_array($key, $existingKeys, true)) {
                continue;
            }

            $theme->sections()->create([
                'page' => 'home',
                'section_key' => $key,
                'position' => ++$position,
                'is_enabled' => true,
                'settings' => self::SECTIONS[$key]['defaults'] ?? [],
            ]);
        }
    }

    public function activate(Theme $theme): void
    {
        // One transaction so there's never a window with zero (or two) active themes (THM-05).
        DB::transaction(function () use ($theme) {
            Theme::query()->update(['is_active' => false]);
            $theme->update(['is_active' => true]);
        });

        $this->active = null;
        $this->settingsCache = null;
    }

    public function themesRootPath(): string
    {
        return $this->themeRoots()[0];
    }

    public function themePath(string $slug): string
    {
        if (! $this->validSlug($slug)) {
            return '';
        }

        foreach ($this->themeRoots() as $root) {
            $path = $root.'/'.$slug;

            if (is_dir($path)) {
                return $path;
            }
        }

        return '';
    }

    protected function themesPath(): string
    {
        return $this->themesRootPath();
    }

    /** @return array<int, string> theme roots in override priority order */
    public function themeRoots(): array
    {
        $packageRoot = dirname(__DIR__, 4);
        $hostRoot = (string) (config('shopcrafty.themes_path') ?: base_path('themes'));

        return array_values(array_unique(array_filter([
            $hostRoot,
            $packageRoot.'/themes',
            $packageRoot.'/resources/vendor/Themes',
        ], 'is_dir')));
    }

    protected function validSlug(string $slug): bool
    {
        return preg_match('/^[a-z0-9][a-z0-9_-]*$/i', $slug) === 1;
    }
}
