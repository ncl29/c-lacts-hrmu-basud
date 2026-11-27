<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LeaveApplication;
use App\Models\LeaveAttachment;
use App\Models\LeaveBalance;
use App\Models\LeaveLedgerEntry;
use App\Models\LeaveRecord;
use App\Models\LeaveType;
use App\Models\Office;
use App\Models\RoutingLog;
use App\Models\User;
use App\Services\FileStorageService;
use App\Services\LeaveRecordService;
use App\Services\WorkingDayService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * DocumentRoutingController
 *
 * Coordinates the physical paper document routing and QR traceability module of C-LACTS.
 *
 * Non-Negotiable Operational Guardrails Enforced:
 * 1. STRICTLY NO ELECTRONIC SIGNATURES: Physical wet signatures are retained on paper forms.
 *    This controller tracks physical custody/movement of the paper document between municipal offices.
 * 2. ZERO-HARDWARE FALLBACK: Unified lookup accepts USB laser QR scanners (keyboard emulation),
 *    mobile camera payloads, or manual keyboard entry of alphanumeric reference codes (e.g. 'LV-2026-0001').
 * 3. DATA PRIVACY ACT OF 2012 (RA 10173): Routing payloads expose strictly reference codes and tracking status,
 *    never embedding diagnostic or sensitive details.
 * 4. STATUTORY 5-WORKING-DAY SLA TRACKER: Monitors Civil Service Rule XVI Section 49 deemed approval timer.
 */
class DocumentRoutingController extends Controller
{
    /**
     * Standard allowed routing actions.
     */
    public const ALLOWED_ACTIONS = [
        'RECEIVED' => 'Received',
        'DISPATCHED' => 'Dispatched',
        'RECOMMENDED' => 'Recommended',
        'RECOMMENDED_DISAPPROVAL' => 'Recommended for Disapproval',
        'APPROVED_PHYSICAL' => 'Approved (Physical Wet Signature)',
        'DISAPPROVED_PHYSICAL' => 'Disapproved (Physical Wet Signature)',
    ];

    /**
     * Checks if given office is the Office of the Municipal Mayor (Appointing Authority).
     */
    public static function isMayorOffice(?Office $office): bool
    {
        if (!$office) {
            return false;
        }

        return stripos($office->office_name, 'mayor') !== false && stripos($office->office_name, 'vice') === false;
    }

    /**
     * Checks if given office is the Human Resource Management Unit (HRMU).
     */
    public static function isHrmuOffice(?Office $office): bool
    {
        if (!$office) {
            return false;
        }

        return stripos($office->office_name, 'HRMU') !== false
            || stripos($office->office_name, 'Human Resource') !== false;
    }

    /**
     * Returns office-specific authorized routing actions.
     * Enforces the statutory guardrail:
     * - Only Office of the Municipal Mayor can execute final Approval/Disapproval.
     * - Department offices & HRMU can only Recommend or Dispatch/Receive documents.
     */
    public function getAllowedActionsForOffice(Office $office): array
    {
        if (self::isMayorOffice($office)) {
            return [
                'RECEIVED' => 'Received — Physical document arrived at Office of the Municipal Mayor',
                'APPROVED_PHYSICAL' => 'Approved — Signed & approved by Municipal Mayor (Physical Wet Signature)',
                'DISAPPROVED_PHYSICAL' => 'Disapproved — Signed & disapproved by Municipal Mayor',
                'DISPATCHED' => 'Dispatched / Forwarded — Return acted form to HRMU',
            ];
        }

        if (self::isHrmuOffice($office)) {
            return [
                'RECEIVED' => 'Received — Document physically arrived at HRMU',
                'DISPATCHED' => 'Dispatched / Forwarded — Transmitted to Office of the Municipal Mayor',
                'RECOMMENDED' => 'Recommended — Signed & endorsed for Approval by HR Head (for HR staff)',
                'RECOMMENDED_DISAPPROVAL' => 'Recommended for Disapproval — HR Head recommendation (for HR staff)',
            ];
        }

        // Standard Department Offices (e.g. MPDO, MEO, MHO, MSWDO, MAO, GSO, MTO, etc.)
        return [
            'RECEIVED' => "Received — Document physically arrived at {$office->office_name}",
            'RECOMMENDED' => 'Recommended — Signed & endorsed for Approval by Department Head (Section 7.B)',
            'RECOMMENDED_DISAPPROVAL' => 'Recommended for Disapproval — Signed with reason by Department Head (Section 7.B)',
            'DISPATCHED' => 'Dispatched / Forwarded — Transmit physical form to HRMU',
        ];
    }

