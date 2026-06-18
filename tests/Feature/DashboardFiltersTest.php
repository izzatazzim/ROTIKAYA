<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFiltersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @std TC001_04
     * STD Description: Apply date filter no results
     * Expected Result: Dashboard shows explicit no-data messaging with zeroed values.
     */
    public function test_TC001_04_date_filter_with_no_data_shows_empty_states(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get('/dashboard?start=2010-01-01&end=2010-12-31');

        $response->assertOk();
        $response->assertSee('RM0.00');
        $response->assertSee('No revenue data for this period yet.');
        $response->assertSee('No payments match the selected filters.');
    }

    /**
     * @std TC001_03
     * STD Description: View dashboard with available data
     * Expected Result: Dashboard displays available data for selected period.
     */
    public function test_TC001_03_dashboard_displays_data_for_filtered_date_range(): void
    {
        $accountant = $this->createUserWithRole('accountant');
        [$client, $salesperson] = $this->seedSalesGraph();

        $response = $this->actingAs($accountant)->get('/dashboard?start=2026-05-01&end=2026-05-31');

        $response->assertOk();
        $response->assertSee('RM1,000.00');
        $response->assertSee('value="' . $client->id . '"', false);
        $response->assertSee('value="' . $salesperson->id . '"', false);
    }

    public function test_client_filter_scopes_dashboard_data(): void
    {
        $admin = $this->createUserWithRole('admin');
        [$clientA, $salesperson] = $this->seedSalesGraph();
        $clientB = Client::query()->create(['name' => 'Beta LLC']);
        $saleB = Sale::query()->create([
            'client_id' => $clientB->id,
            'salesperson_id' => $salesperson->id,
            'campaign_name' => 'Campaign B',
            'amount' => 5000,
            'status' => 'completed',
            'created_at' => '2026-05-10 10:00:00',
            'updated_at' => '2026-05-10 10:00:00',
        ]);
        $invoiceB = Invoice::query()->create([
            'invoice_number' => 'INV-DASH-200',
            'sale_id' => $saleB->id,
            'issue_date' => '2026-05-10',
            'due_date' => '2026-06-10',
            'total_amount' => 5000,
            'paid_amount' => 5000,
            'status' => 'paid',
        ]);
        Payment::query()->create([
            'invoice_id' => $invoiceB->id,
            'amount' => 5000,
            'payment_date' => '2026-05-12',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard?start=2026-05-01&end=2026-05-31&client_id=' . $clientA->id);

        $response->assertOk();
        $response->assertSee('RM1,000.00');
        $response->assertDontSee('RM6,000.00');
    }

    public function test_salesperson_filter_scopes_for_admin_and_accountant(): void
    {
        $admin = $this->createUserWithRole('admin');
        $salesRole = Role::query()->firstOrCreate(['name' => 'sales_staff']);
        $client = Client::query()->create(['name' => 'Client Z']);
        $salesA = User::factory()->create(['role_id' => $salesRole->id, 'name' => 'Sales A']);
        $salesB = User::factory()->create(['role_id' => $salesRole->id, 'name' => 'Sales B']);

        $this->seedInvoicePayment($client, $salesA, 'INV-A', 1000, '2026-05-03', '2026-05-06');
        $this->seedInvoicePayment($client, $salesB, 'INV-B', 2000, '2026-05-04', '2026-05-07');

        $response = $this->actingAs($admin)->get('/dashboard?start=2026-05-01&end=2026-05-31&salesperson_id=' . $salesA->id);

        $response->assertOk();
        $response->assertSee('RM1,000.00');
        $response->assertDontSee('RM3,000.00');
    }

    public function test_combined_filters_intersect_correctly(): void
    {
        $admin = $this->createUserWithRole('admin');
        $salesRole = Role::query()->firstOrCreate(['name' => 'sales_staff']);
        $clientA = Client::query()->create(['name' => 'Client A']);
        $clientB = Client::query()->create(['name' => 'Client B']);
        $salesA = User::factory()->create(['role_id' => $salesRole->id, 'name' => 'Sales A']);
        $salesB = User::factory()->create(['role_id' => $salesRole->id, 'name' => 'Sales B']);

        $this->seedInvoicePayment($clientA, $salesA, 'INV-COMB-1', 1300, '2026-05-10', '2026-05-15');
        $this->seedInvoicePayment($clientA, $salesB, 'INV-COMB-2', 500, '2026-05-10', '2026-05-15');
        $this->seedInvoicePayment($clientB, $salesA, 'INV-COMB-3', 900, '2026-05-10', '2026-05-15');

        $response = $this->actingAs($admin)->get(
            '/dashboard?start=2026-05-01&end=2026-05-31&client_id=' . $clientA->id . '&salesperson_id=' . $salesA->id
        );

        $response->assertOk();
        $response->assertSee('RM1,300.00');
        $response->assertDontSee('RM2,700.00');
    }

    public function test_sales_staff_cannot_filter_other_salespeople_and_is_auto_scoped(): void
    {
        $salesRole = Role::query()->firstOrCreate(['name' => 'sales_staff']);
        $client = Client::query()->create(['name' => 'Scoped Client']);
        $salesA = User::factory()->create(['role_id' => $salesRole->id, 'name' => 'Sales A']);
        $salesB = User::factory()->create(['role_id' => $salesRole->id, 'name' => 'Sales B']);

        $this->seedInvoicePayment($client, $salesA, 'INV-SA', 700, '2026-05-02', '2026-05-05');
        $this->seedInvoicePayment($client, $salesB, 'INV-SB', 1900, '2026-05-02', '2026-05-05');

        $response = $this->actingAs($salesA)->get('/dashboard?start=2026-05-01&end=2026-05-31&salesperson_id=' . $salesB->id);

        $response->assertOk();
        $response->assertSee('RM700.00');
        $response->assertDontSee('RM2,600.00');
        $response->assertDontSee('name="salesperson_id"', false);
    }

    public function test_validation_end_before_start_returns_422_for_json_requests(): void
    {
        $accountant = $this->createUserWithRole('accountant');

        $response = $this->actingAs($accountant)->getJson('/dashboard?start=2026-05-31&end=2026-05-01');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['end']);
    }

    public function test_default_behavior_without_filters_uses_current_month(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Revenue This Month');
        $response->assertSee('name="start"', false);
        $response->assertSee('name="end"', false);
        $response->assertSee('All Customers');
        $response->assertSee('All Sales Reps');
    }

    private function seedSalesGraph(): array
    {
        $salesRole = Role::query()->firstOrCreate(['name' => 'sales_staff']);
        $salesperson = User::factory()->create(['role_id' => $salesRole->id, 'name' => 'Sales Staff']);
        $client = Client::query()->create(['name' => 'Alpha Sdn Bhd']);

        $this->seedInvoicePayment($client, $salesperson, 'INV-DASH-100', 1000, '2026-05-05', '2026-05-08');
        $this->seedInvoicePayment($client, $salesperson, 'INV-DASH-101', 2000, '2026-04-01', '2026-04-05');

        return [$client, $salesperson];
    }

    private function seedInvoicePayment(
        Client $client,
        User $salesperson,
        string $invoiceNumber,
        float $amount,
        string $issueDate,
        string $paymentDate
    ): void {
        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $salesperson->id,
            'campaign_name' => 'Filtered Campaign ' . $invoiceNumber,
            'amount' => $amount,
            'status' => 'completed',
            'created_at' => $issueDate . ' 10:00:00',
            'updated_at' => $issueDate . ' 10:00:00',
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => $invoiceNumber,
            'sale_id' => $sale->id,
            'issue_date' => $issueDate,
            'due_date' => '2026-06-15',
            'total_amount' => $amount,
            'paid_amount' => $amount,
            'status' => 'paid',
        ]);

        Payment::query()->create([
            'invoice_id' => $invoice->id,
            'amount' => $amount,
            'payment_date' => $paymentDate,
            'method' => 'bank_transfer',
            'reference' => 'REF-' . $invoiceNumber,
        ]);
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(['name' => $roleName]);
        return User::factory()->create(['role_id' => $role->id]);
    }
}
