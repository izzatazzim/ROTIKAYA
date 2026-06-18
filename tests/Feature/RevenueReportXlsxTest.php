<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class RevenueReportXlsxTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @std TC002_05
     * STD Description: Generate revenue report in date range
     * Expected Result: XLSX export is generated with expected sheets and data.
     */
    public function test_TC002_05_generate_xlsx_with_valid_filters_and_expected_sheets(): void
    {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $adminRole = Role::query()->create(['name' => 'admin']);
        $salesRole = Role::query()->create(['name' => 'sales_staff']);

        $admin = User::factory()->create(['role_id' => $adminRole->id, 'name' => 'Admin User']);
        $salesUser = User::factory()->create(['role_id' => $salesRole->id, 'name' => 'Sarah Sales']);
        $client = Client::query()->create(['name' => 'ABC Media']);

        $saleOne = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $salesUser->id,
            'campaign_name' => 'Campaign A',
            'amount' => 1000,
            'status' => 'completed',
        ]);

        $saleTwo = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $salesUser->id,
            'campaign_name' => 'Campaign B',
            'amount' => 2000,
            'status' => 'completed',
        ]);

        $invoiceOne = Invoice::query()->create([
            'invoice_number' => 'INV-RRX-001',
            'sale_id' => $saleOne->id,
            'issue_date' => now()->startOfMonth()->addDays(5)->toDateString(),
            'due_date' => now()->startOfMonth()->addDays(20)->toDateString(),
            'total_amount' => 1000,
            'paid_amount' => 1000,
            'status' => 'paid',
        ]);

        $invoiceTwo = Invoice::query()->create([
            'invoice_number' => 'INV-RRX-002',
            'sale_id' => $saleTwo->id,
            'issue_date' => now()->startOfMonth()->addDays(10)->toDateString(),
            'due_date' => now()->startOfMonth()->addDays(25)->toDateString(),
            'total_amount' => 2000,
            'paid_amount' => 500,
            'status' => 'overdue',
        ]);

        Payment::query()->create([
            'invoice_id' => $invoiceOne->id,
            'amount' => 1000,
            'payment_date' => now()->startOfMonth()->addDays(7)->toDateString(),
            'method' => 'bank_transfer',
            'reference' => 'REF-001',
        ]);

        Payment::query()->create([
            'invoice_id' => $invoiceTwo->id,
            'amount' => 500,
            'payment_date' => now()->startOfMonth()->addDays(12)->toDateString(),
            'method' => 'online',
            'reference' => 'REF-002',
        ]);

        $response = $this->actingAs($admin)->post(route('reports.revenue.export-xlsx'), [
            'from_date' => $start,
            'to_date' => $end,
            'client_id' => $client->id,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('content-disposition', "attachment; filename=revenue-report-abc-media-{$start}-to-{$end}.xlsx");

        $tempPath = storage_path('framework/testing/revenue-report-test.xlsx');
        file_put_contents($tempPath, $response->streamedContent());

        $spreadsheet = IOFactory::load($tempPath);
        $sheetNames = $spreadsheet->getSheetNames();
        $this->assertSame(['Summary', 'Invoices', 'Payments', 'Sales'], $sheetNames);

        $summary = $spreadsheet->getSheetByName('Summary');
        $this->assertSame('Total Revenue', (string) $summary->getCell('C1')->getValue());
        $this->assertSame(3000.0, (float) $summary->getCell('C2')->getValue());
        $this->assertSame(2.0, (float) $summary->getCell('D2')->getValue());
        $this->assertSame(1500.0, (float) $summary->getCell('E2')->getValue());

        $invoices = $spreadsheet->getSheetByName('Invoices');
        $this->assertSame('Invoice No.', (string) $invoices->getCell('A1')->getValue());
        $this->assertSame('Salesperson', (string) $invoices->getCell('I1')->getValue());

        $payments = $spreadsheet->getSheetByName('Payments');
        $this->assertSame('Payment Date', (string) $payments->getCell('A1')->getValue());
        $this->assertSame('Recorded By', (string) $payments->getCell('G1')->getValue());

        $sales = $spreadsheet->getSheetByName('Sales');
        $this->assertSame('Sale No.', (string) $sales->getCell('A1')->getValue());
        $this->assertSame('Status', (string) $sales->getCell('G1')->getValue());

        $this->assertDatabaseHas('reports_logs', [
            'generated_by' => $admin->id,
            'report_type' => 'revenue_report_xlsx',
        ]);
    }

    /**
     * @std TC002_06
     * STD Description: Generate revenue report with no data
     * Expected Result: Explicit no-records message is shown and no export log is written.
     */
    public function test_TC002_06_revenue_report_with_no_data_shows_explicit_message(): void
    {
        $adminRole = Role::query()->create(['name' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $client = Client::query()->create(['name' => 'No Data Client']);

        $response = $this->from(route('reports.index'))
            ->actingAs($admin)
            ->post(route('reports.revenue.export-xlsx'), [
                'from_date' => '2020-01-01',
                'to_date' => '2020-01-31',
                'client_id' => $client->id,
            ]);

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('reports_logs', 0);
    }

    public function test_authorization_for_revenue_xlsx_export(): void
    {
        $salesRole = Role::query()->create(['name' => 'sales_staff']);
        $sales = User::factory()->create(['role_id' => $salesRole->id]);

        $salesResponse = $this->actingAs($sales)->post(route('reports.revenue.export-xlsx'), [
            'from_date' => '2026-01-01',
            'to_date' => '2026-01-31',
        ]);
        $salesResponse->assertStatus(403);

        auth()->logout();
        $guestResponse = $this->post(route('reports.revenue.export-xlsx'), [
            'from_date' => '2026-01-01',
            'to_date' => '2026-01-31',
        ]);
        $guestResponse->assertRedirect(route('login'));
    }

    public function test_invalid_date_range_returns_validation_error(): void
    {
        $accountantRole = Role::query()->create(['name' => 'accountant']);
        $accountant = User::factory()->create(['role_id' => $accountantRole->id]);

        $response = $this->from(route('reports.index'))
            ->actingAs($accountant)
            ->post(route('reports.revenue.export-xlsx'), [
                'from_date' => '2026-02-01',
                'to_date' => '2026-01-01',
            ]);

        $response->assertRedirect(route('reports.index'));
        $response->assertSessionHasErrors('to_date');
    }
}
