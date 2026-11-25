<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Concerns\SafeDecimalCast;

class LeaveBalance extends Model
{
    use HasFactory, SafeDecimalCast;

    protected $table = 'leave_balances';
    protected $primaryKey = 'balance_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'balance',
        'last_posted_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'balance' => 'decimal:3',
        'last_posted_at' => 'datetime',
    ];

    /**
     * Employee holding this leave balance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * The leave category (e.g. Vacation Leave, Sick Leave, Wellness Leave).
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'leave_type_id');
    }
}
