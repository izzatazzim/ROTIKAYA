<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientStatementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @std TC002_07
     * STD Description: Export client statement PDF
     * Expected Result: Statement PDF is generated and downloaded.
     */
    public function test_TC002_07_export_client_statement_as_pdf(): void
    {
        $accountantRole = Role::query()->create(['name' => 'accountant']);
        $salesRole = Role::query()->create(['name' => 'sales_staff']);

        $accountant = User::factory()->create(['role_id' => $accountantRole->id]);
        $salesUser = User::factory()->create(['role_id' => $salesRole->id]);
        $client = Client::query()->create([
            'name' => 'ABC Media',
            'email' => 'abc@media.test',
            'phone' => '+60123456789',
        ]);

        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $salesUser->id,
            'campaign_name' => 'Quarterly Campaign',
            'amount' => 2500,
            'status' => 'completed',
        ]);

        Invoice::query()->create([
            'invoice_number' => 'INV-CS-001',
            'sale_id' => $sale->id,
            'issue_date' => '2026-05-01',
            'due_date' => '2026-05-31',
            'total_amount' => 2500,
            'paid_amount' => 1000,
            'status' => 'partial',
        ]);

        $response = $this->actingAs($accountant)->post(route('reports.client-statement.export'), [
            'client_id' => $client->id,
            'start_date' => '2026-05-01',
            'end_date' => '2026-05-31',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader(
            'content-disposition',
            'attachment; filename=client-statement-abc-media-2026-05-01-to-2026-05-31.pdf'
        );

        $this->assertDatabaseHas('reports_logs', [
            'generated_by' => $accountant->id,
            'report_type' => 'client_statement',
        ]);
    }

    /**
     * @std TC002_08
     * STD Description: Export client statement with no transactions
     * Expected Result: Clear no-transaction message is shown and export is blocked.
     */
    public function test_TC002_08_export_client_statement_with_no_transactions_shows_inline_error(): void
    {
        $accountantRole = Role::query()->create(['name' => 'accountant']);
        $accountant = User::factory()->create(['role_id' => $accountantRole->id]);
        $client = Client::query()->create(['name' => 'Sony Digital']);

        $response = $this->from(route('reports.client-statement'))
            ->actingAs($accountant)
            ->post(route('reports.client-statement.export'), [
                'client_id' => $client->id,
                'start_date' => '2020-01-01',
                'end_date' => '2020-01-31',
            ]);

        $response->assertRedirect(route('reports.client-statement'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('reports_logs', 0);
    }
}
