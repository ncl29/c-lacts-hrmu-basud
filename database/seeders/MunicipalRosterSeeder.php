<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MunicipalRosterSeeder extends Seeder
{
    /**
     * Seed official Municipality of Basud offices and synthetic dummy personnel
     * for system demonstrations, testing, and GovTech defense.
     * STRICTLY DUMMY DATA — ZERO ACTUAL PERSONNEL NAMES.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Ensure all Municipal Offices exist
        $offices = [
            'Office of the Municipal Mayor' => true,
            'Municipal Persons with Disability Affairs Office' => true,
            'Municipal Environment and Natural Resources Office' => true,
            'Municipal Legal Office' => true,
            'General Service Office' => true,
            'Business Permit Licensing Office' => true,
            'Municipal Library' => true,
            'Human Resource Management Office' => true,
            'Local Youth Development Office' => true,
            'Municipal Planning and Development Office' => true,
            'Municipal Civil Registrar' => true,
            'Municipal Agriculture Office' => true,
            'Public Employment Service Office' => true,
            'Accounting Office' => false,
            'Municipal Assessors Office' => true,
            'Municipal Budget Office' => false,
            'Municipal Disaster Risk Reduction Office' => true,
            'OSSB / OSB' => true,
            'Municipal Treasurer\'s Office' => true,
            'Municipal Social Welfare and Development Office' => true,
            'Municipal Health Office' => true,
            'Municipal Engineering Office' => true,
            'Municipal Cooperative and Development Office' => true,
            'Office of the Municipal Vice Mayor / Sangguniang Bayan' => true,
        ];

        foreach ($offices as $name => $isRoutingPoint) {
            DB::table('offices')->updateOrInsert(
                ['office_name' => $name],
                [
                    'is_routing_point' => $isRoutingPoint,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        $officeMap = DB::table('offices')->pluck('office_id', 'office_name')->toArray();

        // 2. Synthetic Dummy Personnel for offices not covered by the 10 core system personas
        // The 10 core personas:
        // - Vicky C. Alvaro (HRMU)
        // - Camille Faurillo (HRMU)
        // - Grace Bolos (HRMU)
        // - Hon. Shunn Frederick S. Davoco (Mayor's Office)
        // - Mikylla Nanez (Mayor's Office)
        // - Marjory A. Santillan (MPDO)
        // - Johann Chavez (MPDO)
        // - Divine Ponciano (MPDO)
        // - ShalieMar De Vera (GSO)
        // - Craton Z. Genorga (MTO)

        $additionalDummyStaff = [
            'Accounting Office' => [
                'Jan Mark De Vera',
                'Patricia B. Reyes',
                'Bernard C. Adano',
            ],
            'Municipal Agriculture Office' => [
                'Juan M. Dela Cruz',
                'Emilio K. Jacinto',
                'Pio D. Valenzuela',
            ],
            'Municipal Engineering Office' => [
                'Jose P. Rizal',
                'Antonio N. Luna',
                'Bayani R. Juance',
            ],
            'Municipal Health Office' => [
                'Gabriela C. Silang',
                'Marcelo H. Del Pilar',
                'Remedios M. Taal',
            ],
            'Municipal Social Welfare and Development Office' => [
                'Corazon T. Aquino',
                'Josefa L. Escoda',
            ],
            'Municipal Civil Registrar' => [
                'Apolinario M. Mabini',
                'Francisco P. Balagtas',
            ],
            'Municipal Assessors Office' => [
                'Manuel A. Roxas',
                'Elpidio R. Quirino',
            ],
            'Municipal Budget Office' => [
                'Manuel L. Quezon',
                'Sergio S. Osmeña',
            ],
            'Municipal Disaster Risk Reduction Office' => [
                'Ramon F. Magsaysay',
                'Carlos P. Garcia',
            ],
            'Public Employment Service Office' => [
                'Pedro A. Paterno',
                'Eleanor Y. Cabangon',
            ],
            'Municipal Persons with Disability Affairs Office' => [
                'Mariano T. Ponce',
            ],
            'Municipal Environment and Natural Resources Office' => [
                'Graciano P. Lopez Jaena',
            ],
            'Municipal Legal Office' => [
                'Jose W. Diokno',
            ],
            'Business Permit Licensing Office' => [
                'Diosdado P. Macapagal',
                'Renesson B. Solver',
            ],
            'Municipal Library' => [
                'Claro M. Recto',
                'Clarissa A. Odi',
            ],
            'Local Youth Development Office' => [
                'Jose P. Laurel',
            ],
            'OSSB / OSB' => [
                'Teodoro A. Agoncillo',
                'Manuelita L. Guillermo',
            ],
            'Municipal Cooperative and Development Office' => [
                'Cayetano S. Arellano',
                'Joy P. Jamito',
            ],
            'Office of the Municipal Mayor' => [
                'Mateo R. Santos',
            ],
            'General Service Office' => [
                'Alex T. Mendoza',
            ],
            'Municipal Treasurer\'s Office' => [
                'Maria Clara C. Santos',
                'Diego M. Silang',
            ],
        ];

        $deptCounters = [];
        foreach ($additionalDummyStaff as $deptName => $staffList) {
            $officeId = $officeMap[$deptName] ?? null;
            if (!$officeId) continue;

            $prefix = \App\Models\Employee::getOfficePrefix($deptName);
            if (!isset($deptCounters[$prefix])) {
                $deptCounters[$prefix] = 0;
            }

            foreach ($staffList as $dummyName) {
                // Check if already exists
                $exists = DB::table('employees')->where('full_name', $dummyName)->exists();
                if ($exists) continue;

                $deptCounters[$prefix]++;
                $empNo = sprintf('%s-%04d', $prefix, $deptCounters[$prefix]);
                DB::table('employees')->insert([
                    'employee_no' => $empNo,
                    'full_name' => $dummyName,
                    'position' => 'Regular Municipal Personnel',
                    'employment_type' => 'Permanent',
                    'office_id' => $officeId,
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
