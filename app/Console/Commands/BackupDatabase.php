<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--manual : Mark as manual trigger} {--triggered-by= : Internal user id for manual trigger}';

    protected $description = 'Create a compressed database backup file';

    public function __construct(private readonly DatabaseBackupService $backupService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $manual = (bool) $this->option('manual');
        $triggerType = $manual ? 'manual' : 'scheduled';
        $triggeredBy = $manual && $this->option('triggered-by')
            ? (int) $this->option('triggered-by')
            : null;

        $result = $this->backupService->run($triggerType, $triggeredBy);
        if (! $result['success']) {
            $this->error('Backup failed: ' . ($result['error'] ?? 'Unknown error.'));
            return self::FAILURE;
        }

        $this->info('Backup completed: ' . $result['filename']);
        return self::SUCCESS;
    }
}
