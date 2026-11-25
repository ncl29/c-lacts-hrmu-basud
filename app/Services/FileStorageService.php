<?php

namespace App\Services;

use App\Models\LeaveApplication;
use App\Models\LeaveAttachment;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Get the base directory for stored files.
     * Guaranteed to return normalized absolute path without trailing slash.
     */
    public static function getBaseDir(): string
    {
        // 1. If explicit env variable is provided, prioritize it
        $envBase = env('FILE_BASE_DIR');
        if (!empty($envBase)) {
            $normalized = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $envBase), DIRECTORY_SEPARATOR);
            if (!is_dir($normalized)) {
                @mkdir($normalized, 0755, true);
            }
            return $normalized;
        }

        // 2. Try configured base directory (e.g. %LOCALAPPDATA%/CLACTS/files)
        $configured = config('files.base_dir');
        if (!empty($configured)) {
            $normalized = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $configured), DIRECTORY_SEPARATOR);
            if (!is_dir($normalized)) {
                @mkdir($normalized, 0755, true);
            }
            if (is_dir($normalized) && is_writable($normalized)) {
                return $normalized;
            }
        }

        // 3. Resilient workspace storage fallback (storage/app/files)
        $fallback = storage_path('app' . DIRECTORY_SEPARATOR . 'files');
        if (!is_dir($fallback)) {
            @mkdir($fallback, 0755, true);
        }

        return $fallback;
    }

    /**
     * Calculate current total storage consumed in FILE_BASE_DIR (in bytes).
     */
    public static function getTotalStorageBytes(): int
    {
        $baseDir = self::getBaseDir();
        if (!is_dir($baseDir)) {
            return 0;
        }

        $size = 0;
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($baseDir, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
        } catch (\Throwable $e) {
            // Fallback sum from DB if directory traversal encounters transient lock
            $size = (int) DB::table('leave_attachments')->sum('size_bytes');
        }

        return $size;
    }

    /**
     * Store an uploaded file attachment compliant with C-LACTS File Handling Rules.
     *
     * @param UploadedFile $file
     * @param LeaveApplication $application
     * @param string|null $documentType
     * @param string|null $documentLabel
     * @return LeaveAttachment
     * @throws Exception
     */
    public static function storeAttachment(
        UploadedFile $file,
        LeaveApplication $application,
        ?string $documentType = 'supporting_document',
        ?string $documentLabel = null
    ): LeaveAttachment {
        $originalName = $file->getClientOriginalName();
        $clientSize = $file->getSize();

        // 1. Check for empty (0-byte) files
        if ($clientSize <= 0) {
            throw new Exception("Attachment rejected: File '{$originalName}' is empty (0 bytes).");
        }

        // 2. Classify file type: 'documents' or 'images'
        $clientMime = strtolower($file->getMimeType() ?? '');
        $extension = strtolower($file->getClientOriginalExtension());
        $isImage = str_starts_with($clientMime, 'image/') || in_array($extension, ['jpg', 'jpeg', 'png']);
        $isPdf = $clientMime === 'application/pdf' || $extension === 'pdf';

        if (!$isImage && !$isPdf) {
            throw new Exception("Attachment rejected: File '{$originalName}' is not an accepted format. Only PDF, JPG, and PNG are allowed.");
        }

        $folderType = $isImage ? 'images' : 'documents';

        // 3. Enforce size limits before writing
        $maxImageMb = (int) config('files.max_image_size_mb', 10);
        $maxDocMb = (int) config('files.max_document_size_mb', 25);
        $maxLimitMb = $isImage ? $maxImageMb : $maxDocMb;
        $maxLimitBytes = $maxLimitMb * 1024 * 1024;

        if ($clientSize > $maxLimitBytes) {
            $sizeMb = number_format($clientSize / 1048576, 2);
            throw new Exception("Attachment rejected: '{$originalName}' ({$sizeMb} MB) exceeds maximum allowed limit of {$maxLimitMb} MB for " . ($isImage ? 'images' : 'documents') . ".");
        }

        // 4. Verify total storage capacity limit (MAX_TOTAL_STORAGE_GB = 50)
        $maxTotalGb = (int) config('files.max_total_storage_gb', 50);
        $maxTotalBytes = $maxTotalGb * 1024 * 1024 * 1024;
        $currentUsageBytes = self::getTotalStorageBytes();

        if (($currentUsageBytes + $clientSize) > $maxTotalBytes) {
            $usedGb = number_format($currentUsageBytes / (1024 * 1024 * 1024), 2);
            throw new Exception("Storage capacity reached: Current usage is {$usedGb} GB. Adding '{$originalName}' would exceed the limit of {$maxTotalGb} GB.");
        }

        // 5. Generate secure, non-user-supplied UUID filename and relative path
        // Format: <type>/<YYYY>/<MM>/<generated-id>.<ext>
        $now = Carbon::now();
        $year = $now->format('Y');
        $month = $now->format('m');
        $uuid = (string) Str::uuid();
        $safeExtension = $isPdf ? 'pdf' : ($extension ?: ($clientMime === 'image/png' ? 'png' : 'jpg'));
        $generatedFilename = "{$uuid}.{$safeExtension}";

        // Relative path with forward slashes (never absolute paths stored in DB)
        $relativePath = "{$folderType}/{$year}/{$month}/{$generatedFilename}";

        // Absolute target directory & file
        $baseDir = self::getBaseDir();
        $targetDir = $baseDir . DIRECTORY_SEPARATOR . $folderType . DIRECTORY_SEPARATOR . $year . DIRECTORY_SEPARATOR . $month;

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $resolvedPath = $targetDir . DIRECTORY_SEPARATOR . $generatedFilename;

        // Verify resolved path stays strictly inside FILE_BASE_DIR (path traversal protection)
        $realBase = realpath($baseDir) ?: $baseDir;
        if (!str_starts_with(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $resolvedPath), $realBase)) {
            throw new Exception("Security violation: Resolved path is outside designated FILE_BASE_DIR.");
        }

        // 6. Compute SHA-256 for integrity and duplicate check
        $tempPath = $file->getRealPath();
        $sha256 = hash_file('sha256', $tempPath);

        // 7. Write file first, verify it exists and measure exact size
        // Avoid Symfony move() Windows NTFS directory is_writable bug
        $moved = false;
        if (is_uploaded_file($tempPath)) {
            $moved = @move_uploaded_file($tempPath, $resolvedPath);
        }
        if (!$moved) {
            $moved = @copy($tempPath, $resolvedPath) || @rename($tempPath, $resolvedPath);
        }
        if (!$moved && !file_exists($resolvedPath)) {
            $bytes = @file_put_contents($resolvedPath, file_get_contents($tempPath));
            $moved = ($bytes !== false);
        }

        if (!$moved || !file_exists($resolvedPath)) {
            throw new Exception("Failed to save attachment '{$originalName}' to local storage.");
        }

        clearstatcache(true, $resolvedPath);
        $measuredSize = (int) filesize($resolvedPath);

        // Check if measured file exceeds limit
        if ($measuredSize > $maxLimitBytes || $measuredSize <= 0) {
            @unlink($resolvedPath);
            throw new Exception("Attachment rejected: File size validation failed after saving (measured {$measuredSize} bytes, limit {$maxLimitBytes}).");
        }

        // 8. Insert DB record. If DB insert fails, rollback by deleting the written file
        try {
            $attachment = LeaveAttachment::create([
                'application_id' => $application->application_id,
                'document_type' => $documentType ?? 'supporting_document',
                'document_label' => $documentLabel,
                'original_filename' => $originalName,
                'file_path' => $relativePath,
                'mime_type' => $clientMime ?: ($isPdf ? 'application/pdf' : 'image/jpeg'),
                'size_bytes' => $measuredSize,
                'sha256' => $sha256,
                'verification_status' => 'PENDING',
            ]);

            return $attachment;
        } catch (\Throwable $e) {
            // Delete file if DB insert fails
            if (file_exists($resolvedPath)) {
                @unlink($resolvedPath);
            }
            throw new Exception("Database record creation failed for '{$originalName}': " . $e->getMessage());
        }
    }

    /**
     * Resolve the absolute path of a stored attachment.
     * Joins FILE_BASE_DIR with relative path. Never reconstructs paths any other way.
     */
    public static function getAbsolutePath(LeaveAttachment $attachment): string
    {
        $baseDir = self::getBaseDir();
        $relative = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, ltrim($attachment->file_path, '\\/'));

        // Prevent traversal
        if (str_contains($relative, '..')) {
            throw new Exception("Security violation: Invalid relative path characters.");
        }

        $fullPath = $baseDir . DIRECTORY_SEPARATOR . $relative;

        if (!file_exists($fullPath)) {
            throw new Exception("Attachment file not found on disk (Record ID #{$attachment->attachment_id}).");
        }

        return $fullPath;
    }
}
