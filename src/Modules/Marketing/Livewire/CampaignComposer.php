<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Ai\AiNotConfiguredException;
use Themicly\Shopcrafty\Ai\AiRequestException;
use Themicly\Shopcrafty\Ai\AiService;
use Themicly\Shopcrafty\Ai\Generators\CampaignCopyGenerator;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Modules\Marketing\Actions\SendCampaign;
use Themicly\Shopcrafty\Modules\Marketing\Models\NewsletterCampaign;
use Themicly\Shopcrafty\Modules\Marketing\Models\NewsletterSubscriber;

class CampaignComposer extends Component
{
    public string $subject = '';

    public string $body = '';

    /** 'subscribers' (default, the full newsletter list) or 'customers' (tag-targeted). */
    public string $audience = 'subscribers';

    /** @var array<int, string> selected customer tags when audience = 'customers' */
    public array $tags = [];

    /** Short description of the campaign used to prompt the AI. */
    public string $aiGoal = '';

    public function getAiEnabledProperty(): bool
    {
        return app(AddonRegistry::class)->installed('ai') && app(AiService::class)->featureEnabled('campaign_copy');
    }

    /** Draft subject-line variants and an HTML body from the campaign goal, staged for review. */
    public function generateWithAi(): void
    {
        if (! app(AddonRegistry::class)->installed('ai')) {
            abort(404);
        }

        $generator = app(CampaignCopyGenerator::class);

        if (trim($this->aiGoal) === '') {
            $this->dispatch('toast', message: 'Describe what the campaign is about first', type: 'danger');

            return;
        }

        try {
            $result = $generator->generate(trim($this->aiGoal));
        } catch (AiNotConfiguredException) {
            $this->dispatch('toast', message: 'Turn on AI in Settings → AI first', type: 'danger');

            return;
        } catch (AiRequestException $e) {
            $this->dispatch('toast', message: 'AI generation failed: '.$e->getMessage(), type: 'danger');

            return;
        } catch (\Throwable) {
            $this->dispatch('toast', message: 'AI generation failed. Please try again.', type: 'danger');

            return;
        }

        if ($result['subjects'] === [] && $result['body'] === '') {
            $this->dispatch('toast', message: 'AI generation failed. Please try again.', type: 'danger');

            return;
        }

        // Every subject variant targets the same field, so only the first one
        // is pre-checked — the others stay visible in the modal (unchecked) as
        // alternates the owner can pick instead by checking one and unchecking it.
        $items = [];
        foreach ($result['subjects'] as $i => $suggestion) {
            $items[] = [
                'key' => 'subject',
                'label' => $i === 0 ? 'Subject' : 'Subject (alternate)',
                'before' => $i === 0 ? $this->subject : '',
                'after' => $suggestion,
                'selected' => $i === 0,
            ];
        }
        if ($result['body'] !== '') {
            $items[] = ['key' => 'body', 'label' => 'Body', 'before' => $this->body, 'after' => $result['body']];
        }

        $this->openAiReview($items);
    }

    /** Apply whichever AI-suggested fields the owner left checked in the review modal. */
    public function applyAiReview(): void
    {
        $subjectApplied = false;

        foreach ($this->aiReview as $item) {
            if (! $item['selected']) {
                continue;
            }

            if ($item['key'] === 'subject' && ! $subjectApplied) {
                $this->subject = $item['value'];
                $subjectApplied = true;
            } elseif ($item['key'] === 'body') {
                $this->body = $item['value'];
            }
        }

        $this->discardAiReview();
        $this->resetErrorBag(['subject', 'body']);
        $this->dispatch('toast', message: 'AI suggestions applied', type: 'success');
    }

    /** Live "who will get this" count as the audience/tags change. */
    public function getRecipientCountProperty(): int
    {
        return $this->audience === 'customers'
            ? $this->taggedCustomersQuery()->count()
            : NewsletterSubscriber::subscribed()->count();
    }

    protected function taggedCustomersQuery()
    {
        return Customer::whereNotNull('email')
            ->when($this->tags !== [], function ($q) {
                $q->where(function ($q) {
                    foreach ($this->tags as $tag) {
                        $q->orWhereJsonContains('tags', $tag);
                    }
                });
            }, fn ($q) => $q->whereRaw('1 = 0')); // no tags picked yet = nobody, not everybody
    }

    public function send(SendCampaign $sender): void
    {
        $data = $this->validate([
            'subject' => ['required', 'string', 'max:190'],
            'body' => ['required', 'string'],
            'tags' => $this->audience === 'customers' ? ['required', 'array', 'min:1'] : ['array'],
        ], [
            'tags.required' => 'Pick at least one customer tag to target.',
        ]);

        if ($this->recipientCount === 0) {
            $this->dispatch('toast', message: 'No recipients match this audience yet', type: 'danger');

            return;
        }

        $tags = $this->audience === 'customers' ? array_values($data['tags']) : [];

        $campaign = NewsletterCampaign::create(['subject' => $data['subject'], 'body' => $data['body'], 'audience_tags' => $tags ?: null]);
        $count = $sender->handle($campaign, $tags);

        $this->reset('subject', 'body', 'tags');
        $this->audience = 'subscribers';
        $this->dispatch('toast', message: "Campaign queued to {$count} recipient(s)", type: 'success');
    }

    public function render()
    {
        return View::make('marketing::livewire.campaign-composer', [
            'subscribedCount' => NewsletterSubscriber::subscribed()->count(),
            'availableTags' => Customer::whereNotNull('tags')->pluck('tags')->flatten()->filter()->unique()->sort()->values()->all(),
            'campaigns' => NewsletterCampaign::latest()->limit(10)->get(),
        ]);
    }
}
