<?php

namespace Themicly\Shopcrafty\Core\Navigation;

final class NavigationRegistry
{
    /** @var array<string, array<int, array<string, mixed>>> */
    private array $groups;

    /** @var array<int, array<string, mixed>> */
    private array $footer;

    /** @var array<int, array<string, mixed>> */
    private array $quickCreate;

    /** @var array<string, array<int, array<string, mixed>>> */
    private array $indexMenus = [];

    public function __construct()
    {
        $this->groups = collect((array) config('navigation.main', []))
            ->mapWithKeys(fn (array $group) => [$group['label'] => $group['items'] ?? []])
            ->all();
        $this->footer = (array) config('navigation.footer', []);
        $this->quickCreate = (array) config('navigation.quick_create', []);
    }

    /** Add an item to a sidebar group, footer, quick-create menu, or index menu. */
    public function register(string $location, array $item, ?string $group = null): void
    {
        if ($location === 'footer') {
            $this->footer[] = $item;

            return;
        }

        if ($location === 'quick_create') {
            $this->quickCreate[] = $item;

            return;
        }

        if (str_starts_with($location, 'index:')) {
            $this->indexMenus[substr($location, 6)][] = $item;

            return;
        }

        $this->groups[$group ?: $location] ??= [];
        $this->groups[$group ?: $location][] = $item;
    }

    /** @return array<int, array{label:string, items:array}> */
    public function main(): array
    {
        return collect($this->groups)
            ->map(fn (array $items, string $label) => ['label' => $label, 'items' => $items])
            ->values()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    public function footer(): array
    {
        return $this->footer;
    }

    /** @return array<int, array<string, mixed>> */
    public function quickCreate(): array
    {
        return $this->quickCreate;
    }

    /** @return array<int, array<string, mixed>> */
    public function indexMenu(string $key): array
    {
        return $this->indexMenus[$key] ?? [];
    }
}
