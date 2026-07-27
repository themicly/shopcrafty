<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class OrderTracker extends Component
{
    public string $number = '';

    public string $phone = '';

    public ?Order $order = null;

    public bool $searched = false;

    public function search(): void
    {
        $this->validate([
            'number' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $this->order = Order::where('number', $this->number)
            ->whereHas('addresses', fn ($q) => $q->where('phone', $this->phone))
            ->with(['items', 'shippingAddress'])
            ->first();

        $this->searched = true;
    }

    public function render()
    {
        return View::make('theme::livewire.order-tracker');
    }
}
