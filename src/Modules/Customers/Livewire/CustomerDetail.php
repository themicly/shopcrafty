<?php

namespace Themicly\Shopcrafty\Modules\Customers\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;

class CustomerDetail extends Component
{
    public Customer $customer;

    /** @var array<int, string> */
    public array $tags = [];

    public string $newTag = '';

    public function mount(int $customerId): void
    {
        $this->customer = Customer::with('addresses')->findOrFail($customerId);
        $this->tags = $this->customer->tags ?? [];
    }

    public function addTag(): void
    {
        $tag = trim(mb_strtolower($this->newTag));
        $this->newTag = '';

        if ($tag === '' || in_array($tag, $this->tags, true)) {
            return;
        }

        $this->tags[] = $tag;
        $this->persistTags();
    }

    public function removeTag(string $tag): void
    {
        $this->tags = array_values(array_filter($this->tags, fn ($t) => $t !== $tag));
        $this->persistTags();
    }

    protected function persistTags(): void
    {
        $this->customer->update(['tags' => $this->tags]);
        $this->dispatch('toast', message: 'Tags updated', type: 'success');
    }

    public function block(): void
    {
        $this->customer->status = $this->customer->status === 'active' ? 'blocked' : 'active';
        $this->customer->save();

        $message = $this->customer->status === 'active' ? 'Customer unblocked' : 'Customer blocked';
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function render()
    {
        $orders = Order::where('customer_id', $this->customer->id)->latest()->get();

        // LTV uses the same revenue-status set as Reports and the customer list, so
        // the same customer never shows two different lifetime values (B4).
        $lifetimeValue = (int) Order::where('customer_id', $this->customer->id)
            ->whereIn('status', Order::REVENUE_STATUSES)
            ->sum('grand_total');

        $orderCount = $orders->count();

        return View::make('customers::livewire.customer-detail', compact('orders', 'lifetimeValue', 'orderCount'));
    }
}
