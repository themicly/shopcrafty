<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Livewire;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Marketing\Services\NewsletterService;

class NewsletterForm extends Component
{
    #[Locked]
    public string $heading = 'Join the list';

    #[Locked]
    public string $subheading = '';

    public string $email = '';

    public bool $done = false;

    public function mount(?string $heading = null, ?string $subheading = null): void
    {
        $this->heading = $heading ?: $this->heading;
        $this->subheading = (string) $subheading;
    }

    public function subscribe(NewsletterService $newsletter): void
    {
        abort_unless((bool) settings('marketing.newsletter_enabled', true), 404);

        $data = $this->validate(['email' => ['required', 'email', 'max:190']]);

        // Throttle so the public form can't be used to mass-subscribe addresses (MKT-06).
        $key = 'newsletter:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many attempts. Please try again later.');

            return;
        }

        RateLimiter::hit($key, 3600);

        $newsletter->subscribe($data['email'], source: 'storefront');

        $this->done = true;
        $this->reset('email');
    }

    public function render()
    {
        // Feature disabled: render nothing (Livewire still needs a root element).
        if (! settings('marketing.newsletter_enabled', true)) {
            return <<<'BLADE'
                <div></div>
            BLADE;
        }

        return View::make('theme::livewire.newsletter-form');
    }
}
