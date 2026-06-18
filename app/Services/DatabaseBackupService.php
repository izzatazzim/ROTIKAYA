<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DatabaseBackupService
{
    public function __construct(private readonly BackupDumpRunner $dumpRunner)
    {
    }

    public function run(string $triggerType, ?int $triggeredBy = null): array
    {
        $timestamp = now()->format('Y-m-d-His');
        $filename = "rotikaya-backup-{$timestamp}.sql.gz";
        $filePath = "backups/{$filename}";
        $sqlTempPath = Storage::disk('local')->path("backups/{$timestamp}.sql");

        Storage::disk('local')->makeDirectory('backups');

        try {
            $databaseConfig = config('database.connections.' . config('database.default'));
            $this->dumpRunner->dumpToFile($databaseConfig, $sqlTempPath);

            $sqlContent = file_get_contents($sqlTempPath);
            if ($sqlContent === false) {
                throw new \RuntimeException('Unable to read generated SQL dump for compression.');
            }

            $compressed = gzencode($sqlContent, 9);
            if ($compressed === false) {
                throw new \RuntimeException('Failed to gzip compress SQL dump.');
            }

            Storage::disk('local')->put($filePath, $compressed);
            @unlink($sqlTempPath);

            $fileSize = (int) Storage::disk('local')->size($filePath);

            Backup::query()->create([
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'trigger_type' => $triggerType,
                'triggered_by' => $triggeredBy,
                'status' => 'success',
                'error_message' => null,
                'completed_at' => now(),
            ]);

            $this->enforceRetention();

            return [
                'success' => true,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => $fileSize,
            ];
        } catch (Throwable $throwable) {
            @unlink($sqlTempPath);
            if (Storage::disk('local')->exists($filePath)) {
                Storage::disk('local')->delete($filePath);
            }

            Backup::query()->create([
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => 0,
                'trigger_type' => $triggerType,
                'triggered_by' => $triggeredBy,
                'status' => 'failed',
                'error_message' => $throwable->getMessage(),
                'completed_at' => now(),
            ]);

            return [
                'success' => false,
                'error' => $throwable->getMessage(),
                'filename' => $filename,
            ];
        }
    }

    private function enforceRetention(): void
    {
        $expired = Backup::query()
            ->latest('completed_at')
            ->skip(30)
            ->take(500)
            ->get();

        foreach ($expired as $backup) {
            if (Storage::disk('local')->exists($backup->file_path)) {
                Storage::disk('local')->delete($backup->file_path);
            }
            Log::info('Backup removed by retention policy', [
                'backup_id' => $backup->id,
                'filename' => $backup->filename,
            ]);
            $backup->delete();
        }
    }
}
