<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAttachment extends Model
{
    use HasFactory;

    protected $table = 'leave_attachments';
    protected $primaryKey = 'attachment_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'application_id',
        'document_type',
        'document_label',
        'original_filename',
        'file_path',
        'mime_type',
        'size_bytes',
        'sha256',
        'verification_status',
        'verification_remarks',
        'verified_by_user_id',
        'verified_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'verified_at' => 'datetime',
    ];

    /**
     * Associated Leave Application.
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(LeaveApplication::class, 'application_id', 'application_id');
    }

    /**
     * HRMU Staff or Admin who reviewed this document.
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id', 'user_id');
    }

    /**
     * Check if attachment is a PDF.
     */
    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->mime_type) === 'application/pdf'
            || strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)) === 'pdf';
    }

    /**
     * Check if attachment is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with(strtolower($this->mime_type), 'image/')
            || in_array(strtolower(pathinfo($this->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png']);
    }

    /**
     * Human-readable file size formatting.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->size_bytes;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}

