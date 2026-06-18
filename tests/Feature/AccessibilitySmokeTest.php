<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilitySmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_auth_pages_render_with_expected_content(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign In');

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Forgot Password');

        $this->get(route('password.reset', ['token' => 'sample-token', 'email' => 'user@example.test']))
            ->assertOk()
            ->assertSee('Reset Password');
    }

    public function test_admin_pages_render_successfully(): void
    {
        $admin = $this->makeUserWithRole('admin');
        [$invoice] = $this->seedInvoiceData($admin);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()
            ->assertSee('Users');

        $this->actingAs($admin)->get(route('users.edit', $admin))
            ->assertOk()
            ->assertSee('Edit User');

        $this->actingAs($admin)->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Database Backup');

        $this->actingAs($admin)->get(route('permissions.index'))
            ->assertOk()
            ->assertSee('Roles & Access');

        $this->actingAs($admin)->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number);
    }

    public function test_accountant_pages_render_successfully(): void
    {
        $admin = $this->makeUserWithRole('admin');
        [$invoice] = $this->seedInvoiceData($admin);
        $accountant = $this->makeUserWithRole('accountant');

        $this->actingAs($accountant)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');

        $this->actingAs($accountant)->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('Invoices');

        $this->actingAs($accountant)->get(route('invoices.show', $invoice))
            ->assertOk()
            ->assertSee($invoice->invoice_number);

        $this->actingAs($accountant)->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Payments');

        $this->actingAs($accountant)->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Revenue Report');

        $this->actingAs($accountant)->get(route('reports.client-statement'))
            ->assertOk()
            ->assertSee('Customer Statement');
    }

    public function test_sales_staff_pages_render_successfully(): void
    {
        $salesStaff = $this->makeUserWithRole('sales_staff');

        $this->actingAs($salesStaff)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');

        $this->actingAs($salesStaff)->get(route('invoices.index'))
            ->assertOk()
            ->assertSee('Invoices');

        $this->actingAs($salesStaff)->get(route('sales.index'))
            ->assertOk()
            ->assertSee('Record New Sale');

        $this->actingAs($salesStaff)->get(route('clients.index'))
            ->assertOk()
            ->assertSee('Customers');
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
        $client = Client::query()->create(['name' => 'Contrast Test Client']);

        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $salesperson->id,
            'campaign_name' => 'Contrast Campaign',
            'amount' => 1200,
            'status' => 'completed',
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-A11Y-001',
            'sale_id' => $sale->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'total_amount' => 1200,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        return [$invoice, $admin];
    }
}
