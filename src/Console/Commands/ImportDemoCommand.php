<?php

namespace Themicly\Shopcrafty\Console\Commands;

use Illuminate\Console\Command;
use Themicly\Shopcrafty\Modules\Settings\Services\DemoImporter;

final class ImportDemoCommand extends Command
{
    protected $signature = 'shopcrafty:demo
        {pack? : Demo pack to import (default, boutique, or market)}
        {--list : List available demo packs without importing one}';

    protected $description = 'Import a complete Shopcrafty demo storefront';

    public function handle(DemoImporter $importer): int
    {
        if ($this->option('list')) {
            $this->table(
                ['Pack', 'Name', 'Theme', 'Description'],
                collect($importer->packs())->map(fn (array $pack, string $key): array => [
                    $key,
                    $pack['label'],
                    $pack['theme'],
                    $pack['description'],
                ])->all(),
            );

            return self::SUCCESS;
        }

        $pack = (string) ($this->argument('pack') ?: 'default');

        if (! $importer->has($pack)) {
            $this->components->error("Unknown demo pack [{$pack}]. Use --list to see available packs.");

            return self::FAILURE;
        }

        $this->components->info("Importing the [{$pack}] demo pack...");
        $importer->import($pack);
        $this->components->info('Demo storefront imported successfully.');

        return self::SUCCESS;
    }
}
