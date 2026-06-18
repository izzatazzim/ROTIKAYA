<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_visiting_reports_index_does_not_create_report_log(): void
    {
        $accountantRole = Role::query()->create(['name' => 'accountant']);
        $accountant = User::factory()->create(['role_id' => $accountantRole->id]);

        $response = $this->actingAs($accountant)->get(route('reports.index'));

        $response->assertOk();
        $this->assertDatabaseCount('reports_logs', 0);
    }

    public function test_actual_report_export_creates_report_log(): void
    {
        $accountantRole = Role::query()->create(['name' => 'accountant']);
        $accountant = User::factory()->create(['role_id' => $accountantRole->id]);

        $response = $this->actingAs($accountant)->get(route('reports.download', 'financial-summary'));

        $response->assertOk();
        $this->assertDatabaseHas('reports_logs', [
            'generated_by' => $accountant->id,
            'report_type' => 'financial-summary',
        ]);
    }
}
