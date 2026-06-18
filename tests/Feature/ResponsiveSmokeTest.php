<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResponsiveSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_all_return_ok_after_responsive_layout(): void
    {
        $admin = $this->makeUserWithRole('admin');
        [$invoice] = $this->seedInvoiceData($admin);

        foreach ([
            route('dashboard'),
            route('users.index'),
            route('users.edit', $admin),
            route('permissions.index'),
            route('settings.index'),
            route('invoices.index'),
            route('invoices.show', $invoice),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_accountant_pages_all_return_ok_after_responsive_layout(): void
    {
        $admin = $this->makeUserWithRole('admin');
        [$invoice] = $this->seedInvoiceData($admin);
        $accountant = $this->makeUserWithRole('accountant');

        foreach ([
            route('dashboard'),
            route('invoices.index'),
            route('invoices.show', $invoice),
            route('payments.index'),
            route('reports.index'),
            route('reports.client-statement'),
        ] as $url) {
            $this->actingAs($accountant)->get($url)->assertOk();
        }
    }

    public function test_sales_staff_pages_all_return_ok_after_responsive_layout(): void
    {
        $salesStaff = $this->makeUserWithRole('sales_staff');

        foreach ([
            route('dashboard'),
            route('invoices.index'),
            route('clients.index'),
            route('sales.index'),
        ] as $url) {
            $this->actingAs($salesStaff)->get($url)->assertOk();
        }
    }

    private function makeUserWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(['name' => $roleName]);
        return User::factory()->create(['role_id' => $role->id]);
    }

    private function seedInvoiceData(User $admin): array
    {
        $salesRole = Role::query()->firstOrCreate(['name' => 'sales_staff']);
        $salesperson = User::factory()->create(['role_id' => $salesRole->id]);
        $client = Client::query()->create(['name' => 'Responsive Test Client']);

        InvoiceTemplate::query()->create([
            'name' => 'Default Template',
            'is_active' => true,
            'is_default' => true,
            'content' => '{"body":"x"}',
            'created_by' => $admin->id,
        ]);

        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $salesperson->id,
            'campaign_name' => 'Responsive Campaign',
            'amount' => 1000,
            'status' => 'completed',
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-RSP-001',
            'sale_id' => $sale->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        return [$invoice, $admin];
    }
}
