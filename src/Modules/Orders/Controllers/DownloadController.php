<?php

namespace Themicly\Shopcrafty\Modules\Orders\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Modules\Orders\Models\DownloadGrant;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class DownloadController
{
    /**
     * Public downloads page for an order. The order number is an unguessable
     * capability (same model as the thank-you / tracking pages), so a buyer who
     * has the confirmation link can reach their files without an account. Each
     * file link is a time-limited signed URL.
     */
    public function order(string $number)
    {
        $order = Order::where('number', $number)->firstOrFail();

        $lines = $this->linesFor($order);

        abort_if($lines->isEmpty(), 404);

        return View::make('theme::order-downloads', ['order' => $order, 'lines' => $lines]);
    }

    /**
     * Stream a granted file. Allowed either by a valid signature (from the
     * emailed/order-page link) or when the requester is the logged-in customer
     * who owns the order. Enforces per-grant download limit / expiry.
     */
    public function show(Request $request, DownloadGrant $grant)
    {
        $customerId = Auth::guard('customer')->id();
        $ownsOrder = $customerId && $grant->order && $grant->order->customer_id === $customerId;

        abort_unless($request->hasValidSignature() || $ownsOrder, 403);
        abort_unless($grant->isDownloadable(), 410);

        $file = $grant->file;
        abort_unless($file && Storage::disk($file->disk)->exists($file->path), 404);

        $grant->increment('download_count');

        return Storage::disk($file->disk)->download($file->path, $file->name);
    }

    /**
     * Build the download rows for one order: one entry per purchased digital
     * line, each with its license key and signed file links.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function linesFor(Order $order)
    {
        return $order->items()
            ->has('downloadGrants')
            ->with('downloadGrants.file')
            ->get()
            ->map(fn ($item) => [
                'name' => $item->name,
                'order_number' => $order->number,
                'license_key' => $item->license_key,
                'files' => $item->downloadGrants->map(fn (DownloadGrant $grant) => [
                    'name' => $grant->file?->name,
                    'size' => $grant->file?->humanSize(),
                    'downloadable' => $grant->isDownloadable(),
                    'url' => URL::temporarySignedRoute('storefront.download', now()->addDays(30), ['grant' => $grant->id]),
                ])->all(),
            ]);
    }
}
