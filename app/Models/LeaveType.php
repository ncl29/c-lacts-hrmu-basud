<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Concerns\SafeDecimalCast;

class LeaveType extends Model
{
    use HasFactory, SafeDecimalCast;

    protected $table = 'leave_types';
    protected $primaryKey = 'leave_type_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'code',
        'type_name',
        'is_cumulative',
        'is_commutable',
        'annual_limit',
    ];

    protected $casts = [
        'is_cumulative' => 'boolean',
        'is_commutable' => 'boolean',
        'annual_limit' => 'decimal:3',
    ];

    /**
     * Leave balances tracked for this type.
     */
    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'leave_type_id', 'leave_type_id');
    }

    /**
     * Leave applications filed under this type.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class, 'leave_type_id', 'leave_type_id');
    }

    /**
     * Ledger entries categorized by this leave type.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LeaveLedgerEntry::class, 'leave_type_id', 'leave_type_id');
    }
}
