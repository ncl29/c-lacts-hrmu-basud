<?php

namespace App\Console\Commands;

use App\Services\LeaveRecordService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateAllLeaveRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:update-all-records 
                            {--year=2026 : Target calendar year} 
                            {--up-to-month= : Target end month (1-12, defaults to current month)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates all Leave Records across all employees, ensuring each month starts with an "Earned" entry (1.25 VL / 1.25 SL) in strict chronological sequence.';

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
        $this->info('   HRMU BASUD — LEAVE RECORD SYNCHRONIZATION & CHRONOLOGY ENGINE    ');
        $this->info('   Enforces CSC Rule XVI: "Earned" (1.25 VL / 1.25 SL) at Start of Month');
        $this->info('====================================================================');

        $year = (int) ($this->option('year') ?? 2026);
        $upToMonth = $this->option('up-to-month') ? (int) $this->option('up-to-month') : Carbon::now()->month;

        $this->line("Target Year: <fg=yellow>{$year}</> | Up To Month: <fg=yellow>{$upToMonth}</>");
        $this->newLine();

        $results = $this->service->updateAllLeaveRecordsWithMonthlyEarned($year, $upToMonth, 1);

        $this->info("✔ Synchronization Completed Successfully!");
        $this->line(" • Total Personnel Processed: <fg=cyan>{$results['total_employees']}</>");
        $this->line(" • Monthly 'Earned' Rows Created: <fg=green;options=bold>{$results['records_created']}</>");
        $this->line(" • Personnel Ledgers Recalculated: <fg=cyan>{$results['employees_updated']}</>");
        $this->newLine();

        return Command::SUCCESS;
    }
}
