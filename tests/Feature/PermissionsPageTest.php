<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_permissions_page_and_see_matrix(): void
    {
        $admin = $this->makeUserWithRole('admin');

        $this->actingAs($admin)
            ->get(route('permissions.index'))
            ->assertOk()
            ->assertSee('Roles & Access');
    }

    public function test_accountant_gets_forbidden(): void
    {
        $accountant = $this->makeUserWithRole('accountant');

        $this->actingAs($accountant)
            ->get(route('permissions.index'))
            ->assertForbidden();
    }

    public function test_sales_staff_gets_forbidden(): void
    {
        $sales = $this->makeUserWithRole('sales_staff');

        $this->actingAs($sales)
            ->get(route('permissions.index'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('permissions.index'))
            ->assertRedirect(route('login'));
    }

    public function test_permissions_page_renders_all_roles_from_config(): void
    {
        $admin = $this->makeUserWithRole('admin');
        $response = $this->actingAs($admin)->get(route('permissions.index'));
        $response->assertOk();

        foreach ((array) config('permissions.roles') as $roleConfig) {
            $response->assertSee((string) ($roleConfig['label'] ?? ''));
        }
    }

    public function test_permissions_page_renders_all_permissions_from_config(): void
    {
        $admin = $this->makeUserWithRole('admin');
        $response = $this->actingAs($admin)->get(route('permissions.index'));
        $response->assertOk();

        foreach ((array) config('permissions.roles') as $roleConfig) {
            foreach ((array) ($roleConfig['permissions'] ?? []) as $permission) {
                $response->assertSee($permission);
                $response->assertSee((string) (config("permissions.permission_descriptions.{$permission}") ?? ''));
            }
        }
    }

    private function makeUserWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(['name' => $roleName]);

        return User::factory()->create(['role_id' => $role->id]);
    }
}