    /**
     * Evaluates the complete routing state of an application across all physical checkpoints.
     * Determines whether the workflow is fully completed, station-completed, or has pending actions.
     */
    public function evaluateRoutingWorkflowState(LeaveApplication $application, Office $currentOffice, ?User $user = null): array
    {
        $isMayor = self::isMayorOffice($currentOffice);
        $isHrmu = self::isHrmuOffice($currentOffice);
        $status = $application->status;

        if (!$application->relationLoaded('routingLogs')) {
            $application->load(['routingLogs.office']);
        }

        $allLogs = $application->routingLogs;
        $latestLog = $application->latestRoutingLog;

        // Check for cancellation
        if ($status === 'Cancelled') {
            return [
                'is_completed' => true,
                'can_act' => false,
                'completion_type' => 'CANCELLED',
                'title' => 'Application Cancelled',
                'reason' => 'This leave application has been cancelled. No further physical routing actions are permitted.',
                'suggested_action' => null,
                'allowed_actions' => [],
            ];
        }

        $hasMayorActed = $allLogs->contains(fn($l) => in_array($l->action, ['APPROVED_PHYSICAL', 'DISAPPROVED_PHYSICAL']))
            || in_array($status, ['Approved', 'Disapproved']);

        // Check if acted document has arrived and been archived/received at HRMU
        $hasFinalHrmuCustody = false;
        if ($hasMayorActed) {
            $mayorActionLog = $allLogs->first(fn($l) => in_array($l->action, ['APPROVED_PHYSICAL', 'DISAPPROVED_PHYSICAL']));
            $mayorActedAt = $mayorActionLog ? $mayorActionLog->scanned_at : null;

            // Check if there is an HRMU RECEIVED log on or after the Mayor's action
            $hasHrmuReceivedAfterMayor = $allLogs->contains(function ($l) use ($mayorActedAt) {
                $isHrmuStation = self::isHrmuOffice($l->office) || $l->office_id == 1;
                if (!$isHrmuStation || $l->action !== 'RECEIVED') {
                    return false;
                }
                return $mayorActedAt ? ($l->scanned_at >= $mayorActedAt) : true;
            });

            if ($hasHrmuReceivedAfterMayor || ($application->current_location_office_id == 1 && $latestLog && $latestLog->action === 'RECEIVED')) {
                $hasFinalHrmuCustody = true;
            }
        }

        // 1. GLOBAL LIFECYCLE COMPLETION: Document has been acted on by Mayor and filed/archived at HRMU
        if ($hasFinalHrmuCustody) {
            $statusLabel = in_array($status, ['Approved', 'Disapproved']) ? $status : 'Approved';
            return [
                'is_completed' => true,
                'can_act' => false,
                'completion_type' => 'FULLY_COMPLETED',
                'title' => 'Routing Complete — Document Processed & Archived',
                'reason' => "All required physical routing checkpoints have been completed. This document was officially {$statusLabel} by the Municipal Mayor and permanently received & filed in HRMU records.",
                'suggested_action' => null,
                'allowed_actions' => [],
            ];
        }

        // 2. STATION-SPECIFIC COMPLETION & PROGRESSION GUARDS:
        $logsAtStation = $allLogs->where('office_id', $currentOffice->office_id);
        $latestAtStation = $logsAtStation->first();

        // If at Department Office (e.g. MPDO, MEO):
        // Once this station has DISPATCHED the document, or if it has reached higher office (HRMU/Mayor):
        if (!$isMayor && !$isHrmu) {
            $isDeptHeadApp = $application->employee && $application->employee->isDepartmentHead();

            // Guard for Department Head Applications at their Department Office:
            if ($isDeptHeadApp) {
                return [
                    'is_completed' => false,
                    'can_act' => false,
                    'completion_type' => 'STATION_COMPLETED',
                    'title' => 'Department Head Application — Bypasses Department Recommendation',
                    'reason' => 'This application was filed by the Department Head. Pursuant to Civil Service Commission (CSC) Rule XVI and municipal governance regulations, self-recommendation (Box 7.B) is strictly prohibited. This document bypasses department recommendation and must be forwarded directly to the Municipal Administrator or HRMU for credit certification and Mayoral action.',
                    'suggested_action' => null,
                    'allowed_actions' => [],
                ];
            }

            $hasDispatched = $logsAtStation->contains(fn($l) => $l->action === 'DISPATCHED' && $l->method !== 'SYSTEM');
            $hasReachedHigherOffice = $allLogs->contains(fn($l) => $l->office_id == 1 || $l->office_id == 2);

            if ($hasDispatched || $hasReachedHigherOffice || $hasMayorActed) {
                return [
                    'is_completed' => false,
                    'can_act' => false,
                    'completion_type' => 'STATION_COMPLETED',
                    'title' => "Station Actions Completed ({$currentOffice->office_name})",
                    'reason' => "Document has been endorsed/recommended by the Department Head and dispatched to HRMU from this terminal. Currently in transit or stationed at the next office.",
                    'suggested_action' => null,
                    'allowed_actions' => [],
                ];
            }
        }

        // If at Office of the Municipal Mayor:
        // Once Mayor has acted AND the document has been DISPATCHED from Mayor's Office back to HRMU:
        if ($isMayor) {
            $hasMayorDispatched = $logsAtStation->contains(fn($l) => $l->action === 'DISPATCHED' && $l->method !== 'SYSTEM');
            if ($hasMayorActed && $hasMayorDispatched) {
                return [
                    'is_completed' => false,
                    'can_act' => false,
                    'completion_type' => 'STATION_COMPLETED',
                    'title' => "Station Actions Completed (Office of the Municipal Mayor)",
                    'reason' => "Document was acted on ({$status}) and dispatched back to HRMU for filing. No further action needed at the Mayor's Office terminal.",
                    'suggested_action' => null,
                    'allowed_actions' => [],
                ];
            }
        }

        // If at HRMU:
        // If HRMU certified and DISPATCHED to Mayor's Office, and it hasn't returned from Mayor yet:
        if ($isHrmu) {
            $hasDispatchedToMayor = $logsAtStation->contains(fn($l) => $l->action === 'DISPATCHED' && $l->method !== 'SYSTEM');
            if (!$hasMayorActed && $hasDispatchedToMayor) {
                return [
                    'is_completed' => false,
                    'can_act' => false,
                    'completion_type' => 'STATION_COMPLETED',
                    'title' => "Dispatched to Office of the Municipal Mayor",
                    'reason' => "Document leave credits have been certified and forwarded to the Office of the Municipal Mayor. Awaiting Mayor's review and signature.",
                    'suggested_action' => null,
                    'allowed_actions' => [],
                ];
            }
        }

        // 3. Document has pending actions at this station
        $actingUser = $user ?? Auth::user();
        $suggested = $this->determineSuggestedAction($application, $currentOffice, $actingUser);
        $allowedActions = $this->getAllowedActionsForOffice($currentOffice);

        // Filter allowed actions based on current phase to prevent out-of-order or duplicate actions:
        // E.g., if at Mayor's Office and Mayor already signed (hasMayorActed), only DISPATCHED should be allowed!
        if ($isMayor && $hasMayorActed) {
            $allowedActions = array_intersect_key($allowedActions, ['DISPATCHED' => true]);
        }

        // If at Dept Head office and already recommended, only DISPATCHED should be allowed!
        if (!$isMayor && !$isHrmu && $logsAtStation->contains(fn($l) => in_array($l->action, ['RECOMMENDED', 'RECOMMENDED_DISAPPROVAL']))) {
            $allowedActions = array_intersect_key($allowedActions, ['DISPATCHED' => true]);
        }

        // Non-Negotiable CSC Rule XVI Guardrail:
        // A Department Head (or applicant) cannot recommend or approve their own leave application!
        $isApplicantDeptHead = $application->employee && $application->employee->isDepartmentHead();
        $isOwnApplication = false;
        if ($actingUser && $actingUser->employee) {
            $isOwnApplication = ((int) $application->employee_id === (int) $actingUser->employee->employee_id);
        }

        // 1) If the applicant is a Department Head at their department office, or if the current user is the applicant:
        // Strictly remove RECOMMENDED and RECOMMENDED_DISAPPROVAL from allowed actions!
        if ((!$isMayor && !$isHrmu && $isApplicantDeptHead) || $isOwnApplication) {
            unset($allowedActions['RECOMMENDED'], $allowedActions['RECOMMENDED_DISAPPROVAL']);
        }

        // 2) If the user is the applicant, they cannot approve or disapprove their own leave either!
        if ($isOwnApplication) {
            unset($allowedActions['APPROVED_PHYSICAL'], $allowedActions['DISAPPROVED_PHYSICAL']);
        }

        return [
            'is_completed' => false,
            'can_act' => true,
            'completion_type' => null,
            'title' => null,
            'reason' => $suggested['reason'],
            'suggested_action' => $suggested['action'],
            'allowed_actions' => $allowedActions,
        ];
    }

