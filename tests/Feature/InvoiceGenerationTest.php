<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\InvoiceTemplate;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceGenerationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @std TC002_01
     * STD Description: Generate invoice from completed sale
     * Expected Result: Invoice is generated and listed successfully.
     */
    public function test_TC002_01_accountant_can_generate_invoice_from_completed_sale(): void
    {
        $role = Role::query()->create(['name' => 'accountant']);
        $salesRole = Role::query()->create(['name' => 'sales_staff']);
        $accountant = User::factory()->create(['role_id' => $role->id]);
        $sales = User::factory()->create(['role_id' => $salesRole->id]);
        $client = Client::query()->create(['name' => 'Acme']);
        SystemSetting::query()->create(['default_payment_term_days' => 30, 'reminder_intervals' => [15, 30, 45]]);
        InvoiceTemplate::query()->create([
            'name' => 'Default Invoice Template',
            'is_active' => true,
            'is_default' => true,
            'content' => '{"body":"Default invoice layout"}',
            'created_by' => $accountant->id,
        ]);

        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $sales->id,
            'campaign_name' => 'Completed Campaign',
            'amount' => 2000,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($accountant)->post('/invoices', ['sale_id' => $sale->id]);

        $response->assertRedirect('/invoices');
        $this->assertDatabaseHas('invoices', ['sale_id' => $sale->id]);
    }

    /**
     * @std TC002_02
     * STD Description: Generate invoice without template
     * Expected Result: Generation is blocked with explicit template error.
     */
    public function test_TC002_02_generation_is_blocked_when_no_active_default_template(): void
    {
        $role = Role::query()->create(['name' => 'accountant']);
        $salesRole = Role::query()->create(['name' => 'sales_staff']);
        $accountant = User::factory()->create(['role_id' => $role->id]);
        $sales = User::factory()->create(['role_id' => $salesRole->id]);
        $client = Client::query()->create(['name' => 'Acme']);
        SystemSetting::query()->create(['default_payment_term_days' => 30, 'reminder_intervals' => [15, 30, 45]]);

        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $sales->id,
            'campaign_name' => 'Completed Campaign',
            'amount' => 2000,
            'status' => 'completed',
        ]);

        $response = $this->from('/invoices')->actingAs($accountant)->post('/invoices', ['sale_id' => $sale->id]);

        $response->assertRedirect('/invoices');
        $response->assertSessionHasErrors('invoice_template');
        $this->assertDatabaseMissing('invoices', ['sale_id' => $sale->id]);
    }
}
