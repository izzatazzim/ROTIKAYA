<?php

namespace App\Services;

interface BackupDumpRunner
{
    public function dumpToFile(array $databaseConfig, string $sqlOutputPath): void;
}
