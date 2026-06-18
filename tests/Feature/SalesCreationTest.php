<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesCreationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @std TC001_01
     * STD Description: Add Sale with valid inputs
     * Expected Result: New sale is saved and visible in system records.
     */
    public function test_TC001_01_sales_staff_can_create_sale_with_valid_inputs(): void
    {
        $role = Role::query()->create(['name' => 'sales_staff']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $client = Client::query()->create(['name' => 'Acme']);

        $response = $this->actingAs($user)->post('/sales', [
            'client_id' => $client->id,
            'salesperson_id' => $user->id,
            'campaign_name' => 'Test Campaign',
            'amount' => 1200,
            'status' => 'pending',
        ]);

        $response->assertRedirect('/sales/new');
        $this->assertDatabaseHas('sales', ['campaign_name' => 'Test Campaign']);
    }

    /**
     * @std TC001_02
     * STD Description: Add Sale missing client name
     * Expected Result: Validation error is shown and sale is not created.
     */
    public function test_TC001_02_sale_creation_blocked_when_client_name_missing(): void
    {
        $role = Role::query()->create(['name' => 'sales_staff']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $response = $this->from('/sales/new')->actingAs($user)->post('/sales', [
            'salesperson_id' => $user->id,
            'campaign_name' => 'Missing Client Campaign',
            'amount' => 1200,
            'status' => 'pending',
        ]);

        $response->assertRedirect('/sales/new');
        $response->assertSessionHasErrors(['client_id']);
        $this->assertSame(
            'Please select a customer.',
            session('errors')->first('client_id')
        );
        $this->assertDatabaseMissing('sales', ['campaign_name' => 'Missing Client Campaign']);
    }
}
