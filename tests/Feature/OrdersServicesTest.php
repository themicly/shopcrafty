<?php

use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Orders\Services\CartService;
use Themicly\Shopcrafty\Modules\Orders\Services\OrderStatusService;
use Themicly\Shopcrafty\Modules\Orders\Services\TaxService;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;
use Themicly\Shopcrafty\Tests\TestCase;

final class OrdersServicesTest extends TestCase
{
    protected function migrateCore(): void
    {
        $this->artisan('migrate')->assertExitCode(0);
    }

    public function test_cart_service_adds_updates_and_removes_items(): void
    {
        $this->migrateCore();
        $product = Product::create(['name' => 'Notebook', 'price' => 500, 'stock_qty' => 10, 'status' => 'active']);
        $cart = app(CartService::class);

        $this->assertTrue($cart->add($product->id, qty: 2));
        $this->assertSame(1000, $cart->subtotal());
        $this->assertSame(2, $cart->count());
        $item = $cart->items()->first();
        $cart->updateQty($item->id, 3);
        $this->assertSame(1500, $cart->subtotal());
        $cart->remove($item->id);
        $this->assertTrue($cart->isEmpty());
    }

    public function test_tax_service_handles_exclusive_and_inclusive_tax(): void
    {
        $this->migrateCore();
        app(Settings::class)->setMany(['tax.enabled' => true, 'tax.rate' => 10, 'tax.inclusive' => false]);
        $tax = app(TaxService::class);

        $this->assertSame(100, $tax->taxFor(1000));
        $this->assertSame(100, $tax->addedTaxFor(1000));

        app(Settings::class)->set('tax.inclusive', true);
        $this->assertSame(91, $tax->taxFor(1000));
        $this->assertSame(0, $tax->addedTaxFor(1000));
    }

    public function test_order_status_service_records_status_changes(): void
    {
        $this->migrateCore();
        $order = Order::create([
            'number' => 'SC-1001', 'status' => 'pending', 'payment_status' => 'unpaid',
            'grand_total' => 1000, 'discount_total' => 0, 'refunded_total' => 0,
        ]);
        $service = app(OrderStatusService::class);

        $service->change($order, 'confirmed', 'Payment verified', 'tester');

        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_history', [
            'order_id' => $order->id, 'from_status' => 'pending', 'to_status' => 'confirmed', 'actor' => 'tester',
        ]);
    }
}
