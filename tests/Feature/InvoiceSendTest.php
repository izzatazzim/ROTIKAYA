<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InvoiceSendTest extends TestCase
{
    use RefreshDatabase;

    public function test_successful_whatsapp_send_creates_dispatch_row_and_audit_log(): void
    {
        config()->set('services.whatsapp.simulate_mode', 'always_success');
        Mail::fake();
        $invoice = $this->seedInvoiceWithClient();
        $accountant = $this->makeUserWithRole('accountant');

        $response = $this->actingAs($accountant)->postJson(route('invoices.send', $invoice));

        $response->assertOk()
            ->assertJson(['ok' => true, 'status' => 'sent', 'channel' => 'whatsapp']);

        $this->assertDatabaseHas('invoice_dispatches', [
            'invoice_id' => $invoice->id,
            'channel' => 'whatsapp',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'invoice.dispatched',
            'entity_type' => 'InvoiceDispatch',
        ]);
    }

    public function test_whatsapp_fails_then_email_succeeds_creates_two_dispatch_rows(): void
    {
        config()->set('services.whatsapp.simulate_mode', 'always_fail');
        Mail::fake();
        $invoice = $this->seedInvoiceWithClient();
        $accountant = $this->makeUserWithRole('accountant');

        $response = $this->actingAs($accountant)->postJson(route('invoices.send', $invoice));

        $response->assertOk()
            ->assertJson(['ok' => true, 'status' => 'sent', 'channel' => 'email']);

        $this->assertDatabaseHas('invoice_dispatches', [
            'invoice_id' => $invoice->id,
            'channel' => 'whatsapp',
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('invoice_dispatches', [
            'invoice_id' => $invoice->id,
            'channel' => 'email',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'invoice.dispatch_failed',
            'entity_type' => 'InvoiceDispatch',
        ]);

    }

    public function test_whatsapp_and_email_failure_returns_error_and_failed_rows(): void
    {
        config()->set('services.whatsapp.simulate_mode', 'always_fail');
        Mail::fake();
        Mail::shouldReceive('raw')->andThrow(new \RuntimeException('SMTP down'));
        $invoice = $this->seedInvoiceWithClient();
        $accountant = $this->makeUserWithRole('accountant');

        $response = $this->actingAs($accountant)->postJson(route('invoices.send', $invoice));

        $response->assertStatus(422)
            ->assertJson(['ok' => false, 'status' => 'failed']);

        $this->assertDatabaseHas('invoice_dispatches', [
            'invoice_id' => $invoice->id,
            'channel' => 'whatsapp',
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('invoice_dispatches', [
            'invoice_id' => $invoice->id,
            'channel' => 'email',
            'status' => 'failed',
        ]);
    }

    public function test_authorization_for_send_invoice(): void
    {
        $invoice = $this->seedInvoiceWithClient();
        $salesStaff = $this->makeUserWithRole('sales_staff');

        $salesResponse = $this->actingAs($salesStaff)->post(route('invoices.send', $invoice));
        $salesResponse->assertStatus(403);

        auth()->logout();
        $guestResponse = $this->post(route('invoices.send', $invoice));
        $guestResponse->assertRedirect(route('login'));
    }

    public function test_resend_creates_multiple_rows_and_updates_last_sent(): void
    {
        config()->set('services.whatsapp.simulate_mode', 'always_success');
        Mail::fake();
        $invoice = $this->seedInvoiceWithClient();
        $accountant = $this->makeUserWithRole('accountant');

        $first = $this->actingAs($accountant)->postJson(route('invoices.send', $invoice));
        $first->assertOk()->assertJson(['ok' => true]);

        sleep(1);

        $second = $this->actingAs($accountant)->postJson(route('invoices.send', $invoice));
        $second->assertOk()->assertJson(['ok' => true]);

        $invoice->refresh()->load('lastSuccessfulDispatch');
        $this->assertDatabaseCount('invoice_dispatches', 2);
        $this->assertNotNull($invoice->lastSuccessfulDispatch);
        $this->assertSame(
            $invoice->lastSuccessfulDispatch->dispatched_at?->toDateTimeString(),
            $second->json('last_sent_at')
        );
    }

    public function test_invalid_invoice_id_returns_404(): void
    {
        $accountant = $this->makeUserWithRole('accountant');
        $response = $this->actingAs($accountant)->post('/invoices/999999/send');
        $response->assertNotFound();
    }

    private function seedInvoiceWithClient(): Invoice
    {
        $salesRole = Role::query()->firstOrCreate(['name' => 'sales_staff']);
        $salesUser = User::factory()->create(['role_id' => $salesRole->id, 'name' => 'Sales Test']);
        $client = Client::query()->create([
            'name' => 'ABC Media',
            'phone' => '+60111111111',
            'email' => 'abc@example.test',
        ]);
        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $salesUser->id,
            'campaign_name' => 'Campaign X',
            'amount' => 2500,
            'status' => 'completed',
        ]);

        return Invoice::query()->create([
            'invoice_number' => 'INV-SEND-001',
            'sale_id' => $sale->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'total_amount' => 2500,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);
    }

    private function makeUserWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(['name' => $roleName]);
        return User::factory()->create(['role_id' => $role->id]);
    }
}
