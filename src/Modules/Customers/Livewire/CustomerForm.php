<?php

namespace Themicly\Shopcrafty\Modules\Customers\Livewire;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;

/**
 * Admin-side customer creation (for phone/WhatsApp shoppers). Sets a random
 * password so the account exists; the customer can use "forgot password" later.
 */
class CustomerForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $mobile = '';

    public string $status = 'active';

    public function create()
    {
        if ($this->mobile !== '') {
            $this->mobile = Customer::normalizeMobile($this->mobile);
        }

        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:190', Rule::unique('customers', 'email')],
            'mobile' => ['nullable', 'string', 'max:40', Rule::unique('customers', 'mobile')],
            'status' => ['in:active,blocked'],
        ]);

        if (! $data['email'] && ! $data['mobile']) {
            $this->addError('email', 'Enter an email or a mobile number.');

            return null;
        }

        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'] ?: null,
            'mobile' => $data['mobile'] ?: null,
            'status' => $data['status'],
            'password' => Str::random(24),
        ]);

        $this->dispatch('toast', message: 'Customer created', type: 'success');

        return $this->redirectRoute('admin.customers.show', $customer->id, navigate: true);
    }

    public function render()
    {
        return View::make('customers::livewire.customer-form');
    }
}
