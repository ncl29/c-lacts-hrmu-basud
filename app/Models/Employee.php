<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employee_no',
        'full_name',
        'last_name',
        'first_name',
        'middle_name',
        'position',
        'employment_type',
        'office_id',
        'status',
        'date_hired',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'date_hired' => 'date',
    ];

    /**
     * Scope query to active employees (case-insensitive for SQLite and MySQL compatibility).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'Active']);
    }

    /**
     * Check if employee is a Job Order personnel.
     * Under CSC Omnibus Rules on Leave (Rule XVI), Job Order personnel do not earn leave credits.
     */
    public function isJobOrder(): bool
    {
        return strtolower(trim((string) $this->employment_type)) === 'job order';
    }

    /**
     * Checks if the employee is a Department Head, Head of Office, or Local Chief Executive.
     * Pursuant to CSC Rule XVI, department heads cannot recommend their own leave applications.
     */
    public function isDepartmentHead(): bool
    {
        $pos = strtolower(trim((string) $this->position));
        return str_contains($pos, 'head')
            || str_contains($pos, 'coordinator') // e.g. Municipal Planning & Development Coordinator
            || str_contains($pos, 'officer iv') // e.g. Administrative Officer IV - HRMU II
            || str_contains($pos, 'hrmo')
            || str_contains($pos, 'treasurer')
            || str_contains($pos, 'assessor')
            || str_contains($pos, 'budget officer')
            || str_contains($pos, 'accountant')
            || str_contains($pos, 'engineer')
            || str_contains($pos, 'health officer')
            || str_contains($pos, 'administrator')
            || str_contains($pos, 'mayor')
            || str_contains($pos, 'officer-in-charge')
            || str_contains($pos, 'oic');
    }

    /**
     * Scope query to employees eligible for leave credits (excludes Job Order personnel).
     */
    public function scopeEligibleForLeave($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('employment_type')
              ->orWhereRaw("LOWER(TRIM(employment_type)) != 'job order'");
        });
    }

    /**
     * Official Leave Record (CS Form 41) Control Number.
     * Strictly numbers only, assigned sequentially based on how many leave records have been released.
     * e.g. 1st released = 1, 2nd released = 2, 10th released = 10.
     */
    public function getControlNoAttribute(): string
    {
        $release = LeaveRecordRelease::where('employee_id', $this->employee_id)
            ->orderBy('control_no', 'asc')
            ->first();

        if ($release) {
            return (string) $release->control_no;
        }

        // Auto-register release if employee already has recorded leave entries
        if ($this->leaveRecords()->exists()) {
            $newRelease = LeaveRecordRelease::getOrCreateForEmployee($this->employee_id);
            return (string) $newRelease->control_no;
        }

        // If not yet released, return the next sequential release number based on total released count
        return (string) LeaveRecordRelease::getNextControlNumber();
    }

    public function getControlNumberAttribute(): string
    {
        return $this->getControlNoAttribute();
    }

    /**
     * Compute Table II fractional leave credits earned for the starting/hire month.
     * Pursuant to CSC Table II, an employee earns leave credits on a daily basis (1-30 days).
     * For example, starting on the 26th of the month equals 5 days of service (30 - 26 + 1 = 5 days)
     * which corresponds to 0.208 Vacation Leave and 0.208 Sick Leave.
     *
     * @return array{days_of_service: int, credit: float}|null
     */
    public function getStartingMonthTableIICredit(): ?array
    {
        if (!$this->date_hired) {
            return null;
        }

        $hireDate = \Carbon\Carbon::parse($this->date_hired);
        $dayOfMonth = (int) $hireDate->day;

        // Days of service in the starting month (standard 30-day civil service baseline)
        // e.g. Day 26 = 30 - 26 + 1 = 5 days of service -> 0.208
        // e.g. Day 1 = 30 - 1 + 1 = 30 days of service -> 1.250
        $daysOfService = max(1, min(30, 30 - $dayOfMonth + 1));

        $ref = \App\Models\StarlaReference::where('table_ref', 'Table II')
            ->where('input_value', "{$daysOfService} " . ($daysOfService === 1 ? 'day' : 'days'))
            ->first();

        $credit = $ref ? (float) $ref->day_equivalent : round(($daysOfService / 30) * 1.250, 3);

        return [
            'hire_date' => $hireDate->format('Y-m-d'),
            'hire_day' => $dayOfMonth,
            'days_of_service' => $daysOfService,
            'credit' => $credit,
        ];
    }

    /**
     * Set employee_no (Control Number / Employee ID), trimmed and formatted.
     */
    public function setEmployeeNoAttribute($value): void
    {
        $this->attributes['employee_no'] = strtoupper(trim((string) $value));
    }

    /**
     * Automatically normalize status attribute to lowercase.
     */
    public function setStatusAttribute($value): void
    {
        $this->attributes['status'] = strtolower((string) $value);
    }

    /**
     * When full_name is set, auto-populate last_name, first_name, and middle_name if missing.
     */
    public function setFullNameAttribute($value): void
    {
        $this->attributes['full_name'] = trim((string) $value);
        if (empty($this->attributes['last_name']) && empty($this->attributes['first_name'])) {
            $parsed = self::parsePhilippineName($this->attributes['full_name']);
            $this->attributes['last_name'] = $parsed['last_name'];
            $this->attributes['first_name'] = $parsed['first_name'];
            $this->attributes['middle_name'] = $parsed['middle_name'];
        }
    }

    /**
     * Accessor for last_name with automatic fallback parsing.
     */
    public function getLastNameAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        $parsed = self::parsePhilippineName($this->attributes['full_name'] ?? '');
        return $parsed['last_name'];
    }

    /**
     * Accessor for first_name with automatic fallback parsing.
     */
    public function getFirstNameAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }
        $parsed = self::parsePhilippineName($this->attributes['full_name'] ?? '');
        return $parsed['first_name'];
    }

    /**
     * Accessor for middle_name with automatic fallback parsing.
     */
    public function getMiddleNameAttribute($value): ?string
    {
        if ($value !== null && $value !== '') {
            return $value;
        }
        $parsed = self::parsePhilippineName($this->attributes['full_name'] ?? '');
        return $parsed['middle_name'];
    }

    /**
     * Official CSC Last, First, and Middle name format in UPPERCASE:
     * e.g., "ALVARO, VICKY C." or "DE VERA, SHALIEMAR"
     */
    public function getLastFirstMiddleAttribute(): string
    {
        $last = trim((string) $this->last_name);
        $first = trim((string) $this->first_name);
        $mid = trim((string) $this->middle_name);

        $out = $last . ($first !== '' ? ', ' . $first : '');
        if ($mid !== '') {
            $out .= ' ' . $mid;
        }
        return mb_strtoupper($out);
    }

    /**
     * Title case Last, First, and Middle name format:
     * e.g., "Alvaro, Vicky C." or "De Vera, ShalieMar"
     */
    public function getFormalNameAttribute(): string
    {
        $last = trim((string) $this->last_name);
        $first = trim((string) $this->first_name);
        $mid = trim((string) $this->middle_name);

        $out = $last . ($first !== '' ? ', ' . $first : '');
        if ($mid !== '') {
            $out .= ' ' . $mid;
        }
        return $out;
    }

    /**
     * Parse full name into Philippine standard Last, First, and Middle name parts.
     */
    public static function parsePhilippineName(string $fullName): array
    {
        $name = trim($fullName);
        if ($name === '') {
            return ['first_name' => '', 'middle_name' => null, 'last_name' => ''];
        }

        // Remove honorific prefix if present (e.g. 'Hon. Shunn...')
        $name = preg_replace('/^(Hon\.|Dr\.|Atty\.|Engr\.|Mayor)\s+/i', '', $name);

        $compoundSurnames = [
            'de vera', 'dela cruz', 'de la cruz', 'del pilar', 'del rosario',
            'delos santos', 'de los santos', 'de leon', 'san juan', 'san jose',
            'lopez jaena', 'santa maria', 'san pedro'
        ];

        $tokens = preg_split('/\s+/', $name);
        $count = count($tokens);

        if ($count === 1) {
            return [
                'first_name' => $tokens[0],
                'middle_name' => null,
                'last_name' => $tokens[0]
            ];
        }

        $last2 = strtolower(implode(' ', array_slice($tokens, -2)));
        $last3 = $count >= 3 ? strtolower(implode(' ', array_slice($tokens, -3))) : '';

        $lastName = '';
        $middleName = null;

        if ($count >= 3 && in_array($last3, $compoundSurnames, true)) {
            $lastName = implode(' ', array_slice($tokens, -3));
            $remaining = array_slice($tokens, 0, -3);
        } elseif ($count >= 2 && in_array($last2, $compoundSurnames, true)) {
            $lastName = implode(' ', array_slice($tokens, -2));
            $remaining = array_slice($tokens, 0, -2);
        } else {
            $lastName = array_pop($tokens);
            $remaining = $tokens;
        }

        if (count($remaining) > 1) {
            $lastRem = end($remaining);
            if (preg_match('/^[A-Z]\.?$/i', $lastRem)) {
                $middleName = array_pop($remaining);
                if (!str_ends_with($middleName, '.')) {
                    $middleName .= '.';
                }
            }
        }

        $firstName = implode(' ', $remaining);

        return [
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
        ];
    }

    /**
     * The office / department this employee belongs to.
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id', 'office_id');
    }

    /**
     * User authentication account linked to this employee.
     */
    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'employee_id', 'employee_id');
    }

    /**
     * Certified leave balances held by this employee.
     */
    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class, 'employee_id', 'employee_id');
    }

    /**
     * Leave applications filed by this employee.
     */
    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveApplication::class, 'employee_id', 'employee_id');
    }

    /**
     * Digital leave ledger transactions (CS Form 41 history).
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(LeaveLedgerEntry::class, 'employee_id', 'employee_id');
    }

    /**
     * Attendance deductions incurred by this employee.
     */
    public function attendanceDeductions(): HasMany
    {
        return $this->hasMany(AttendanceDeduction::class, 'employee_id', 'employee_id');
    }

    /**
     * Digital CS Form 41 leave records for this employee.
     */
    public function leaveRecords(): HasMany
    {
        return $this->hasMany(LeaveRecord::class, 'employee_id', 'employee_id');
    }

    /**
     * Official release history / sequential control number tracking for this employee.
     */
    public function leaveRecordReleases(): HasMany
    {
        return $this->hasMany(LeaveRecordRelease::class, 'employee_id', 'employee_id')->orderBy('control_no', 'asc');
    }
}

