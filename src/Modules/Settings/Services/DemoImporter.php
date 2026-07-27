<?php

namespace Themicly\Shopcrafty\Modules\Settings\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Themicly\Shopcrafty\Modules\Catalog\Models\Brand;
use Themicly\Shopcrafty\Modules\Catalog\Models\Category;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\CMS\Models\Menu;
use Themicly\Shopcrafty\Modules\CMS\Models\MenuItem;
use Themicly\Shopcrafty\Modules\CMS\Models\Page;
use Themicly\Shopcrafty\Modules\Marketing\Models\Coupon;
use Themicly\Shopcrafty\Modules\Settings\Support\DemoImageFactory;
use Themicly\Shopcrafty\Modules\Themes\Models\Banner;
use Themicly\Shopcrafty\Modules\Themes\Models\Theme;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

/**
 * One-click demo import. Seeds a cohesive, self-contained storefront for a
 * chosen pack (fashion / electronics / grocery / furniture) — catalogue, images
 * (bundled concept art), a welcome coupon, legal pages — and pins the pack's
 * theme "look" + store name so the storefront looks premium immediately.
 * Idempotent: safe to run more than once.
 */
class DemoImporter
{
    public function __construct(
        private Settings $settings,
        private ThemeService $themes,
        private DemoImageFactory $images,
    ) {}

    /**
     * Pack metadata for pickers (key => label + description + theme + store name).
     *
     * @return array<string, array{label:string, description:string, theme:string, store_name:string}>
     */
    public function packs(): array
    {
        return collect(config('demo-packs', []))
            ->map(fn ($p) => [
                'label' => $p['label'],
                'description' => $p['description'],
                'theme' => $p['theme'],
                'store_name' => $p['store_name'],
            ])->all();
    }

    public function has(string $pack): bool
    {
        return config("demo-packs.{$pack}") !== null;
    }

    public function import(string $pack): void
    {
        $data = config("demo-packs.{$pack}");

        if (! $data) {
            throw new InvalidArgumentException("Unknown demo pack [{$pack}].");
        }

        // Public disk must be reachable for the generated imagery.
        $this->ensurePublicLink();

        $this->themes->syncFromDisk();
        $themeId = Theme::where('slug', $data['theme'])->value('id');

        $brands = $this->brands($data['brands']);
        $categories = $this->categories($pack, $data['categories']);
        $this->products($pack, $data['products'], $brands, $categories);
        $this->banners($pack, $data['store_name'], $data['copy']['banners'] ?? [], $themeId);
        $this->coupon();
        $this->legalPages();
        $this->applyLook($data['theme'], $data['store_name'], $categories, $brands, $data['copy']['hero'] ?? []);
    }

