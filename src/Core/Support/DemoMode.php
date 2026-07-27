<?php

namespace Themicly\Shopcrafty\Core\Support;

use Illuminate\Database\Eloquent\Model;
use Themicly\Shopcrafty\Modules\Orders\Models\Cart;
use Themicly\Shopcrafty\Modules\Orders\Models\CartItem;
use Themicly\Shopcrafty\Modules\Settings\Services\InstallerService;

/**
 * Public demo installs let real visitors click around a shared admin and
 * storefront. This blocks Eloquent writes made during an actual request so
 * nothing persists, while leaving read-only interactions (search, filters,
 * pagination) alone even though Livewire also sends those as POSTs — they
 * never call save()/delete(), so the guard below never sees them. Session/auth
 * bookkeeping (e.g. remember-token cycling on login) is allow-listed so
 * visitors can still sign in.
 *
 * Cart and CartItem are allow-listed by model too: a cart is ephemeral,
 * per-session shopping state keyed by a random token, not a real business
 * record — a demo visitor should be able to add/remove/update items and see
 * the cart drawer work like the real thing. The actual point of no return is
 * placing the order, which PlaceOrder guards explicitly with its own message.
 */
class DemoMode
{
    protected const ALLOWED_DIRTY_KEYS = ['remember_token', 'last_login_at', 'email_verified_at'];

    protected const ALLOWED_MODELS = [Cart::class, CartItem::class];

    public static function enabled(): bool
    {
        return (bool) config('demo.enabled', false);
    }

    /**
     * Whether a storefront visitor may preview a different theme, language,
     * or text direction for just this request via unsigned ?theme=/?lang=/
     * ?dir= query params (see ApplyThemePreview, SetStorefrontLocale, and the
     * text_direction() helper) — never persisted. Implied by full demo mode,
     * but can also be enabled on its own via DEMO_UI_MODE, independently of
     * whether writes are being blocked.
     */
    public static function uiEnabled(): bool
    {
        return static::enabled() || (bool) config('demo.ui_enabled', false);
    }

    /** Whether saving this model right now should be blocked. */
    public static function blocksSave(Model $model): bool
    {
        if (! static::active() || in_array($model::class, static::ALLOWED_MODELS, true)) {
            return false;
        }

        return array_diff(array_keys($model->getDirty()), static::ALLOWED_DIRTY_KEYS) !== [];
    }

    /** Whether deleting this model right now should be blocked. */
    public static function blocksDelete(Model $model): bool
    {
        if (in_array($model::class, static::ALLOWED_MODELS, true)) {
            return false;
        }

        return static::active();
    }

    /** General-purpose check for call sites that want to fail fast with a custom message (e.g. order placement). */
    public static function blocksAction(): bool
    {
        return static::active();
    }

    /**
     * Demo mode is on, the store is installed, and this is a real visitor
     * write request. Gating on the HTTP verb (rather than e.g.
     * app()->runningInConsole()) is what keeps artisan commands, seeders and
     * queue workers unaffected: none of them ever have a POST/PUT/PATCH/DELETE
     * request bound in the container, so this is false for all of them without
     * needing a separate console check.
     */
    protected static function active(): bool
    {
        if (! static::enabled() || ! InstallerService::installed()) {
            return false;
        }

        $request = request();

        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }
}
