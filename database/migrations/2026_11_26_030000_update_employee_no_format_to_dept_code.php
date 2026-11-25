<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Employee;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Updates legacy numeric-only employee IDs (e.g. 001, 101, 0501) to official
     * department-coded formats (e.g. HRMU-0001, MAYOR-0001, MPDO-0001, GSO-0001).
     */
    public function up(): void
    {
        // 1. Direct canonical map for the established municipal personas
        $canonicalMap = [
            '001' => 'HRMU-0001', // Vicky C. Alvaro
            '002' => 'HRMU-0002', // Camille Faurillo
            '003' => 'MAYOR-0002', // Mikylla Nanez
            '004' => 'MPDO-0002', // Divine Ponciano
            '101' => 'HRMU-0003', // Grace Bolos
            '102' => 'GSO-0001', // ShalieMar De Vera
            '103' => 'MTO-0001', // Craton Z. Genorga
            '008' => 'MAYOR-0001', // Hon. Shunn Frederick S. Davoco
            '009' => 'MPDO-0001', // Marjory A. Santillan
            '105' => 'MPDO-0003', // Johann Chavez
            'BASUD-EMP-101' => 'HRMU-0003',
            'BASUD-EMP-102' => 'GSO-0001',
            'BASUD-EMP-103' => 'MTO-0001',
            'BASUD-EMP-105' => 'MPDO-0003',
            'BASUD-HR-002'  => 'HRMU-0002',
        ];

        foreach ($canonicalMap as $oldNo => $newNo) {
            // Only update if target newNo is not already taken by another row
            $alreadyExists = DB::table('employees')->where('employee_no', $newNo)->exists();
            if (!$alreadyExists) {
                DB::table('employees')
                    ->where('employee_no', $oldNo)
                    ->update(['employee_no' => $newNo]);
            }
        }

        // 2. For any remaining numeric-only employee_no, format them with office prefix
        $numericEmployees = DB::table('employees')
            ->whereRaw("employee_no NOT LIKE '%-%'")
            ->get();

        $deptCounters = [];

        foreach ($numericEmployees as $emp) {
            $prefix = Employee::getOfficePrefix((int) $emp->office_id);
            if (!isset($deptCounters[$prefix])) {
                // Find highest existing index for this prefix
                $maxNum = DB::table('employees')
                    ->where('employee_no', 'like', "{$prefix}-%")
                    ->pluck('employee_no')
                    ->map(function ($val) use ($prefix) {
                        return preg_match("/^{$prefix}-(\\d+)$/", $val, $m) ? (int)$m[1] : 0;
                    })
                    ->max() ?? 0;
                $deptCounters[$prefix] = $maxNum;
            }

            $deptCounters[$prefix]++;
            $formattedNo = sprintf('%s-%04d', $prefix, $deptCounters[$prefix]);

            DB::table('employees')
                ->where('employee_id', $emp->employee_id)
                ->update(['employee_no' => $formattedNo]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way formatting improvement
    }
};
