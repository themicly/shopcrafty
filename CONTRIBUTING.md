# Contributing

Thanks for contributing to Shopcrafty.

## Development

1. Install PHP 8.3+, Composer, and this package's dependencies.
2. Keep changes within this package's scope.
3. Keep optional functionality, routes, migrations, views, and navigation inside its addon package.
4. Run the package test suite before opening a pull request:

```bash
composer install
vendor/bin/phpunit -c phpunit.xml.dist
```

For the core package, also run the fresh Laravel scratch application when a change affects installation, routes, themes, assets, or database behavior.

## Pull requests

- Explain the behavior change and affected package.
- Add or update tests for changed behavior.
- Do not commit generated dependencies, local environment files, credentials, or published host resources.
- Keep pull requests small and independently reviewable.
- Call out migrations, breaking changes, and required installation steps.
