<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\LeaveApplication;
use App\Models\LeaveBalance;
use App\Models\LeaveRecord;
use App\Models\LeaveType;
use App\Models\Office;
use App\Models\StarlaReference;
use App\Models\User;
use App\Services\BackupService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * AdminController
 *
 * Coordinates the System Administrator / HR Head Governance Level:
 * - Municipal Executive Overview & KPI Metrics.
 * - User Account Management & Role-Based Access Control (RBAC).
 * - "Starla" Reference Conversion Matrix Inspection (Tables II, III, & IV).
 * - Audit Trail Oversight.
 */
class AdminController extends Controller
{
    /**
     * Display the System Administrator Governance Control Center.
     */
    public function dashboard(Request $request): View
    {
        $currentYear = Carbon::now()->year;

        // 1. Executive Municipal KPIs
        $totalEmployees = Employee::count();
        $activeEmployees = Employee::active()->count();
        $totalOffices = Office::count();
        $totalUsers = User::count();

        // 2. Leave Application Pipeline Metrics
        $totalApplications = LeaveApplication::count();
        $pendingApplications = LeaveApplication::whereIn('status', ['Pending', 'Under Processing', 'In Transit'])->count();
        $approvedApplications = LeaveApplication::where('status', 'Approved')->count();
        $disapprovedApplications = LeaveApplication::where('status', 'Disapproved')->count();

        // 3. User Accounts by Role
        $usersByRole = [
            'admin' => User::where('role', 'admin')->count(),
            'hrmu_staff' => User::where('role', 'hrmu_staff')->count(),
            'routing_clerk' => User::where('role', 'routing_clerk')->count(),
            'employee' => User::where('role', 'employee')->count(),
        ];

        // 4. Municipal Certified Balances Overview
        $cumulativeVl = LeaveBalance::whereHas('leaveType', fn($q) => $q->where('code', 'VL'))
            ->where('year', $currentYear)
            ->sum('balance');
        $cumulativeSl = LeaveBalance::whereHas('leaveType', fn($q) => $q->where('code', 'SL'))
            ->where('year', $currentYear)
            ->sum('balance');

        // 5. System Users Directory
        $users = User::with(['employee.office'])
            ->orderBy('role')
            ->orderBy('username')
            ->get();

        // 6. Starla Tables Summary (Table II and Table IV per municipal policy)
        $starlaCountTable2 = StarlaReference::where('table_ref', 'Table II')->count();
        $starlaCountTable4 = StarlaReference::where('table_ref', 'Table IV')->count();
        $starlaTotal = StarlaReference::count();

        // Filterable Starla Reference sample
        $selectedTable = $request->query('table_ref', 'Table IV');
        $starlaRecords = StarlaReference::where('table_ref', $selectedTable)
            ->orderBy('starla_id', 'asc')
            ->paginate(15, ['*'], 'starla_page');

        // 7. Recent Audit Logs
        $recentAuditLogs = AuditLog::with('user.employee')
            ->orderBy('logged_at', 'desc')
            ->paginate(20, ['*'], 'audit_page');

        $offices = Office::orderBy('office_name')->get();

        // 8. Disaster Recovery & System Backups
        $backups = BackupService::listBackups();
        $totalBackupBytes = array_sum(array_column($backups, 'size_bytes'));
        $totalBackupSizeFormatted = BackupService::formatBytes($totalBackupBytes);
        $lastBackupDate = !empty($backups) ? $backups[0]['created_at'] : 'None Recorded';

        return view('admin.dashboard', compact(
            'totalEmployees',
            'activeEmployees',
            'totalOffices',
            'totalUsers',
            'totalApplications',
            'pendingApplications',
            'approvedApplications',
            'disapprovedApplications',
            'usersByRole',
            'cumulativeVl',
            'cumulativeSl',
            'users',
            'starlaCountTable2',
            'starlaCountTable4',
            'starlaTotal',
            'selectedTable',
            'starlaRecords',
            'recentAuditLogs',
            'offices',
            'currentYear',
            'backups',
            'totalBackupSizeFormatted',
            'lastBackupDate'
        ));
    }

