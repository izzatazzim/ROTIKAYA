<?php

namespace Tests\Feature;

use App\Models\Backup;
use App\Models\Role;
use App\Models\User;
use App\Services\BackupDumpRunner;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_manual_trigger_from_admin_creates_backup_file_and_row(): void
    {
        $admin = $this->createUserWithRole('admin');
        $this->bindRunner(new class implements BackupDumpRunner {
            public function dumpToFile(array $databaseConfig, string $sqlOutputPath): void
            {
                file_put_contents($sqlOutputPath, 'CREATE TABLE demo (id INT);');
            }
        });

        $response = $this->actingAs($admin)->post(route('settings.backup.run'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $backup = Backup::query()->latest('id')->first();
        $this->assertNotNull($backup);
        $this->assertSame('manual', $backup->trigger_type);
        $this->assertSame($admin->id, $backup->triggered_by);
        $this->assertSame('success', $backup->status);
        Storage::disk('local')->assertExists($backup->file_path);
    }

    public function test_authorization_for_manual_trigger(): void
    {
        $accountant = $this->createUserWithRole('accountant');
        $sales = $this->createUserWithRole('sales_staff');

        $this->actingAs($accountant)->post(route('settings.backup.run'))->assertStatus(403);
        $this->actingAs($sales)->post(route('settings.backup.run'))->assertStatus(403);
        auth()->logout();
        $this->post(route('settings.backup.run'))->assertRedirect(route('login'));
    }

    public function test_download_permissions_admin_allowed_others_blocked(): void
    {
        $admin = $this->createUserWithRole('admin');
        $accountant = $this->createUserWithRole('accountant');
        $sales = $this->createUserWithRole('sales_staff');

        Storage::disk('local')->put('backups/demo.sql.gz', 'gz-content');
        $backup = Backup::query()->create([
            'filename' => 'demo.sql.gz',
            'file_path' => 'backups/demo.sql.gz',
            'file_size' => 10,
            'trigger_type' => 'manual',
            'triggered_by' => $admin->id,
            'status' => 'success',
            'completed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('settings.backup.download', $backup))
            ->assertOk();

        $this->actingAs($accountant)->get(route('settings.backup.download', $backup))->assertStatus(403);
        $this->actingAs($sales)->get(route('settings.backup.download', $backup))->assertStatus(403);
    }

    public function test_retention_deletes_oldest_file_and_row_when_31st_backup_runs(): void
    {
        $admin = $this->createUserWithRole('admin');
        $this->bindRunner(new class implements BackupDumpRunner {
            public function dumpToFile(array $databaseConfig, string $sqlOutputPath): void
            {
                file_put_contents($sqlOutputPath, 'DUMP');
            }
        });

        $start = Carbon::create(2026, 1, 1, 0, 0, 0);
        Carbon::setTestNow($start);

        for ($i = 0; $i < 31; $i++) {
            Carbon::setTestNow($start->copy()->addSeconds($i));
            $this->actingAs($admin)->post(route('settings.backup.run'))->assertRedirect();
        }

        Carbon::setTestNow();

        $this->assertSame(30, Backup::query()->count());
        $oldestDeleted = Backup::query()->where('filename', 'rotikaya-backup-2026-01-01-000000.sql.gz')->first();
        $this->assertNull($oldestDeleted);
        Storage::disk('local')->assertMissing('backups/rotikaya-backup-2026-01-01-000000.sql.gz');
    }

    public function test_failure_handling_creates_failed_row_without_orphan_file(): void
    {
        $admin = $this->createUserWithRole('admin');
        $this->bindRunner(new class implements BackupDumpRunner {
            public function dumpToFile(array $databaseConfig, string $sqlOutputPath): void
            {
                throw new \RuntimeException('mysqldump not found');
            }
        });

        $response = $this->actingAs($admin)->post(route('settings.backup.run'));
        $response->assertRedirect();
        $response->assertSessionHasErrors('backup');

        $backup = Backup::query()->latest('id')->first();
        $this->assertNotNull($backup);
        $this->assertSame('failed', $backup->status);
        $this->assertStringContainsString('mysqldump not found', (string) $backup->error_message);
        Storage::disk('local')->assertMissing($backup->file_path);
    }

    /**
     * @std TC003_03
     * STD Description: Update payment terms setting
     * Expected Result: Setting update is saved and persisted successfully.
     */
    public function test_TC003_03_admin_can_update_payment_terms_setting(): void
    {
        $admin = $this->createUserWithRole('admin');

        $response = $this->actingAs($admin)->post(route('settings.update'), [
            'default_payment_term_days' => 45,
            'reminder_intervals' => '15,30,45',
            'invoice_template' => 'Template body v2',
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success', 'Settings updated successfully.');
        $this->assertDatabaseHas('system_settings', ['id' => 1, 'default_payment_term_days' => 45]);
    }

    /**
     * @std TC003_04
     * STD Description: Set invalid reminder interval
     * Expected Result: Invalid reminder interval is rejected with explicit error and previous value remains.
     */
    public function test_TC003_04_invalid_reminder_interval_explicitly_rejected(): void
    {
        $admin = $this->createUserWithRole('admin');

        \App\Models\SystemSetting::query()->create([
            'id' => 1,
            'default_payment_term_days' => 30,
            'reminder_intervals' => [15, 30, 45],
            'invoice_template' => 'Baseline template',
        ]);

        foreach (['-5', 'abc', '0', ''] as $invalidIntervals) {
            $response = $this->from(route('settings.index'))->actingAs($admin)->post(route('settings.update'), [
                'default_payment_term_days' => 30,
                'reminder_intervals' => $invalidIntervals,
                'invoice_template' => 'Baseline template',
            ]);

            $response->assertRedirect(route('settings.index'));
            $response->assertSessionHasErrors(['reminder_intervals']);
            $expectedMessage = $invalidIntervals === ''
                ? 'The reminder intervals field is required.'
                : 'Please enter whole numbers separated by commas, for example: 15,30,45.';
            $this->assertSame($expectedMessage, session('errors')->first('reminder_intervals'));

            $this->assertDatabaseHas('system_settings', [
                'id' => 1,
                'default_payment_term_days' => 30,
            ]);
            $this->assertSame([15, 30, 45], \App\Models\SystemSetting::query()->findOrFail(1)->reminder_intervals);
        }
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::query()->firstOrCreate(['name' => $roleName]);
        return User::factory()->create(['role_id' => $role->id]);
    }

    private function bindRunner(BackupDumpRunner $runner): void
    {
        $this->app->instance(BackupDumpRunner::class, $runner);
    }
}