    /**
     * Determines what routing action is needed or what to do next based on document status,
     * physical custody trail, and the current office station.
     */
    public function determineSuggestedAction(LeaveApplication $application, Office $currentOffice, ?User $user = null): array
    {
        $isMayor = self::isMayorOffice($currentOffice);
        $isHrmu = self::isHrmuOffice($currentOffice);
        $latestLog = $application->latestRoutingLog;
        $status = $application->status;

        if (!$application->relationLoaded('routingLogs')) {
            $application->load(['routingLogs.office']);
        }

        $allLogs = $application->routingLogs;
        $hasBeenRecommended = $allLogs->contains(fn($l) => in_array($l->action, ['RECOMMENDED', 'RECOMMENDED_DISAPPROVAL']));
        $hasMayorActed = $allLogs->contains(fn($l) => in_array($l->action, ['APPROVED_PHYSICAL', 'DISAPPROVED_PHYSICAL']))
            || in_array($status, ['Approved', 'Disapproved']);

        // Check recent logs at this current office station
        $logsAtStation = $allLogs->where('office_id', $currentOffice->office_id);
        $latestAtStation = $logsAtStation->first();

        // 1. If document has already been fully Approved or Disapproved:
        if ($hasMayorActed) {
            if ($isMayor) {
                // If at Mayor's Office and already acted, next is returning to HRMU
                if (!$latestAtStation || $latestAtStation->action !== 'DISPATCHED') {
                    return [
                        'action' => 'DISPATCHED',
                        'reason' => "Action taken by Mayor ({$status}). Document should now be dispatched / returned to HRMU.",
                    ];
                }
                return [
                    'action' => 'DISPATCHED',
                    'reason' => "Document already acted on by Mayor and dispatched.",
                ];
            }

            if ($isHrmu) {
                // Returning to HRMU after Mayor's approval
                if (!$latestAtStation || $latestAtStation->scanned_at < ($latestLog?->scanned_at ?? 0)) {
                    return [
                        'action' => 'RECEIVED',
                        'reason' => "Acted document returned from Office of the Municipal Mayor. Mark Received at HRMU for recording/filing.",
                    ];
                }
                return [
                    'action' => 'RECEIVED',
                    'reason' => "Application is complete ({$status}) and filed in HRMU records.",
                ];
            }

            return [
                'action' => 'RECEIVED',
                'reason' => "Application is complete ({$status}).",
            ];
        }

        // 2. Active Application - Check if physical document has arrived & been marked RECEIVED at this station
        $isReceivedAtStation = $latestAtStation && in_array($latestAtStation->action, [
            'RECEIVED', 'RECOMMENDED', 'RECOMMENDED_DISAPPROVAL', 'APPROVED_PHYSICAL', 'DISAPPROVED_PHYSICAL'
        ]);
        $isCurrentlyAtStation = ($application->current_location_office_id == $currentOffice->office_id);

        if (!$isReceivedAtStation || !$isCurrentlyAtStation || ($latestLog && $latestLog->action === 'DISPATCHED')) {
            return [
                'action' => 'RECEIVED',
                'reason' => "Physical paper arrived at {$currentOffice->office_name}. Initial required action is to mark it as Received.",
            ];
        }

        // 3. Document has been RECEIVED and is currently physically present at this station:
        if ($isMayor) {
            // At Mayor's Office:
            // Mayor reviews and signs Box 7.C / 7.D
            return [
                'action' => 'APPROVED_PHYSICAL',
                'reason' => "Document is received at Office of the Municipal Mayor. Awaiting Municipal Mayor's physical signature (Box 7.C).",
            ];
        }

        if ($isHrmu) {
            // At HRMU:
            // Check if employee is HRMU staff and hasn't been recommended yet
            $isApplicantFromHrmu = ($application->employee && $application->employee->office_id == $currentOffice->office_id);
            if ($isApplicantFromHrmu && !$hasBeenRecommended) {
                return [
                    'action' => 'RECOMMENDED',
                    'reason' => "Applicant is HRMU personnel. HR Head endorsement / recommendation (Box 7.B) is needed next.",
                ];
            }

            if (!$application->is_certified) {
                return [
                    'action' => 'DISPATCHED',
                    'reason' => "Document received at HRMU. Verify and certify Box 7.A Leave Credits before transmitting to Mayor's Office.",
                ];
            }

            return [
                'action' => 'DISPATCHED',
                'reason' => "Leave credits certified (Box 7.A). Ready to forward to Office of the Municipal Mayor.",
            ];
        }

        // Standard Department Office (e.g. MPDO, MEO, etc.)
        $actingUser = $user ?? Auth::user();
        $isApplicantDeptHead = $application->employee && $application->employee->isDepartmentHead();
        $isOwnApplication = false;
        if ($actingUser && $actingUser->employee) {
            $isOwnApplication = ((int) $application->employee_id === (int) $actingUser->employee->employee_id);
        }

        // If the applicant is a Department Head or scanning their own application, bypass department recommendation
        if ($isApplicantDeptHead || $isOwnApplication) {
            return [
                'action' => 'DISPATCHED',
                'reason' => 'Applicant is Department Head. Self-recommendation is strictly prohibited under CSC Rule XVI. Forward physical form directly to Municipal Administrator / HRMU for credit certification and Mayoral action.',
            ];
        }

        if (!$hasBeenRecommended) {
            return [
                'action' => 'RECOMMENDED',
                'reason' => "Document is received at {$currentOffice->office_name}. Department Head recommendation (Box 7.B) is needed next.",
            ];
        }

        // Already recommended by Dept Head, now needs to be dispatched to HRMU
        return [
            'action' => 'DISPATCHED',
            'reason' => "Department Head recommendation completed. Document should now be forwarded / transmitted to HRMU.",
        ];
    }

    /**
     * Display the Clerk Scanner / Reception Desk View.
     */
    public function scannerView(Request $request)
    {
        $user = Auth::user();
        $offices = Office::where('is_routing_point', true)->orderBy('office_name')->get();

        // Safety fallback: if no routing points are flagged, load all municipal offices
        if ($offices->isEmpty()) {
            $offices = Office::orderBy('office_name')->get();
        }

        // Default office station (from query param, logged-in user's assigned office, or fallback)
        $defaultOfficeId = $request->query('office_id') ?? $user?->employee?->office_id ?? $offices->first()?->office_id;
        $currentOffice = Office::find($defaultOfficeId) ?? $offices->first();

        // Allowed actions and role for current station
        $allowedActions = $this->getAllowedActionsForOffice($currentOffice);
        $isMayorOffice = self::isMayorOffice($currentOffice);

        // Filter authorized operator list for the selected station office:
        $stationUsers = User::with('employee.office')
            ->where('is_active', true)
            ->whereHas('employee', function ($q) use ($currentOffice) {
                $q->where('office_id', $currentOffice->office_id);
            })
            ->get()
            ->filter(fn($u) => $u->canAccessQrScanner())
            ->sortBy(fn($u) => $u->employee?->full_name ?? $u->username)
            ->values();

        // If no specific users are assigned to this station, allow system admins or fall back to all scanner users
        if ($stationUsers->isEmpty()) {
            $stationUsers = User::with('employee.office')
                ->where('is_active', true)
                ->whereIn('role', ['admin', 'hrmu_staff'])
                ->get()
                ->filter(fn($u) => $u->canAccessQrScanner())
                ->sortBy(fn($u) => $u->employee?->full_name ?? $u->username)
                ->values();

            if ($stationUsers->isEmpty()) {
                $stationUsers = User::with('employee.office')
                    ->where('is_active', true)
                    ->get()
                    ->filter(fn($u) => $u->canAccessQrScanner())
                    ->values();
            }
        }

        $authorizedUsers = $stationUsers;

        // Strictly use the currently authenticated operator (never switch login session via query params)
        $currentClerk = ($user && $user->canAccessQrScanner()) ? $user : null;

        if (!$currentClerk) {
            abort(403, 'Access Restricted: Only authorized routing personnel may open the QR Document Scanner.');
        }

        // Recent routing logs recorded today / recently
        $recentLogs = RoutingLog::with(['leaveApplication.employee', 'office', 'scanner.employee'])
            ->orderBy('scanned_at', 'desc')
            ->limit(10)
            ->get();

        return view('clerk.scanner', compact('offices', 'currentOffice', 'authorizedUsers', 'currentClerk', 'recentLogs', 'allowedActions', 'isMayorOffice'));
    }

