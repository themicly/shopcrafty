<?php

namespace Themicly\Shopcrafty\Modules\Customers\Livewire;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Notifications\Actions\SendNotification;

/**
 * Storefront contact form for the support hub. Submits through the notifications
 * pipeline (emails the store owner) and confirms with a success toast.
 */
class SupportForm extends Component
{
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public bool $sent = false;

    public function mount(): void
    {
        if ($customer = auth('customer')->user()) {
            $this->name = (string) $customer->name;
            $this->email = (string) $customer->email;
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string> */
    protected function validationAttributes(): array
    {
        return [
            'name' => __('storefront.full_name'),
            'email' => __('storefront.email'),
            'subject' => __('storefront.subject'),
            'message' => __('storefront.message'),
        ];
    }

    public function submit(SendNotification $notifier): void
    {
        // Throttle to curb spam / abuse of the contact channel.
        $key = 'support-contact:'.(auth('customer')->id() ?? request()->ip());

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('message', 'Too many messages. Please try again in a minute.');

            return;
        }

        $data = $this->validate();

        RateLimiter::hit($key, 60);

        $notifier->handle('support.contact', [
            'contact' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'subject' => $data['subject'],
                'message' => $data['message'],
            ],
        ]);

        $this->sent = true;
        $this->reset('subject', 'message');

        $this->dispatch('toast', message: __('storefront.support_sent'), type: 'success');
    }

    public function render()
    {
        return View::make('theme::livewire.support-form');
    }
}
