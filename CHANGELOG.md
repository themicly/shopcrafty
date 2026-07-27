# Changelog

## [1.0.0] - 2026-07-27

### Added

- Added first-install demo packs for the Default, Boutique, and Marketplace themes.
- Added automatic Shopcrafty CSS and JavaScript integration to the host Vite graph during installation.
- Added Boutique as the default first-install theme with seeded homepage sections.
- Added host theme discovery with host themes taking priority over package themes.

### Changed

- Limited the built-in theme catalog to Default, Boutique, and Marketplace.
- Kept shared storefront fallback views in the official Default theme.
- Added optional add-on gating so core menus and storefront features appear only when their package is installed.
- Kept admin light mode as the default with dark mode available as a user preference.

### Fixed

- Fixed optional wishlist and compare services being resolved when their add-ons are absent.
- Fixed missing Vite manifests and asset imports in fresh Laravel installations.
- Fixed admin login controller autoloading and fresh-install storefront rendering.
