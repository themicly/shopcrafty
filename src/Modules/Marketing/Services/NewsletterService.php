<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Services;

use Themicly\Shopcrafty\Modules\Marketing\Models\NewsletterSubscriber;

class NewsletterService
{
    /** Subscribe (or re-subscribe) an address. Idempotent on email. */
    public function subscribe(string $email, ?string $name = null, string $source = 'storefront'): NewsletterSubscriber
    {
        $email = mb_strtolower(trim($email));

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => $email]);
        $subscriber->fill(['name' => $name ?: $subscriber->name, 'status' => 'subscribed', 'source' => $source]);
        $subscriber->save();

        return $subscriber;
    }

    /** Unsubscribe by token. Returns true if a subscriber was found. */
    public function unsubscribe(string $token): bool
    {
        $subscriber = NewsletterSubscriber::where('token', $token)->first();

        if (! $subscriber) {
            return false;
        }

        $subscriber->update(['status' => 'unsubscribed']);

        return true;
    }
}
