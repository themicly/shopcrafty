<?php

namespace Themicly\Shopcrafty\Core\Module;

final class AddonRegistry
{
    /** @var array<string, array<string, mixed>> */
    private array $addons = [];

    /** @var array<string, array<string, mixed>> */
    private array $searchTypes = [];

    /** @var array<string, array<string, mixed>> */
    private array $settingsSchemas = [];

    /** @var array<string, array<string, array<string, mixed>>> */
    private array $storefrontFeatures = [];

    /** @param array<string, mixed> $meta */
    public function register(string $key, array $meta = []): void
    {
        $this->addons[$key] = [...($this->addons[$key] ?? []), ...$meta];
    }

    public function installed(string $key): bool
    {
        return isset($this->addons[$key]);
    }

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return $this->addons;
    }

    /** @param array<string, mixed> $definition */
    public function registerSearchType(string $key, array $definition): void
    {
        $this->searchTypes[$key] = $definition;
    }

    /** @return array<string, array<string, mixed>> */
    public function searchTypes(): array
    {
        return $this->searchTypes;
    }

    /** @param array<string, mixed> $schema */
    public function registerSettingsSchema(string $key, array $schema): void
    {
        $this->settingsSchemas[$key] = $schema;
    }

    /** @return array<string, array<string, mixed>> */
    public function settingsSchemas(): array
    {
        return $this->settingsSchemas;
    }

    /** @param array<string, mixed> $definition */
    public function registerStorefrontFeature(string $location, string $key, array $definition): void
    {
        $this->storefrontFeatures[$location][$key] = $definition;
    }

    /** @return array<string, array<string, mixed>> */
    public function storefrontFeatures(string $location): array
    {
        return $this->storefrontFeatures[$location] ?? [];
    }
}
