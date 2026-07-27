<?php

namespace Themicly\Shopcrafty\Modules\Orders\Controllers;

use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Marketing\Services\BoughtTogether;
use Themicly\Shopcrafty\Modules\Orders\Contracts\ConfirmsReturnPayment;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentLogger;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentReconciler;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentRegistry;

class OrderController
{
    public function thankyou(string $number)
    {
        $order = Order::where('number', $number)->with(['items', 'shippingAddress'])->firstOrFail();

        // The order number is an unguessable capability; additionally, a logged-in
        // customer may only see their own orders (CUS-01 / ORD-03).
        if ($order->customer_id && auth('customer')->check() && auth('customer')->id() !== $order->customer_id) {
            abort(403);
        }

        $this->confirmReturnPayment($order);

        $suggestions = $this->crossSell($order);

        return View::make('theme::thankyou', compact('order', 'suggestions'));
    }

    /**
     * Standalone printable invoice — an on-screen preview (opened in a new tab
     * from the thank-you page) with its own Print / Save-as-PDF button. Same
     * capability check as the thank-you page: the order number is unguessable
     * and a logged-in customer may only view their own orders.
     */
    public function invoice(string $number)
    {
        $order = Order::where('number', $number)->with(['items', 'shippingAddress'])->firstOrFail();

        if ($order->customer_id && auth('customer')->check() && auth('customer')->id() !== $order->customer_id) {
            abort(403);
        }

        return View::make('theme::invoice-print', compact('order'));
    }

    /**
     * Webhook-less confirmation: when the shopper lands back here unpaid and
     * the gateway can verify the return (Stripe checks the session via its
     * API), mark the order paid. The webhook remains the primary path; this
     * one covers installs it can't reach. Never breaks the thank-you page.
     */
    protected function confirmReturnPayment(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        try {
            $gateway = app(PaymentRegistry::class)->find((string) $order->payment_method);

            if ($gateway instanceof ConfirmsReturnPayment) {
                $confirmed = $gateway->confirmReturn($order, request());

                if ($confirmed) {
                    app(PaymentReconciler::class)->markPaid($order);
                    $order->refresh();
                }

                app(PaymentLogger::class)->record((string) $order->payment_method, 'return_confirm', $confirmed, [
                    'order' => $order,
                    'message' => $confirmed
                        ? 'Return URL confirmed payment for '.$order->number
                        : 'Return URL did not confirm payment (unpaid session or mismatched order).',
                    'context' => [
                        'order_number' => $order->number,
                        'session_id' => (string) request()->query('session_id'),
                        'matched' => $confirmed,
                    ],
                ]);
            }
        } catch (\Throwable $e) {
            report($e); // stay unpaid; the webhook or admin mark-as-paid can still reconcile
        }
    }

    /** Products frequently bought with what was just ordered (fallback to newest). */
    protected function crossSell(Order $order)
    {
        $orderedIds = $order->items->pluck('product_id')->filter()->all();
        $bought = app(BoughtTogether::class);

        $suggestions = collect($orderedIds)
            ->flatMap(fn ($id) => $bought->forProduct((int) $id, 4))
            ->unique('id')
            ->reject(fn ($p) => in_array($p->id, $orderedIds, true))
            ->take(4)
            ->values();

        if ($suggestions->isEmpty()) {
            $suggestions = Product::active()
                ->whereNotIn('id', $orderedIds)->with('media')->latest()->limit(4)->get();
        }

        return $suggestions;
    }

    public function track()
    {
        return View::make('theme::track');
    }
}