    /** @param array<int, string> $names */
    private function brands(array $names): array
    {
        $out = [];
        foreach ($names as $name) {
            $out[$name] = Brand::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'is_active' => true]);
        }

        return $out;
    }

    /** @param array<string, string> $defs */
    private function categories(string $pack, array $defs): array
    {
        $out = [];
        $pos = 0;
        foreach ($defs as $name => $desc) {
            $slug = Str::slug($name);
            $image = $this->images->tile($pack, 'cat-'.$slug, $name, 800, 800);
            $out[$name] = Category::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $desc, 'is_active' => true, 'position' => $pos++, 'image' => $image],
            );
        }

        return $out;
    }

    /** @param array<int, array{0:string,1:string,2:string,3:int,4:?int,5:int}> $defs */
    private function products(string $pack, array $defs, array $brands, array $categories): void
    {
        foreach ($defs as $i => [$name, $cat, $brand, $price, $compare, $stock]) {
            $slug = Str::slug($name);
            $product = Product::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'type' => 'simple',
                    'status' => 'active',
                    'description' => "Meet the {$name} — a considered piece from {$brand}, made to be used and loved. Quality materials, honest pricing, and a finish that lasts.",
                    'category_id' => $categories[$cat]->id ?? null,
                    'brand_id' => $brands[$brand]->id ?? null,
                    'price' => $price * 100,
                    'compare_at_price' => $compare ? $compare * 100 : null,
                    'sku' => strtoupper(substr($pack, 0, 2)).'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'stock_qty' => $stock,
                    'track_inventory' => true,
                    'low_stock_threshold' => 5,
                    'published_at' => now()->subDays(30 - $i),
                ],
            );

            if ($product->media()->count() === 0) {
                foreach ([0, 1] as $n) {
                    $product->media()->create([
                        'path' => $this->images->product($pack, $slug, $name, $n),
                        'position' => $n,
                        'is_featured' => $n === 0,
                    ]);
                }
            }
        }
    }

    /** @param array<int, array{title:string, subtitle:string, label:string, url:string}> $copy */
    private function banners(string $pack, string $storeName, array $copy, ?int $themeId): void
    {
        $fallback = [
            ['title' => 'New season, new arrivals', 'subtitle' => 'Discover what just landed', 'label' => 'Shop now', 'url' => '/shop'],
            ['title' => $storeName.' picks', 'subtitle' => 'Our team’s favourites this month', 'label' => 'Explore', 'url' => '/shop'],
        ];
        $slides = count($copy) === 2 ? $copy : $fallback;
        $keepIds = [];

        foreach ($slides as $i => $slide) {
            $key = $i === 0 ? 'banner-hero' : 'banner-picks';

            $banner = Banner::updateOrCreate(
                ['placement' => 'home_slider', 'theme_id' => $themeId, 'sort' => $i],
                [
                    'title' => $slide['title'],
                    'subtitle' => $slide['subtitle'],
                    'image_large' => $this->images->tile($pack, $key.'-lg', $storeName, 1600, 520),
                    'image_small' => $this->images->tile($pack, $key.'-sm', $storeName, 800, 900),
                    'link_url' => $slide['url'], 'link_label' => $slide['label'],
                    'is_active' => true,
                ],
            );
            $keepIds[] = $banner->id;
        }

        // Older code matched banners by title (and didn't scope by theme), so every
        // past import left its own never-cleaned-up "X picks" row. Prune anything
        // for this theme not touched just now.
        Banner::where('placement', 'home_slider')->where('theme_id', $themeId)->whereNotIn('id', $keepIds)->delete();
    }

    private function coupon(): void
    {
        Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            ['name' => 'Welcome offer', 'type' => 'percentage', 'value' => 10, 'is_enabled' => true],
        );
    }

    private function legalPages(): void
    {
        $pages = [
            ['about', 'About us', "We started with a simple idea: quality products, honest prices, delivered fast.\n\nReplace this with your own story before launch."],
            ['contact', 'Contact us', "Questions about an order or a product? Email hello@example.com and we'll reply within one business day."],
            ['shipping-returns', 'Shipping & Returns', 'We ship worldwide with rates shown at checkout. Not in love with your order? Request a return within 14 days for a refund.'],
            ['privacy', 'Privacy Policy', 'We only use your information to fulfil orders and improve your experience. Replace with your own policy before launch.'],
            ['terms', 'Terms of Service', 'These demo terms outline how you may use this store. Replace this content with your own policies before launch.'],
        ];

        foreach ($pages as [$slug, $title, $body]) {
            Page::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title, 'type' => 'page', 'status' => 'published', 'published_at' => now(),
                    'blocks' => [['type' => 'text', 'settings' => ['body' => $body]]],
                ],
            );
        }

        $this->footerLinks($pages);
    }

    /**
     * Every theme's footer renders whatever links sit on the "footer" Menu —
     * an empty or stray one (e.g. leftover category links from a manual edit)
     * leaves the legal pages built above invisible on the storefront. Prune
     * anything that isn't one of our legal pages and (re)link all of them.
     *
     * @param  array<int, array{0:string,1:string,2:string}>  $pages
     */
    private function footerLinks(array $pages): void
    {
        $menu = Menu::firstOrCreate(['location' => 'footer'], ['name' => 'Footer menu']);
        $titles = array_column($pages, 1);

        $menu->items()->get()->each(function (MenuItem $item) use ($titles) {
            if (! in_array($item->label, $titles, true)) {
                $item->delete();
            }
        });

        foreach ($pages as $i => [$slug, $title]) {
            MenuItem::updateOrCreate(
                ['menu_id' => $menu->id, 'label' => $title],
                ['url' => url('/pages/'.$slug), 'position' => $i],
            );
        }
    }

    /**
     * Activate the pack's theme, seed its homepage sections, set the store name.
     *
     * @param  array<string, Category>  $categories
     * @param  array<string, Brand>  $brands
     * @param  array<string, string>  $heroCopy
     */
    private function applyLook(string $themeSlug, string $storeName, array $categories, array $brands, array $heroCopy): void
    {
        if ($theme = Theme::where('slug', $themeSlug)->first()) {
            $this->themes->activate($theme);
            $this->seedSections($theme, collect($categories)->pluck('id')->all(), collect($brands)->pluck('id')->all(), $heroCopy);
        }

        $this->settings->setMany(['general.store_name' => $storeName]);
    }

    /**
     * Seed the theme's homepage sections. Sections that list products, categories
     * or brands (featured_products, flash_sale, categories, brands) get pinned to
     * this pack's own IDs — otherwise every theme would draw from the whole shared
     * catalog and mix verticals (e.g. a sofa on the fashion homepage) once more
     * than one pack has been imported. The hero gets the pack's own heading/copy
     * instead of the generic "Welcome to our store" default.
     *
     * @param  array<int, int>  $categoryIds
     * @param  array<int, int>  $brandIds
     * @param  array<string, string>  $heroCopy
     */
    private function seedSections(Theme $theme, array $categoryIds, array $brandIds, array $heroCopy): void
    {
        $enabled = ThemeService::HOMEPAGE_LAYOUTS[$theme->slug] ?? array_keys(ThemeService::SECTIONS);
        $ordered = array_merge($enabled, array_values(array_diff(array_keys(ThemeService::SECTIONS), $enabled)));
        $scoped = ['featured_products' => 'scope_categories', 'flash_sale' => 'scope_categories', 'categories' => 'scope_categories', 'instagram' => 'scope_categories', 'brands' => 'scope_brands'];
        $scopeIds = ['scope_categories' => $categoryIds, 'scope_brands' => $brandIds];

        $position = 0;
        foreach ($ordered as $key) {
            $section = $theme->sections()->firstOrCreate(
                ['page' => 'home', 'section_key' => $key],
                ['position' => $position, 'is_enabled' => in_array($key, $enabled, true)],
            );

            if (isset($scoped[$key])) {
                $field = $scoped[$key];
                $section->update(['settings' => array_merge($section->settings ?? [], [$field => $scopeIds[$field]])]);
            }

            if ($key === 'hero' && $heroCopy !== []) {
                $section->update(['settings' => array_merge($section->settings ?? [], $heroCopy)]);
            }

            $position++;
        }
    }

    private function ensurePublicLink(): void
    {
        if (! is_link(public_path('storage')) && ! is_dir(public_path('storage'))) {
            Artisan::call('storage:link');
        }
    }
}
