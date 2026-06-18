<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $role = Role::query()->create(['name' => 'admin']);
        $user = User::factory()->create(['role_id' => $role->id]);
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
    }
}
