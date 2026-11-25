<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureRole Middleware
 *
 * Enforces Role-Based Access Control (RBAC) across C-LACTS municipal modules:
 * 1. 'admin': System Administrator / HR Head (Full Clearance)
 * 2. 'hrmu_staff': HR Records Officer (Leave Ledger & Starla Engine)
 * 3. 'routing_clerk': Office Receptionist / Receiving Clerk (QR Custody Terminal)
 * 4. 'employee': Standard Municipal Personnel (Self-Service Dashboard & Tracking)
 */
class EnsureRole
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     * @return Response
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Require authentication first
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please log in to continue.',
                ], 401);
            }

            return redirect()->guest(route('login'))
                ->with('warning', 'Please sign in to access C-LACTS.');
        }

        $user = Auth::user();

        // System Administrator holds superuser clearance across all modules
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Special handling for QR Scanner clearance:
        // Accessible by clerk, hr staff, admin, department head, and mayor.
        if (in_array('scanner_authorized', $roles)) {
            if ($user->canAccessQrScanner()) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. The QR Document Scanner is restricted to receiving clerks, HR staff, department heads, and the Mayor.',
                ], 403);
            }

            return redirect()->route('employee.dashboard')->with(
                'error',
                'Access Restricted: The QR Document Scanner is designated exclusively for Receiving Clerks, HR Staff, Department Heads, and the Municipal Mayor.'
            );
        }

        // Verify if user's role is in the allowed whitelist
        if (!empty($roles) && !in_array($user->role, $roles)) {
            $formattedAllowed = implode(', ', array_map(fn($r) => ucfirst(str_replace('_', ' ', $r)), $roles));
            $userRoleTitle = ucfirst(str_replace('_', ' ', $user->role));

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => "Access denied. Required role: {$formattedAllowed}. Your role: {$userRoleTitle}.",
                ], 403);
            }

            // Redirect back to their primary home portal with an informative GovTech notice
            $redirectRoute = match ($user->role) {
                'routing_clerk' => route('clerk.scanner'),
                'hrmu_staff' => route('hr.tardiness.create'),
                default => route('employee.dashboard'),
            };

            return redirect($redirectRoute)->with(
                'error',
                "Access Restricted: Only authorized {$formattedAllowed} personnel can access that module. You are currently logged in as {$userRoleTitle}."
            );
        }

        return $next($request);
    }
}
