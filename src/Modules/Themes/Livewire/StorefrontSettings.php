<?php

namespace Themicly\Shopcrafty\Modules\Themes\Livewire;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

/**
 * Site-wide storefront configuration — feature modules shoppers see. Lives
 * under the Storefront menu so all frontpage configuration sits together.
 */
class StorefrontSettings extends Component
{
    public bool $reviewsEnabled = true;

    public bool $wishlistEnabled = true;

    public bool $compareEnabled = true;

    public bool $newsletterEnabled = true;

    /** Up to 10 terms shown as suggestion chips under the header search bar. */
    public array $popularSearchTerms = [];

    public string $newPopularSearchTerm = '';

    /** Chips are capped so the suggestion row never wraps into a wall of text. */
    public const MAX_POPULAR_TERMS = 10;

    public function mount(Settings $settings, AddonRegistry $addons): void
    {
        abort_unless(Gate::allows('manage-config'), 403);

        $this->reviewsEnabled = $addons->installed('reviews') && (bool) $settings->get('catalog.reviews_enabled', true);
        $this->wishlistEnabled = $addons->installed('wishlist') && (bool) $settings->get('catalog.wishlist_enabled', true);
        $this->compareEnabled = $addons->installed('compare') && (bool) $settings->get('catalog.compare_enabled', true);
        $this->newsletterEnabled = (bool) $settings->get('marketing.newsletter_enabled', true);
        $this->popularSearchTerms = (array) $settings->get('search.popular_terms', []);
    }

    public function addPopularSearchTerm(): void
    {
        $term = trim($this->newPopularSearchTerm);
        $this->newPopularSearchTerm = '';

        if ($term === '' || count($this->popularSearchTerms) >= self::MAX_POPULAR_TERMS) {
            return;
        }

        if (in_array($term, $this->popularSearchTerms, true)) {
            return;
        }

        $this->popularSearchTerms[] = $term;
    }

    public function removePopularSearchTerm(int $index): void
    {
        unset($this->popularSearchTerms[$index]);
        $this->popularSearchTerms = array_values($this->popularSearchTerms);
    }

    public function save(Settings $settings, AddonRegistry $addons): void
    {
        abort_unless(Gate::allows('manage-config'), 403);

        $values = [
            'marketing.newsletter_enabled' => $this->newsletterEnabled,
            'search.popular_terms' => array_values($this->popularSearchTerms),
        ];

        if ($addons->installed('reviews')) {
            $values['catalog.reviews_enabled'] = $this->reviewsEnabled;
        }
        if ($addons->installed('wishlist')) {
            $values['catalog.wishlist_enabled'] = $this->wishlistEnabled;
        }
        if ($addons->installed('compare')) {
            $values['catalog.compare_enabled'] = $this->compareEnabled;
        }

        $settings->setMany($values);

        $this->dispatch('toast', message: 'Storefront settings saved', type: 'success');
    }

    public function render()
    {
        return View::make('themes::livewire.storefront-settings');
    }
}
