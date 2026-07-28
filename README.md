# Shopcrafty

Open-source Laravel e-commerce core by Themicly.

This package is the required foundation for a Shopcrafty store. Optional features are
published as independent packages such as `themicly/shopcrafty-wishlist` and
`themicly/shopcrafty-backup`.

## Install

```bash
composer require themicly/shopcrafty
php artisan shopcrafty:install
```

Themes, views, and admin resources are loaded directly from the package and are
never published into the host application. Import the package Vite entries into
the host application's existing `resources/css/app.css` and `resources/js/app.js`
so Shopcrafty is included in the same build and manifest.

The package is MIT licensed and targets PHP 8.3+ and Laravel 13+.

The installer publishes configuration, adds the package's Vite entries to the host,
and runs `npm install` followed by `npm run build`. Use `--store-name` and
`--currency` to initialize the store. Installations start empty; sample content is
not bundled with the core package.

### Requirements

- PHP 8.3 or newer
- Laravel 13 and Livewire 4
- PDO, mbstring, OpenSSL, tokenizer, JSON, ctype, fileinfo, curl, and GD
- Writable storage/ and bootstrap/cache/ directories
- Node.js and npm for the frontend asset build
- A host Vite build that imports the package entries:

    /* resources/css/app.css */
    @import "../../vendor/themicly/shopcrafty/resources/assets/shopcrafty.css";

    // resources/js/app.js
    import "../../vendor/themicly/shopcrafty/resources/assets/shopcrafty.js";

The relative paths may differ when the host uses a different Composer vendor
layout; resolve them to the installed package resources/assets directory.

### Host themes and overrides

Add themes to the host application's themes/{slug} directory. Each theme needs
a theme.json manifest and a views/ directory. Shopcrafty merges themes in this
priority order:

1. host application themes/ (or shopcrafty.themes_path when configured)
2. the package's open-source themes/ directory
3. the package's bundled vendor themes

If two themes use the same slug, the host theme wins. Run
php artisan shopcrafty:install on a new store, or open Admin → Themes, to
discover newly added themes.

### Upgrades and package boundaries

Theme settings, sections, media, and store data live in the host database and
storage, not in published package views. Package updates replace code and
bundled themes while preserving those records. Optional functionality,
migrations, routes, views, and navigation belong to their own add-on packages.

## Extending admin navigation

Core modules and add-ons can extend the admin UI without editing the core sidebar:

```php
app(\Themicly\Shopcrafty\Core\Navigation\NavigationRegistry::class)
    ->register('main', [
        'label' => 'Reviews',
        'icon' => 'sparkles',
        'route' => 'admin.reviews.index',
        'gate' => 'manage-content',
    ], group: 'Commerce');

app(\Themicly\Shopcrafty\Core\Navigation\NavigationRegistry::class)
    ->register('index:products', [
        'label' => 'Import reviews',
        'route' => 'admin.reviews.import',
    ]);
```

Use `main`, `footer`, and `quick_create` for global menus. Use `index:<key>` for
menus owned by a specific index page.

## Reflecting installed add-ons

An add-on registers its capability metadata from its service provider. Core
renders only registered storefront contributions, and Settings → Add-ons lists
the installed package and its configuration fields:

```php
$addons = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class);
$addons->register('reviews', ['name' => 'Reviews']);
$addons->registerSettingsSchema('reviews', [
    'label' => 'Reviews settings',
    'fields' => ['catalog.reviews_enabled'],
]);
$addons->registerStorefrontFeature('product', 'reviews', [
    'label' => 'Reviews',
    'route' => 'storefront.product',
]);
```

This keeps optional package behavior discoverable while allowing each theme to
choose how a registered storefront location is presented.
