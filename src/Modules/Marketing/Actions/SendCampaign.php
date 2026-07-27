<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Actions;

use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Marketing\Models\NewsletterCampaign;
use Themicly\Shopcrafty\Modules\Marketing\Models\NewsletterSubscriber;
use Themicly\Shopcrafty\Modules\Notifications\Jobs\DeliverMessage;

/**
 * Queues one email per recipient through the shared notification delivery
 * job (so it uses the configured email gateway and is logged). Two audiences:
 * the full newsletter subscriber list (default, unchanged), or customers
 * matching one or more tags — for a targeted send instead of a broadcast.
 */
class SendCampaign
{
    /** @param  array<int, string>  $tags  empty = every subscribed address; non-empty = customers tagged with any of these */
    public function handle(NewsletterCampaign $campaign, array $tags = []): int
    {
        $count = empty($tags)
            ? $this->sendToSubscribers($campaign)
            : $this->sendToTaggedCustomers($campaign, $tags);

        $campaign->update([
            'status' => 'sent',
            'recipients_count' => $count,
            'sent_at' => now(),
        ]);

        return $count;
    }

    private function sendToSubscribers(NewsletterCampaign $campaign): int
    {
        $storeName = settings('general.store_name', config('app.name'));
        $count = 0;

        // Stream in chunks so a large list can't exhaust memory mid-send (MKT-07);
        // each delivery is itself a queued, retrying job.
        NewsletterSubscriber::subscribed()->chunkById(500, function ($subscribers) use ($campaign, $storeName, &$count) {
            foreach ($subscribers as $subscriber) {
                $unsubscribe = route('storefront.newsletter.unsubscribe', $subscriber->token);

                $body = $campaign->body
                    .'<hr style="margin-top:24px"><p style="font-size:12px;color:#888">'
                    ."You're receiving this because you subscribed to {$storeName}. "
                    ."<a href=\"{$unsubscribe}\">Unsubscribe</a></p>";

                DeliverMessage::dispatch('newsletter.campaign', 'email', $subscriber->email, $campaign->subject, $body, 'subscriber');
                $count++;
            }
        });

        return $count;
    }

    /** @param  array<int, string>  $tags */
    private function sendToTaggedCustomers(NewsletterCampaign $campaign, array $tags): int
    {
        $storeName = settings('general.store_name', config('app.name'));
        $count = 0;

        Customer::whereNotNull('email')
            ->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            })
            ->chunkById(500, function ($customers) use ($campaign, $storeName, &$count) {
                foreach ($customers as $customer) {
                    // Targeted at a segment, not the general list — no unsubscribe
                    // token exists for this path, so the footer is informational only.
                    $body = $campaign->body
                        .'<hr style="margin-top:24px"><p style="font-size:12px;color:#888">'
                        ."You're receiving this because you're a customer of {$storeName}.</p>";

                    DeliverMessage::dispatch('newsletter.campaign', 'email', $customer->email, $campaign->subject, $body, 'customer');
                    $count++;
                }
            });

        return $count;
    }
}