    /**
     * 1. Single Scan/Search Unified Lookup Endpoint
     *
     * Accepts an incoming reference code from USB scanner, mobile camera, or manual typing.
     * Returns the application details, applicant profile, current physical location,
     * latest routing log, and allowed next actions for the clerk's office station.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function scanOrEnterReference(Request $request): JsonResponse
    {
        $request->validate([
            'reference_code' => ['required', 'string', 'max:60'],
            'office_id' => ['nullable', 'integer', 'exists:offices,office_id'],
        ]);

        $referenceCode = trim($request->input('reference_code'));

        $application = LeaveApplication::with([
            'employee.office',
            'leaveType',
            'currentLocation',
            'latestRoutingLog.office',
            'latestRoutingLog.scanner.employee',
            'routingLogs.office',
            'attachments.verifiedBy',
        ])
        ->where('reference_code', $referenceCode)
        ->first();

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application reference not found in system.',
            ], 404);
        }

        // Resolve active station office from request or user
        $officeId = $request->input('office_id') ?? Auth::user()?->employee?->office_id ?? 2;
        $currentOffice = Office::find($officeId) ?? Office::first();
        $actingUser = Auth::user();

        $isApplicantDeptHead = $application->employee && $application->employee->isDepartmentHead();
        $isOwnApplication = false;
        if ($actingUser && $actingUser->employee) {
            $isOwnApplication = ((int) $application->employee_id === (int) $actingUser->employee->employee_id);
        }

        // Operational Guardrail: QR Scanner Access Restriction
        // QR Scanner is restricted to clerk, hr staff, admin, department head, and mayor.
        // Standard employees do NOT have access.
        if ($actingUser && !$actingUser->canAccessQrScanner()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Restricted: Standard employees do not have access to the QR Document Scanner terminal.',
            ], 403);
        }

        // Evaluate workflow state (fully completed, station completed, or pending actions)
        $workflowState = $this->evaluateRoutingWorkflowState($application, $currentOffice, $actingUser);
        $allowedActions = $workflowState['allowed_actions'];
        $suggestedAction = $workflowState['suggested_action'];
        $suggestedReason = $workflowState['reason'];

        // Compute Statutory SLA status (5 working days rule)
        $sla = $this->calculateSlaStatus($application);

        return response()->json([
            'success' => true,
            'data' => [
                'application_id' => $application->application_id,
                'reference_code' => $application->reference_code,
                'status' => $application->status,
                'current_status' => $application->current_status,
                'is_department_head' => $isApplicantDeptHead,
                'is_own_application' => $isOwnApplication,
                'current_location' => $application->currentLocation ? [
                    'office_id' => $application->currentLocation->office_id,
                    'office_name' => $application->currentLocation->office_name,
                ] : null,
                'employee' => [
                    'employee_id' => $application->employee->employee_id,
                    'employee_no' => $application->employee->employee_no,
                    'full_name' => $application->employee->full_name,
                    'position' => $application->employee->position,
                    'office_name' => $application->employee->office->office_name ?? 'N/A',
                ],
                'leave_details' => [
                    'leave_type' => $application->leaveType->type_name,
                    'leave_code' => $application->leaveType->code,
                    'date_from' => $application->date_from->format('Y-m-d'),
                    'date_to' => $application->date_to->format('Y-m-d'),
                    'days_requested' => (float) $application->days_requested,
                    'details_of_leave' => $application->details_of_leave,
                    'commutation_requested' => $application->commutation_requested,
                ],
                'latest_routing_step' => $application->latestRoutingLog ? [
                    'log_id' => $application->latestRoutingLog->log_id,
                    'action' => $application->latestRoutingLog->action,
                    'method' => $application->latestRoutingLog->method,
                    'office_name' => $application->latestRoutingLog->office->office_name ?? 'N/A',
                    'scanned_by' => $application->latestRoutingLog->scanner->employee->full_name ?? $application->latestRoutingLog->scanner->username,
                    'scanned_at' => $application->latestRoutingLog->scanned_at->format('Y-m-d H:i:s'),
                    'time_ago' => $application->latestRoutingLog->scanned_at->diffForHumans(),
                    'remarks' => $application->latestRoutingLog->remarks,
                ] : null,
                'sla_tracker' => $sla,
                'workflow_state' => $workflowState,
                'is_completed' => $workflowState['is_completed'],
                'can_act' => $workflowState['can_act'],
                'completion_type' => $workflowState['completion_type'],
                'allowed_actions' => $allowedActions,
                'suggested_action' => $suggestedAction,
                'suggested_reason' => $suggestedReason,
                'is_mayor_office' => self::isMayorOffice($currentOffice),
                'is_hrmu_office' => self::isHrmuOffice($currentOffice),
                'has_reached_hrmu' => $application->hasReachedHrmu(),
                'attachments' => $application->attachments->map(fn($att) => [
                    'attachment_id' => $att->attachment_id,
                    'document_type' => $att->document_type,
                    'document_label' => $att->document_label ?? ucfirst(str_replace('_', ' ', $att->document_type)),
                    'original_filename' => $att->original_filename,
                    'size_formatted' => $att->formatted_size,
                    'mime_type' => $att->mime_type,
                    'is_pdf' => $att->is_pdf,
                    'is_image' => $att->is_image,
                    'verification_status' => $att->verification_status,
                    'verification_remarks' => $att->verification_remarks,
                    'verified_by' => $att->verifiedBy?->employee?->full_name ?? $att->verifiedBy?->username,
                    'verified_at' => $att->verified_at ? $att->verified_at->format('M d, Y h:i A') : null,
                    'view_url' => route('documents.view', $att->attachment_id),
                    'download_url' => route('documents.download', $att->attachment_id),
                ]),
                'certification' => [
                    'is_certified' => (bool) $application->is_certified,
                    'certified_as_of' => $application->certified_as_of ? $application->certified_as_of->format('F d, Y') : null,
                    'vl_earned' => $application->certified_vl_earned !== null ? (float) $application->certified_vl_earned : null,
                    'vl_less' => $application->certified_vl_less !== null ? (float) $application->certified_vl_less : null,
                    'vl_balance' => $application->certified_vl_balance !== null ? (float) $application->certified_vl_balance : null,
                    'sl_earned' => $application->certified_sl_earned !== null ? (float) $application->certified_sl_earned : null,
                    'sl_less' => $application->certified_sl_less !== null ? (float) $application->certified_sl_less : null,
                    'sl_balance' => $application->certified_sl_balance !== null ? (float) $application->certified_sl_balance : null,
                    'certified_by' => $application->certifiedBy?->employee?->full_name ?? $application->certifiedBy?->username,
                    'certified_at' => $application->certified_at ? $application->certified_at->format('M d, Y h:i A') : null,
                    'system_live_balances' => (function() use ($application) {
                        $year = Carbon::parse($application->date_from)->year;
                        $vl = (float) (LeaveBalance::where('employee_id', $application->employee_id)
                            ->whereHas('leaveType', fn($q) => $q->where('code', 'VL'))
                            ->where('year', $year)
                            ->value('balance') ?? 0.000);
                        $sl = (float) (LeaveBalance::where('employee_id', $application->employee_id)
                            ->whereHas('leaveType', fn($q) => $q->where('code', 'SL'))
                            ->where('year', $year)
                            ->value('balance') ?? 0.000);
                        $isVl = in_array($application->leaveType->code, ['VL', 'FL']);
                        $isSl = ($application->leaveType->code === 'SL');
                        $vlLess = $isVl ? (float)$application->days_requested : 0.000;
                        $slLess = $isSl ? (float)$application->days_requested : 0.000;
                        return [
                            'as_of_date' => Carbon::now()->format('Y-m-d'),
                            'as_of_formatted' => Carbon::now()->format('F d, Y'),
                            'vl_earned' => $vl,
                            'vl_less' => $vlLess,
                            'vl_balance' => max(0.000, round($vl - $vlLess, 3)),
                            'sl_earned' => $sl,
                            'sl_less' => $slLess,
                            'sl_balance' => max(0.000, round($sl - $slLess, 3)),
                        ];
                    })(),
                ],
            ],
        ]);
    }

    /**
     * 2. Update Routing Status Endpoint
     *
     * Logs the movement of the physical paper application form:
     * - Requires authentication with `routing_clerk`, `hrmu_staff`, or `admin` role.
     * - Captures clerk's station office, timestamp, scan method, and notes.
     * - Creates permanent entry in `ROUTING_LOGS`.
     * - Updates `status` and `current_location_office_id` on `LEAVE_APPLICATIONS`.
     * - If signed/approved by Mayor, updates digital ledger.
     *
     * @param Request $request
     * @param int|string $applicationId
     * @return JsonResponse
     */
    public function updateRoutingStatus(Request $request, $applicationId): JsonResponse
    {
        $user = Auth::user();

        // Validate incoming payload
        $validated = $request->validate([
            'action' => ['required', 'string'],
            'method' => ['nullable', 'string', 'in:QR_SCAN,MANUAL_ENTRY'],
            'office_id' => ['nullable', 'integer', 'exists:offices,office_id'],
            'user_id' => ['nullable', 'integer', 'exists:users,user_id'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        // Authentication Enforcement: Strictly use the currently authenticated active user
        if (!$user || !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only authenticated municipal personnel can update document routing status.',
            ], 403);
        }

        // Resolve active office station:
        // Priority: explicitly passed office_id (if clerk has multi-office clearance) -> user's assigned employee office
        $officeId = $validated['office_id'] ?? $user->employee?->office_id;

        if (!$officeId) {
            return response()->json([
                'success' => false,
                'message' => 'No designated office station found for the current authenticated user.',
            ], 422);
        }

        $office = Office::findOrFail($officeId);
        $application = $this->resolveApplication($applicationId);

        // Operational Guardrail: QR Scanner Action Restriction
        // Only clerk, hr staff, admin, department head, and mayor can record routing actions.
        if (!$user->canAccessQrScanner()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Restricted: Standard employees are not authorized to update document routing status.',
            ], 403);
        }

