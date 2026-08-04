<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup
                            {--path= : Custom directory to store the backup}
                            {--keep=30 : Number of days to retain old backups}';

    protected $description = 'Create a daily backup of the database';

    public function handle(): int
    {
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        $backupDir = $this->option('path') ?: storage_path('app/backups');
        $keepDays = (int) $this->option('keep');
        $timestamp = now()->format('Y-m-d_His');
        $filename = "backup_{$timestamp}.sql";
        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0750, true);
        }

        $this->info("Starting {$connection} database backup...");

        $result = match ($connection) {
            'mysql' => $this->backupMysql($dbConfig, $filepath),
            'pgsql' => $this->backupPostgres($dbConfig, $filepath),
            'sqlite' => $this->backupSqlite($dbConfig, $filepath),
            default => $this->fail("Unsupported database driver: {$connection}"),
        };

        if ($result !== 0) {
            return $result;
        }

        $this->info("Backup saved to: {$filepath}");

        $this->pruneOldBackups($backupDir, $keepDays);

        return 0;
    }

    protected function backupMysql(array $config, string $filepath): int
    {
        $command = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s --single-transaction --routines --triggers > %s',
            escapeshellarg($config['host']),
            escapeshellarg($config['port'] ?? '3306'),
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['database']),
            escapeshellarg($filepath)
        );

        $result = Process::run($command);

        if (!$result->successful()) {
            $this->error('MySQL backup failed: ' . $result->errorOutput());
            return 1;
        }

        return 0;
    }

    protected function backupPostgres(array $config, string $filepath): int
    {
        $command = sprintf(
            'PGPASSWORD=%s pg_dump --host=%s --port=%s --username=%s --format=plain --no-owner %s > %s',
            escapeshellarg($config['password']),
            escapeshellarg($config['host']),
            escapeshellarg($config['port'] ?? '5432'),
            escapeshellarg($config['username']),
            escapeshellarg($config['database']),
            escapeshellarg($filepath)
        );

        $result = Process::run($command);

        if (!$result->successful()) {
            $this->error('PostgreSQL backup failed: ' . $result->errorOutput());
            return 1;
        }

        return 0;
    }

    protected function backupSqlite(array $config, string $filepath): int
    {
        $dbPath = $config['database'];

        if (!file_exists($dbPath)) {
            $this->error("SQLite database not found: {$dbPath}");
            return 1;
        }

        $destPath = str_replace('.sql', '.sqlite', $filepath);

        if (!copy($dbPath, $destPath)) {
            $this->error('Failed to copy SQLite database.');
            return 1;
        }

        $this->info("SQLite backup saved to: {$destPath}");
        return 0;
    }

    protected function pruneOldBackups(string $backupDir, int $keepDays): void
    {
        $cutoff = now()->subDays($keepDays)->getTimestamp();
        $files = glob($backupDir . DIRECTORY_SEPARATOR . 'backup_*');

        $deleted = 0;

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Pruned {$deleted} old backup(s) older than {$keepDays} days.");
        }
    }

    protected function fail(string $message): int
    {
        $this->error($message);
        return 1;
    }
}
