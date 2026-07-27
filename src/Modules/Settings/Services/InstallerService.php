<?php

namespace Themicly\Shopcrafty\Modules\Settings\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use PDO;
use Throwable;

/**
 * Powers the web installer: environment checks, a database connection test, an
 * atomic .env writer, and the install lock. Designed to run on plain shared
 * hosting with no shell — the whole setup happens in the browser.
 */
class InstallerService
{
    /** Minimum PHP the app supports — must match composer.json's "php" constraint. */
    public const MIN_PHP = '8.3.0';

    /** PHP extensions Laravel + this app rely on. */
    public const REQUIRED_EXTENSIONS = [
        'pdo', 'mbstring', 'openssl', 'tokenizer', 'json', 'ctype', 'fileinfo', 'curl', 'gd',
    ];

    /** The lock file. Its presence means setup is complete. */
    public static function lockPath(): string
    {
        return storage_path('installed');
    }

    public static function installed(): bool
    {
        return File::exists(self::lockPath());
    }

    public function markInstalled(): void
    {
        File::put(self::lockPath(), 'installed '.now()->toIso8601String().PHP_EOL);

        // Demo import (DemoImporter::ensurePublicLink) also creates this link,
        // but only runs when a demo pack is chosen — buyers who skip it would
        // otherwise never get a working public disk for uploaded media.
        $this->ensureStorageLink();
    }

