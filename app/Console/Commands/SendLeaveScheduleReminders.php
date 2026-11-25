<?php

namespace App\Console\Commands;

use App\Models\LeaveApplication;
use App\Services\LeaveNotificationService;
use App\Services\WorkingDayService;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * SendLeaveScheduleReminders
 *
 * Dispatches non-intrusive milestone email reminders for upcoming leave schedules:
 * - Milestone 1: 7 Days Prior (starts in 1 week)
 * - Milestone 2: 3 Days Prior (midpoint reminder)
 * - Milestone 3: 1 Day Prior (starts tomorrow)
 * - Milestone 4: Starts Today
 * - Milestone 5: Cancellation Window Closing (<= 1 working day left to cancel under 7-working-day rule)
 *
 * Strict Guardrails:
 * - Deduplication: Uses cache keys per milestone to prevent multiple emails for the same milestone.
 * - Negative Guardrail: Strictly refrains from emailing if the application was already Cancelled or Disapproved.
 */
class SendLeaveScheduleReminders extends Command
{
    protected $signature = 'leave:send-reminders 
                            {--employee_id= : Specific employee ID to target for testing}
                            {--force : Bypass milestone deduplication cache for testing}';

    protected $description = 'Dispatch non-intrusive milestone email reminders for upcoming leave schedules with active 7-working-day cancellation notice.';

    public function handle(): int
    {
        $now = Carbon::now();
        $today = Carbon::today();
        $targetEmployeeId = $this->option('employee_id');
        $force = (bool) $this->option('force');

        // Query active applications that either:
        // A) Start within the next 7 days
        // B) Have an active 7-working-day cancellation window closing soon (within 24 hours / 1 working day)
        $query = LeaveApplication::with(['employee.user', 'leaveType'])
            ->whereNotIn('status', ['Cancelled', 'Disapproved']);

        if ($targetEmployeeId) {
            $query->where('employee_id', $targetEmployeeId);
        }

        $allActiveApps = $query->get();

        if ($allActiveApps->isEmpty()) {
            $this->info("No active leave applications found.");
            return 0;
        }

        $this->info("Evaluating " . $allActiveApps->count() . " active leave application(s) against reminder milestones...");

        $dispatched = 0;

        foreach ($allActiveApps as $app) {
            // Strict Negative Guardrail: Cancelled or Disapproved never receive reminders
            if (in_array($app->status, ['Cancelled', 'Disapproved'])) {
                continue;
            }

            $dateFrom = Carbon::parse($app->date_from);
            $daysUntil = (int) $today->diffInDays($dateFrom, false);

            $filedAt = Carbon::parse($app->created_at ?? $app->date_of_filing);
            $cancelDeadline = WorkingDayService::getCancellationDeadline($filedAt, 7);
            $canCancel = $now->lte($cancelDeadline);
            $workdaysLeft = $canCancel ? WorkingDayService::getRemainingWorkingDays($cancelDeadline, $now) : 0;

            // Determine applicable milestone(s) for today
            $milestonesToTrigger = [];

            // 1. Schedule date milestone
            if ($daysUntil === 7) {
                $milestonesToTrigger[] = '7_days_prior';
            } elseif ($daysUntil === 3) {
                $milestonesToTrigger[] = '3_days_prior';
            } elseif ($daysUntil === 1) {
                $milestonesToTrigger[] = '1_day_prior';
            } elseif ($daysUntil === 0) {
                $milestonesToTrigger[] = 'starts_today';
            }

            // 2. Cancellation deadline alert (1 working day remaining to cancel)
            if ($canCancel && $workdaysLeft <= 1) {
                $milestonesToTrigger[] = 'cancellation_closing';
            }

            // If forced without matching a specific date, fallback to general for manual test
            if (empty($milestonesToTrigger) && $force && $daysUntil >= 0 && $daysUntil <= 7) {
                $milestonesToTrigger[] = 'general';
            }

            foreach ($milestonesToTrigger as $milestone) {
                $sent = LeaveNotificationService::notifyUpcomingLeaveReminder($app, $daysUntil, $milestone, $force);
                if ($sent) {
                    $recipientEmail = $app->employee?->user?->email;
                    $this->line("  ✓ Sent [{$milestone}] reminder for [{$app->reference_code}] to {$recipientEmail} (Starts in {$daysUntil}d)");
                    $dispatched++;
                }
            }
        }

        $this->info("Milestone email reminder run complete: {$dispatched} email(s) dispatched.");
        return 0;
    }
}
