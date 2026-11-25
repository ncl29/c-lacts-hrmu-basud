<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRecordRelease extends Model
{
    use HasFactory;

    protected $table = 'leave_record_releases';
    protected $primaryKey = 'release_id';

    protected $fillable = [
        'employee_id',
        'control_no',
        'release_type',
        'released_by',
        'released_at',
        'remarks',
    ];

    protected $casts = [
        'control_no' => 'integer',
        'released_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    public function releasedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by', 'user_id');
    }

    /**
     * Get the total count of leave records that have been released.
     */
    public static function totalReleasedCount(): int
    {
        return (int) self::count();
    }

    /**
     * Get next sequential control number based on how many have been released.
     */
    public static function getNextControlNumber(): int
    {
        $max = (int) self::max('control_no');
        $count = (int) self::count();
        return max($max, $count) + 1;
    }

    /**
     * Get or create an official release record for an employee.
     * If already released, returns the existing release (preserving sequential control number).
     * If not yet released, registers a new release with the next sequential number based on count released.
     */
    public static function getOrCreateForEmployee(int $employeeId, ?int $releasedBy = null, string $type = 'official_cardstock'): self
    {
        $existing = self::where('employee_id', $employeeId)->orderBy('control_no', 'asc')->first();
        if ($existing) {
            return $existing;
        }

        $nextNo = self::getNextControlNumber();
        return self::create([
            'employee_id' => $employeeId,
            'control_no' => $nextNo,
            'release_type' => $type,
            'released_by' => $releasedBy,
            'released_at' => now(),
            'remarks' => 'Civil Service Form No. 41 Official Leave Record Release #' . $nextNo,
        ]);
    }
}

