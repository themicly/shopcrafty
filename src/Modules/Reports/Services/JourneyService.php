<?php

namespace Themicly\Shopcrafty\Modules\Reports\Services;

use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Themes\Models\Banner;
use Themicly\Shopcrafty\Modules\Themes\Models\ThemeSetting;

/**
 * Adaptive "guided journey" — surfaces the next best actions for a store owner.
 * Not a blocking onboarding: it simply reflects real state and points forward.
 */
class JourneyService
{
    /** @return array<int, array{key:string,label:string,description:string,done:bool,route:string,icon:string,color:string}> */
    public function steps(): array
    {
        $steps = [
            [
                'key' => 'store', 'label' => 'Set up your store',
                'description' => 'Add your store name, contact, and WhatsApp.',
                'done' => filled(settings('general.store_email')),
                'route' => 'admin.settings.index', 'icon' => 'settings', 'color' => 'primary',
            ],
            [
                'key' => 'product', 'label' => 'Add your first product',
                'description' => 'Create a product with photos and pricing.',
                'done' => Product::query()->exists(),
                'route' => 'admin.catalog.products.create', 'icon' => 'products', 'color' => 'info',
            ],
            [
                'key' => 'banner', 'label' => 'Publish a homepage banner',
                'description' => 'Promote a collection or a sale up top.',
                'done' => Banner::query()->exists(),
                'route' => 'admin.banners.index', 'icon' => 'image', 'color' => 'warning',
            ],
            [
                'key' => 'theme', 'label' => 'Customize your storefront',
                'description' => 'Make it yours — colors, fonts, sections.',
                'done' => ThemeSetting::query()->exists(),
                'route' => 'admin.themes.customize', 'icon' => 'themes', 'color' => 'success',
            ],
            [
                'key' => 'order', 'label' => 'Get your first order',
                'description' => 'Share your store and start selling.',
                'done' => Order::query()->exists(),
                'route' => 'admin.orders.index', 'icon' => 'orders', 'color' => 'danger',
            ],
        ];

        return $steps;
    }

    public function completed(): int
    {
        return collect($this->steps())->where('done', true)->count();
    }

    public function total(): int
    {
        return count($this->steps());
    }

    public function isComplete(): bool
    {
        return $this->completed() === $this->total();
    }
}