    /**
     * Create an on-demand system backup (Database Dump or Full System Archive).
     */
    public function createBackup(Request $request): RedirectResponse
    {
        $type = $request->input('type', 'db');
        if (!in_array($type, ['db', 'full'], true)) {
            $type = 'db';
        }

        try {
            $backup = BackupService::createBackup($type);
            return redirect()->route('admin.dashboard', ['tab' => 'backups'])
                ->with('success', "{$backup['type']} created successfully: {$backup['filename']} ({$backup['size_formatted']})");
        } catch (\Throwable $e) {
            return redirect()->route('admin.dashboard', ['tab' => 'backups'])
                ->with('error', "Backup generation failed: " . $e->getMessage());
        }
    }

    /**
     * Securely download a system backup file.
     */
    public function downloadBackup(string $filename)
    {
        if (str_contains($filename, '..') || str_contains($filename, '/') || str_contains($filename, '\\')) {
            abort(400, "Invalid backup filename pattern.");
        }

        $filePath = BackupService::getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filePath)) {
            abort(404, "Backup archive not found on server.");
        }

        return response()->download($filePath, $filename);
    }

    /**
     * Delete an existing backup file.
     */
    public function destroyBackup(string $filename): RedirectResponse
    {
        try {
            $deleted = BackupService::deleteBackup($filename);
            if ($deleted) {
                return redirect()->route('admin.dashboard', ['tab' => 'backups'])
                    ->with('success', "Backup archive [{$filename}] has been permanently removed.");
            }
            return redirect()->route('admin.dashboard', ['tab' => 'backups'])
                ->with('error', "Backup file not found or could not be removed.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.dashboard', ['tab' => 'backups'])
                ->with('error', "Deletion failed: " . $e->getMessage());
        }
    }

    /**
     * Update an existing user's RBAC role, account active state, and employee master record details.
     */
    public function updateUser(Request $request, $id): RedirectResponse
    {
        $targetUser = User::with('employee')->findOrFail($id);
        $employee = $targetUser->employee;

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,hrmu_staff,routing_clerk,employee'],
            'is_active' => ['nullable', 'boolean'],
            'username' => ['required', 'string', 'max:60', 'regex:/^[a-zA-Z0-9_.-]+$/', 'unique:users,username,' . $targetUser->user_id . ',user_id'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email,' . $targetUser->user_id . ',user_id'],
            'full_name' => ['nullable', 'string', 'max:255'],
            'employee_no' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Za-z0-9_-]+$/',
                $employee ? 'unique:employees,employee_no,' . $employee->employee_id . ',employee_id' : 'unique:employees,employee_no'
            ],
            'position' => ['nullable', 'string', 'max:150'],
            'employment_type' => ['nullable', 'string', 'in:Permanent,Casual,Elected,Co-Terminous,Job Order'],
            'office_id' => ['nullable', 'integer', 'exists:offices,office_id'],
            'employee_status' => ['nullable', 'string', 'in:active,inactive,retired,transferred'],
            'date_hired' => ['nullable', 'date'],
        ], [
            'username.unique' => 'This username is already taken by another account.',
            'employee_no.unique' => 'This Employee ID is already assigned to another personnel.',
            'employee_no.regex' => 'The Employee ID may contain letters, numbers, and hyphens (e.g. HRMU-0001).',
        ]);

        $oldRole = $targetUser->role;
        $newRole = $validated['role'];
        $isActive = $request->boolean('is_active', true);

        // Security Guard: Prevent self-demotion or self-deactivation of the active superadmin
        if ($targetUser->user_id === Auth::id()) {
            if ($newRole !== 'admin') {
                return back()->with('error', 'Security Guard: You cannot demote your own active Administrator account.');
            }
            if (!$isActive) {
                return back()->with('error', 'Security Guard: You cannot deactivate your own active Administrator account.');
            }
        }

        DB::transaction(function () use ($targetUser, $employee, $validated, $newRole, $isActive, $request) {
            // 1. Update User Login Account
            $targetUser->update([
                'username' => strtolower(trim($validated['username'])),
                'email' => !empty($validated['email']) ? strtolower(trim($validated['email'])) : null,
                'role' => $newRole,
                'is_active' => $isActive,
            ]);

            // 2. Update Employee Master Profile if associated
            if ($employee) {
                $employeeData = [];
                if (!empty($validated['full_name'])) {
                    $employeeData['full_name'] = trim($validated['full_name']);
                }
                if (!empty($validated['employee_no'])) {
                    $normalizedNo = strtoupper(trim($validated['employee_no']));
                    // Secondary guard: check if taken by another employee record
                    $existsAnother = Employee::where('employee_no', $normalizedNo)
                        ->where('employee_id', '!=', $employee->employee_id)
                        ->exists();
                    if ($existsAnother) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'employee_no' => 'This Employee ID is already assigned to another personnel.'
                        ]);
                    }
                    $employeeData['employee_no'] = $normalizedNo;
                }
                if (!empty($validated['position'])) {
                    $employeeData['position'] = trim($validated['position']);
                }
                if (!empty($validated['employment_type'])) {
                    $employeeData['employment_type'] = $validated['employment_type'];
                }
                if (!empty($validated['office_id'])) {
                    $employeeData['office_id'] = (int) $validated['office_id'];
                }
                if (!empty($validated['employee_status'])) {
                    $employeeData['status'] = $validated['employee_status'];
                }
                if ($request->has('date_hired')) {
                    $employeeData['date_hired'] = !empty($validated['date_hired']) ? $validated['date_hired'] : null;
                }

                if (!empty($employeeData)) {
                    $employee->update($employeeData);
                }
            }

            // 3. Log Audit Trail
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_USER_AND_PROFILE',
                'table_name' => 'users',
                'record_id' => $targetUser->user_id,
                'details' => json_encode([
                    'target_username' => $targetUser->username,
                    'target_employee' => $employee?->full_name,
                    'role' => $newRole,
                    'is_active' => $isActive,
                    'employee_no' => $employee?->employee_no,
                    'position' => $employee?->position,
                    'office_id' => $employee?->office_id,
                    'updated_by' => Auth::user()?->username ?? 'admin',
                ]),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);
        });

        return redirect()->route('admin.dashboard')->with(
            'success',
            "Account @{$targetUser->username} (" . ($targetUser->employee?->full_name ?? 'Personnel') . ") and profile details successfully updated!"
        );
    }

    /**
     * Backward-compatible alias for legacy role updates.
     */
    public function updateUserRole(Request $request, $id): RedirectResponse
    {
        return $this->updateUser($request, $id);
    }

    /**
     * Register a new municipal personnel and associated system user account.
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'employee_no' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]+$/', 'max:50', 'unique:employees,employee_no'],
            'position' => ['required', 'string', 'max:150'],
            'employment_type' => ['required', 'string', 'in:Permanent,Casual,Elected,Co-Terminous,Job Order'],
            'office_id' => ['required', 'integer', 'exists:offices,office_id'],
            'date_hired' => ['nullable', 'date'],
            'username' => ['required', 'string', 'max:60', 'unique:users,username', 'regex:/^[a-zA-Z0-9_.-]+$/'],
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'role' => ['required', 'string', 'in:admin,hrmu_staff,routing_clerk,employee'],
            'is_active' => ['nullable', 'boolean'],
            'vl_balance' => ['nullable', 'numeric', 'min:0', 'max:999.999'],
            'sl_balance' => ['nullable', 'numeric', 'min:0', 'max:999.999'],
            'fl_balance' => ['nullable', 'numeric', 'min:0', 'max:5.000'],
            'spl_balance' => ['nullable', 'numeric', 'min:0', 'max:3.000'],
            'wl_balance' => ['nullable', 'numeric', 'min:0', 'max:5.000'],
        ], [
            'employee_no.regex' => 'The employee control number may contain letters, numbers, and hyphens (e.g. HRMU-0001).',
        ]);

        $currentYear = Carbon::now()->year;

        $created = DB::transaction(function () use ($validated, $request, $currentYear) {
            $normalizedNo = strtoupper(trim($validated['employee_no']));
            if (Employee::where('employee_no', $normalizedNo)->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'employee_no' => 'This Employee ID is already assigned to another personnel.'
                ]);
            }

            // 1. Create Employee Record
            $employee = Employee::create([
                'employee_no' => $normalizedNo,
                'full_name' => trim($validated['full_name']),
                'position' => trim($validated['position']),
                'employment_type' => $validated['employment_type'],
                'office_id' => (int) $validated['office_id'],
                'date_hired' => !empty($validated['date_hired']) ? $validated['date_hired'] : null,
                'status' => 'active',
            ]);

            // 2. Create User Login Account
            $user = User::create([
                'employee_id' => $employee->employee_id,
                'username' => strtolower(trim($validated['username'])),
                'email' => !empty($validated['email']) ? strtolower(trim($validated['email'])) : null,
                'password_hash' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'is_active' => $request->boolean('is_active', true),
            ]);

            // 3. Seed Certified Baseline Leave Balances
            $leaveTypes = LeaveType::whereIn('code', ['VL', 'SL', 'FL', 'SPL', 'WL'])->get()->keyBy('code');

            // CSC Omnibus Rules: If Job Order personnel, leave credits do not accrue (0.000)
            $isJobOrder = strtolower(trim((string) $employee->employment_type)) === 'job order';

            $vlBalance = (float) ($validated['vl_balance'] ?? 0.000);
            $slBalance = (float) ($validated['sl_balance'] ?? 0.000);

            // CSC First-Month Service Accrual Formula:
            // If opening balance was left at 0 or empty, and date_hired is specified,
            // automatically calculate the exact fractional Table II credits earned from the first month of service.
            if (!$isJobOrder && $employee->date_hired && $vlBalance == 0.000 && $slBalance == 0.000) {
                $table2Calc = $employee->getStartingMonthTableIICredit();
                if ($table2Calc && isset($table2Calc['credit'])) {
                    $vlBalance = (float) $table2Calc['credit'];
                    $slBalance = (float) $table2Calc['credit'];
                }
            } elseif ($isJobOrder) {
                $vlBalance = 0.000;
                $slBalance = 0.000;
            }

            $flBalance = $isJobOrder ? 0.000 : (float) ($validated['fl_balance'] ?? 5.000);
            $splBalance = $isJobOrder ? 0.000 : (float) ($validated['spl_balance'] ?? 3.000);
            $wlBalance = $isJobOrder ? 0.000 : (float) ($validated['wl_balance'] ?? 5.000);

            $baselineMap = [
                'VL' => $vlBalance,
                'SL' => $slBalance,
                'FL' => $flBalance,
                'SPL' => $splBalance,
                'WL' => $wlBalance,
            ];

            foreach ($baselineMap as $code => $balance) {
                if (isset($leaveTypes[$code])) {
                    LeaveBalance::create([
                        'employee_id' => $employee->employee_id,
                        'leave_type_id' => $leaveTypes[$code]->leave_type_id,
                        'year' => $currentYear,
                        'balance' => $balance,
                        'last_posted_at' => Carbon::now(),
                    ]);
                }
            }

            // Post initial Leave Record (CS Form 41 ledger row) for the first month if eligible
            if (!$isJobOrder && ($vlBalance > 0 || $slBalance > 0)) {
                $hireDate = $employee->date_hired ? Carbon::parse($employee->date_hired) : Carbon::now();
                $periodFormatted = $hireDate->format('F Y');
                $accrualMonth = $hireDate->format('Y-m');
                $particular = $employee->date_hired 
                    ? "Earned (Table II: {$hireDate->format('M d, Y')})"
                    : "Certified Baseline Opening Balance";

                LeaveRecord::create([
                    'employee_id' => $employee->employee_id,
                    'period' => $periodFormatted,
                    'particular' => $particular,
                    'accrual_month' => $accrualMonth,
                    'is_prorated' => (bool) ($employee->date_hired && $hireDate->day > 1),
                    'vl_earned' => number_format($vlBalance, 3, '.', ''),
                    'vl_absence_with_pay' => null,
                    'vl_balance' => number_format($vlBalance, 3, '.', ''),
                    'vl_absence_without_pay' => null,
                    'sl_earned' => number_format($slBalance, 3, '.', ''),
                    'sl_absence_with_pay' => null,
                    'sl_balance' => number_format($slBalance, 3, '.', ''),
                    'sl_absence_without_pay' => null,
                    'date_action_taken' => $hireDate->format('n/j/Y'),
                    'row_order' => 1,
                    'encoded_by' => Auth::id() ?? 1,
                ]);
            }

            // 4. Log to Audit Trail
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'CREATE_USER_AND_EMPLOYEE',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'details' => json_encode([
                    'employee_no' => $employee->employee_no,
                    'full_name' => $employee->full_name,
                    'position' => $employee->position,
                    'employment_type' => $employee->employment_type,
                    'office_id' => $employee->office_id,
                    'username' => $user->username,
                    'role' => $user->role,
                    'opening_balances' => $baselineMap,
                    'created_by' => Auth::user()?->username ?? 'admin',
                ]),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);

            return [$employee, $user];
        });

        [$employee, $user] = $created;

        return redirect()->route('admin.dashboard')
            ->with(
                'success',
                "Personnel record for {$employee->full_name} ({$employee->employee_no}) and system user account @{$user->username} successfully registered!"
            );
    }

    /**
     * Administrator reset password for any user account.
     */
    public function resetUserPassword(Request $request, int $id): RedirectResponse
    {
        $user = User::with('employee')->findOrFail($id);

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:100', 'confirmed'],
        ], [
            'password.required' => 'Please provide a new password.',
            'password.min' => 'Password must be at least 6 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
        ]);

        $user->update([
            'password_hash' => Hash::make($validated['password']),
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'ADMIN_RESET_PASSWORD',
            'table_name' => 'users',
            'record_id' => $user->user_id,
            'details' => json_encode([
                'target_user_id' => $user->user_id,
                'target_username' => $user->username,
                'target_employee' => $user->employee?->full_name,
                'reset_by' => Auth::user()?->username ?? 'admin',
            ]),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', "Password for @{$user->username} ({$user->employee?->full_name}) has been reset successfully!");
    }

    /**
     * Delete a municipal user account with strict governance and safety checks.
     */
    public function destroyUser(Request $request, int $id): RedirectResponse
    {
        $targetUser = User::with('employee')->findOrFail($id);

        // Security Guard 1: Administrator cannot delete their own active account
        if ($targetUser->user_id === Auth::id()) {
            return back()->with('error', 'Security Guard: You cannot delete your own active Administrator account.');
        }

        // Security Guard 2: Prevent deleting the primary municipal system admin
        if ($targetUser->username === 'admin_hrmu') {
            return back()->with('error', 'Security Guard: The root system administrator account (@admin_hrmu) is protected from deletion.');
        }

        $username = $targetUser->username;
        $employeeName = $targetUser->employee?->full_name ?? 'Unassigned';
        $roleName = ucfirst(str_replace('_', ' ', $targetUser->role));

        DB::transaction(function () use ($targetUser, $username, $employeeName, $roleName, $request) {
            // Nullify or dissociate foreign key references where appropriate to protect historical records
            DB::table('audit_logs')->where('user_id', $targetUser->user_id)->update(['user_id' => null]);
            DB::table('routing_logs')->where('scanned_by', $targetUser->user_id)->update(['scanned_by' => null]);
            DB::table('transmittals')->where('prepared_by', $targetUser->user_id)->update(['prepared_by' => null]);
            DB::table('attendance_deductions')->where('encoded_by', $targetUser->user_id)->update(['encoded_by' => null]);
            DB::table('leave_records')->where('encoded_by', $targetUser->user_id)->update(['encoded_by' => null]);
            DB::table('leave_record_releases')->where('released_by', $targetUser->user_id)->update(['released_by' => null]);
            DB::table('starla_reference')->where('validated_by', $targetUser->user_id)->update(['validated_by' => null]);

            if (Schema::hasTable('leave_applications')) {
                DB::table('leave_applications')->where('certified_by_user_id', $targetUser->user_id)->update(['certified_by_user_id' => null]);
            }
            if (Schema::hasTable('leave_attachments')) {
                DB::table('leave_attachments')->where('verified_by_user_id', $targetUser->user_id)->update(['verified_by_user_id' => null]);
            }

            // Record audit log entry before deleting user
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'DELETE_USER_ACCOUNT',
                'table_name' => 'users',
                'record_id' => $targetUser->user_id,
                'details' => json_encode([
                    'deleted_username' => $username,
                    'deleted_employee' => $employeeName,
                    'deleted_role' => $roleName,
                    'deleted_by' => Auth::user()?->username ?? 'admin',
                    'timestamp' => Carbon::now()->toDateTimeString(),
                ]),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);

            // Delete user login record
            $targetUser->delete();
        });

        return redirect()->route('admin.dashboard')
            ->with('success', "User account @{$username} ({$employeeName}) has been permanently deleted from the system.");
    }
}