        // Non-Negotiable Operational Guardrail:
        // Enforce Workflow Completion - if station or full lifecycle is finished, prevent any further action
        $workflowState = $this->evaluateRoutingWorkflowState($application, $office, $user);
        if (!$workflowState['can_act']) {
            return response()->json([
                'success' => false,
                'message' => $workflowState['reason'] ?? 'All required routing actions for this document have already been completed.',
                'workflow_state' => $workflowState,
                'is_completed' => $workflowState['is_completed'],
            ], 422);
        }

        // Normalize action string (supports case-insensitive and friendly terms)
        $normalizedAction = $this->normalizeAction($validated['action']);

        // Non-Negotiable Operational Guardrail (CSC Rule XVI & Anti-Conflict-of-Interest):
        // Prevent Department Heads and applicants from recommending or approving their own leave applications
        $isOwnApplication = ($user->employee && (int) $user->employee->employee_id === (int) $application->employee_id);
        $isApplicantDeptHead = $application->employee && $application->employee->isDepartmentHead();

        if (in_array($normalizedAction, ['RECOMMENDED', 'RECOMMENDED_DISAPPROVAL'])) {
            if ($isOwnApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Self-recommendation prohibited: Pursuant to Civil Service Commission (CSC MC No. 41, s. 1998, Rule XVI) and municipal governance regulations, an employee or Department Head cannot recommend or endorse their own leave application (Box 7.B). Your application must be forwarded to the Municipal Administrator or Municipal Mayor.',
                ], 422);
            }

            if ($isApplicantDeptHead && !self::isMayorOffice($office)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Department Head recommendation prohibited at department station: Pursuant to Civil Service Commission (CSC) rules, a Department Head cannot be recommended by themselves or subordinate personnel. Box 7.B must be acted upon by the Municipal Administrator or Municipal Mayor. Please dispatch this application to HRMU.',
                ], 422);
            }
        }

        if (in_array($normalizedAction, ['APPROVED_PHYSICAL', 'DISAPPROVED_PHYSICAL'])) {
            if ($isOwnApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'Self-approval prohibited: An applicant cannot approve or disapprove their own leave application.',
                ], 422);
            }
        }

        // Non-Negotiable Operational Guardrail:
        // Enforce Office-Based Routing Actions (Premature Approval Prevention)
        $allowedActionsForOffice = $this->getAllowedActionsForOffice($office);
        if (!array_key_exists($normalizedAction, $allowedActionsForOffice)) {
            if (in_array($normalizedAction, ['APPROVED_PHYSICAL', 'DISAPPROVED_PHYSICAL'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Premature approval restricted: Only the Office of the Municipal Mayor has legal appointing authority to approve or disapprove leave applications. Your station ({$office->office_name}) may only recommend or dispatch documents.",
                ], 422);
            }

            if (in_array($normalizedAction, ['RECOMMENDED', 'RECOMMENDED_DISAPPROVAL']) && self::isMayorOffice($office)) {
                return response()->json([
                    'success' => false,
                    'message' => "The Office of the Municipal Mayor is the final deciding authority and executes final Approval or Disapproval, not recommendations.",
                ], 422);
            }

            return response()->json([
                'success' => false,
                'message' => "Action '{$normalizedAction}' is not permitted at station '{$office->office_name}'. Allowed actions: " . implode(', ', array_keys($allowedActionsForOffice)),
            ], 422);
        }

        $scanMethod = $validated['method'] ?? 'QR_SCAN';
        $now = Carbon::now();

        return DB::transaction(function () use (
            $application,
            $office,
            $user,
            $normalizedAction,
            $scanMethod,
            $validated,
            $now,
            $request
        ) {
            // 1. Create entry in ROUTING_LOGS
            $routingLog = RoutingLog::create([
                'application_id' => $application->application_id,
                'office_id' => $office->office_id,
                'scanned_by' => $user->user_id,
                'action' => $normalizedAction,
                'method' => $scanMethod,
                'scanned_at' => $now,
                'remarks' => $validated['remarks'] ?? null,
            ]);

            // 2. Compute new application status based on action and office station
            $newStatus = $this->deriveApplicationStatus($normalizedAction, $office->office_name);

            // 3. Update LEAVE_APPLICATIONS table
            $application->update([
                'status' => $newStatus,
                'current_location_office_id' => $office->office_id,
            ]);

            // 4. Special Workflow: Physical Approval by Municipal Mayor
            // When document receives Mayor's physical signature, post to digital ledger if not already recorded
            if ($normalizedAction === 'APPROVED_PHYSICAL') {
                $this->handlePhysicalApprovalLedgerPost($application, $now);
            } elseif ($normalizedAction === 'DISAPPROVED_PHYSICAL') {
                $application->update(['status' => 'Disapproved']);
            }

            // 5. Box 7.A Certification Update for HRMU:
            // When handled at HRMU by authorized HRMU Staff or Admin, can certify leave credits or auto-certify upon dispatch to Mayor
            if (self::isHrmuOffice($office) && in_array($user->role, ['hrmu_staff', 'admin'])) {
                $shouldCertify = $request->boolean('certify_credits') 
                    || $normalizedAction === 'DISPATCHED'
                    || !empty($validated['certify_credits']);

                if ($shouldCertify && !$application->is_certified) {
                    $year = Carbon::parse($application->date_from)->year;
                    $vlEarned = (float) (LeaveBalance::where('employee_id', $application->employee_id)
                        ->whereHas('leaveType', fn($q) => $q->where('code', 'VL'))
                        ->where('year', $year)
                        ->value('balance') ?? 0.000);

                    $slEarned = (float) (LeaveBalance::where('employee_id', $application->employee_id)
                        ->whereHas('leaveType', fn($q) => $q->where('code', 'SL'))
                        ->where('year', $year)
                        ->value('balance') ?? 0.000);

                    $isVl = in_array($application->leaveType->code, ['VL', 'FL']);
                    $isSl = ($application->leaveType->code === 'SL');
                    $vlLess = $isVl ? (float)$application->days_requested : 0.000;
                    $slLess = $isSl ? (float)$application->days_requested : 0.000;

                    $application->update([
                        'is_certified' => true,
                        'certified_as_of' => $now,
                        'certified_vl_earned' => $vlEarned,
                        'certified_vl_less' => $vlLess,
                        'certified_vl_balance' => max(0.000, round($vlEarned - $vlLess, 3)),
                        'certified_sl_earned' => $slEarned,
                        'certified_sl_less' => $slLess,
                        'certified_sl_balance' => max(0.000, round($slEarned - $slLess, 3)),
                        'certified_by_user_id' => $user->user_id,
                        'certified_at' => $now,
                    ]);
                }
            }

            // 5. Audit Log Oversight
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'UPDATE_ROUTING_STATUS',
                'table_name' => 'routing_logs',
                'record_id' => $routingLog->log_id,
                'details' => json_encode([
                    'reference_code' => $application->reference_code,
                    'action' => $normalizedAction,
                    'office' => $office->office_name,
                    'method' => $scanMethod,
                    'new_status' => $application->status,
                ]),
                'ip_address' => request()->ip() ?? '127.0.0.1',
                'logged_at' => $now,
            ]);

            // 6. Real-Time Email Notification to Applicant (sent right after DB commit)
            $remarkCopy = $validated['remarks'] ?? null;
            DB::afterCommit(function () use ($application, $normalizedAction, $office, $remarkCopy) {
                \App\Services\LeaveNotificationService::notifyStatusUpdate(
                    $application,
                    $normalizedAction,
                    $office,
                    $remarkCopy
                );
            });

            $updatedApplication = $application->fresh([
                'employee.office',
                'leaveType',
                'currentLocation',
                'latestRoutingLog.office',
                'latestRoutingLog.scanner.employee',
                'routingLogs.office',
            ]);
            $newWorkflowState = $this->evaluateRoutingWorkflowState($updatedApplication, $office);

            return response()->json([
                'success' => true,
                'message' => "Document {$application->reference_code} marked as '{$normalizedAction}' at {$office->office_name}.",
                'is_completed' => $newWorkflowState['is_completed'],
                'workflow_state' => $newWorkflowState,
                'data' => [
                    'application_id' => $application->application_id,
                    'reference_code' => $application->reference_code,
                    'status' => $application->status,
                    'current_status' => $application->current_status,
                    'current_location' => [
                        'office_id' => $office->office_id,
                        'office_name' => $office->office_name,
                    ],
                    'log_entry' => [
                        'log_id' => $routingLog->log_id,
                        'action' => $routingLog->action,
                        'method' => $routingLog->method,
                        'scanned_at' => $routingLog->scanned_at->format('Y-m-d H:i:s'),
                        'remarks' => $routingLog->remarks,
                    ],
                ],
            ]);
        });
    }

    /**
     * 3. Track Application Status & Visual Timeline
     *
     * Employee and clerk tracking endpoint returning chronological paper movement steps.
     * Enforces privacy: standard employees can only view their own filed leave applications.
     *
     * @param int|string $applicationId
     * @return JsonResponse
     */
    public function trackApplicationStatus($applicationId): JsonResponse
    {
        $user = Auth::user();
        $application = $this->resolveApplication($applicationId);

        // RBAC & Privacy Check: User must be authorized to track this application (Applicant, Dept Head, HRMU, Admin, etc.)
        if ($user && !$user->canViewApplicationDocument($application)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You can only track your own filed leave applications or those in your department.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $this->buildApplicationTimelinePayload($application),
        ]);
    }

    /**
     * Builds the timeline tracker payload from a LeaveApplication (reusing preloaded relations when available).
     */
    public function buildApplicationTimelinePayload(LeaveApplication $application): array
    {
        $missing = [];
        foreach (['employee.office', 'leaveType', 'currentLocation', 'attachments.verifiedBy.employee', 'routingLogs.office', 'routingLogs.scanner.employee'] as $rel) {
            $topRel = explode('.', $rel)[0];
            if (!$application->relationLoaded($topRel)) {
                $missing[] = $rel;
            }
        }
        if (!empty($missing)) {
            $application->load($missing);
        }

        $logs = $application->routingLogs->sortBy('scanned_at')->values();

        // Build Visual Timeline Steps
        $timeline = [];

        // Step 0: Initial Filing
        $timeline[] = [
            'step_index' => 1,
            'title' => 'Leave Application Filed',
            'description' => "Filed by {$application->employee->full_name} for {$application->days_requested} day(s) of {$application->leaveType->type_name}.",
            'office' => $application->employee->office->office_name ?? 'HRMU',
            'action' => 'FILED',
            'method' => 'SYSTEM',
            'timestamp' => $application->created_at->format('Y-m-d H:i:s'),
            'time_ago' => $application->created_at->diffForHumans(),
            'handler' => $application->employee->full_name,
            'remarks' => $application->details_of_leave,
        ];

        // Subsequent physical routing steps
        $stepCounter = 2;
        foreach ($logs as $log) {
            $clerkName = $log->scanner?->employee?->full_name ?? $log->scanner?->username ?? 'System';

            $timeline[] = [
                'step_index' => $stepCounter++,
                'title' => $this->formatTimelineTitle($log->action, $log->office->office_name ?? 'Office'),
                'description' => "Physical paper {$log->action} at " . ($log->office->office_name ?? 'Office') . " (via {$log->method}).",
                'office' => $log->office->office_name ?? 'Municipal Office',
                'action' => $log->action,
                'method' => $log->method,
                'timestamp' => $log->scanned_at->format('Y-m-d H:i:s'),
                'time_ago' => $log->scanned_at->diffForHumans(),
                'handler' => $clerkName,
                'remarks' => $log->remarks,
            ];
        }

        // SLA Compliance calculation
        $sla = $this->calculateSlaStatus($application);

        return [
            'application_id' => $application->application_id,
            'reference_code' => $application->reference_code,
            'status' => $application->status,
            'current_status' => $application->current_status,
            'days_requested' => (float) $application->days_requested,
            'date_range' => $application->date_from->format('M d, Y') . ' to ' . $application->date_to->format('M d, Y'),
            'current_location' => $application->currentLocation ? [
                'office_id' => $application->currentLocation->office_id,
                'office_name' => $application->currentLocation->office_name,
            ] : null,
            'sla_tracker' => $sla,
            'timeline' => $timeline,
            'attachments' => $application->attachments->map(fn($att) => [
                'attachment_id' => $att->attachment_id,
                'document_type' => $att->document_type,
                'document_label' => $att->document_label ?? ucfirst(str_replace('_', ' ', $att->document_type)),
                'original_filename' => $att->original_filename,
                'size_formatted' => $att->formatted_size,
                'mime_type' => $att->mime_type,
                'is_pdf' => $att->is_pdf,
                'is_image' => $att->is_image,
                'verification_status' => $att->verification_status,
                'verification_remarks' => $att->verification_remarks,
                'verified_by' => $att->verifiedBy?->employee?->full_name ?? $att->verifiedBy?->username,
                'verified_at' => $att->verified_at ? $att->verified_at->format('M d, Y h:i A') : null,
                'view_url' => route('documents.view', $att->attachment_id),
                'download_url' => route('documents.download', $att->attachment_id),
            ])->values()->all(),
        ];
    }

    /**
     * Ultra-lightweight fingerprint endpoint for 1-second cross-device live sync.
     */
    public function liveSyncVersion(): JsonResponse
    {
        $row = DB::selectOne("
            SELECT
                COALESCE((SELECT MAX(updated_at) FROM leave_applications), '') AS a_u,
                (SELECT COUNT(*) FROM leave_applications) AS a_c,
                COALESCE((SELECT MAX(scanned_at) FROM routing_logs), '') AS r_u,
                (SELECT COUNT(*) FROM routing_logs) AS r_c,
                COALESCE((SELECT MAX(updated_at) FROM leave_records), '') AS l_u,
                (SELECT COUNT(*) FROM leave_records) AS l_c,
                COALESCE((SELECT MAX(updated_at) FROM leave_balances), '') AS b_u,
                COALESCE((SELECT MAX(updated_at) FROM users), '') AS u_u,
                (SELECT COUNT(*) FROM users) AS u_c,
                COALESCE((SELECT MAX(created_at) FROM audit_logs), '') AS au_u
        ");

        return response()->json([
            'version' => md5(json_encode($row)),
        ])->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper / Domain Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Resolves an application by primary key integer or alphanumeric reference code.
     */
    protected function resolveApplication($identifier): LeaveApplication
    {
        if (is_numeric($identifier)) {
            $app = LeaveApplication::find($identifier);
            if ($app) {
                return $app;
            }
        }

        return LeaveApplication::where('reference_code', $identifier)
            ->firstOrFail();
    }

    /**
     * Normalizes free-form or incoming action strings into standard system codes.
     */
    protected function normalizeAction(string $rawAction): string
    {
        $normalized = strtoupper(trim($rawAction));

        return match ($normalized) {
            'RECEIVED', 'RECEIVE' => 'RECEIVED',
            'FORWARDED', 'FORWARD', 'DISPATCHED', 'DISPATCH' => 'DISPATCHED',
            'RECOMMENDED', 'RECOMMEND', 'RECOMMENDED_APPROVAL', 'RECOMMEND_APPROVAL' => 'RECOMMENDED',
            'RECOMMENDED_DISAPPROVAL', 'RECOMMEND_DISAPPROVAL' => 'RECOMMENDED_DISAPPROVAL',
            'SIGNED/APPROVED', 'APPROVED', 'APPROVE', 'APPROVED_PHYSICAL' => 'APPROVED_PHYSICAL',
            'SIGNED/DISAPPROVED', 'DISAPPROVED', 'DISAPPROVE', 'DISAPPROVED_PHYSICAL' => 'DISAPPROVED_PHYSICAL',
            default => in_array($normalized, array_keys(self::ALLOWED_ACTIONS))
                ? $normalized
                : throw ValidationException::withMessages([
                    'action' => ["Invalid routing action '{$rawAction}'. Allowed: " . implode(', ', array_keys(self::ALLOWED_ACTIONS))],
                ]),
        };
    }

    /**
     * Derives human-scannable application status based on physical action and station.
     */
    protected function deriveApplicationStatus(string $action, string $officeName): string
    {
        return match ($action) {
            'RECEIVED' => "Received at {$officeName}",
            'DISPATCHED' => 'In Transit',
            'RECOMMENDED' => 'Recommended for Approval',
            'RECOMMENDED_DISAPPROVAL' => 'Recommended for Disapproval',
            'APPROVED_PHYSICAL' => 'Approved',
            'DISAPPROVED_PHYSICAL' => 'Disapproved',
            default => 'Under Processing',
        };
    }

    /**
     * Formats visual timeline entry headers.
     */
    protected function formatTimelineTitle(string $action, string $officeName): string
    {
        return match ($action) {
            'RECEIVED' => "Document Received at {$officeName}",
            'DISPATCHED' => "Dispatched / Forwarded from {$officeName}",
            'RECOMMENDED' => "Recommended for Approval by Dept Head",
            'RECOMMENDED_DISAPPROVAL' => "Recommended for Disapproval by Dept Head",
            'APPROVED_PHYSICAL' => "Approved by Municipal Mayor (Physical Signature)",
            'DISAPPROVED_PHYSICAL' => "Disapproved by Municipal Mayor",
            default => "{$action} at {$officeName}",
        };
    }

    /**
     * Posts approved leave deduction to digital ledger (CS Form 41) upon Mayor's physical signature.
     */
    protected function handlePhysicalApprovalLedgerPost(LeaveApplication $application, Carbon $postedAt): void
    {
        $year = $application->date_from->year;
        $days = (float) $application->days_requested;
        $leaveTypeCode = $application->leaveType->code ?? 'VL';
        $service = app(LeaveRecordService::class);

        // Insufficient VL/SL credits: recorded as Absence/Undertime Without Pay in the
        // Leave Record Card only. No credit balance or ledger deduction is posted.
        $isLwop = $service->isLeaveWithoutPay($application);

        if (!$isLwop) {
        // 1. Retrieve & update specific leave balance (VL, SL, FL, etc.)
        $balance = LeaveBalance::lockForUpdate()->firstOrCreate(
            [
                'employee_id' => $application->employee_id,
                'leave_type_id' => $application->leave_type_id,
                'year' => $year,
            ],
            [
                'balance' => 0.000,
                'last_posted_at' => $postedAt,
            ]
        );

        $newBalance = round((float) $balance->balance - $days, 3);
        $balance->update([
            'balance' => $newBalance,
            'last_posted_at' => $postedAt,
        ]);

        // If leave type is Mandatory Forced Leave (FL), also deduct against Vacation Leave (VL)
        if ($leaveTypeCode === 'FL') {
            $vlType = LeaveType::where('code', 'VL')->first();
            if ($vlType && $vlType->leave_type_id !== $application->leave_type_id) {
                $vlBal = LeaveBalance::lockForUpdate()->firstOrCreate(
                    [
                        'employee_id' => $application->employee_id,
                        'leave_type_id' => $vlType->leave_type_id,
                        'year' => $year,
                    ],
                    [
                        'balance' => 0.000,
                        'last_posted_at' => $postedAt,
                    ]
                );
                $vlBal->update([
                    'balance' => round((float) $vlBal->balance - $days, 3),
                    'last_posted_at' => $postedAt,
                ]);
            }
        }

        // 2. Prevent duplicate ledger entries for the same application
        $exists = LeaveLedgerEntry::where('application_id', $application->application_id)->exists();
        if (!$exists) {
            LeaveLedgerEntry::create([
                'employee_id' => $application->employee_id,
                'leave_type_id' => $application->leave_type_id,
                'application_id' => $application->application_id,
                'deduction_id' => null,
                'entry_type' => 'LEAVE_AVAILED',
                'days' => $days,
                'balance_after' => $newBalance,
                'particular' => "Approved Leave ({$application->reference_code})",
                'posted_at' => $postedAt,
            ]);
        }
        }

        // 3. Synchronize into official 11-column Civil Service Leave Record Card (leave_records)
        // Applies the deduction at the appropriate processing stage (Mayor physical approval),
        // updating the row created upon submission and strictly preventing duplicate entries.

        // Ensure employee has earned credits for the filing month if it has arrived and not yet accrued
        $filingDate = $application->date_of_filing 
            ? Carbon::parse($application->date_of_filing) 
            : ($application->created_at ? Carbon::parse($application->created_at) : Carbon::now());

        if (!$service->hasEmployeeAccruedMonth($application->employee_id, $filingDate)) {
            $service->accrueMonthlyCreditsForEmployee(
                $application->employee,
                $filingDate,
                '1.25',
                '1.25',
                'Earned',
                false,
                Auth::id() ?? 1
            );
        }

        $service->applyApprovedLeaveDeduction($application, $postedAt, Auth::id() ?? 1);
    }

    /**
     * Calculates the Civil Service Section 49 SLA timer (5 working days deemed approval rule).
     */
    protected function calculateSlaStatus(LeaveApplication $application): array
    {
        $filedAt = $application->created_at;
        $now = Carbon::now();

        // Calculate working days elapsed (excluding Saturdays, Sundays, and official holidays)
        $workingDaysElapsed = $filedAt->diffInDaysFiltered(function (Carbon $date) {
            return WorkingDayService::isWorkingDay($date);
        }, $now);

        $slaLimitWorkingDays = 5;
        $isTerminal = in_array($application->status, ['Approved', 'Disapproved', 'Cancelled']);

        $complianceStatus = 'ON_TIME';
        if (!$isTerminal) {
            if ($workingDaysElapsed > $slaLimitWorkingDays) {
                $complianceStatus = 'BREACHED'; // Deemed approval risk under CSC Rule XVI Section 49
            } elseif ($workingDaysElapsed >= 3) {
                $complianceStatus = 'WARNING';
            }
        }

        return [
            'working_days_elapsed' => $workingDaysElapsed,
            'sla_limit_days' => $slaLimitWorkingDays,
            'compliance_status' => $complianceStatus,
            'is_terminal' => $isTerminal,
            'filed_date' => $filedAt->format('Y-m-d'),
        ];
    }

    /**
     * Official HRMU Certification of Leave Credits (Box 7.A on CSC Form 6).
     *
     * Permitted ONLY for HRMU staff or Administrators once the physical document reaches HRMU.
     */
    public function certifyCredits(Request $request, $applicationId): JsonResponse
    {
        $user = Auth::user();

        // RBAC: Only HRMU staff or admin can certify leave credits
        if (!$user || !in_array($user->role, ['hrmu_staff', 'admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only HRMU staff or administrators can certify leave credits in Section 7.A.',
            ], 403);
        }

        $application = $this->resolveApplication($applicationId);

        // Operational Guardrail: Physical paper must have reached HRMU
        $stationOfficeId = $request->input('office_id') ?? $user->employee?->office_id ?? 1;
        $stationOffice = Office::find($stationOfficeId);
        $isHrmuStation = self::isHrmuOffice($stationOffice);

        if (!$application->hasReachedHrmu() && !$isHrmuStation) {
            return response()->json([
                'success' => false,
                'message' => 'The leave application has not yet physically reached HRMU. Section 7.A must remain blank until received by HRMU.',
            ], 422);
        }

        $validated = $request->validate([
            'as_of_date' => ['nullable', 'date'],
            'vl_earned' => ['nullable', 'numeric', 'min:0'],
            'sl_earned' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:500'],
        ]);

        $year = Carbon::parse($application->date_from)->year;
        $defaultVl = (float) (LeaveBalance::where('employee_id', $application->employee_id)
            ->whereHas('leaveType', fn($q) => $q->where('code', 'VL'))
            ->where('year', $year)
            ->value('balance') ?? 0.000);

        $defaultSl = (float) (LeaveBalance::where('employee_id', $application->employee_id)
            ->whereHas('leaveType', fn($q) => $q->where('code', 'SL'))
            ->where('year', $year)
            ->value('balance') ?? 0.000);

        $vlEarned = isset($validated['vl_earned']) ? (float)$validated['vl_earned'] : $defaultVl;
        $slEarned = isset($validated['sl_earned']) ? (float)$validated['sl_earned'] : $defaultSl;

        $isVl = in_array($application->leaveType->code, ['VL', 'FL']);
        $isSl = ($application->leaveType->code === 'SL');

        $vlLess = $isVl ? (float)$application->days_requested : 0.000;
        $slLess = $isSl ? (float)$application->days_requested : 0.000;

        $vlBalance = max(0.000, round($vlEarned - $vlLess, 3));
        $slBalance = max(0.000, round($slEarned - $slLess, 3));

        $asOfDate = !empty($validated['as_of_date']) 
            ? Carbon::parse($validated['as_of_date']) 
            : Carbon::now();

        $application->update([
            'is_certified' => true,
            'certified_as_of' => $asOfDate,
            'certified_vl_earned' => $vlEarned,
            'certified_vl_less' => $vlLess,
            'certified_vl_balance' => $vlBalance,
            'certified_sl_earned' => $slEarned,
            'certified_sl_less' => $slLess,
            'certified_sl_balance' => $slBalance,
            'certified_by_user_id' => $user->user_id,
            'certified_at' => Carbon::now(),
        ]);

        AuditLog::create([
            'user_id' => $user->user_id,
            'action' => 'CERTIFY_LEAVE_CREDITS_SECTION_7A',
            'table_name' => 'leave_applications',
            'record_id' => $application->application_id,
            'details' => json_encode([
                'reference_code' => $application->reference_code,
                'as_of' => $asOfDate->format('Y-m-d'),
                'vl_earned' => $vlEarned,
                'vl_balance' => $vlBalance,
                'sl_earned' => $slEarned,
                'sl_balance' => $slBalance,
                'remarks' => $validated['remarks'] ?? null,
            ]),
            'ip_address' => request()->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        \App\Services\LeaveNotificationService::notifyStatusUpdate(
            $application,
            'CERTIFIED_SECTION_7A',
            $stationOffice,
            $validated['remarks'] ?? "VL Balance: " . number_format($vlBalance, 3) . "d • SL Balance: " . number_format($slBalance, 3) . "d"
        );

        return response()->json([
            'success' => true,
            'message' => "Civil Service Form 6 Section 7.A certified successfully for {$application->reference_code}.",
            'data' => [
                'reference_code' => $application->reference_code,
                'is_certified' => true,
                'certified_as_of' => $asOfDate->format('F d, Y'),
                'vl_earned' => $vlEarned,
                'vl_less' => $vlLess,
                'vl_balance' => $vlBalance,
                'sl_earned' => $slEarned,
                'sl_less' => $slLess,
                'sl_balance' => $slBalance,
                'certified_by' => $user->employee->full_name ?? $user->username,
                'certified_at' => Carbon::now()->format('M d, Y h:i A'),
            ],
        ]);
    }

    /**
     * View a supporting document attachment in-browser (PDF or Image).
     *
     * @param int|string $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function viewAttachment($id)
    {
        $attachment = LeaveAttachment::with(['application.employee.office', 'application.currentLocation', 'application.routingLogs'])->findOrFail($id);
        $user = Auth::user();

        // RBAC: Verify if authenticated user is authorized (Applicant, Department Head, Mayor, HRMU, Admin, etc.)
        if (!$user || !$user->canViewApplicationDocument($attachment->application)) {
            abort(403, 'Unauthorized access to application document.');
        }

        try {
            $path = FileStorageService::getAbsolutePath($attachment);

            return response()->stream(function () use ($path) {
                $stream = fopen($path, 'rb');
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
            }, 200, [
                'Content-Type' => $attachment->mime_type,
                'Content-Length' => $attachment->size_bytes,
                'Content-Disposition' => 'inline; filename="' . addslashes($attachment->original_filename) . '"',
                'Cache-Control' => 'private, max-age=3600, must-revalidate',
            ]);
        } catch (\Throwable $e) {
            abort(404, 'File not found: ' . $e->getMessage());
        }
    }

    /**
     * Download a supporting document attachment with its original filename.
     *
     * @param int|string $id
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadAttachment($id)
    {
        $attachment = LeaveAttachment::with(['application.employee.office', 'application.currentLocation', 'application.routingLogs'])->findOrFail($id);
        $user = Auth::user();

        // RBAC: Verify if authenticated user is authorized (Applicant, Department Head, Mayor, HRMU, Admin, etc.)
        if (!$user || !$user->canViewApplicationDocument($attachment->application)) {
            abort(403, 'Unauthorized access to application document.');
        }

        try {
            $path = FileStorageService::getAbsolutePath($attachment);

            return response()->streamDownload(function () use ($path) {
                $stream = fopen($path, 'rb');
                if ($stream) {
                    fpassthru($stream);
                    fclose($stream);
                }
            }, $attachment->original_filename, [
                'Content-Type' => $attachment->mime_type,
                'Content-Length' => $attachment->size_bytes,
            ]);
        } catch (\Throwable $e) {
            abort(404, 'File not found: ' . $e->getMessage());
        }
    }

    /**
     * HRMU Verification of Digital Supporting Document.
     * Allows HRMU Staff and Admins to verify or reject documents and record remarks.
     * Kept strictly independent from physical paper document custody logs.
     *
     * @param Request $request
     * @param int|string $id
     * @return JsonResponse
     */
    public function verifyAttachment(Request $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'verification_status' => ['required', 'string', 'in:VERIFIED,REJECTED,PENDING'],
            'verification_remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $attachment = LeaveAttachment::with('application')->findOrFail($id);
        $user = Auth::user();

        $attachment->update([
            'verification_status' => $validated['verification_status'],
            'verification_remarks' => $validated['verification_remarks'] ?? null,
            'verified_by_user_id' => $user->user_id,
            'verified_at' => Carbon::now(),
        ]);

        // Audit Trail
        AuditLog::create([
            'user_id' => $user->user_id,
            'action' => 'VERIFY_SUPPORTING_DOCUMENT',
            'table_name' => 'leave_attachments',
            'record_id' => $attachment->attachment_id,
            'details' => json_encode([
                'reference_code' => $attachment->application->reference_code,
                'filename' => $attachment->original_filename,
                'status' => $attachment->verification_status,
                'remarks' => $attachment->verification_remarks,
            ]),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        if ($attachment->application) {
            $docAction = match ($attachment->verification_status) {
                'VERIFIED' => 'ATTACHMENT_VERIFIED',
                'REJECTED' => 'ATTACHMENT_REJECTED',
                default => 'ATTACHMENT_PENDING',
            };
            $docRemark = $attachment->original_filename
                . (!empty($attachment->verification_remarks) ? " — {$attachment->verification_remarks}" : '');
            \App\Services\LeaveNotificationService::notifyStatusUpdate(
                $attachment->application,
                $docAction,
                $user->employee?->office,
                $docRemark
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Document '{$attachment->original_filename}' marked as {$attachment->verification_status}.",
            'data' => [
                'attachment_id' => $attachment->attachment_id,
                'verification_status' => $attachment->verification_status,
                'verification_remarks' => $attachment->verification_remarks,
                'verified_by' => $user->employee?->full_name ?? $user->username,
                'verified_at' => $attachment->verified_at->format('M d, Y h:i A'),
            ],
        ]);
    }
}
