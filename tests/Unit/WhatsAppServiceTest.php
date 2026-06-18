<?php

namespace Tests\Unit;

use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class WhatsAppServiceTest extends TestCase
{
    public function test_always_success_mode_returns_success_structure(): void
    {
        config()->set('services.whatsapp.driver', 'simulator');
        config()->set('services.whatsapp.simulate_mode', 'always_success');

        $service = new WhatsAppService();
        $result = $service->send('+60111111111', 'Hello');

        $this->assertResponseShape($result);
        $this->assertTrue($result['success']);
        $this->assertNotNull($result['message_id']);
        $this->assertNull($result['error']);
    }

    public function test_always_fail_mode_returns_failure_with_error_message(): void
    {
        config()->set('services.whatsapp.driver', 'simulator');
        config()->set('services.whatsapp.simulate_mode', 'always_fail');

        $service = new WhatsAppService();
        $result = $service->send('+60111111111', 'Hello');

        $this->assertResponseShape($result);
        $this->assertFalse($result['success']);
        $this->assertNull($result['message_id']);
        $this->assertNotNull($result['error']);
    }

    public function test_random_mode_with_zero_failure_rate_always_succeeds(): void
    {
        config()->set('services.whatsapp.driver', 'simulator');
        config()->set('services.whatsapp.simulate_mode', 'random');
        config()->set('services.whatsapp.simulate_failure_rate', 0);

        $service = new WhatsAppService();
        $result = $service->send('+60111111111', 'Hello');

        $this->assertTrue($result['success']);
        $this->assertNull($result['error']);
    }

    public function test_random_mode_with_hundred_failure_rate_always_fails(): void
    {
        config()->set('services.whatsapp.driver', 'simulator');
        config()->set('services.whatsapp.simulate_mode', 'random');
        config()->set('services.whatsapp.simulate_failure_rate', 100);

        $service = new WhatsAppService();
        $result = $service->send('+60111111111', 'Hello');

        $this->assertFalse($result['success']);
        $this->assertNotNull($result['error']);
    }

    public function test_logs_are_written_for_every_attempt(): void
    {
        config()->set('services.whatsapp.driver', 'simulator');
        config()->set('services.whatsapp.simulate_mode', 'always_success');
        Log::spy();

        $service = new WhatsAppService();
        $service->send('+60111111111', 'Hello');

        Log::shouldHaveReceived('info')->once();
    }

    private function assertResponseShape(array $result): void
    {
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('message_id', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertArrayHasKey('driver', $result);
        $this->assertArrayHasKey('simulated', $result);
    }
}
