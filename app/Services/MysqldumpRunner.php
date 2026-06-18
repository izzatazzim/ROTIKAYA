<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class MysqldumpRunner implements BackupDumpRunner
{
    public function dumpToFile(array $databaseConfig, string $sqlOutputPath): void
    {
        $finder = new ExecutableFinder();
        $mysqldump = $finder->find('mysqldump');

        if (! $mysqldump) {
            throw new RuntimeException('mysqldump is not available in PATH. Install MySQL client tools and ensure mysqldump is accessible.');
        }

        if (($databaseConfig['driver'] ?? null) !== 'mysql') {
            throw new RuntimeException('backup:database currently supports mysql driver only.');
        }

        $command = [
            $mysqldump,
            '--host=' . ($databaseConfig['host'] ?? '127.0.0.1'),
            '--port=' . ($databaseConfig['port'] ?? '3306'),
            '--user=' . ($databaseConfig['username'] ?? ''),
            '--single-transaction',
            '--skip-lock-tables',
            $databaseConfig['database'] ?? '',
        ];

        if (! empty($databaseConfig['password'])) {
            $command[] = '--password=' . $databaseConfig['password'];
        }

        $process = new Process($command);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'mysqldump failed without details.');
        }

        file_put_contents($sqlOutputPath, $process->getOutput());
    }
}
