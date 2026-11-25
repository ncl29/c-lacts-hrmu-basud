<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

use App\Models\Concerns\SafeDecimalCast;

class LeaveApplication extends Model
{
    use HasFactory, SafeDecimalCast;

    protected $table = 'leave_applications';
    protected $primaryKey = 'application_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'reference_code',
        'employee_id',
        'leave_type_id',
        'other_leave_type',
        'date_from',
        'date_to',
        'date_of_filing',
        'declared_salary',
        'days_requested',
        'status',
        'current_location_office_id',
        'commutation_requested',
        'details_type',
        'details_specify',
        'details_of_leave',
        'is_certified',
        'certified_as_of',
        'certified_vl_earned',
        'certified_vl_less',
        'certified_vl_balance',
        'certified_sl_earned',
        'certified_sl_less',
        'certified_sl_balance',
        'certified_by_user_id',
        'certified_at',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'date_of_filing' => 'date',
        'declared_salary' => 'decimal:2',
        'days_requested' => 'decimal:3',
        'commutation_requested' => 'boolean',
        'is_certified' => 'boolean',
        'certified_as_of' => 'date',
        'certified_vl_earned' => 'decimal:3',
        'certified_vl_less' => 'decimal:3',
        'certified_vl_balance' => 'decimal:3',
        'certified_sl_earned' => 'decimal:3',
        'certified_sl_less' => 'decimal:3',
        'certified_sl_balance' => 'decimal:3',
        'certified_at' => 'datetime',
    ];

    /**
     * Checks if the physical leave application document has reached HRMU.
     * Section 7.A (Certification of Leave Credits) must remain BLANK until
     * the physical document reaches HRMU for official certification.
     */
    public function hasReachedHrmu(): bool
    {
        if ($this->is_certified) {
            return true;
        }

        // If currently stationed at HRMU
        if ($this->current_location_office_id == 1) {
            return true;
        }

        // If any routing log exists at HRMU
        if ($this->relationLoaded('routingLogs')) {
            $hasHrmuLog = $this->routingLogs->contains(fn($l) => $l->office_id == 1 || (stripos($l->office->office_name ?? '', 'HRMU') !== false));
            if ($hasHrmuLog) {
                return true;
            }
        } else {
            $hasHrmuLog = $this->routingLogs()->where(function ($q) {
                $q->where('office_id', 1)
                  ->orWhereHas('office', function ($oq) {
                      $oq->where('office_name', 'like', '%HRMU%')
                         ->orWhere('office_name', 'like', '%Human Resource%');
                  });
            })->exists();

            if ($hasHrmuLog) {
                return true;
            }
        }

        // If already progressed to Mayor's Office or final action (which occurs after HRMU)
        if ($this->current_location_office_id == 2 || in_array($this->status, ['Approved', 'Disapproved'])) {
            return true;
        }

        return false;
    }

    /**
     * User/HRMU staff who certified leave credits in Box 7.A.
     */
    public function certifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by_user_id', 'user_id');
    }

    /**
     * Alias for current status to match controller/spec nomenclature.
     */
    public function getCurrentStatusAttribute(): string
    {
        return $this->status;
    }

    public function setCurrentStatusAttribute(string $value): void
    {
        $this->attributes['status'] = $value;
    }

    /**
     * Applicant employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Applied leave type category.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'leave_type_id');
    }

    /**
     * Physical office currently holding the document.
     */
    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'current_location_office_id', 'office_id');
    }

    /**
     * Physical document routing audit steps.
     */
    public function routingLogs(): HasMany
    {
        return $this->hasMany(RoutingLog::class, 'application_id', 'application_id')->orderBy('scanned_at', 'desc');
    }

    /**
     * Most recent routing log event.
     */
    public function latestRoutingLog()
    {
        return $this->hasOne(RoutingLog::class, 'application_id', 'application_id')->latestOfMany('scanned_at');
    }

    /**
     * Digital leave ledger transactions resulting from this application.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LeaveLedgerEntry::class, 'application_id', 'application_id');
    }

    /**
     * Corresponding row in Civil Service Leave Record (leave_records).
     */
    public function leaveRecord(): HasOne
    {
        return $this->hasOne(LeaveRecord::class, 'application_id', 'application_id');
    }

    /**
     * Digital supporting document attachments (medical certs, IDs, clearances).
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(LeaveAttachment::class, 'application_id', 'application_id');
    }
}
