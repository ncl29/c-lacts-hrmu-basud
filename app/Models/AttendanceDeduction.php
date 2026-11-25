<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Concerns\SafeDecimalCast;

class AttendanceDeduction extends Model
{
    use HasFactory, SafeDecimalCast;

    protected $table = 'attendance_deductions';
    protected $primaryKey = 'deduction_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'pay_period_id',
        'encoded_by',
        'days_absent',
        'late_hours',
        'late_minutes',
        'days_deducted',
        'encoded_at',
    ];

    protected $casts = [
        'days_absent' => 'decimal:3',
        'late_hours' => 'integer',
        'late_minutes' => 'integer',
        'days_deducted' => 'decimal:3',
        'encoded_at' => 'datetime',
    ];

    /**
     * The employee penalized or charged for tardiness / absence.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Semi-monthly pay period for this summary.
     */
    public function payPeriod(): BelongsTo
    {
        return $this->belongsTo(PayPeriod::class, 'pay_period_id', 'pay_period_id');
    }

    /**
     * HR Staff user who verified and encoded the summary.
     */
    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by', 'user_id');
    }

    /**
     * Resulting leave ledger debit entries.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LeaveLedgerEntry::class, 'deduction_id', 'deduction_id');
    }
}
