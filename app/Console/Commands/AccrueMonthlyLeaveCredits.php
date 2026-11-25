<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\LeaveRecordService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AccrueMonthlyLeaveCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:accrue-monthly 
                            {--month= : Specific month (1-12)} 
                            {--year= : Specific year (e.g. 2026)} 
                            {--employee= : Specific Employee ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically accrues 1.25 days of Vacation Leave and 1.25 days of Sick Leave for each eligible employee at the 1st of every month without overwriting previous balances or duplicating accruals.';

    protected LeaveRecordService $service;

    public function __construct(LeaveRecordService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================================');
        $this->info('   HRMU BASUD — AUTOMATED MONTHLY LEAVE ACCRUAL ENGINE (C-LACTS)   ');
        $this->info('   CSC MC No. 41, s. 1998 / Omnibus Rules on Leave (Rule XVI)      ');
        $this->info('====================================================================');

        $now = Carbon::now();
        $year = (int) ($this->option('year') ?? $now->year);
        $month = (int) ($this->option('month') ?? $now->month);

        $targetDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
        $targetMonthStr = $targetDate->format('F Y');

        $this->line("Target Accrual Period: <fg=yellow>{$targetMonthStr}</>");

        $specificEmployeeId = $this->option('employee');

        if ($specificEmployeeId) {
            $employee = Employee::find($specificEmployeeId);
            if (!$employee) {
                $this->error("Employee with ID {$specificEmployeeId} not found.");
                return Command::FAILURE;
            }
            $employees = collect([$employee]);
        } else {
            $employees = Employee::where('status', 'active')
                ->orderBy('full_name')
                ->get();
        }

        $this->line("Found <fg=cyan>{$employees->count()}</> active municipal personnel.");
        $this->newLine();

        $rows = [];
        $accruedCount = 0;
        $skippedCount = 0;

        foreach ($employees as $emp) {
            if ($this->service->hasEmployeeAccruedMonth($emp->employee_id, $targetDate)) {
                $skippedCount++;
                $rows[] = [
                    $emp->employee_no,
                    $emp->full_name,
                    '<fg=yellow>SKIPPED</>',
                    '0.000',
                    '0.000',
                    '—',
                    '—',
                    "Already accrued for {$targetMonthStr}",
                ];
                continue;
            }

            $record = $this->service->accrueMonthlyCreditsForEmployee(
                $emp,
                $targetDate,
                '1.25',
                '1.25',
                'Earned',
                false,
                1 // System/Admin user_id
            );

            if ($record) {
                $accruedCount++;
                $rows[] = [
                    $emp->employee_no,
                    $emp->full_name,
                    '<fg=green;options=bold>ACCRUED</>',
                    '+1.250',
                    '+1.250',
                    $record->vl_balance,
                    $record->sl_balance,
                    "Earned 1.25 VL & 1.25 SL",
                ];
            }
        }

        $this->table(
            ['Control No', 'Employee Name', 'Status', 'VL Added', 'SL Added', 'New VL Bal', 'New SL Bal', 'Remarks'],
            $rows
        );

        $this->newLine();
        $this->info("Accrual Summary for {$targetMonthStr}:");
        $this->line(" • Successfully Accrued: <fg=green;options=bold>{$accruedCount}</> employees");
        $this->line(" • Skipped (Protected against duplicate): <fg=yellow;options=bold>{$skippedCount}</> employees");
        $this->info("Zero duplicate guarantee enforced. All balances recalculated with exact decimal precision.");

        return Command::SUCCESS;
    }
}
