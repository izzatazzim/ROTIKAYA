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

class FlashMessagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @std TC003_01
     * STD Description: Create user with valid data
     * Expected Result: User account is created successfully.
     */
    public function test_TC003_01_admin_can_create_user_with_valid_data(): void
    {
        $adminRole = Role::query()->create(['name' => 'admin']);
        $accountantRole = Role::query()->create(['name' => 'accountant']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'New Accountant',
            'email' => 'new-accountant@example.test',
            'password' => 'password123',
            'role_id' => $accountantRole->id,
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success', 'User created successfully.');
    }

    /**
     * @std TC003_02
     * STD Description: Create user with duplicate email
     * Expected Result: Duplicate email is rejected with explicit validation error.
     */
    public function test_TC003_02_user_creation_blocked_for_duplicate_email(): void
    {
        $adminRole = Role::query()->create(['name' => 'admin']);
        $accountantRole = Role::query()->create(['name' => 'accountant']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        User::factory()->create(['role_id' => $accountantRole->id, 'email' => 'duplicate@example.test']);

        $response = $this->from(route('users.index'))->actingAs($admin)->post(route('users.store'), [
            'name' => 'Duplicate Attempt',
            'email' => 'duplicate@example.test',
            'password' => 'password123',
            'role_id' => $accountantRole->id,
        ]);

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHasErrors(['email']);
        $this->assertSame('Someone is already using this email. Try another one.', session('errors')->first('email'));
        $this->assertSame(2, User::query()->count());
    }

    public function test_self_delete_attempt_sets_error_flash_and_user_still_exists(): void
    {
        $adminRole = Role::query()->create(['name' => 'admin']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);

        $response = $this->actingAs($admin)->delete(route('users.destroy', $admin));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'You cannot delete your own account.');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_invalid_invoice_send_sets_error_flash_for_non_json_request(): void
    {
        config()->set('services.whatsapp.simulate_mode', 'always_fail');

        $adminRole = Role::query()->create(['name' => 'admin']);
        $salesRole = Role::query()->create(['name' => 'sales_staff']);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $sales = User::factory()->create(['role_id' => $salesRole->id]);
        $client = Client::query()->create([
            'name' => 'No Email Client',
            'phone' => '+60111111111',
            'email' => null,
        ]);

        InvoiceTemplate::query()->create([
            'name' => 'Default Template',
            'is_active' => true,
            'is_default' => true,
            'content' => '{"body":"x"}',
            'created_by' => $admin->id,
        ]);

        $sale = Sale::query()->create([
            'client_id' => $client->id,
            'salesperson_id' => $sales->id,
            'campaign_name' => 'Campaign',
            'amount' => 1000,
            'status' => 'completed',
        ]);

        $invoice = Invoice::query()->create([
            'invoice_number' => 'INV-FLASH-001',
            'sale_id' => $sale->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'total_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($admin)->post(route('invoices.send', $invoice));

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
