<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Themicly\Shopcrafty\Modules\Settings\Services\MediaService;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

class GeneralSettings extends Component
{
    use WithFileUploads;

    public string $storeName = '';

    public string $storeEmail = '';

    public string $storePhone = '';

    public string $orderNumberPrefix = '';

    public string $whatsapp = '';

    public string $facebook = '';

    public string $instagram = '';

    // WhatsApp commerce buttons on the product page (only meaningful when a
    // WhatsApp number is set above). Default on so existing installs are unchanged.
    public bool $whatsappBuy = true;

    public bool $whatsappInquiry = true;

    public bool $whatsappShare = true;

    public string $logoUrl = '';

    public string $faviconUrl = '';

    // Shopper feature toggles moved to Storefront → Site settings
    // (Themes\Livewire\StorefrontSettings) so frontpage config lives together.

    public $logoUpload;

    public $faviconUpload;

    public function mount(Settings $settings): void
    {
        $this->storeName = (string) $settings->get('general.store_name', config('app.name'));
        $this->storeEmail = (string) $settings->get('general.store_email', '');
        $this->storePhone = (string) $settings->get('general.store_phone', '');
        $this->orderNumberPrefix = (string) $settings->get('general.order_number_prefix', 'SC');
        $this->whatsapp = (string) $settings->get('general.whatsapp', '');
        $this->facebook = (string) $settings->get('general.facebook', '');
        $this->instagram = (string) $settings->get('general.instagram', '');
        $this->whatsappBuy = (bool) $settings->get('general.whatsapp_buy_enabled', true);
        $this->whatsappInquiry = (bool) $settings->get('general.whatsapp_inquiry', true);
        $this->whatsappShare = (bool) $settings->get('general.whatsapp_share', true);
        $this->logoUrl = (string) $settings->get('general.logo', '');
        $this->faviconUrl = (string) $settings->get('general.favicon', '');
    }

    public function updatedLogoUpload(MediaService $media, Settings $settings): void
    {
        $this->validate(['logoUpload' => MediaService::imageRules()]);
        $this->logoUrl = $media->store($this->logoUpload)->url('medium');
        $settings->set('general.logo', $this->logoUrl);
        $this->reset('logoUpload');
        $this->dispatch('toast', message: 'Logo updated', type: 'success');
    }

    public function updatedFaviconUpload(MediaService $media, Settings $settings): void
    {
        $this->validate(['faviconUpload' => MediaService::imageRules()]);
        $this->faviconUrl = $media->store($this->faviconUpload)->url();
        $settings->set('general.favicon', $this->faviconUrl);
        $this->reset('faviconUpload');
        $this->dispatch('toast', message: 'Favicon updated', type: 'success');
    }

    public function removeLogo(Settings $settings): void
    {
        $settings->set('general.logo', null);
        $this->logoUrl = '';
    }

    public function removeFavicon(Settings $settings): void
    {
        $settings->set('general.favicon', null);
        $this->faviconUrl = '';
    }

    public function save(Settings $settings): void
    {
        $data = $this->validate([
            'storeName' => ['required', 'string', 'max:120'],
            'storeEmail' => ['nullable', 'email', 'max:190'],
            'storePhone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'facebook' => ['nullable', 'string', 'max:190'],
            'instagram' => ['nullable', 'string', 'max:190'],
            // Prefixes the random order number (e.g. "SC-9F3K2LQXAB") — kept short
            // and letters/digits only since it's shown to customers and gateways.
            'orderNumberPrefix' => ['required', 'string', 'regex:/^[A-Za-z0-9]{2,8}$/'],
        ], [
            'orderNumberPrefix.regex' => 'Use 2–8 letters or numbers only, e.g. SC.',
        ]);

        $data['orderNumberPrefix'] = strtoupper($data['orderNumberPrefix']);
        $this->orderNumberPrefix = $data['orderNumberPrefix'];

        $settings->setMany([
            'general.store_name' => $data['storeName'],
            'general.store_email' => $data['storeEmail'],
            'general.store_phone' => $data['storePhone'],
            'general.whatsapp' => $data['whatsapp'],
            'general.facebook' => $data['facebook'],
            'general.instagram' => $data['instagram'],
            'general.whatsapp_buy_enabled' => $this->whatsappBuy,
            'general.whatsapp_inquiry' => $this->whatsappInquiry,
            'general.whatsapp_share' => $this->whatsappShare,
            'general.order_number_prefix' => $data['orderNumberPrefix'],
        ]);

        $this->dispatch('toast', message: 'General settings saved', type: 'success');
    }

    public function render()
    {
        return View::make('settings::livewire.general-settings');
    }
}
