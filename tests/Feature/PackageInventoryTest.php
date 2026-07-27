<?php

use Illuminate\Support\Facades\Route;
use Themicly\Shopcrafty\Tests\TestCase;

final class PackageInventoryTest extends TestCase
{
    public function test_every_named_package_type_is_autoloadable(): void
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__.'/../../src'));
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }
        $types = [];

        foreach ($files as $file) {
            $tokens = token_get_all((string) file_get_contents($file));
            $namespace = '';

            for ($i = 0, $length = count($tokens); $i < $length; $i++) {
                $token = $tokens[$i];

                if (is_array($token) && $token[0] === T_NAMESPACE) {
                    $namespace = '';
                    for ($i++; $i < $length; $i++) {
                        $part = $tokens[$i];
                        if (is_array($part) && in_array($part[0], [T_STRING, T_NAME_QUALIFIED], true)) {
                            $namespace .= $part[1];
                        } elseif ($part === ';' || $part === '{') {
                            break;
                        }
                    }
                }

                if (is_array($token) && in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT, T_ENUM], true)) {
                    $previous = $tokens[$i - 1] ?? null;
                    if ($token[0] === T_CLASS && ($previous === '::' || (is_array($previous) && $previous[0] === T_DOUBLE_COLON))) {
                        continue;
                    }

                    for ($j = $i + 1; $j < $length; $j++) {
                        if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                            $types[] = $namespace.'\\'.$tokens[$j][1];
                            break;
                        }
                        if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }
            }
        }

        $types = array_values(array_unique(array_filter($types, fn (string $type) => str_starts_with($type, 'Themicly\\Shopcrafty\\'))));

        $this->assertGreaterThan(150, count($types));

        foreach ($types as $type) {
            $this->assertTrue(
                class_exists($type) || interface_exists($type) || trait_exists($type),
                "Package type {$type} is not autoloadable.",
            );
        }
    }

    public function test_core_route_contracts_are_registered_and_optional_routes_are_absent(): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->mapWithKeys(fn ($route) => [$route->getName() => true])
            ->filter(fn ($value, $name) => $name !== null);

        foreach ([
            'shopcrafty.storefront', 'login', 'admin.dashboard', 'admin.catalog.products.index',
            'admin.orders.index', 'admin.settings.index', 'admin.themes.index', 'storefront.shop',
        ] as $name) {
            $this->assertTrue($routes->has($name), "Required route {$name} is missing.");
        }

        $this->assertFalse($routes->has('admin.catalog.size-charts.index'));
        $this->assertFalse($routes->has('admin.reviews.index'));
    }
}
