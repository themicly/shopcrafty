# Shopcrafty architecture

Shopcrafty is a Laravel package composed of self-contained modules. The core
service provider registers the modules, while each module owns its routes,
views, migrations, translations, and Livewire components.

The host application owns its database, environment, Vite build, storage, and
optional themes/ directory. Core views are loaded from the package and are not
published into the host application.

Optional features register themselves through AddonRegistry. Core guards
optional settings and storefront contributions through that registry; the
add-on owns its implementation and routes.
