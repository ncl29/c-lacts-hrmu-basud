<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutingLog extends Model
{
    use HasFactory;

    protected $table = 'routing_logs';
    protected $primaryKey = 'log_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'application_id',
        'office_id',
        'scanned_by',
        'action',
        'method',
        'scanned_at',
        'remarks',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    /**
     * The leave application tracked in this routing step.
     */
    public function leaveApplication(): BelongsTo
    {
        return $this->belongsTo(LeaveApplication::class, 'application_id', 'application_id');
    }

    /**
     * Office where the document physical event took place.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id', 'office_id');
    }

    /**
     * Receiving clerk or user who registered the scan / update.
     */
    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by', 'user_id');
    }
}
