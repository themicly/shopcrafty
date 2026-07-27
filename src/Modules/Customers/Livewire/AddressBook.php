<?php

namespace Themicly\Shopcrafty\Modules\Customers\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Customers\Models\CustomerAddress;

class AddressBook extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $label = '';

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    public string $city = '';

    public string $region = '';

    public bool $is_default = false;

    protected function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'region' => ['nullable', 'string', 'max:120'],
            'is_default' => ['boolean'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('storefront.full_name'),
            'phone' => __('storefront.phone'),
            'address' => __('storefront.address'),
            'city' => __('storefront.city'),
            'region' => __('checkout.state_region'),
        ];
    }

    public function create(): void
    {
        $this->reset('editingId', 'label', 'name', 'phone', 'address', 'city', 'region', 'is_default');
        $this->name = (string) Auth::guard('customer')->user()->name;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $address = $this->query()->findOrFail($id);
        $this->editingId = $address->id;
        $this->fill($address->only('label', 'name', 'phone', 'address', 'city', 'region', 'is_default'));
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();
        $customer = Auth::guard('customer')->user();

        if ($data['is_default']) {
            $customer->addresses()->update(['is_default' => false]);
        }

        if ($this->editingId) {
            $this->query()->whereKey($this->editingId)->update($data);
        } else {
            $customer->addresses()->create($data);
        }

        $this->showForm = false;
        $this->dispatch('toast', message: __('storefront.address_saved'), type: 'success');
    }

    public function delete(int $id): void
    {
        $this->query()->whereKey($id)->delete();
        $this->dispatch('toast', message: __('storefront.address_removed'), type: 'success');
    }

    protected function query()
    {
        return CustomerAddress::where('customer_id', Auth::guard('customer')->id());
    }

    public function render()
    {
        return View::make('theme::livewire.address-book', [
            'addresses' => $this->query()->orderByDesc('is_default')->get(),
        ]);
    }
}
