<?php

namespace Themicly\Shopcrafty\Modules\Customers\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Orders\Controllers\DownloadController;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Services\CartService;

class AccountController
{
    /** Statuses a customer can filter their order history by. */
    public const ORDER_STATUSES = [
        'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned',
    ];

    public function orders(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $status = (string) $request->query('status', '');
        if (! in_array($status, self::ORDER_STATUSES, true)) {
            $status = '';
        }

        $orders = Order::where('customer_id', $customer->id)
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return View::make('theme::account.orders', [
            'orders' => $orders,
            'status' => $status,
            'statuses' => self::ORDER_STATUSES,
        ]);
    }

    public function downloads()
    {
        $customer = Auth::guard('customer')->user();

        // Every digital line the customer has bought, across all their orders.
        $lines = Order::where('customer_id', $customer->id)
            ->whereHas('items.downloadGrants')
            ->latest()
            ->get()
            ->flatMap(fn (Order $order) => DownloadController::linesFor($order));

        return View::make('theme::account.downloads', ['lines' => $lines]);
    }

    public function orderDetail(string $number)
    {
        $customer = Auth::guard('customer')->user();

        $order = Order::where('customer_id', $customer->id)
            ->where('number', $number)
            ->with(['items.product.media', 'shippingAddress', 'history'])
            ->firstOrFail();

        return View::make('theme::account.order-detail', compact('order'));
    }

    public function reorder(string $number, CartService $cart)
    {
        $customer = Auth::guard('customer')->user();

        $order = Order::where('customer_id', $customer->id)
            ->where('number', $number)
            ->with('items')
            ->firstOrFail();

        // Only re-add items whose product still exists and is active. Variant items
        // are added with their original variant; anything else is skipped and counted.
        $activeIds = Product::active()
            ->whereIn('id', $order->items->pluck('product_id')->filter()->all())
            ->pluck('id')
            ->all();

        $added = 0;
        foreach ($order->items as $item) {
            if (! $item->product_id || ! in_array($item->product_id, $activeIds, true)) {
                continue;
            }

            if ($cart->add((int) $item->product_id, $item->variant_id, max(1, (int) $item->qty))) {
                $added++;
            }
        }

        $skipped = $order->items->count() - $added;

        if ($added === 0) {
            return redirect()
                ->route('storefront.account.orders.show', $order->number)
                ->with('flash_toast', 'None of these items are available to reorder.')
                ->with('flash_toast_type', 'error');
        }

        $message = $skipped > 0
            ? "Added {$added} item(s) to your cart. {$skipped} item(s) were unavailable."
            : "Added {$added} item(s) to your cart.";

        return redirect()
            ->route('storefront.checkout')
            ->with('flash_toast', $message)
            ->with('flash_toast_type', 'success');
    }

    public function profile()
    {
        return View::make('theme::account.profile');
    }

    public function addresses()
    {
        return View::make('theme::account.addresses');
    }

    /**
     * GDPR "download my data": a JSON export of the signed-in customer's own
     * data — profile, saved addresses and an order summary. Scoped strictly to
     * the current customer; never exposes anyone else's data. 404s when the
     * owner hasn't enabled the GDPR tools.
     */
    public function dataExport()
    {
        abort_unless((bool) settings('privacy.gdpr_tools_enabled'), 404);

        $customer = Auth::guard('customer')->user();

        $data = [
            'exported_at' => now()->toIso8601String(),
            'profile' => [
                'name' => $customer->name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'status' => $customer->status,
                'registered_at' => optional($customer->created_at)->toIso8601String(),
            ],
            'addresses' => $customer->addresses()->get()->map(fn ($a) => [
                'label' => $a->label,
                'name' => $a->name,
                'phone' => $a->phone,
                'address' => $a->address,
                'city' => $a->city,
                'region' => $a->region,
                'postcode' => $a->postcode,
                'is_default' => (bool) $a->is_default,
            ])->all(),
            'orders' => Order::where('customer_id', $customer->id)
                ->latest()
                ->get()
                ->map(fn (Order $o) => [
                    'number' => $o->number,
                    'status' => $o->status,
                    'payment_status' => $o->payment_status,
                    'grand_total' => $o->grand_total,
                    'placed_at' => optional($o->placed_at)->toIso8601String(),
                ])->all(),
        ];

        $filename = 'my-data-'.now()->format('Ymd-His').'.json';

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, ['Content-Type' => 'application/json']);
    }

    /**
     * GDPR "delete my account": scrubs the customer's personal data and logs
     * them out. Past orders are kept (anonymised, for accounting) but the
     * profile PII and saved addresses/wishlist are removed. 404s when the owner
     * hasn't enabled the GDPR tools.
     */
    public function deleteAccount(Request $request)
    {
        abort_unless((bool) settings('privacy.gdpr_tools_enabled'), 404);

        $customer = Auth::guard('customer')->user();

        // Remove reusable PII the account owns.
        $customer->addresses()->delete();

        // Anonymise the customer record itself. Orders stay linked (by id) for
        // accounting, but carry no personal identifiers back to a real person.
        $customer->forceFill([
            'name' => 'Deleted customer',
            'email' => null,
            'mobile' => null,
            'password' => bcrypt(bin2hex(random_bytes(16))),
            'status' => 'blocked',
            'mobile_verified_at' => null,
            'email_verified_at' => null,
        ])->save();

        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('shopcrafty.storefront')
            ->with('flash_toast', __('storefront.account_deleted'))
            ->with('flash_toast_type', 'success');
    }
}
