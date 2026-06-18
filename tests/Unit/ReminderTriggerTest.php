<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ReminderService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReminderTriggerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @std TC002_04
     * STD Description: Trigger overdue WhatsApp reminder
     * Expected Result: Reminder attempt is recorded for overdue invoice.
     */
    public function test_TC002_04_overdue_invoice_triggers_whatsapp_reminder(): void
    {
        config()->set('services.whatsapp.simulate_mode', 'always_success');
        $salesRole = Role::query()->create(['name' => 'sales_staff']);
        $sales = User::factory()->create(['role_id' => $salesRole->id]);
        $client = Client::query()->create(['name' => 'Acme', 'phone' => '+60123456789']);
        SystemSetting::query()->create(['default_payment_term_days' => 30, 'reminder_intervals' => [15]]);

        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $sales->id,
            'campaign_name' => 'Campaign',
            'amount' => 1200,
            'status' => 'completed',
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-TEST-002',
            'sale_id' => $sale->id,
            'issue_date' => now()->subDays(45)->toDateString(),
            'due_date' => now()->subDays(15)->toDateString(),
            'total_amount' => 1200,
            'paid_amount' => 0,
            'status' => 'overdue',
        ]);

        $service = new ReminderService(new WhatsAppService());
        $service->processOverdueInvoices();

        $this->assertDatabaseHas('reminders', [
            'invoice_id' => $invoice->id,
            'days_overdue' => 15,
            'status' => 'sent',
        ]);
    }

    public function test_whatsapp_always_fail_triggers_email_fallback_for_reminder(): void
    {
        config()->set('services.whatsapp.simulate_mode', 'always_fail');
        Mail::fake();

        $salesRole = Role::query()->create(['name' => 'sales_staff']);
        $sales = User::factory()->create(['role_id' => $salesRole->id]);
        $client = Client::query()->create([
            'name' => 'Acme',
            'phone' => '+60123456789',
            'email' => 'acme@example.test',
        ]);
        SystemSetting::query()->create(['default_payment_term_days' => 30, 'reminder_intervals' => [15]]);

        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $sales->id,
            'campaign_name' => 'Campaign',
            'amount' => 1200,
            'status' => 'completed',
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-TEST-003',
            'sale_id' => $sale->id,
            'issue_date' => now()->subDays(45)->toDateString(),
            'due_date' => now()->subDays(15)->toDateString(),
            'total_amount' => 1200,
            'paid_amount' => 0,
            'status' => 'overdue',
        ]);

        $service = new ReminderService(new WhatsAppService());
        $service->processOverdueInvoices();

        $this->assertDatabaseHas('payment_reminders', [
            'invoice_id' => $invoice->id,
            'channel' => 'whatsapp',
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('payment_reminders', [
            'invoice_id' => $invoice->id,
            'channel' => 'email',
            'status' => 'sent',
        ]);
    }

    public function test_random_mode_is_deterministic_when_seeded(): void
    {
        config()->set('services.whatsapp.simulate_mode', 'random');
        config()->set('services.whatsapp.simulate_failure_rate', 30);

        $service = new WhatsAppService();

        mt_srand(2026);
        $first = $service->send('+60111111111', 'Test one');
        mt_srand(2026);
        $second = $service->send('+60111111111', 'Test two');

        $this->assertSame($first['success'], $second['success']);
        $this->assertSame($first['driver'], $second['driver']);
    }
}
