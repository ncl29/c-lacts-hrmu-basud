<?php

namespace App\Services;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

/**
 * BackupService
 *
 * Handles database dumps and disaster recovery archives for C-LACTS:
 * - Pure PHP PDO MySQL schema & data dumper (Zero external binary dependency).
 * - Full system archive (Database dump + physical document attachments + manifest).
 * - SHA-256 integrity hashing and automated retention pruning.
 */
class BackupService
{
    /**
     * Absolute directory path where backups are securely stored.
     */
    public static function getBackupDirectory(): string
    {
        $path = storage_path('app/backups');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
        return $path;
    }

    /**
     * Create a backup based on the requested type ('db' or 'full').
     *
     * @param string $type 'db' for database dump, 'full' for complete archive
     * @return array Metadata of the created backup
     */
    public static function createBackup(string $type = 'db'): array
    {
        $backupDir = self::getBackupDirectory();
        $timestamp = Carbon::now()->format('Y-m-d_His');

        if ($type === 'full') {
            return self::createFullSystemArchive($backupDir, $timestamp);
        }

        return self::createDatabaseBackup($backupDir, $timestamp);
    }

    /**
     * Generate a complete SQL database dump and compress it.
     */
    public static function createDatabaseBackup(string $backupDir, string $timestamp): array
    {
        $filename = "clacts_db_{$timestamp}.sql.gz";
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $sqlContent = self::dumpDatabaseToSqlString();

        // Compress using gzencode (fallback to raw .sql if gz missing)
        if (function_exists('gzencode')) {
            $dataToWrite = gzencode($sqlContent, 9);
        } else {
            $filename = "clacts_db_{$timestamp}.sql";
            $filePath = $backupDir . DIRECTORY_SEPARATOR . $filename;
            $dataToWrite = $sqlContent;
        }

        File::put($filePath, $dataToWrite);

        $size = filesize($filePath);
        $sha256 = hash_file('sha256', $filePath);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'BACKUP_CREATED',
            'table_name' => 'system_backups',
            'record_id' => null,
            'details' => json_encode([
                'filename' => $filename,
                'type' => 'Database Dump',
                'size_bytes' => $size,
                'sha256' => $sha256,
            ]),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'logged_at' => now(),
        ]);

        return [
            'filename' => $filename,
            'type' => 'Database Dump',
            'path' => $filePath,
            'size' => $size,
            'size_formatted' => self::formatBytes($size),
            'sha256' => $sha256,
            'created_at' => Carbon::now()->format('M d, Y • h:i A'),
        ];
    }

    /**
     * Generate a full disaster recovery archive (.zip) containing:
     * 1. database.sql
     * 2. Physical document attachments (from storage/app/public / documents)
     * 3. manifest.json (Integrity, metadata, app encryption key hash)
     */
    public static function createFullSystemArchive(string $backupDir, string $timestamp): array
    {
        $filename = "clacts_full_{$timestamp}.zip";
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        $sqlContent = self::dumpDatabaseToSqlString();

        $zip = new ZipArchive();
        if ($zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("Could not create ZIP archive at {$filePath}");
        }

        // 1. Add SQL Dump
        $zip->addFromString('database.sql', $sqlContent);

        // 2. Add Uploaded Attachments
        $attachmentDirs = array_unique([
            FileStorageService::getBaseDir(),
            storage_path('app/public'),
            storage_path('app/documents'),
            storage_path('app/attachments'),
        ]);

        $filesCount = 0;
        foreach ($attachmentDirs as $dir) {
            if (File::exists($dir) && File::isDirectory($dir)) {
                $files = File::allFiles($dir);
                foreach ($files as $file) {
                    $relativePath = 'attachments/' . $file->getRelativePathname();
                    $zip->addFile($file->getRealPath(), $relativePath);
                    $filesCount++;
                }
            }
        }

        // 3. Add Manifest Metadata
        $manifest = [
            'system' => 'Centralized Leave Application & Credit Tracking System (C-LACTS)',
            'municipality' => 'Municipality of Basud, Camarines Norte',
            'unit' => 'Human Resource Management Unit (HRMU)',
            'created_at' => Carbon::now()->toIso8601String(),
            'created_by' => Auth::user()?->full_name ?? 'System Automated Scheduler',
            'type' => 'Full Disaster Recovery Archive',
            'database_sql_size_bytes' => strlen($sqlContent),
            'attachment_files_count' => $filesCount,
            'app_key_fingerprint' => hash('sha256', config('app.key') ?? 'default_key'),
        ];

        $zip->addFromString('manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        $zip->close();

        $size = filesize($filePath);
        $sha256 = hash_file('sha256', $filePath);

        // Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'BACKUP_CREATED',
            'table_name' => 'system_backups',
            'record_id' => null,
            'details' => json_encode([
                'filename' => $filename,
                'type' => 'Full System Archive',
                'size_bytes' => $size,
                'attachments_count' => $filesCount,
                'sha256' => $sha256,
            ]),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'logged_at' => now(),
        ]);

        return [
            'filename' => $filename,
            'type' => 'Full System Archive',
            'path' => $filePath,
            'size' => $size,
            'size_formatted' => self::formatBytes($size),
            'sha256' => $sha256,
            'created_at' => Carbon::now()->format('M d, Y • h:i A'),
        ];
    }

    /**
     * Dump all database tables to a SQL string using PDO (supports MySQL and SQLite).
     */
    public static function dumpDatabaseToSqlString(): string
    {
        $connection = DB::connection();
        $pdo = $connection->getPdo();
        $driver = $connection->getDriverName(); // 'mysql' or 'sqlite'
        $databaseName = $connection->getDatabaseName();

        $output = "-- ================================================================\n";
        $output .= "-- C-LACTS DATABASE BACKUP ARCHIVE\n";
        $output .= "-- Municipality of Basud • Human Resource Management Unit (HRMU)\n";
        $output .= "-- Generated: " . Carbon::now()->format('Y-m-d H:i:s T') . "\n";
        $output .= "-- Engine: {$driver} ({$databaseName})\n";
        $output .= "-- ================================================================\n\n";

        if ($driver === 'mysql') {
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
            $output .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
            $output .= "SET NAMES utf8mb4;\n\n";

            $tables = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tableKey = "Tables_in_{$databaseName}";

            foreach ($tables as $tableRow) {
                $table = $tableRow->$tableKey ?? current((array) $tableRow);

                $output .= "-- --------------------------------------------------------\n";
                $output .= "-- Table structure & data for `{$table}`\n";
                $output .= "-- --------------------------------------------------------\n";
                $output .= "DROP TABLE IF EXISTS `{$table}`;\n";

                $createTable = DB::selectOne("SHOW CREATE TABLE `{$table}`");
                $createStatement = $createTable->{'Create Table'} ?? '';
                $output .= $createStatement . ";\n\n";

                $output .= self::dumpTableRows($table, $pdo, '`');
            }

            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        } elseif ($driver === 'sqlite') {
            $output .= "PRAGMA foreign_keys = OFF;\n\n";

            $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name");

            foreach ($tables as $tableRow) {
                $table = $tableRow->name;
                $createSql = $tableRow->sql;

                $output .= "-- --------------------------------------------------------\n";
                $output .= "-- Table structure & data for \"{$table}\"\n";
                $output .= "-- --------------------------------------------------------\n";
                $output .= "DROP TABLE IF EXISTS \"{$table}\";\n";
                $output .= $createSql . ";\n\n";

                $output .= self::dumpTableRows($table, $pdo, '"');
            }

            $output .= "PRAGMA foreign_keys = ON;\n";
        }

        $output .= "-- Dump completed successfully.\n";
        return $output;
    }

    /**
     * Dump data rows for a specific table.
     */
    private static function dumpTableRows(string $table, \PDO $pdo, string $quoteChar): string
    {
        $output = '';
        $rows = DB::table($table)->get();
        if ($rows->isEmpty()) {
            return $output;
        }

        $columns = array_keys((array) $rows->first());
        $quotedColumns = array_map(fn($col) => "{$quoteChar}{$col}{$quoteChar}", $columns);
        $columnsList = implode(', ', $quotedColumns);

        $output .= "INSERT INTO {$quoteChar}{$table}{$quoteChar} ({$columnsList}) VALUES\n";

        $rowValues = [];
        foreach ($rows as $row) {
            $vals = [];
            foreach ((array) $row as $val) {
                if ($val === null) {
                    $vals[] = 'NULL';
                } elseif (is_numeric($val) && !is_string($val)) {
                    $vals[] = $val;
                } else {
                    $vals[] = $pdo->quote((string) $val);
                }
            }
            $rowValues[] = "(" . implode(', ', $vals) . ")";
        }

        $output .= implode(",\n", $rowValues) . ";\n\n";
        return $output;
    }

    /**
     * List all available backup files sorted by creation date descending.
     */
    public static function listBackups(): array
    {
        $backupDir = self::getBackupDirectory();
        $files = File::files($backupDir);

        $backups = [];
        foreach ($files as $file) {
            $filename = $file->getFilename();
            $ext = strtolower($file->getExtension());

            // Match .sql, .gz, .zip
            if (!in_array($ext, ['sql', 'gz', 'zip'])) {
                continue;
            }

            $isFull = str_contains($filename, '_full_') || $ext === 'zip';
            $size = $file->getSize();

            $backups[] = [
                'filename' => $filename,
                'type' => $isFull ? 'Full System Archive' : 'Database Dump',
                'badge_color' => $isFull ? 'purple' : 'emerald',
                'icon' => $isFull ? 'ph-archive' : 'ph-database',
                'format' => $ext === 'gz' ? 'SQL.GZ' : strtoupper($ext),
                'size_bytes' => $size,
                'size_formatted' => self::formatBytes($size),
                'created_at' => Carbon::createFromTimestamp($file->getMTime())->format('M d, Y • h:i A'),
                'timestamp' => $file->getMTime(),
                'sha256' => hash_file('sha256', $file->getRealPath()),
            ];
        }

        // Sort latest first
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return $backups;
    }

    /**
     * Safely delete a backup file.
     */
    public static function deleteBackup(string $filename): bool
    {
        // Path traversal guardrail
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            throw new \InvalidArgumentException("Invalid filename pattern.");
        }

        $filePath = self::getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;
        if (File::exists($filePath)) {
            $deleted = File::delete($filePath);
            if ($deleted) {
                AuditLog::create([
                    'user_id' => Auth::id(),
                    'action' => 'BACKUP_DELETED',
                    'table_name' => 'system_backups',
                    'record_id' => null,
                    'details' => json_encode(['filename' => $filename]),
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                    'logged_at' => now(),
                ]);
            }
            return $deleted;
        }

        return false;
    }

    /**
     * Purge old backup files exceeding the retention limit.
     * Keeps the newest $keep backups.
     */
    public static function pruneOldBackups(int $keep = 4): int
    {
        $backups = self::listBackups();
        if (count($backups) <= $keep) {
            return 0;
        }

        $toDelete = array_slice($backups, $keep);
        $deletedCount = 0;

        foreach ($toDelete as $b) {
            if (self::deleteBackup($b['filename'])) {
                $deletedCount++;
                Log::info("Auto-pruned expired backup: {$b['filename']}");
            }
        }

        return $deletedCount;
    }

    /**
     * Format byte sizes into human readable units.
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
