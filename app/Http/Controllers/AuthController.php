<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * AuthController
 *
 * Handles Civil Service employee and staff authentication for C-LACTS:
 * - Secure session-based authentication with SHA-256 password hashing.
 * - Automatic role-based dispatching upon login.
 * - Comprehensive GovTech audit logging for security events (USER_LOGIN, USER_LOGOUT).
 * - Fast demo account credentials helper for academic review & thesis defense.
 */
class AuthController extends Controller
{
    /**
     * Show the GovTech C-LACTS login interface.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'username.required' => 'Please enter your Employee ID or Email.',
        ]);

        $loginInput = trim($credentials['username']);

        // 1. Look up user(s) strictly by Email OR assigned Employee ID (e.g. HRMU-0001, MPDO-0003)
        $candidates = User::with('employee.office')
            ->where(function ($query) use ($loginInput) {
                $query->whereRaw('LOWER(email) = ?', [strtolower($loginInput)])
                    ->orWhereHas('employee', function ($empQuery) use ($loginInput) {
                        $empQuery->where('employee_no', strtoupper($loginInput))
                            ->orWhere('employee_no', $loginInput);
                    });
            })
            ->orderBy('user_id', 'asc')
            ->get();

        // Verify existence and active state
        if ($candidates->isEmpty()) {
            return back()->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => 'No municipal user account found with this Employee ID or Email.']);
        }

        $activeCandidates = $candidates->where('is_active', true);
        if ($activeCandidates->isEmpty()) {
            return back()->withInput($request->only('username', 'remember'))
                ->withErrors(['username' => 'This account has been deactivated. Please contact the HRMU Administrator.']);
        }

        // 2. Match password across active candidate(s) (handles legacy alias user rows sharing an employee_id)
        $remember = $request->boolean('remember');
        $user = $activeCandidates->first(function ($candidate) use ($credentials) {
            return Hash::check($credentials['password'], $candidate->password_hash);
        });

        if (!$user || !Auth::attempt(['username' => $user->username, 'password' => $credentials['password']], $remember)) {
            return back()->withInput($request->only('username', 'remember'))
                ->withErrors(['password' => 'Incorrect password entered.']);
        }

        // 3. Prevent session fixation
        $request->session()->regenerate();

        // 4. Log audit event
        AuditLog::create([
            'user_id' => $user->user_id,
            'action' => 'USER_LOGIN',
            'table_name' => 'users',
            'record_id' => $user->user_id,
            'details' => json_encode([
                'username' => $user->username,
                'role' => $user->role,
                'employee_name' => $user->employee?->full_name,
                'ip' => $request->ip(),
                'user_agent' => substr($request->userAgent() ?? '', 0, 255),
            ]),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        return $this->redirectBasedOnRole($user)->with(
            'success',
            "Welcome, {$user->employee?->first_name}! Logged in as " . ucfirst(str_replace('_', ' ', $user->role)) . "."
        );
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            AuditLog::create([
                'user_id' => $user->user_id,
                'action' => 'USER_LOGOUT',
                'table_name' => 'users',
                'record_id' => $user->user_id,
                'details' => json_encode([
                    'username' => $user->username,
                    'role' => $user->role,
                ]),
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'logged_at' => Carbon::now(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been securely logged out of C-LACTS.');
    }

    /**
     * Update authenticated user's own password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'max:100', 'confirmed'],
        ], [
            'current_password.required' => 'Please enter your current password.',
            'new_password.required' => 'Please enter a new password.',
            'new_password.min' => 'The new password must be at least 6 characters.',
            'new_password.confirmed' => 'The password confirmation does not match.',
        ]);

        if (!Hash::check($validated['current_password'], $user->password_hash)) {
            return back()->withErrors([
                'current_password' => 'The current password you entered is incorrect.',
            ])->with('password_modal_open', true);
        }

        $user->update([
            'password_hash' => Hash::make($validated['new_password']),
        ]);

        AuditLog::create([
            'user_id' => $user->user_id,
            'action' => 'USER_CHANGE_PASSWORD',
            'table_name' => 'users',
            'record_id' => $user->user_id,
            'details' => json_encode([
                'username' => $user->username,
                'role' => $user->role,
                'ip' => $request->ip(),
            ]),
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'logged_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Your password has been changed successfully!');
    }

    /**
     * Redirect authenticated users to their role-appropriate default landing portal.
     */
    protected function redirectBasedOnRole(User $user): RedirectResponse
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'routing_clerk' => redirect()->route('clerk.scanner'),
            'hrmu_staff' => redirect()->route('hr.tardiness.create'),
            default => redirect()->route('employee.dashboard'),
        };
    }
}
