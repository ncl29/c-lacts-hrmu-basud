<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('last_name', 100)->nullable()->after('full_name')->index();
            $table->string('first_name', 100)->nullable()->after('last_name');
            $table->string('middle_name', 100)->nullable()->after('first_name');
        });

        // Populate existing employees with parsed last, first, and middle names
        $employees = DB::table('employees')->get();
        foreach ($employees as $emp) {
            $parsed = $this->parsePhilippineName($emp->full_name);
            DB::table('employees')
                ->where('employee_id', $emp->employee_id)
                ->update([
                    'last_name' => $parsed['last_name'],
                    'first_name' => $parsed['first_name'],
                    'middle_name' => $parsed['middle_name'],
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['last_name']);
            $table->dropColumn(['last_name', 'first_name', 'middle_name']);
        });
    }

    /**
     * Parse full name into Philippine standard Last, First, and Middle name parts.
     */
    private function parsePhilippineName(string $fullName): array
    {
        $name = trim($fullName);
        
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
};
