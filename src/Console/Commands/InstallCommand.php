<?php

namespace Themicly\Shopcrafty\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Themicly\Shopcrafty\Models\User;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

final class InstallCommand extends Command
{
    protected $signature = 'shopcrafty:install
        {--store-name= : Store name}
        {--currency=USD : Base currency}
        {--admin-email= : Administrator email address}
        {--admin-password= : Administrator password}';

    protected $description = 'Install Shopcrafty into the host Laravel application';

    public function handle(): int
    {
        $this->call('migrate', ['--force' => true]);
        $this->call('vendor:publish', ['--tag' => 'shopcrafty-config']);
        $this->call('storage:link', ['--force' => true]);

        [$adminEmail, $adminPassword, $generatedPassword] = $this->adminCredentials();

        $admin = User::firstOrNew(['email' => $adminEmail]);
        $isNewAdmin = ! $admin->exists;
        $admin->name ??= 'Shopcrafty Admin';
        $admin->role = User::ROLE_OWNER;
        $admin->status = 'active';
        if ($isNewAdmin || $this->option('admin-password')) {
            $admin->password = $adminPassword;
        } else {
            $generatedPassword = false;
        }
        $admin->save();

        app(Settings::class)->setMany([
            'general.store_name' => $this->option('store-name') ?: config('shopcrafty.store_name'),
            'localization.currency_code' => $this->option('currency') ?: 'USD',
        ]);

        app(ThemeService::class)->syncFromDisk();

        $this->components->info('Shopcrafty installed successfully.');

        if ($generatedPassword) {
            $this->components->warn("Admin email: {$adminEmail}");
            $this->components->warn("Admin password: {$adminPassword}");
            $this->components->warn('Change this default password after signing in.');
        } else {
            $this->components->info("Admin email: {$adminEmail}");
        }

        return self::SUCCESS;
    }

    /** @return array{0: string, 1: string, 2: bool} */
    private function adminCredentials(): array
    {
        $defaultEmail = (string) config('shopcrafty.admin_email', 'admin@example.com');
        $optionEmail = trim((string) $this->option('admin-email'));
        $email = $optionEmail ?: ($this->input->isInteractive() ? trim((string) $this->ask('Admin email', $defaultEmail)) : $defaultEmail);

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('A valid admin email address is required.');

            throw new \InvalidArgumentException('Invalid admin email address.');
        }

        $optionPassword = (string) $this->option('admin-password');
        if ($optionPassword !== '') {
            return [$email, $optionPassword, false];
        }

        $password = $this->input->isInteractive()
            ? (string) $this->secret('Admin password (leave blank to generate one)')
            : '';

        if ($password !== '') {
            return [$email, $password, false];
        }

        return [$email, (string) config('shopcrafty.admin_password', 'password'), true];
    }
}
