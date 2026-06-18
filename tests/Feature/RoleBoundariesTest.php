<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceTemplate;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Role-Based Access Control Boundary Tests
 *
 * This file is the canonical regression suite for role enforcement.
 * Every protected route in the application has explicit tests here
 * for: authorized access, each unauthorized role, and guest access.
 *
 * URL tampering scenarios are also covered to ensure scope-bypass
 * attempts (e.g., manipulating salesperson_id query param) cannot
 * circumvent role-based data scoping.
 *
 * If you add a new protected route, add corresponding tests here.
 */
class RoleBoundariesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $accountant;

    private User $salesStaff;

    private User $otherSalesStaff;

    private User $targetUser;

    private Client $clientA;

    private Client $clientB;

    private Sale $saleA;

    private Sale $saleB;

    private Invoice $invoiceA;

    private Invoice $invoiceB;

    private Backup $backup;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::query()->create(['name' => 'admin']);
        $accountantRole = Role::query()->create(['name' => 'accountant']);
        $salesRole = Role::query()->create(['name' => 'sales_staff']);

        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'email' => 'admin@test.local']);
        $this->accountant = User::factory()->create(['role_id' => $accountantRole->id, 'email' => 'accountant@test.local']);
        $this->salesStaff = User::factory()->create(['role_id' => $salesRole->id, 'email' => 'sales-a@test.local']);
        $this->otherSalesStaff = User::factory()->create(['role_id' => $salesRole->id, 'email' => 'sales-b@test.local']);
        $this->targetUser = User::factory()->create(['role_id' => $accountantRole->id, 'email' => 'edit-target@test.local']);

        $this->clientA = Client::query()->create(['name' => 'Client Alpha']);
        $this->clientB = Client::query()->create(['name' => 'Client Bravo']);

        $this->saleA = Sale::query()->create([
            'client_id' => $this->clientA->id,
            'salesperson_id' => $this->salesStaff->id,
            'campaign_name' => 'Campaign Alpha',
            'amount' => 1000,
            'status' => 'completed',
        ]);

        $this->saleB = Sale::query()->create([
            'client_id' => $this->clientB->id,
            'salesperson_id' => $this->otherSalesStaff->id,
            'campaign_name' => 'Campaign Bravo',
            'amount' => 2000,
            'status' => 'completed',
        ]);

        $this->invoiceA = Invoice::query()->create([
            'invoice_number' => 'INV-RBAC-001',
            'sale_id' => $this->saleA->id,
            'issue_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'overdue',
        ]);

        $this->invoiceB = Invoice::query()->create([
            'invoice_number' => 'INV-RBAC-002',
            'sale_id' => $this->saleB->id,
            'issue_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(5)->toDateString(),
            'total_amount' => 2000,
            'paid_amount' => 0,
            'status' => 'overdue',
        ]);

        Backup::query()->create([
            'filename' => 'dummy-old.sql.gz',
            'file_path' => 'backups/dummy-old.sql.gz',
            'file_size' => 100,
            'trigger_type' => 'manual',
            'triggered_by' => $this->admin->id,
            'status' => 'success',
            'completed_at' => now()->subHour(),
        ]);

        $this->backup = Backup::query()->create([
            'filename' => 'dummy.sql.gz',
            'file_path' => 'backups/dummy.sql.gz',
            'file_size' => 200,
            'trigger_type' => 'manual',
            'triggered_by' => $this->admin->id,
            'status' => 'success',
            'completed_at' => now(),
        ]);

        SystemSetting::query()->create([
            'default_payment_term_days' => 30,
            'reminder_intervals' => [15, 30, 45],
            'invoice_template' => 'Default template',
        ]);

        InvoiceTemplate::query()->create([
            'name' => 'RBAC Template',
            'is_active' => true,
            'is_default' => true,
            'content' => '{"body":"template"}',
            'created_by' => $this->admin->id,
        ]);
    }

    // ---------------- Admin-only routes ----------------
    public function test_users_index_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('users.index'), ['admin']);
    }

    public function test_users_store_role_boundaries(): void
    {
        $payload = [
            'name' => 'New User',
            'email' => 'new-user@test.local',
            'password' => 'password123',
            'role_id' => Role::query()->where('name', 'sales_staff')->value('id'),
        ];

        $this->assertRouteRoles('post', route('users.store'), ['admin'], $payload);
    }

    public function test_users_edit_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('users.edit', $this->targetUser), ['admin']);
    }

    public function test_users_update_role_boundaries(): void
    {
        $payload = [
            'name' => 'Updated Name',
            'email' => $this->targetUser->email,
            'password' => '',
            'role_id' => Role::query()->where('name', 'accountant')->value('id'),
        ];

        $this->assertRouteRoles('put', route('users.update', $this->targetUser), ['admin'], $payload);
    }

    public function test_users_destroy_role_boundaries(): void
    {
        $deletable = User::factory()->create([
            'role_id' => Role::query()->where('name', 'sales_staff')->value('id'),
            'email' => 'deletable@test.local',
            'password' => Hash::make('password123'),
        ]);

        $this->assertRouteRoles('delete', route('users.destroy', $deletable), ['admin']);
    }

    public function test_settings_index_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('settings.index'), ['admin']);
    }

    public function test_settings_update_role_boundaries(): void
    {
        $payload = [
            'default_payment_term_days' => 21,
            'reminder_intervals' => '15,30,45',
            'invoice_template' => 'Updated template',
        ];

        $this->assertRouteRoles('post', route('settings.update'), ['admin'], $payload);
    }

    public function test_settings_backup_run_role_boundaries(): void
    {
        $this->assertRouteRoles('post', route('settings.backup.run'), ['admin']);
    }

    public function test_settings_backup_download_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('settings.backup.download', $this->backup), ['admin']);
    }

    public function test_permissions_index_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('permissions.index'), ['admin']);
    }

    // ---------------- Admin + Accountant routes ----------------
    public function test_invoices_store_role_boundaries(): void
    {
        $this->assertRouteRoles('post', route('invoices.store'), ['admin', 'accountant'], ['sale_id' => $this->saleA->id]);
    }

    public function test_invoices_send_role_boundaries(): void
    {
        $this->assertRouteRoles('post', route('invoices.send', $this->invoiceA), ['admin', 'accountant']);
    }

    public function test_payments_index_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('payments.index'), ['admin', 'accountant']);
    }

    public function test_payments_store_role_boundaries(): void
    {
        $payload = [
            'invoice_id' => $this->invoiceA->id,
            'amount' => 200,
            'payment_date' => now()->toDateString(),
            'method' => 'online',
        ];

        $this->assertRouteRoles('post', route('payments.store'), ['admin', 'accountant'], $payload);
    }

    public function test_reports_index_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('reports.index'), ['admin', 'accountant']);
    }

    public function test_reports_download_pdf_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('reports.download', 'financial-summary'), ['admin', 'accountant']);
    }

    public function test_reports_revenue_export_pdf_role_boundaries(): void
    {
        $payload = [
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->endOfMonth()->toDateString(),
        ];

        $this->assertRouteRoles('post', route('reports.revenue.export-pdf'), ['admin', 'accountant'], $payload);
    }

    public function test_reports_revenue_export_xlsx_role_boundaries(): void
    {
        $payload = [
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date' => now()->endOfMonth()->toDateString(),
        ];

        $this->assertRouteRoles('post', route('reports.revenue.export-xlsx'), ['admin', 'accountant'], $payload);
    }

    public function test_reports_client_statement_form_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('reports.client-statement'), ['admin', 'accountant']);
    }

    public function test_reports_client_statement_export_role_boundaries(): void
    {
        $payload = [
            'client_id' => $this->clientA->id,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
        ];

        $this->assertRouteRoles('post', route('reports.client-statement.export'), ['admin', 'accountant'], $payload);
    }

    public function test_reminders_trigger_role_boundaries(): void
    {
        $this->assertRouteRoles('post', route('reminders.trigger'), ['admin', 'accountant']);
    }

    // ---------------- Admin + Sales Staff routes ----------------
    public function test_sales_index_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('sales.index'), ['admin', 'sales_staff']);
    }

    public function test_sales_store_role_boundaries(): void
    {
        $payload = [
            'client_id' => $this->clientA->id,
            'salesperson_id' => $this->salesStaff->id,
            'campaign_name' => 'Boundary Campaign',
            'amount' => 500,
            'status' => 'pending',
        ];

        $this->assertRouteRoles('post', route('sales.store'), ['admin', 'sales_staff'], $payload);
    }

    public function test_clients_index_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('clients.index'), ['admin', 'sales_staff']);
    }

    public function test_clients_store_role_boundaries(): void
    {
        $payload = [
            'name' => 'New Boundary Client',
            'email' => 'boundary-client@test.local',
        ];

        $this->assertRouteRoles('post', route('clients.store'), ['admin', 'sales_staff'], $payload);
    }

    // ---------------- Shared authenticated routes ----------------
    public function test_dashboard_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('dashboard'), ['admin', 'accountant', 'sales_staff']);
    }

    public function test_invoices_index_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('invoices.index'), ['admin', 'accountant', 'sales_staff']);
    }

    public function test_invoices_show_role_boundaries(): void
    {
        $this->assertRouteRoles('get', route('invoices.show', $this->invoiceA), ['admin', 'accountant', 'sales_staff']);
    }

    // ---------------- URL tampering scenarios ----------------
    public function test_sales_staff_dashboard_tampering_cannot_view_other_salesperson_data(): void
    {
        $response = $this->actingAs($this->salesStaff)
            ->get(route('dashboard', ['start' => now()->startOfMonth()->toDateString(), 'end' => now()->endOfMonth()->toDateString(), 'salesperson_id' => $this->otherSalesStaff->id]));

        $response->assertOk();
        $response->assertSee('RM1,000.00');
        $response->assertDontSee('RM3,000.00');
    }

    public function test_sales_staff_invoices_tampering_cannot_view_other_clients_invoices(): void
    {
        $response = $this->actingAs($this->salesStaff)
            ->get(route('invoices.index', ['customer' => 'Client Bravo']));

        $response->assertOk();
        $response->assertDontSee('INV-RBAC-002');
    }

    private function assertRouteRoles(string $method, string $uri, array $allowedRoles, array $payload = []): void
    {
        $roleUsers = [
            'admin' => $this->admin,
            'accountant' => $this->accountant,
            'sales_staff' => $this->salesStaff,
        ];

        foreach ($roleUsers as $role => $user) {
            if (in_array($role, $allowedRoles, true)) {
                continue;
            }

            $response = $this->hitRouteAs($method, $uri, $user, $payload);
            $response->assertForbidden();
        }

        $guestResponse = $this->hitRouteAs($method, $uri, null, $payload);
        $guestResponse->assertRedirect(route('login'));

        foreach ($roleUsers as $role => $user) {
            if (!in_array($role, $allowedRoles, true)) {
                continue;
            }

            $response = $this->hitRouteAs($method, $uri, $user, $payload);
            $this->assertNotSame(403, $response->getStatusCode(), "Role [{$role}] unexpectedly forbidden for {$method} {$uri}");
        }
    }

    private function hitRouteAs(string $method, string $uri, ?User $user, array $payload = [])
    {
        if ($user === null) {
            Auth::logout();
        }

        $request = $user ? $this->actingAs($user) : $this;

        return match (strtolower($method)) {
            'get' => $request->get($uri),
            'post' => $request->post($uri, $payload),
            'put' => $request->put($uri, $payload),
            'delete' => $request->delete($uri, $payload),
            default => throw new \InvalidArgumentException("Unsupported HTTP method [{$method}]"),
        };
    }
}
