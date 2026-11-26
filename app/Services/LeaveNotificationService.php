<?php

namespace App\Services;

use App\Models\LeaveApplication;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * LeaveNotificationService
 *
 * Dispatches real-time email notifications to municipal personnel
 * whenever their leave application status or physical routing updates.
 */
class LeaveNotificationService
{
    /**
     * Dispatch an email notification to the applicant whenever their leave status updates.
     *
     * @param LeaveApplication $application
     * @param string $actionName Action recorded (e.g. RECEIVED, RECOMMENDED, APPROVED_PHYSICAL, DISAPPROVED_PHYSICAL, DISPATCHED, FILED, CANCELLED)
     * @param Office|null $station Office station where action occurred
     * @param string|null $remarks Any clerk/head remarks or notes
     * @return bool
     */
    public static function notifyStatusUpdate(
        LeaveApplication $application,
        string $actionName,
        ?Office $station = null,
        ?string $remarks = null
    ): bool {
        // Ensure relations are loaded (even if employee was preloaded without user)
        $application->loadMissing(['employee.user', 'leaveType']);

        $employee = $application->employee;
        if (!$employee) {
            return false;
        }

        // Determine recipient email from linked user account (with lookup fallback)
        $recipientEmail = $employee->user?->email;
        if (empty($recipientEmail)) {
            $recipientEmail = \App\Models\User::where('employee_id', $employee->employee_id)
                ->whereNotNull('email')
                ->where('email', '!=', '')
                ->value('email');
        }
        if (empty($recipientEmail)) {
            Log::info("Status update email skipped: No registered email found for employee #{$employee->employee_id} ({$employee->full_name}).");
            return false;
        }

        $recipientName = $employee->full_name;
        $referenceCode = $application->reference_code;
        $leaveType = $application->leaveType?->type_name ?? 'Leave';
        $daysRequested = number_format($application->days_requested, 3);
        $stationName = $station?->office_name ?? 'Municipal Office';
        $currentStatus = $application->status;
        $formattedDate = Carbon::now()->format('F d, Y • h:i A');
        $inclusiveDates = ($application->date_from && $application->date_to)
            ? Carbon::parse($application->date_from)->format('M d, Y') . ' to ' . Carbon::parse($application->date_to)->format('M d, Y')
            : 'N/A';

        // Human-friendly action title
        $friendlyAction = match ($actionName) {
            'FILED' => 'Leave Application Filed (CS Form 6 Generated)',
            'RECEIVED' => "Physically Received at {$stationName}",
            'RECOMMENDED' => "Recommended for Approval by Department Head ({$stationName})",
            'RECOMMENDED_DISAPPROVAL' => "Recommended for Disapproval by Department Head ({$stationName})",
            'DISPATCHED' => "Dispatched / Forwarded from {$stationName}",
            'APPROVED_PHYSICAL' => 'Officially Approved by Municipal Mayor (Physical Wet Signature)',
            'DISAPPROVED_PHYSICAL' => 'Disapproved by Municipal Mayor (Physical Signature)',
            'CERTIFIED_SECTION_7A' => 'Section 7.A Leave Credits Certified by HRMU',
            'ATTACHMENT_VERIFIED' => 'Supporting Document Verified by HRMU',
            'ATTACHMENT_REJECTED' => 'Supporting Document Rejected by HRMU',
            'ATTACHMENT_PENDING' => 'Supporting Document Status Reset to Pending',
            'CANCELLED' => 'Application Cancelled within 7-Working-Day Period',
            default => str_replace('_', ' ', $actionName),
        };

        // Construct clean GovTech email body
        $subject = "C-LACTS Status Update: [{$referenceCode}] - {$friendlyAction}";

        $messageBody = "MUNICIPAL GOVERNMENT OF BASUD - HRMU\n" .
            "Centralized Leave Application & Credit Tracking System (C-LACTS)\n" .
            "--------------------------------------------------------------------\n\n" .
            "Hello {$recipientName},\n\n" .
            "This is an automated notification regarding your Civil Service leave application.\n\n" .
            "APPLICATION DETAILS:\n" .
            "• Reference Code: {$referenceCode}\n" .
            "• Leave Type: {$leaveType}\n" .
            "• Days Applied: {$daysRequested} day(s)\n" .
            "• Inclusive Dates: {$inclusiveDates}\n\n" .
            "ROUTING & STATUS UPDATE:\n" .
            "• Action Taken: {$friendlyAction}\n" .
            "• Office Station: {$stationName}\n" .
            "• Current Overall Status: {$currentStatus}\n" .
            "• Timestamp: {$formattedDate}\n";

        if (!empty($remarks)) {
            $messageBody .= "• Notes / Remarks: {$remarks}\n";
        }

        $messageBody .= "\n--------------------------------------------------------------------\n" .
            "You can log in to your C-LACTS account at any time to inspect your physical document timeline, print pre-filled CS Form 6, or review your certified leave balance.\n\n" .
            "Human Resource Management Unit (HRMU)\n" .
            "Municipality of Basud, Camarines Norte\n";

        try {
            Mail::raw($messageBody, function ($message) use ($recipientEmail, $recipientName, $subject) {
                $message->to($recipientEmail, $recipientName)
                    ->subject($subject);
            });
            Log::info("Status update notification sent to {$recipientEmail} for [{$referenceCode}] - Action: {$actionName}");
            return true;
        } catch (\Throwable $e) {
            Log::warning("Status update notification failed for [{$referenceCode}]: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dispatch an upcoming leave schedule reminder with cancellation window notice.
     * Strict Guardrails:
     * - If the application is already Cancelled or Disapproved, refrains from sending email.
     * - Evaluates whether the statutory 7-working-day cancellation window is still active.
     * - Milestone Deduplication: Prevents email fatigue by caching dispatched milestone keys.
     *
     * @param LeaveApplication $application
     * @param int $daysUntilStart Days until leave commences
     * @param string $milestone Milestone key: '7_days_prior', '3_days_prior', '1_day_prior', 'starts_today', 'cancellation_closing', or 'general'
     * @param bool $force Bypass milestone deduplication cache (useful for testing or manual triggers)
     * @return bool
     */
    public static function notifyUpcomingLeaveReminder(
        LeaveApplication $application, 
        int $daysUntilStart, 
        string $milestone = 'general', 
        bool $force = false
    ): bool {
        // 1. Strict Negative Guardrail: Refrain if application is already cancelled or disapproved
        if (in_array($application->status, ['Cancelled', 'Disapproved'])) {
            Log::info("Upcoming leave reminder skipped: Application #{$application->reference_code} is already {$application->status}.");
            return false;
        }

        // 2. Deduplication check: prevent sending the same milestone email multiple times
        $cacheKey = "leave_reminder_milestone_{$application->application_id}_{$milestone}";
        if (!$force && $milestone !== 'general') {
            if (Cache::has($cacheKey)) {
                Log::info("Upcoming leave reminder [{$milestone}] skipped for [{$application->reference_code}]: already dispatched previously.");
                return false;
            }
        }

        if (!$application->relationLoaded('employee')) {
            $application->load(['employee.user', 'leaveType']);
        }

        $employee = $application->employee;
        if (!$employee) {
            return false;
        }

        $recipientEmail = $employee->user?->email;
        if (empty($recipientEmail)) {
            Log::info("Upcoming leave reminder skipped: No email found for employee #{$employee->employee_id}.");
            return false;
        }

        $recipientName = $employee->full_name;
        $referenceCode = $application->reference_code;
        $leaveType = $application->leaveType?->type_name ?? 'Leave';
        $daysRequested = number_format($application->days_requested, 3);
        $dateFrom = Carbon::parse($application->date_from);
        $dateTo = Carbon::parse($application->date_to);
        $inclusiveDates = $dateFrom->format('F d, Y') . ($dateFrom->ne($dateTo) ? ' to ' . $dateTo->format('F d, Y') : '');

        // 3. Cancellation window evaluation (7 working days from filing)
        $now = Carbon::now();
        $filedAt = Carbon::parse($application->created_at ?? $application->date_of_filing);
        $cancelDeadline = \App\Services\WorkingDayService::getCancellationDeadline($filedAt, 7);
        $canCancel = $now->lte($cancelDeadline);
        $formattedCancelDeadline = $cancelDeadline->format('F d, Y • h:i A');
        $workdaysLeft = $canCancel ? \App\Services\WorkingDayService::getRemainingWorkingDays($cancelDeadline, $now) : 0;

        // Formulate milestone-specific Subject and Intro
        if ($milestone === 'cancellation_closing') {
            $subject = "C-LACTS Urgent Notice: 1 Working Day Left to Cancel Leave Application [{$referenceCode}]";
            $headlineAlert = "URGENT CANCELLATION NOTICE:\n" .
                "• Under municipal leave guidelines, your statutory 7-working-day cancellation window expires on {$formattedCancelDeadline}.\n" .
                "• If your schedule has changed and you no longer wish to proceed with this leave, please cancel it today via the C-LACTS portal.\n" .
                "• Cancelling will automatically restore any deducted credits back to your official leave ledger.\n\n";
        } elseif ($milestone === '1_day_prior') {
            $subject = "C-LACTS Final Reminder: Your Leave Begins Tomorrow [{$referenceCode}]";
            $headlineAlert = "LEAVE COMMENCEMENT REMINDER:\n" .
                "• Your scheduled leave commences TOMORROW (" . $dateFrom->format('F d, Y') . ").\n" .
                "• Please coordinate turnover of responsibilities with your office supervisor if applicable.\n\n";
        } elseif ($milestone === '3_days_prior') {
            $subject = "C-LACTS Reminder: Your Leave Begins in 3 Days [{$referenceCode}]";
            $headlineAlert = "UPCOMING SCHEDULE NOTICE:\n" .
                "• Your leave is scheduled to begin in 3 days on " . $dateFrom->format('F d, Y') . ".\n\n";
        } elseif ($milestone === '7_days_prior') {
            $subject = "C-LACTS Notice: Upcoming Leave in 7 Days [{$referenceCode}] - Cancellation Window Active";
            $headlineAlert = "ADVANCE SCHEDULE NOTICE:\n" .
                "• Your leave is scheduled to begin in 7 days on " . $dateFrom->format('F d, Y') . ".\n\n";
        } elseif ($milestone === 'starts_today') {
            $subject = "C-LACTS Notice: Your Scheduled Leave Begins Today [{$referenceCode}]";
            $headlineAlert = "LEAVE COMMENCEMENT TODAY:\n" .
                "• Your scheduled leave begins TODAY (" . $dateFrom->format('F d, Y') . ").\n\n";
        } else {
            $subject = "C-LACTS Notice: Upcoming Leave Schedule [{$referenceCode}] - Starts in {$daysUntilStart} Day(s)";
            $headlineAlert = "";
        }

        // Formulate timing text
        if ($daysUntilStart === 0) {
            $whenText = "begins TODAY (" . $dateFrom->format('F d, Y') . ")";
        } elseif ($daysUntilStart === 1) {
            $whenText = "begins TOMORROW (" . $dateFrom->format('F d, Y') . ")";
        } else {
            $whenText = "begins in {$daysUntilStart} days on " . $dateFrom->format('F d, Y');
        }

        $messageBody = "MUNICIPAL GOVERNMENT OF BASUD - HRMU\n" .
            "Centralized Leave Application & Credit Tracking System (C-LACTS)\n" .
            "--------------------------------------------------------------------\n\n" .
            "Hello {$recipientName},\n\n" .
            "This is an automated notification regarding your Civil Service leave application.\n\n";

        if (!empty($headlineAlert)) {
            $messageBody .= $headlineAlert;
        }

        $messageBody .= "APPLICATION & SCHEDULE DETAILS:\n" .
            "• Reference Code: {$referenceCode}\n" .
            "• Leave Type: {$leaveType}\n" .
            "• Duration: {$daysRequested} day(s)\n" .
            "• Inclusive Dates: {$inclusiveDates}\n" .
            "• Schedule Notice: Your leave {$whenText}.\n" .
            "• Current Status: {$application->status}\n\n";

        if ($canCancel) {
            $messageBody .= "CANCELLATION OPTION ACTIVE (7-WORKING-DAY WINDOW):\n" .
                "• Under municipal leave guidelines, you currently have the ability to CANCEL this leave application.\n" .
                "• Cancellation Deadline: {$formattedCancelDeadline} ({$workdaysLeft} working day(s) remaining).\n" .
                "• How to Cancel: Log in to your C-LACTS Employee Self-Service dashboard and click 'Cancel Application'. Any deducted leave credits will be automatically restored to your digital ledger.\n\n";
        } else {
            $messageBody .= "CANCELLATION POLICY NOTICE:\n" .
                "• The statutory 7-working-day cancellation window for this application expired on {$formattedCancelDeadline}.\n" .
                "• For modifications or cancellation past this deadline, please coordinate directly with the Human Resource Management Unit (HRMU).\n\n";
        }

        $messageBody .= "--------------------------------------------------------------------\n" .
            "Human Resource Management Unit (HRMU)\n" .
            "Municipality of Basud, Camarines Norte\n";

        try {
            Mail::raw($messageBody, function ($message) use ($recipientEmail, $recipientName, $subject) {
                $message->to($recipientEmail, $recipientName)
                    ->subject($subject);
            });
            Log::info("Upcoming leave reminder [{$milestone}] email sent to {$recipientEmail} for [{$referenceCode}].");

            // Store milestone in cache for 45 days to eliminate duplicates
            if ($milestone !== 'general') {
                Cache::put($cacheKey, now()->toIso8601String(), now()->addDays(45));
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("Upcoming leave reminder email failed for [{$referenceCode}]: " . $e->getMessage());
            return false;
        }
    }
}
