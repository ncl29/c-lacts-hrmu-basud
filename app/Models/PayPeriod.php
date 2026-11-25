<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayPeriod extends Model
{
    use HasFactory;

    protected $table = 'pay_periods';
    protected $primaryKey = 'pay_period_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'month',
        'half',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'month' => 'integer',
        'half' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Semi-monthly attendance deductions encoded for this pay period.
     */
    public function attendanceDeductions(): HasMany
    {
        return $this->hasMany(AttendanceDeduction::class, 'pay_period_id', 'pay_period_id');
    }
}
