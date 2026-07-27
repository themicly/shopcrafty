<?php

namespace Themicly\Shopcrafty\Core\Module;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Base service provider for every Shopcrafty module.
 *
 * Concrete module providers declare only their name; this base wires up the
 * module's routes, views, migrations, and translations by convention so each
 * module stays self-contained and removable (see docs/01-architecture.md).
 *
 * Extension points {@see registerModule()} and {@see bootModule()} let a module
 * add its own bindings, events, and — once Livewire is installed in Phase 0 —
 * component registration, without overriding the conventions above.
 */
abstract class ModuleServiceProvider extends ServiceProvider
{
    /** Studly module name, e.g. "Catalog". */
    abstract protected function moduleName(): string;

    /** Absolute path to the module directory (concrete provider passes __DIR__). */
    abstract protected function modulePath(): string;

    public function register(): void
    {
        $config = $this->modulePath().'/Config/'.$this->moduleKey().'.php';

        if (is_file($config)) {
            $this->mergeConfigFrom($config, $this->moduleKey());
        }

        $this->registerModule();
    }

    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadViewsFrom($this->modulePath().'/Resources/views', $this->moduleKey());
        $this->loadMigrationsFrom($this->modulePath().'/Database/Migrations');
        $this->loadTranslationsFrom($this->modulePath().'/Resources/lang', $this->moduleKey());
        $this->registerLivewireComponents();

        $this->bootModule();
    }

    /**
     * Auto-register a module's Livewire components under a namespaced alias, e.g.
     * app/Modules/Reports/Livewire/Dashboard.php -> <livewire:reports.dashboard />.
     */
    protected function registerLivewireComponents(): void
    {
        if (! class_exists(Livewire::class)) {
            return;
        }

        $directory = $this->modulePath().'/Livewire';

        if (! is_dir($directory)) {
            return;
        }

        $namespace = (new \ReflectionClass($this))->getNamespaceName().'\\Livewire\\';

        foreach (glob($directory.'/*.php') as $file) {
            $class = $namespace.pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($class)) {
                $alias = $this->moduleKey().'.'.Str::kebab(class_basename($class));
                Livewire::component($alias, $class);
            }
        }
    }

    /**
     * Storefront routes load under the `web` middleware group; admin routes load
     * under `web` + `auth` + the `access-admin` gate, with an `/admin` prefix and
     * `admin.` route-name prefix. The login/reset pages live in routes/web.php so
     * they stay outside this authenticated group.
     */
    protected function loadModuleRoutes(): void
    {
        $web = $this->modulePath().'/Routes/web.php';
        $admin = $this->modulePath().'/Routes/admin.php';

        if (is_file($web)) {
            Route::middleware('web')->group($web);
        }

        if (is_file($admin)) {
            Route::middleware(['web', 'auth', 'can:access-admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group($admin);
        }
    }

    protected function addonRegistry(): AddonRegistry
    {
        return $this->app->make(AddonRegistry::class);
    }

    /** Lowercase key used for view/translation namespaces and config, e.g. "catalog". */
    protected function moduleKey(): string
    {
        return Str::lower($this->moduleName());
    }

    /** Override to register container bindings, contracts, and singletons. */
    protected function registerModule(): void
    {
        // no-op by default
    }

    /** Override to register events, listeners, gates, and view composers. */
    protected function bootModule(): void
    {
        // no-op by default
    }
}
