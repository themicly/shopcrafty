<?php

namespace Themicly\Shopcrafty\Modules\Orders\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Modules\Orders\Actions\RequestReturn;
use Themicly\Shopcrafty\Modules\Orders\Models\Order;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;

/**
 * Customer-facing structured return (RMA) control shown per eligible order on
 * the account page. The customer picks which items + quantities to return,
 * gives a reason, and optionally attaches photos. Only the order's owner may
 * request, and only once it's shipped or delivered and has no open request.
 */
class ReturnRequest extends Component
{
    use WithFileUploads;

    public int $orderId;

    public bool $open = false;

    public string $reason = '';

    /** Return quantity keyed by order_item_id (0 = not returning that line). */
    public array $selections = [];

    /** Uploaded photo evidence (temporary uploads until submit). */
    public array $photos = [];

    public bool $submitted = false;

    public function mount(int $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function submit(RequestReturn $action): void
    {
        $order = Order::with('items')->where('customer_id', auth('customer')->id())->findOrFail($this->orderId);

        // Clamp each selected line to what was actually ordered.
        $orderedQty = $order->items->pluck('qty', 'id');

        $this->validate([
            'reason' => ['required', 'string', 'max:500'],
            'selections' => ['array'],
            'selections.*' => ['integer', 'min:0'],
            'photos' => ['array', 'max:4'],
            'photos.*' => MediaService::imageRules(),
        ]);

        $lines = [];
        foreach ($this->selections as $itemId => $qty) {
            $qty = (int) $qty;
            $max = (int) ($orderedQty[$itemId] ?? 0);

            if ($qty > 0 && $max > 0) {
                $lines[] = ['order_item_id' => (int) $itemId, 'qty' => min($qty, $max)];
            }
        }

        $paths = [];
        $media = app(MediaService::class);
        foreach ($this->photos as $photo) {
            $paths[] = $media->store($photo)->url('medium');
        }

        $action->handle($order, auth('customer')->id(), $this->reason, $lines, $paths);

        $this->submitted = true;
        $this->open = false;
        $this->reset('reason', 'selections', 'photos');

        $this->dispatch('toast', message: __('storefront.return_request_submitted'), type: 'success');
    }

    public function render()
    {
        $order = Order::with('items')->where('customer_id', auth('customer')->id())->find($this->orderId);

        $existing = $order?->returns()->with('items')->latest()->first();
        $hasOpen = $this->submitted || ($existing && $existing->isOpen());
        $eligible = $order && in_array($order->status, ['shipped', 'delivered'], true);

        return View::make('theme::livewire.return-request', [
            'order' => $order,
            'items' => $order?->items ?? collect(),
            'existing' => $existing,
            'eligible' => $eligible,
            'hasOpen' => $hasOpen,
        ]);
    }
}