    /** Idempotent: no-op if the link (or a real directory) already exists. */
    private function ensureStorageLink(): void
    {
        if (! is_link(public_path('storage')) && ! is_dir(public_path('storage'))) {
            Artisan::call('storage:link');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Versioning & upgrades
    |--------------------------------------------------------------------------
    | CodeCanyon buyers upgrade by re-uploading files over FTP, which overwrites
    | everything on disk — so the *installed* version can't live in a file. It is
    | stamped in the DB (settings key "system.installed_version"), which survives
    | the re-upload. Compared against the shipped config version, this tells us
    | whether new migrations are waiting to run.
    */

    /** The version shipped in the current set of files. */
    public function appVersion(): string
    {
        return (string) config('shopcrafty.version', '1.0.0');
    }

    /** The version stamped in the DB at last install/upgrade (null if never stamped). */
    public function installedVersion(): ?string
    {
        try {
            $value = app(Settings::class)->get('system.installed_version');

            return $value !== null ? (string) $value : null;
        } catch (Throwable $e) {
            return null;
        }
    }

    /** Record the installed version in the DB (defaults to the shipped version). */
    public function stampVersion(?string $version = null): void
    {
        app(Settings::class)->set('system.installed_version', $version ?? $this->appVersion());
    }

    /**
     * Is a newer version shipped than what's stamped in the DB? An un-stamped
     * store (legacy install predating versioning) counts as needing an upgrade.
     */
    public function upgradeAvailable(): bool
    {
        $installed = $this->installedVersion();

        return $installed === null || version_compare($installed, $this->appVersion(), '<');
    }

    /**
     * How many migration files have not been run yet. Best-effort and never
     * throws — the count is informational; migrate --force is the source of truth.
     */
    public function pendingMigrations(): int
    {
        try {
            $migrator = app('migrator');
            $repository = $migrator->getRepository();

            if (! $repository->repositoryExists()) {
                return 0;
            }

            $paths = array_merge([database_path('migrations')], $migrator->paths());
            $files = $migrator->getMigrationFiles($paths);
            $ran = $repository->getRan();

            return count(array_diff(array_keys($files), $ran));
        } catch (Throwable $e) {
            return 0;
        }
    }

    /**
     * Run pending migrations, clear caches, and stamp the new version. Idempotent:
     * with nothing pending it is a harmless no-op that reports "already up to date".
     * Never wipes data and never re-runs the installer.
     *
     * @return array{ok:bool, message:string, migrated:bool}
     */
    public function runUpgrade(): array
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output());

            // A re-upload over FTP never carries the public/storage symlink —
            // recreate it if a prior install/upgrade never had (or lost) it.
            $this->ensureStorageLink();

            // Rebuild caches so newly shipped code/config/views take effect.
            Artisan::call('optimize:clear');
            Artisan::call('view:clear');

            $this->stampVersion();

            $migrated = ! str_contains($output, 'Nothing to migrate');

            return [
                'ok' => true,
                'migrated' => $migrated,
                'message' => $migrated
                    ? 'Upgrade complete — pending migrations were applied and the store is now on version '.$this->appVersion().'.'
                    : 'Already up to date — no pending migrations. Version stamped to '.$this->appVersion().'.',
            ];
        } catch (Throwable $e) {
            return [
                'ok' => false,
                'migrated' => false,
                'message' => 'Upgrade failed: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Environment readiness rows for the requirements step.
     *
     * @return array<int, array{label:string, ok:bool, hint:string}>
     */
    public function requirements(): array
    {
        $rows = [[
            'label' => 'PHP '.self::MIN_PHP.'+',
            'ok' => version_compare(PHP_VERSION, self::MIN_PHP, '>='),
            'hint' => 'Running '.PHP_VERSION,
        ]];

        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $rows[] = [
                'label' => 'ext-'.$ext,
                'ok' => extension_loaded($ext),
                'hint' => extension_loaded($ext) ? 'Loaded' : 'Missing — enable it in php.ini',
            ];
        }

        foreach (['storage' => storage_path(), 'bootstrap/cache' => base_path('bootstrap/cache'), '.env' => base_path('.env')] as $label => $path) {
            $rows[] = [
                'label' => $label.' writable',
                'ok' => is_writable($path),
                'hint' => is_writable($path) ? 'Writable' : 'Make this path writable (chmod 775)',
            ];
        }

        return $rows;
    }

    public function requirementsPass(): bool
    {
        foreach ($this->requirements() as $row) {
            if (! $row['ok']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Try to open a PDO connection with the given credentials.
     *
     * @return array{ok:bool, message:string}
     */
    public function testDatabase(string $host, string $port, string $database, string $username, string $password): array
    {
        try {
            new PDO(
                "mysql:host={$host};port={$port};dbname={$database}",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5],
            );

            return ['ok' => true, 'message' => 'Connected successfully.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => $this->friendlyDbError($e->getMessage())];
        }
    }

    /**
     * Fresh installs are frequently run by non-technical buyers over cPanel —
     * a raw PDO/SQLSTATE message ("SQLSTATE[HY000] [1045] Access denied for
     * user...") reads as a crash even though the app handled it gracefully.
     * Map the handful of causes that account for nearly every real support
     * ticket to plain English; anything unrecognized still shows the original
     * message in full, so nothing is ever hidden.
     */
    private function friendlyDbError(string $message): string
    {
        return match (true) {
            str_contains($message, '1045') => 'Access denied — double-check the database username and password.',
            str_contains($message, '1044') => 'That user doesn\'t have access to that database — check the database name, or that the user is granted to it (most hosts do this from cPanel → MySQL Databases).',
            str_contains($message, '1049') || str_contains($message, 'Unknown database') => 'That database doesn\'t exist yet — create it first (most hosts do this from cPanel → MySQL Databases), then try again.',
            str_contains($message, 'getaddrinfo') || str_contains($message, 'Name or service not known') => 'Couldn\'t find that database host — check the hostname your host gave you (often "localhost" or "127.0.0.1").',
            str_contains($message, 'Connection refused') => 'The database server refused the connection — check the host and port are correct.',
            str_contains($message, 'timed out') || str_contains($message, 'timeout') => 'Connecting to the database timed out — check the host/port, or that your host allows outbound connections from this server.',
            default => $message,
        };
    }

    /**
     * Atomically merge key/value pairs into the .env file (values are quoted).
     * Existing keys are replaced in place; new keys are appended.
     *
     * @param  array<string, string>  $values
     */
    public function writeEnv(array $values, ?string $path = null): void
    {
        $path ??= base_path('.env');
        $contents = File::exists($path) ? File::get($path) : '';

        foreach ($values as $key => $value) {
            $line = $key.'='.$this->quote($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            $contents = preg_match($pattern, $contents)
                ? preg_replace($pattern, $line, $contents)
                : rtrim($contents, "\n")."\n".$line."\n";
        }

        // Write to a temp file first, then rename — never leave a half-written .env.
        $tmp = $path.'.'.getmypid().'.tmp';
        File::put($tmp, $contents);
        File::move($tmp, $path);
    }

    private function quote(string $value): string
    {
        return preg_match('/\s|#|"|\'/', $value) ? '"'.addcslashes($value, '"\\').'"' : $value;
    }
}
