<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Core\Support\DemoModeException;
use Themicly\Shopcrafty\Modules\Catalog\Models\Product;
use Themicly\Shopcrafty\Modules\Orders\Actions\CreateManualOrder;
use Themicly\Shopcrafty\Modules\Orders\Exceptions\InsufficientStockException;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentRegistry;

class ManualOrder extends Component
{
    public string $productSearch = '';

    /**
     * Shown as an inline banner, not just a toast (dropped/dismissed toasts
     * shouldn't be the only signal that nothing was created).
     */
    public ?string $blockedMessage = null;

    /** @var array<int, array{product_id:int, name:string, price:int, qty:int}> */
    public array $lines = [];

    public string $name = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $city = '';

    public string $region = '';

    public string $paymentMethod = 'cod';

    public string $shippingTotal = '';

    public string $notes = '';

    public function addProduct(int $productId): void
    {
        foreach ($this->lines as $i => $line) {
            if ($line['product_id'] === $productId) {
                $this->lines[$i]['qty']++;
                $this->productSearch = '';

                return;
            }
        }

        $product = Product::find($productId);
        if ($product) {
            $this->lines[] = ['product_id' => $product->id, 'name' => $product->name, 'price' => (int) $product->price, 'qty' => 1];
        }

        $this->productSearch = '';
    }

    public function removeLine(int $index): void
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function create(CreateManualOrder $action)
    {
        $this->blockedMessage = null;

        $data = $this->validate([
            'lines' => ['required', 'array', 'min:1'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:190'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'paymentMethod' => ['required', 'string'],
            'shippingTotal' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], ['lines' => 'products']);

        $decimals = (int) settings('localization.currency_decimals', 2);

        try {
            $order = $action->handle([
                'lines' => array_map(fn ($l) => ['product_id' => $l['product_id'], 'qty' => $l['qty']], $this->lines),
                'name' => $data['name'],
                'phone' => $data['phone'] ?: null,
                'email' => $data['email'] ?: null,
                'address' => $data['address'] ?: null,
                'city' => $data['city'] ?: null,
                'region' => $data['region'] ?: null,
                'payment_method' => $data['paymentMethod'],
                'notes' => $data['notes'] ?: null,
                'shipping_total' => $data['shippingTotal'] !== '' ? (int) round(((float) $data['shippingTotal']) * (10 ** $decimals)) : 0,
            ]);
        } catch (InsufficientStockException $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'danger');

            return null;
        } catch (DemoModeException $e) {
            $this->blockedMessage = $e->getMessage();
            $this->dispatch('toast', message: $e->getMessage(), type: 'warning');

            return null;
        }

        $this->dispatch('toast', message: "Order {$order->number} created", type: 'success');

        return $this->redirectRoute('admin.orders.show', $order->id, navigate: true);
    }

    public function render()
    {
        $results = $this->productSearch !== ''
            ? Product::where('name', 'like', "%{$this->productSearch}%")->limit(6)->get(['id', 'name', 'price'])
            : collect();

        $subtotal = collect($this->lines)->sum(fn ($l) => $l['price'] * $l['qty']);

        return View::make('orders::livewire.manual-order', [
            'results' => $results,
            'subtotal' => $subtotal,
            'methods' => app(PaymentRegistry::class)->enabled(),
        ]);
    }
}
