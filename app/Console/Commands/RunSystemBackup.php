<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

/**
 * RunSystemBackup
 *
 * Automated Artisan command to execute system backups and prune old archives:
 * - Runs weekly via Laravel scheduler.
 * - Supports --type=db (default) and --type=full.
 * - Prunes archives exceeding --retention (default 4).
 */
class RunSystemBackup extends Command
{
    protected $signature = 'backup:run 
                            {--type=db : Backup type: "db" for database dump or "full" for full system archive}
                            {--retention=4 : Number of most recent backups to preserve}';

    protected $description = 'Generate a C-LACTS database dump or full system disaster recovery archive with automatic retention pruning.';

    public function handle(): int
    {
        $type = strtolower($this->option('type') ?? 'db');
        if (!in_array($type, ['db', 'full'], true)) {
            $this->error("Invalid backup type [{$type}]. Supported types are 'db' and 'full'.");
            return 1;
        }

        $retention = (int) ($this->option('retention') ?? 4);

        $this->info("Initiating C-LACTS backup (Type: " . ($type === 'full' ? 'Full System Archive' : 'Database Dump') . ")...");

        try {
            $backup = BackupService::createBackup($type);

            $this->line("  ✓ Backup created successfully: {$backup['filename']}");
            $this->line("  ✓ File Size: {$backup['size_formatted']}");
            $this->line("  ✓ SHA-256: {$backup['sha256']}");

            // Retention pruning
            if ($retention > 0) {
                $pruned = BackupService::pruneOldBackups($retention);
                if ($pruned > 0) {
                    $this->info("  ✓ Pruned {$pruned} expired backup(s) to maintain retention limit of {$retention}.");
                }
            }

            $this->info("Backup process finished successfully.");
            return 0;
        } catch (\Throwable $e) {
            $this->error("Backup failed: " . $e->getMessage());
            return 1;
        }
    }
}
