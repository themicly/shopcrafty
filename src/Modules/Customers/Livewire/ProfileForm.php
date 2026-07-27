<?php

namespace Themicly\Shopcrafty\Modules\Customers\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;

class ProfileForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $mobile = '';

    public string $password = '';

    public function mount(): void
    {
        $customer = Auth::guard('customer')->user();
        $this->name = (string) $customer->name;
        $this->email = (string) $customer->email;
        $this->mobile = (string) $customer->mobile;
    }

    public function save(): void
    {
        $customer = Auth::guard('customer')->user();

        // Normalize before validating so uniqueness is checked against the same
        // canonical form registration stores (otherwise a saved profile could no
        // longer log in by phone, and duplicates in different formats slip through).
        if ($this->mobile !== '') {
            $this->mobile = Customer::normalizeMobile($this->mobile);
        }

        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', Rule::unique('customers', 'email')->ignore($customer->id)],
            'mobile' => ['nullable', 'string', 'max:40', Rule::unique('customers', 'mobile')->ignore($customer->id)],
            // Same strength as registration/reset (CUS-04).
            'password' => ['nullable', Password::defaults()],
        ], attributes: [
            'name' => __('storefront.full_name'),
            'email' => __('storefront.email'),
            'mobile' => __('account.mobile'),
            'password' => __('storefront.password'),
        ]);

        // A customer must keep at least one login identifier (CUS-03).
        if (($data['email'] ?? '') === '' && ($data['mobile'] ?? '') === '') {
            $this->addError('mobile', 'Enter at least an email or a mobile number.');

            return;
        }

        $customer->name = $data['name'];
        $customer->email = $data['email'] ?: null;
        $customer->mobile = $data['mobile'] ?: null;

        if (! empty($data['password'])) {
            $customer->password = $data['password'];
        }

        $customer->save();
        $this->password = '';

        $this->dispatch('toast', message: __('storefront.profile_updated'), type: 'success');
    }

    public function render()
    {
        return View::make('theme::livewire.profile-form');
    }
}
