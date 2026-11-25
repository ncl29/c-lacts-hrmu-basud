<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRecord extends Model
{
    use HasFactory;

    protected $table = 'leave_records';
    protected $primaryKey = 'record_id';

    protected $fillable = [
        'employee_id',
        'application_id',
        'period',
        'particular',
        'accrual_month',
        'is_prorated',
        'vl_earned',
        'vl_absence_with_pay',
        'vl_balance',
        'vl_absence_without_pay',
        'sl_earned',
        'sl_absence_with_pay',
        'sl_balance',
        'sl_absence_without_pay',
        'date_action_taken',
        'row_order',
        'encoded_by',
    ];

    protected $casts = [
        'is_prorated' => 'boolean',
    ];

    /**
     * Relationship to the Employee owning this leave record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Relationship to the Leave Application if linked.
     */
    public function leaveApplication(): BelongsTo
    {
        return $this->belongsTo(LeaveApplication::class, 'application_id', 'application_id');
    }

    public function application(): BelongsTo
    {
        return $this->leaveApplication();
    }

    /**
     * Relationship to the user who encoded/updated this row.
     */
    public function encoder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by', 'user_id');
    }
}
