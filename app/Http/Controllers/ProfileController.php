<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\Office;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * ProfileController
 *
 * Dedicated controller for municipal personnel profile and account settings:
 * - Viewing all personal, employee master, and system user details.
 * - Editing personal information (First Name, Middle Name, Last Name, Email, Position, Office, Date Hired).
 * - Changing password within the profile page.
 */
class ProfileController extends Controller
{
    /**
     * Display the employee profile and account settings page.
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $employee = $user->employee;

        // If user does not have an employee record yet, load or handle gracefully
        if (!$employee && $user->employee_id) {
            $employee = Employee::with('office')->find($user->employee_id);
        } elseif (!$employee) {
            $employee = new Employee();
            $employee->full_name = $user->username;
            $employee->position = ucfirst(str_replace('_', ' ', $user->role));
        } else {
            $employee->load('office');
        }

        $offices = Office::orderBy('office_name', 'asc')->get();

        return view('profile.show', [
            'user' => $user,
            'employee' => $employee,
            'offices' => $offices,
        ]);
    }

    /**
     * Update employee personal information and user account details.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $employee = $user->employee;

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($user->user_id, 'user_id')],
            'position' => ['required', 'string', 'max:150'],
            'office_id' => ['nullable', 'integer', 'exists:offices,office_id'],
            'date_hired' => ['nullable', 'date'],
        ], [
            'first_name.required' => 'Please enter your first name.',
            'last_name.required' => 'Please enter your last name.',
            'email.required' => 'An email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered to another account.',
            'position.required' => 'Please enter your municipal position / title.',
        ]);

        DB::transaction(function () use ($user, $employee, $validated, $request) {
            // 1. Update user account email
            $user->update([
                'email' => strtolower(trim($validated['email'])),
            ]);

            // 2. Update employee master record if linked
            if ($employee) {
                $firstName = trim($validated['first_name']);
                $middleName = !empty($validated['middle_name']) ? trim($validated['middle_name']) : null;
                $lastName = trim($validated['last_name']);

                $fullName = $middleName ? "{$firstName} {$middleName} {$lastName}" : "{$firstName} {$lastName}";

                $employeeData = [
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'full_name' => $fullName,
                    'position' => trim($validated['position']),
                ];

                if (!empty($validated['office_id'])) {
                    $employeeData['office_id'] = (int) $validated['office_id'];
                }

                if ($request->has('date_hired')) {
                    $employeeData['date_hired'] = !empty($validated['date_hired']) ? $validated['date_hired'] : null;
                }

                $employee->update($employeeData);
            }

            // 3. Record Audit Log
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'UPDATE_PERSONAL_PROFILE',
                'table_name' => 'employees',
                'record_id' => $employee?->employee_id ?? $user->user_id,
                'details' => json_encode([
                    'username' => $user->username,
                    'full_name' => $employee?->full_name,
                    'email' => $user->email,
                    'position' => $employee?->position,
                    'office_id' => $employee?->office_id,
                ]),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);
        });

        return redirect()->route('profile.show')->with('success', 'Your personal information and account details have been updated successfully!');
    }

    /**
     * Validate current password and matched new password, then generate and dispatch a 6-digit Security OTP
     * strictly to the employee's registered email address (never displaying the code in the UI response).
     */
    public function sendPasswordOtp(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        if (empty($user->email)) {
            return response()->json([
                'success' => false,
                'message' => 'No registered email found. Please update and save your official email address first.'
            ], 422);
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'max:100', 'confirmed'],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'new_password.required' => 'Please enter your new password.',
            'new_password.min' => 'The new password must be at least 6 characters.',
            'new_password.confirmed' => 'The new password and confirmation password do not match.',
        ]);

        // Verify current password matches before sending OTP
        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'The current password you entered is incorrect.'
            ], 422);
        }

        // Generate 6-digit numeric OTP
        $otp = sprintf('%06d', mt_rand(100000, 999999));
        $expiresAt = Carbon::now()->addMinutes(10);

        session([
            'password_otp' => $otp,
            'password_otp_expires' => $expiresAt->timestamp,
            'password_otp_sent_to' => $user->email,
        ]);

        // Dispatch email strictly to the registered email address
        $recipientEmail = $user->email;
        $recipientName = $user->employee?->full_name ?? $user->username;

        try {
            Mail::raw(
                "MUNICIPAL GOVERNMENT OF BASUD - HRMU\nCentralized Leave Application & Credit Tracking System (C-LACTS)\n\n" .
                "Hello {$recipientName},\n\n" .
                "A password change request was initiated for your Civil Service account (@{$user->username}).\n\n" .
                "Your 6-Digit Email Security Verification Code is: {$otp}\n\n" .
                "This code will expire in 10 minutes. If you did not request this password change, please notify the HRMU Administrator immediately.\n\n" .
                "Human Resource Management Unit (HRMU)\nMunicipality of Basud, Camarines Norte",
                function ($message) use ($recipientEmail, $recipientName) {
                    $message->to($recipientEmail, $recipientName)
                        ->subject('C-LACTS Security Code - Change Password Verification');
                }
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Password OTP email dispatch warning: " . $e->getMessage());
        }

        // Mask email for display in UI status notice (e.g. j***n@basud.gov.ph)
        $parts = explode('@', $user->email);
        $name = $parts[0];
        $domain = $parts[1] ?? 'basud.gov.ph';
        $maskedName = strlen($name) > 2 ? substr($name, 0, 1) . str_repeat('*', strlen($name) - 2) . substr($name, -1) : $name;
        $maskedEmail = "{$maskedName}@{$domain}";

        return response()->json([
            'success' => true,
            'message' => "Verification code has been sent to your registered email ({$maskedEmail}). Please check your inbox.",
            'masked_email' => $maskedEmail,
            'expires_in' => 600,
        ]);
    }

    /**
     * Update password from the profile page with Email OTP verification.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'otp_code' => ['required', 'string', 'size:6'],
            'new_password' => ['required', 'string', 'min:6', 'max:100', 'confirmed'],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'otp_code.required' => 'Please enter the 6-digit security code sent to your email.',
            'otp_code.size' => 'The security verification code must be exactly 6 digits.',
            'new_password.required' => 'Please enter a new password.',
            'new_password.min' => 'The new password must be at least 6 characters.',
            'new_password.confirmed' => 'The password confirmation does not match.',
        ]);

        // 1. Verify Current Password
        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            return redirect()->route('profile.show')->withErrors([
                'current_password' => 'The current password you entered is incorrect.',
            ])->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
        }

        // 2. Verify Email OTP Code
        $storedOtp = session('password_otp');
        $expiresTimestamp = session('password_otp_expires');

        if (!$storedOtp || !$expiresTimestamp || Carbon::now()->timestamp > $expiresTimestamp) {
            return redirect()->route('profile.show')->withErrors([
                'otp_code' => 'The security code has expired or was not requested. Please click "Send Security Code" to receive a fresh code.',
            ])->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
        }

        if (trim($validated['otp_code']) !== (string) $storedOtp) {
            return redirect()->route('profile.show')->withErrors([
                'otp_code' => 'Invalid security verification code. Please check your email inbox and enter the 6-digit code.',
            ])->withInput($request->except(['current_password', 'new_password', 'new_password_confirmation']));
        }

        // Clear verified OTP from session
        session()->forget(['password_otp', 'password_otp_expires', 'password_otp_sent_to']);

        // 3. Update User Password
        $user->update([
            'password_hash' => Hash::make($validated['new_password']),
        ]);

        // 4. Log High-Security Audit Event
        AuditLog::create([
            'user_id' => $user->user_id,
            'action' => 'USER_CHANGE_PASSWORD_WITH_EMAIL_OTP',
            'table_name' => 'users',
            'record_id' => $user->user_id,
            'details' => json_encode([
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'verified_method' => 'EMAIL_2FA_OTP',
                'ip' => $request->ip(),
            ]),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        return redirect()->route('profile.show')
            ->with('password_update_success', true)
            ->with('success', 'Your password has been changed successfully! Your account credentials were authenticated and verified via your registered email.');
    }
}
