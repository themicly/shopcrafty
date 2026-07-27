<?php

use Themicly\Shopcrafty\Core\Navigation\NavigationRegistry;
use Themicly\Shopcrafty\Modules\Notifications\Services\ProviderRegistry;
use Themicly\Shopcrafty\Modules\Notifications\Services\TemplateRenderer;
use Themicly\Shopcrafty\Modules\Notifications\Support\DeliveryResult;
use Themicly\Shopcrafty\Modules\Notifications\Support\OutgoingMessage;
use Themicly\Shopcrafty\Modules\Orders\Services\PaymentRegistry;
use Themicly\Shopcrafty\Tests\TestCase;

final class CoreInfrastructureTest extends TestCase
{
    public function test_navigation_registry_handles_all_extension_points(): void
    {
        $registry = app(NavigationRegistry::class);
        $registry->register('Catalog', ['label' => 'Products']);
        $registry->register('footer', ['label' => 'Privacy']);
        $registry->register('quick_create', ['label' => 'Product']);
        $registry->register('index:products', ['label' => 'Export']);

        $this->assertSame('Products', collect($registry->main())->firstWhere('label', 'Catalog')['items'][0]['label']);
        $footer = $registry->footer();
        $quickCreate = $registry->quickCreate();
        $this->assertSame('Privacy', end($footer)['label']);
        $this->assertSame('Product', end($quickCreate)['label']);
        $this->assertSame('Export', $registry->indexMenu('products')[0]['label']);
    }

    public function test_template_renderer_replaces_nested_values_and_escapes_html(): void
    {
        $renderer = app(TemplateRenderer::class);

        $this->assertSame('Hello Ada, !', $renderer->render('Hello {{ customer.name }}, {{ missing }}!', [
            'customer' => ['name' => 'Ada'],
        ]));
        $this->assertSame('&lt;script&gt;', $renderer->render('{{ value }}', ['value' => '<script>'], true));
    }

    public function test_notification_value_objects_expose_success_and_failure_states(): void
    {
        $ok = DeliveryResult::ok('mail', 'ref-1');
        $fail = DeliveryResult::fail('sms', 'not configured');
        $message = new OutgoingMessage('email', 'ada@example.com', 'Welcome', 'Hello', ['event' => 'welcome']);

        $this->assertTrue($ok->ok);
        $this->assertSame('ref-1', $ok->reference);
        $this->assertFalse($fail->ok);
        $this->assertSame('not configured', $fail->error);
        $this->assertSame(['event' => 'welcome'], $message->meta);
    }

    public function test_payment_registry_contains_only_core_payment_methods(): void
    {
        $this->artisan('migrate')->assertExitCode(0);
        $registry = app(PaymentRegistry::class);

        $this->assertNotNull($registry->find('cod'));
        $this->assertNotNull($registry->find('bank_transfer'));
        $this->assertNull($registry->find('stripe'));
        $this->assertCount(2, $registry->all());
    }

    public function test_notification_registry_deduplicates_gateways_and_filters_channels(): void
    {
        $this->artisan('migrate')->assertExitCode(0);
        $registry = app(ProviderRegistry::class);
        $all = $registry->all();
        $registry->register($all->first()::class);

        $this->assertSame($all->count(), $registry->all()->count());
        $this->assertNotEmpty($registry->all('email'));
        $this->assertContains('email', $registry->channels());
        $this->assertContains('sms', $registry->channels());
    }
}
