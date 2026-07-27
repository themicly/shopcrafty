<?php

namespace Themicly\Shopcrafty\Modules\Orders\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Themicly\Shopcrafty\Core\Support\DemoMode;
use Themicly\Shopcrafty\Core\Support\DemoModeException;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Orders\Events\OrderPlaced;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Services\TaxService;

/**
 * Creates an order the owner is taking by phone/WhatsApp/in person. Mirrors the
 * storefront tax + stock policy: prepaid methods reserve stock now, COD defers
 * to confirmation. Fires OrderPlaced so the usual notifications run.
 *
 * @phpstan-type Line array{product_id:int, qty:int}
 */
class CreateManualOrder
{
    public function __construct(protected CommitStock $commit) {}

    /**
     * @param  array{lines:array<int, array{product_id:int, qty:int}>, name:string, phone:?string, email:?string, address:?string, city:?string, region:?string, payment_method:string, notes:?string, shipping_total?:int, customer_id?:?int}  $data
     */
    public function handle(array $data): Order
    {
        // Fail fast with a clear, order-specific message before any stock
        // locking / product lookups run — mirrors PlaceOrder's storefront guard.
        // The global demo-mode write guard (Themicly\Shopcrafty\Core\Support\DemoMode) would
        // also catch the Order::create() below, but only with a generic
        // message, and only once already inside the DB transaction.
        if (DemoMode::blocksAction()) {
            throw new DemoModeException('Creating orders is disabled in this demo.');
        }

        return DB::transaction(function () use ($data) {
            $tax = app(TaxService::class);
            $subtotal = 0;
            $lines = [];

            foreach ($data['lines'] as $line) {
                $product = Product::find($line['product_id']);
                if (! $product) {
                    continue;
                }
                $qty = max(1, (int) $line['qty']);
                $subtotal += $product->price * $qty;
                $lines[] = ['product' => $product, 'qty' => $qty];
            }

            if (empty($lines)) {
                throw new RuntimeException('Add at least one product to the order.');
            }

            $shipping = (int) ($data['shipping_total'] ?? 0);
            $taxTotal = $tax->taxFor($subtotal);
            $grand = $subtotal + $tax->addedTaxFor($subtotal) + $shipping;

            $order = Order::create([
                'number' => $this->generateNumber(),
                'customer_id' => $data['customer_id'] ?? null,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'],
                'cod_verification_status' => $data['payment_method'] === 'cod' ? 'unverified' : 'unverified',
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'shipping_total' => $shipping,
                'tax_total' => $taxTotal,
                'grand_total' => $grand,
                'currency' => settings('localization.currency_code', 'USD'),
                'notes' => $data['notes'] ?? null,
                'source' => 'manual',
                'placed_at' => now(),
            ]);

            foreach ($lines as $line) {
                $product = $line['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'qty' => $line['qty'],
                    'line_total' => $product->price * $line['qty'],
                ]);
            }

            $order->addresses()->create([
                'type' => 'shipping',
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? '',
                'city' => $data['city'] ?? '',
                'region' => $data['region'] ?? null,
            ]);

            $order->history()->create(['to_status' => 'pending', 'note' => 'Manual order', 'actor' => 'admin']);

            if ($data['payment_method'] !== 'cod') {
                $this->commit->handle($order);
            }

            event(new OrderPlaced($order));

            return $order;
        });
    }

    protected function generateNumber(): string
    {
        $prefix = strtoupper((string) settings('general.order_number_prefix', 'SC'));

        do {
            $number = $prefix.'-'.strtoupper(Str::random(10));
        } while (Order::where('number', $number)->exists());

        return $number;
    }
}
