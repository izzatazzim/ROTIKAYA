<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentUpdateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @std TC002_03
     * STD Description: Update payment status to Paid
     * Expected Result: Invoice status transitions to paid after payment recording.
     */
    public function test_TC002_03_payment_recording_updates_invoice_status_to_paid(): void
    {
        $role = Role::query()->create(['name' => 'accountant']);
        $salesRole = Role::query()->create(['name' => 'sales_staff']);
        $accountant = User::factory()->create(['role_id' => $role->id]);
        $sales = User::factory()->create(['role_id' => $salesRole->id]);
        $client = Client::query()->create(['name' => 'Acme']);
        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $sales->id,
            'campaign_name' => 'Campaign',
            'amount' => 1000,
            'status' => 'completed',
        ]);
        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-TEST-001',
            'sale_id' => $sale->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->subDay()->toDateString(),
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'overdue',
        ]);

        $this->actingAs($accountant)->post('/payments', [
            'invoice_id' => $invoice->id,
            'amount' => 1000,
            'payment_date' => now()->toDateString(),
        ]);

        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'paid']);
    }
}
