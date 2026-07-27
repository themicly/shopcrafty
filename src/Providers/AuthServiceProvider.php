<?php

namespace Themicly\Shopcrafty\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Themicly\Shopcrafty\Models\User;

/**
 * Central home for authorization gates. Module policies register themselves in
 * their own service providers; app-wide gates live here.
 */
class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Guards the entire admin panel. Applied as `can:access-admin` on the
        // admin route group (see Themicly\Shopcrafty\Core\Module\ModuleServiceProvider).
        Gate::define('access-admin', fn (User $user) => $user->canAccessAdmin());

        // Owner-only areas: store configuration and provider credentials (payment
        // keys, notification gateways). Staff run the day-to-day store but can't
        // change these. Applied as `can:manage-config` on those route groups.
        Gate::define('manage-config', fn (User $user) => $user->isOwner());

        // Granular per-module staff permissions. Owners pass everything; staff pass
        // only granted keys. Applied as `can:manage-{key}` on module route groups
        // and in the sidebar nav. See User::PERMISSIONS.
        foreach (array_keys(User::PERMISSIONS) as $key) {
            Gate::define("manage-{$key}", fn (User $user) => $user->hasPermission($key));
        }
    }
}
