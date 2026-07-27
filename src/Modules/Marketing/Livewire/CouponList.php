<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Marketing\Models\Coupon;

class CouponList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $coupons = Coupon::query()
            ->when($this->search !== '', fn ($q) => $q->where(function ($w) {
                $w->where('code', 'like', "%{$this->search}%")
                    ->orWhere('name', 'like', "%{$this->search}%");
            }))
            ->latest()
            ->paginate(20);

        return View::make('marketing::livewire.coupon-list', [
            'coupons' => $coupons,
        ]);
    }
}
