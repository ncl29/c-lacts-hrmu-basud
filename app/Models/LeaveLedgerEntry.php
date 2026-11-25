<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Concerns\SafeDecimalCast;

class LeaveLedgerEntry extends Model
{
    use HasFactory, SafeDecimalCast;

    protected $table = 'leave_ledger_entries';
    protected $primaryKey = 'entry_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'deduction_id',
        'application_id',
        'entry_type',
        'days',
        'balance_after',
        'particular',
        'posted_at',
    ];

    protected $casts = [
        'days' => 'decimal:3',
        'balance_after' => 'decimal:3',
        'posted_at' => 'datetime',
    ];

    /**
     * Employee whose ledger is recorded.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Leave category affected.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'leave_type_id');
    }

    /**
     * Linked attendance deduction (if entry arose from tardiness / absence).
     */
    public function attendanceDeduction(): BelongsTo
    {
        return $this->belongsTo(AttendanceDeduction::class, 'deduction_id', 'deduction_id');
    }

    /**
     * Linked approved leave application (if entry arose from leave taken).
     */
    public function leaveApplication(): BelongsTo
    {
        return $this->belongsTo(LeaveApplication::class, 'application_id', 'application_id');
    }
}
