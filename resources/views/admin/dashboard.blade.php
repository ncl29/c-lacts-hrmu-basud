@extends('layouts.app')

@section('title', 'System Administrator Governance Control Center')

@section('content')
<div class="space-y-6">

    <!-- Top Executive Banner -->
    <div class="bg-gov-navy rounded-xl p-4 sm:p-6 text-white shadow-xs border border-slate-800">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3 sm:gap-4 min-w-0">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-white text-xl sm:text-2xl shrink-0">
                    <i class="ph ph-shield-star"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs uppercase tracking-wider text-slate-400 font-semibold flex flex-wrap items-center gap-2">
                        <span>Level 4: System Administrator / HR Head</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-800 text-slate-300 border border-slate-700">
                            Configuration & Oversight
                        </span>
                    </div>
                    <h1 class="text-lg sm:text-2xl font-bold text-white mt-0.5 leading-tight">
                        Governance & Security Control Center
                    </h1>
                    <div class="text-xs text-slate-400 mt-1">
                        LGU Municipality of Basud • Civil Service Leave Administration Baseline
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs shrink-0">
                <div class="bg-slate-800 px-3 py-2 rounded-lg border border-slate-700 text-left sm:text-right w-full sm:w-auto">
                    <div class="text-[10px] text-slate-400 font-mono">Calendar Year</div>
                    <div class="text-xs font-mono font-bold text-white">{{ $currentYear }} Active</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Executive KPI Metrics Grid (2x2 Cardstock Appearance) -->
    <div class="grid grid-cols-2 gap-2.5 sm:gap-4">
        <!-- Metric 1: Municipal Personnel -->
        <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 border-t-4 border-t-gov-700 shadow-xs relative overflow-hidden flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest font-mono mb-1 truncate">Personnel Baseline</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tracking-tight tabular-nums">
                    {{ $totalEmployees }}
                </div>
            </div>
            <div class="text-[10px] sm:text-[11px] text-emerald-700 mt-2 sm:mt-3 pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-1">
                <span class="font-semibold">{{ $activeEmployees }} Active Regulars</span>
                <span class="text-slate-400 font-mono text-[10px]">~158 Baseline</span>
            </div>
        </div>

        <!-- Metric 2: Municipal Offices -->
        <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 border-t-4 border-t-emerald-600 shadow-xs relative overflow-hidden flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest font-mono mb-1 truncate">Routing Stations</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tracking-tight tabular-nums">
                    {{ $totalOffices }}
                </div>
            </div>
            <div class="text-[10px] sm:text-[11px] text-gov-700 mt-2 sm:mt-3 pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-1">
                <span class="font-semibold">14 Offices</span>
                <span class="text-slate-400 font-mono text-[10px]">LGU Basud</span>
            </div>
        </div>

        <!-- Metric 3: Leave Pipeline -->
        <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 border-t-4 border-t-amber-500 shadow-xs relative overflow-hidden flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest font-mono mb-1 truncate">Applications Processed</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tracking-tight tabular-nums">
                    {{ $totalApplications }}
                </div>
            </div>
            <div class="text-[10px] sm:text-[11px] text-slate-600 mt-2 sm:mt-3 pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-1">
                <span class="text-amber-700 font-semibold font-mono">{{ $pendingApplications }} In Transit</span>
                <span class="text-emerald-700 font-semibold font-mono">{{ $approvedApplications }} Approved</span>
            </div>
        </div>

        <!-- Metric 4: System User Accounts -->
        <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 border-t-4 border-t-purple-600 shadow-xs relative overflow-hidden flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest font-mono mb-1 truncate">RBAC User Accounts</div>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-mono tracking-tight tabular-nums">
                    {{ $totalUsers }}
                </div>
            </div>
            <div class="text-[10px] sm:text-[11px] text-slate-600 mt-2 sm:mt-3 pt-2 border-t border-slate-100 flex flex-wrap items-center justify-between gap-1">
                <span class="text-purple-700 font-semibold font-mono">{{ $usersByRole['admin'] ?? 0 }} Admin • {{ $usersByRole['hrmu_staff'] ?? 0 }} HR</span>
                <span class="text-slate-400 font-mono text-[10px]">{{ $usersByRole['routing_clerk'] ?? 0 }} Clerks</span>
            </div>
        </div>
    </div>

    <!-- Main Admin Sections (Tabs Navigation) -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        
        <!-- Tab Bar -->
        <div class="bg-slate-50 border-b border-slate-200 px-3 sm:px-6 pt-3 sm:pt-4 flex flex-nowrap overflow-x-auto gap-1.5 sm:gap-2 text-xs sm:text-sm font-semibold scrollbar-none">
            <button type="button" onclick="switchAdminTab('users')" id="tabBtnUsers" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-t-lg transition border-b-2 border-gov-700 text-gov-700 bg-white font-bold flex items-center gap-1.5 sm:gap-2 cursor-pointer shrink-0">
                <i class="ph ph-users-three text-base sm:text-lg"></i>
                <span>Users & RBAC ({{ $totalUsers }})</span>
            </button>
            <button type="button" onclick="switchAdminTab('starla')" id="tabBtnStarla" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-t-lg transition border-b-2 border-transparent text-slate-600 hover:text-slate-900 flex items-center gap-1.5 sm:gap-2 cursor-pointer shrink-0">
                <i class="ph ph-calculator text-base sm:text-lg"></i>
                <span>Starla Matrices ({{ $starlaTotal }})</span>
            </button>
            <button type="button" onclick="switchAdminTab('audit')" id="tabBtnAudit" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-t-lg transition border-b-2 border-transparent text-slate-600 hover:text-slate-900 flex items-center gap-1.5 sm:gap-2 cursor-pointer shrink-0">
                <i class="ph ph-shield-check text-base sm:text-lg"></i>
                <span>Audit Trail</span>
            </button>
            <button type="button" onclick="switchAdminTab('backups')" id="tabBtnBackups" class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-t-lg transition border-b-2 border-transparent text-slate-600 hover:text-slate-900 flex items-center gap-1.5 sm:gap-2 cursor-pointer shrink-0">
                <i class="ph ph-archive text-base sm:text-lg"></i>
                <span>Backups ({{ count($backups) }})</span>
            </button>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 1: USER ACCOUNTS & RBAC MANAGEMENT                            -->
        <!-- ================================================================= -->
        <div id="tabContentUsers" class="p-4 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="ph ph-users-three text-gov-700"></i>
                        <span>Municipal Personnel User Directory & RBAC Tiers</span>
                    </h3>
                    <p class="text-xs text-slate-500">
                        Configure municipal employee profiles, assign administrative roles, and initialize certified leave balances.
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button 
                        type="button" 
                        onclick="openCreateUserModal()" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-gov-700 hover:bg-gov-800 transition flex items-center gap-2 shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-gov-600 w-full sm:w-auto justify-center min-h-[40px]"
                    >
                        <i class="ph ph-user-plus text-base"></i>
                        <span>Add Personnel & User</span>
                    </button>
                </div>
            </div>

            <!-- Users Table -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-3 sm:px-4 py-3 text-left">Civil Service User</th>
                            <th class="px-3 sm:px-4 py-3 text-left">Employee ID</th>
                            <th class="px-3 sm:px-4 py-3 text-left">Employee Name & Position</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Assigned Station</th>
                            <th class="px-3 sm:px-4 py-3 text-center">RBAC Role</th>
                            <th class="px-4 py-3 text-center hidden sm:table-cell">Account Status</th>
                            <th class="px-3 sm:px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-3 sm:px-4 py-3 font-mono font-bold text-gov-900">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600 font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($u->username, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="truncate max-w-[100px] sm:max-w-none block">{{ $u->username }}</span>
                                            @if(!empty($u->email))
                                                <span class="text-[10px] text-slate-400 font-sans block truncate max-w-[140px]">{{ $u->email }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 sm:px-4 py-3 font-mono">
                                    <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold tracking-wide bg-slate-100 text-slate-900 border border-slate-200 shadow-2xs">
                                        {{ $u->employee->employee_no ?? 'ID: ' . $u->employee_id }}
                                    </span>
                                </td>
                                <td class="px-3 sm:px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $u->employee->full_name ?? 'Unassigned' }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $u->employee->position ?? 'N/A' }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 md:hidden mt-0.5">
                                        {{ $u->employee->office->office_name ?? 'Municipal Office' }}
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-700 hidden md:table-cell">
                                    {{ $u->employee->office->office_name ?? 'Municipal Office' }}
                                </td>
                                <td class="px-3 sm:px-4 py-3 text-center">
                                    @if($u->role === 'admin')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            Administrator
                                        </span>
                                    @elseif($u->role === 'hrmu_staff')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            HR Staff
                                        </span>
                                    @elseif($u->role === 'routing_clerk')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            Clerk
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                            Employee
                                        </span>
                                    @endif
                                    <div class="sm:hidden text-[10px] mt-0.5">
                                        @if($u->is_active)
                                            <span class="text-emerald-700 font-semibold">• Active</span>
                                        @else
                                            <span class="text-rose-700 font-semibold">• Deactivated</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center hidden sm:table-cell">
                                    @if($u->is_active)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Deactivated
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-4 py-3 text-right">
                                    <button 
                                        type="button" 
                                        onclick="openEditRoleModal({{ $u->user_id }}, '{{ $u->username }}', '{{ addslashes($u->employee->full_name ?? '') }}', '{{ $u->role }}', {{ $u->is_active ? 1 : 0 }}, '{{ $u->employee->employee_no ?? '' }}', '{{ addslashes($u->employee->position ?? '') }}', {{ $u->employee->office_id ?? 'null' }}, '{{ $u->employee->employment_type ?? 'Permanent' }}', '{{ $u->employee->status ?? 'active' }}', '{{ $u->employee->date_hired ? $u->employee->date_hired->format('Y-m-d') : '' }}', '{{ addslashes($u->email ?? '') }}')"
                                        class="px-2.5 py-1.5 text-xs font-semibold text-gov-700 hover:text-gov-900 bg-slate-50 hover:bg-slate-100 rounded-lg transition border border-slate-300 cursor-pointer min-h-[32px] inline-flex items-center gap-1 shadow-2xs"
                                        title="Edit user credentials and personnel profile"
                                    >
                                        <i class="ph-bold ph-pencil-simple text-xs"></i>
                                        <span>Edit</span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 2: STARLA CONVERSION REFERENCE MATRICES (TABLES II, III, IV)   -->
        <!-- ================================================================= -->
        <div id="tabContentStarla" class="hidden p-4 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="ph-bold ph-table text-gov-700"></i>
                        <span>Civil Service "Starla" Fractional Conversion Rules</span>
                    </h3>
                    <p class="text-xs text-slate-500">
                        Official conversion matrices codified under Civil Service Commission (CSC) Omnibus Leave Regulations.
                    </p>
                </div>

                <!-- Table Switcher Buttons -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <a href="?table_ref=Table+IV" class="px-3 py-1.5 rounded-lg border font-semibold transition {{ $selectedTable === 'Table IV' ? 'bg-gov-700 text-white border-gov-700' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' }}">
                        Table IV: Hours & Minutes ({{ $starlaCountTable4 }})
                    </a>
                    <a href="?table_ref=Table+II" class="px-3 py-1.5 rounded-lg border font-semibold transition {{ $selectedTable === 'Table II' ? 'bg-gov-700 text-white border-gov-700' : 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' }}">
                        Table II: Daily Earnings ({{ $starlaCountTable2 }})
                    </a>
                    <a href="{{ route('hr.starla.tables') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-slate-700 font-semibold hover:bg-slate-50 transition flex items-center gap-1.5 shadow-2xs">
                        <i class="ph ph-table text-slate-600"></i>
                        <span>Full Starla Viewer</span>
                    </a>
                </div>
            </div>

            <!-- Table Summary Description Callout -->
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 leading-relaxed">
                @if($selectedTable === 'Table IV')
                    <strong>CSC Table IV (Conversion of Working Hours/Minutes into Fractions of a Day):</strong> Used by the Starla Computation Service to calculate discrete semi-monthly tardiness and undertime deductions. (1 Hour = 0.125 day, 8 Hours = 1.000 day).
                @else
                    <strong>CSC Table II (Daily Accrual Equivalents):</strong> Standard earnings of 1.250 days/month pro-rated across 1 to 30 days of service (15 days of service = 0.625 day VL & SL).
                @endif
            </div>

            <!-- Starla Table Records -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-3 sm:px-4 py-3 text-left hidden sm:table-cell">Record ID</th>
                            <th class="px-3 sm:px-4 py-3 text-left">Table Reference</th>
                            <th class="px-3 sm:px-4 py-3 text-left">Unit / Input Value</th>
                            <th class="px-3 sm:px-4 py-3 text-right">Decimal Day Equiv</th>
                            <th class="px-3 sm:px-4 py-3 text-center hidden sm:table-cell">Calibration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($starlaRecords as $rec)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-3 sm:px-4 py-2.5 font-mono text-slate-400 hidden sm:table-cell">#{{ $rec->starla_id }}</td>
                                <td class="px-3 sm:px-4 py-2.5 font-bold text-gov-900">{{ $rec->table_ref }}</td>
                                <td class="px-3 sm:px-4 py-2.5 font-medium text-slate-800">{{ $rec->input_value }}</td>
                                <td class="px-3 sm:px-4 py-2.5 font-mono font-extrabold text-gov-800 text-right text-xs sm:text-sm">
                                    {{ number_format($rec->day_equivalent, 3) }}
                                </td>
                                <td class="px-3 sm:px-4 py-2.5 text-center hidden sm:table-cell">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        <i class="ph-bold ph-check"></i>
                                        CSC Certified
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-400 text-xs">No records found for {{ $selectedTable }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="pt-2">
                {{ $starlaRecords->links() }}
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 3: AUDIT TRAIL                                                -->
        <!-- ================================================================= -->
        <div id="tabContentAudit" class="hidden p-4 sm:p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="ph ph-shield-check text-gov-700"></i>
                        <span>Audit Trail</span>
                    </h3>
                    <p class="text-xs text-slate-500">
                        System event logging for authentication, leave generation, routing custody transfers, and Starla deductions.
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-300 text-xs text-slate-700 font-mono">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Audit Trail Active</span>
                </div>
            </div>

            <!-- Audit Logs Table -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-3 sm:px-4 py-3 text-left">Timestamp</th>
                            <th class="px-3 sm:px-4 py-3 text-left">Action Event</th>
                            <th class="px-3 sm:px-4 py-3 text-left">Actor / User</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Target Entity</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Security Details</th>
                            <th class="px-4 py-3 text-right hidden sm:table-cell">Client IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white font-mono text-[11px]">
                        @forelse($recentAuditLogs as $log)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-3 sm:px-4 py-2.5 text-slate-500 whitespace-nowrap text-[10px] sm:text-[11px]">
                                    {{ $log->logged_at ? $log->logged_at->format('M d, Y h:i A') : 'N/A' }}
                                </td>
                                <td class="px-3 sm:px-4 py-2.5 whitespace-nowrap">
                                    @if(str_contains($log->action, 'LOGIN'))
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-800">
                                            {{ $log->action }}
                                        </span>
                                    @elseif(str_contains($log->action, 'LOGOUT'))
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-slate-100 text-slate-700">
                                            {{ $log->action }}
                                        </span>
                                    @elseif(str_contains($log->action, 'ROUTING'))
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-amber-100 text-amber-800">
                                            {{ $log->action }}
                                        </span>
                                    @elseif(str_contains($log->action, 'ROLE'))
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-slate-100 text-slate-800">
                                            {{ $log->action }}
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-gov-100 text-gov-800">
                                            {{ $log->action }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 sm:px-4 py-2.5 font-sans font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $log->user->username ?? 'System / Anonymous' }}
                                    @if($log->user && $log->user->employee)
                                        <span class="block text-[10px] text-slate-400 font-mono">{{ $log->user->employee->full_name }}</span>
                                    @endif
                                    <span class="block text-[10px] text-slate-400 md:hidden font-mono mt-0.5">{{ $log->table_name }} #{{ $log->record_id }}</span>
                                </td>
                                <td class="px-4 py-2.5 text-slate-600 whitespace-nowrap hidden md:table-cell">
                                    {{ $log->table_name }} #{{ $log->record_id }}
                                </td>
                                <td class="px-4 py-2.5 text-slate-600 max-w-xs truncate hidden lg:table-cell" title="{{ $log->details }}">
                                    {{ $log->details }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-slate-500 whitespace-nowrap hidden sm:table-cell">
                                    {{ $log->ip_address }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-6 text-center text-slate-400 font-sans text-xs">
                                    No audit entries recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Audit Pagination -->
            <div class="pt-2">
                {{ $recentAuditLogs->links() }}
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 4: SYSTEM BACKUPS & DISASTER RECOVERY                         -->
        <!-- ================================================================= -->
        <div id="tabContentBackups" class="hidden p-4 sm:p-6 space-y-6">
            <!-- Header & Action -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="ph ph-archive text-gov-700"></i>
                        <span>Disaster Recovery & System Archives</span>
                    </h3>
                    <p class="text-xs text-slate-500">
                        Generate on-demand database snapshots, full document archives, and manage weekly automated backups for Civil Service leave records.
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button 
                        type="button" 
                        onclick="openCreateBackupModal()" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-gov-700 hover:bg-gov-800 transition flex items-center gap-2 shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-gov-600 w-full sm:w-auto justify-center min-h-[40px]"
                    >
                        <i class="ph ph-plus-circle text-base"></i>
                        <span>Create Backup Now</span>
                    </button>
                </div>
            </div>

            <!-- Backup Status & Storage KPI Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">Available Archives</div>
                    <div class="text-2xl font-black text-slate-900 mt-0.5 font-mono">{{ count($backups) }}</div>
                    <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                        <i class="ph ph-folder text-slate-400"></i>
                        <span>storage/app/backups</span>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">Total Backup Storage</div>
                    <div class="text-2xl font-black text-slate-900 mt-0.5 font-mono">{{ $totalBackupSizeFormatted }}</div>
                    <div class="text-[11px] text-emerald-700 mt-1 flex items-center gap-1 font-semibold">
                        <i class="ph ph-hard-drive"></i>
                        <span>Compressed & Verified</span>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">Last Generated</div>
                    <div class="text-sm font-bold text-slate-900 mt-1.5 truncate">{{ $lastBackupDate }}</div>
                    <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                        <i class="ph ph-clock text-slate-400"></i>
                        <span>Timestamped snapshot</span>
                    </div>
                </div>

                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-200">
                    <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">Weekly Automation</div>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <i class="ph ph-check-circle"></i>
                            <span>Active</span>
                        </span>
                        <span class="text-xs font-bold text-slate-800">Every Sunday</span>
                    </div>
                    <div class="text-[11px] text-slate-500 mt-1">
                        <span>01:00 AM • Auto-retain 4 weeks</span>
                    </div>
                </div>
            </div>

            <!-- Backups Table -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="min-w-full divide-y divide-slate-200 text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="px-4 py-3 text-left">Archive Filename & Type</th>
                            <th class="px-4 py-3 text-left">Date Created</th>
                            <th class="px-4 py-3 text-left">Size</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">SHA-256 Checksum</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($backups as $b)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $b['type'] === 'Full System Archive' ? 'bg-purple-100 text-purple-700' : 'bg-emerald-100 text-emerald-700' }}">
                                            <i class="ph {{ $b['icon'] }} text-base"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-mono font-bold text-slate-900 text-xs truncate max-w-[280px]">
                                                {{ $b['filename'] }}
                                            </div>
                                            <div class="flex items-center gap-1.5 mt-0.5">
                                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold {{ $b['type'] === 'Full System Archive' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                                    {{ $b['type'] }}
                                                </span>
                                                <span class="text-[10px] font-mono text-slate-400 uppercase">{{ $b['format'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-700 font-medium whitespace-nowrap">
                                    {{ $b['created_at'] }}
                                </td>
                                <td class="px-4 py-3 font-mono font-semibold text-slate-800 whitespace-nowrap">
                                    {{ $b['size_formatted'] }}
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-[11px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 max-w-[160px] truncate" title="{{ $b['sha256'] }}">
                                            {{ substr($b['sha256'], 0, 16) }}...
                                        </span>
                                        <button 
                                            type="button" 
                                            onclick="copyChecksum('{{ $b['sha256'] }}')" 
                                            class="text-slate-400 hover:text-slate-700 p-1 rounded transition cursor-pointer" 
                                            title="Copy full SHA-256 hash"
                                        >
                                            <i class="ph ph-copy text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a 
                                            href="{{ route('admin.backups.download', $b['filename']) }}" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-gov-700 hover:text-gov-800 bg-gov-50 hover:bg-gov-100 border border-gov-200 rounded-lg transition"
                                            title="Download archive to local PC or USB drive"
                                        >
                                            <i class="ph ph-download-simple text-sm"></i>
                                            <span>Download</span>
                                        </a>
                                        <button 
                                            type="button" 
                                            onclick="openDeleteBackupModal('{{ $b['filename'] }}')" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-rose-700 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition cursor-pointer"
                                            title="Delete backup archive"
                                        >
                                            <i class="ph ph-trash text-sm"></i>
                                            <span>Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <i class="ph ph-archive text-4xl mb-2 text-slate-300"></i>
                                        <div class="text-sm font-bold text-slate-600">No backup archives available</div>
                                        <p class="text-xs text-slate-400 mt-0.5">Click "Create Backup Now" above to generate your first system snapshot.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Scheduled Maintenance Policy Info Banner -->
            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-start gap-2.5">
                    <i class="ph ph-info text-gov-700 text-lg shrink-0 mt-0.5"></i>
                    <div>
                        <strong class="text-slate-800 block font-bold">Automatic Municipal Backup Policy</strong>
                        <span class="text-slate-600">
                            The system background worker automatically executes <code class="font-mono bg-slate-200 px-1 py-0.5 rounded text-[11px]">backup:run --type=db --retention=4</code> every Sunday at 01:00 AM. Archives older than 4 weeks are safely pruned to preserve municipal storage.
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ======================================================================= -->
<!-- MODAL: RESET USER PASSWORD (ADMIN OVERRIDE)                             -->
<!-- ======================================================================= -->
<div id="resetPasswordModal" onclick="if(event.target === this) closeResetPasswordModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-md w-full max-h-[92vh] sm:max-h-[88vh] shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col my-auto" onclick="event.stopPropagation()">
        <div class="bg-gov-navy text-white px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-key text-lg sm:text-xl text-amber-400"></i>
                <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider">Reset User Password</h3>
            </div>
            <button type="button" onclick="closeResetPasswordModal()" class="text-slate-400 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-gov-600 rounded-lg p-1 cursor-pointer" aria-label="Close modal">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form id="resetPasswordForm" method="POST" action="" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                <!-- User Details -->
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gov-700 text-white flex items-center justify-center font-bold text-xs shrink-0">
                        <i class="ph ph-user text-base"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 text-sm" id="resetModalEmployeeName">—</div>
                        <div class="text-xs font-mono text-slate-500" id="resetModalUsername">@username</div>
                    </div>
                </div>

                <div>
                    <label for="admin_reset_new_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                        New Password <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="admin_reset_new_password" 
                        name="password" 
                        required 
                        minlength="6"
                        value="password123"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                    >
                    <p class="text-[11px] text-slate-500 mt-1 font-mono">Suggested default: password123</p>
                </div>

                <div>
                    <label for="admin_reset_new_password_confirm" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                        Confirm New Password <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="admin_reset_new_password_confirm" 
                        name="password_confirmation" 
                        required 
                        minlength="6"
                        value="password123"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                    >
                </div>
            </div>

            <div class="bg-slate-50 px-5 sm:px-6 py-3.5 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 shrink-0">
                <button type="button" onclick="closeResetPasswordModal()" class="w-full sm:w-auto px-4 py-2.5 border border-slate-300 rounded-lg text-xs font-medium text-slate-700 hover:bg-slate-100 transition cursor-pointer min-h-[40px]">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold shadow-xs transition cursor-pointer min-h-[40px] flex items-center justify-center gap-1.5">
                    <i class="ph-bold ph-key text-base"></i>
                    <span>Apply New Password</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: DELETE USER ACCOUNT (CONFIRMATION ALERTDIALOG)                   -->
<!-- ======================================================================= -->
<div id="deleteUserModal" onclick="if(event.target === this) closeDeleteUserModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-md w-full max-h-[92vh] sm:max-h-[88vh] shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col my-auto" onclick="event.stopPropagation()">
        <div class="bg-gov-navy text-white px-5 sm:px-6 py-4 flex items-center justify-between border-b border-slate-800 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-rose-500/15 border border-rose-400/40 text-rose-400 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-warning text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Confirm User Deletion</h3>
                    <p class="text-xs text-rose-300 font-mono">Irreversible Administrative Action</p>
                </div>
            </div>
            <button type="button" onclick="closeDeleteUserModal()" class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form id="deleteUserForm" method="POST" action="" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            @method('DELETE')

            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                <div class="p-3.5 bg-rose-50 border border-rose-200 rounded-xl space-y-2 text-xs text-rose-900">
                    <div class="flex items-start gap-2.5">
                        <i class="ph-bold ph-shield-warning text-lg text-rose-600 shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-bold">Are you sure you want to permanently delete this user account?</p>
                            <p class="text-rose-700 mt-1">This will revoke terminal login access for:</p>
                        </div>
                    </div>
                    <div class="p-2.5 bg-white rounded-lg border border-rose-200 font-mono text-xs text-slate-800 space-y-1 mt-2">
                        <div>Personnel: <strong id="deleteModalEmployeeName" class="text-slate-900">—</strong></div>
                        <div>Username: <strong id="deleteModalUsername" class="text-rose-700">@username</strong></div>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">Historical attendance records, submitted leave applications, and audit entries will be safely preserved with unassigned author references.</p>
                </div>
            </div>

            <div class="bg-slate-50 px-5 sm:px-6 py-3.5 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 shrink-0">
                <button type="button" onclick="closeDeleteUserModal()" class="w-full sm:w-auto px-4 py-2.5 border border-slate-300 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-100 transition cursor-pointer min-h-[40px]">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition cursor-pointer min-h-[40px] flex items-center justify-center gap-1.5">
                    <i class="ph-bold ph-trash text-base"></i>
                    <span>Delete User Account</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: EDIT USER & EMPLOYEE PROFILE (ADMIN CONTROL)                     -->
<!-- ======================================================================= -->
<div id="roleModal" onclick="if(event.target === this) closeEditRoleModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-xl w-full max-h-[92vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden transform transition-all my-auto" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="bg-gov-navy text-white px-5 sm:px-6 py-4 border-b border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 text-amber-400 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-user-gear text-xl"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-white tracking-wide">Manage User & Personnel Profile</h3>
                    <p class="text-xs text-slate-300 font-mono">Administrative Authority & Record Modification</p>
                </div>
            </div>
            <button type="button" onclick="closeEditRoleModal()" class="text-slate-400 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-amber-400 rounded-lg p-1.5 cursor-pointer" aria-label="Close modal">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form id="editRoleForm" method="POST" action="" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-5 overflow-y-auto flex-1 min-h-0">

            <!-- Section 1: User Account & Authentication -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-1.5 font-mono">
                    <i class="ph ph-key text-gov-700 text-sm"></i>
                    <span>1. Civil Service Login & RBAC Clearance</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="edit_username" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                            Username <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="edit_username" 
                            name="username" 
                            required 
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm font-mono text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                        >
                    </div>

                    <div>
                        <label for="edit_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="edit_email" 
                            name="email" 
                            required
                            placeholder="e.g. user@basud.gov.ph"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                        >
                    </div>

                    <div>
                        <label for="modalRoleSelect" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                            RBAC Role Clearance <span class="text-rose-500">*</span>
                        </label>
                        <select id="modalRoleSelect" name="role" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 font-semibold focus:ring-2 focus:ring-gov-600 bg-white">
                            <option value="admin">System Administrator / HR Head (Level 4)</option>
                            <option value="hrmu_staff">HR Records Officer / Staff (Level 3)</option>
                            <option value="routing_clerk">Routing / Receiving Clerk (Level 2)</option>
                            <option value="employee">Standard Municipal Employee (Level 1)</option>
                        </select>
                    </div>

                    <div class="flex flex-col justify-end">
                        <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-between">
                            <div>
                                <div class="text-xs font-bold text-slate-800">Login Authorization</div>
                                <div class="text-[11px] text-slate-500">Allow this user to sign in</div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="modalIsActive" name="is_active" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-gov-600 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Employee Master Record Details -->
            <div class="space-y-3.5 pt-2">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-1.5 font-mono">
                    <i class="ph ph-identification-card text-gov-700 text-sm"></i>
                    <span>2. Personnel Master Details</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="edit_full_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Full Name (Last, First M.I.) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="edit_full_name" 
                            name="full_name" 
                            required 
                            placeholder="e.g. Mendoza, Juanita R."
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                        >
                    </div>

                    <div>
                        <label for="edit_employee_no" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1 font-mono">
                            Employee ID (DEPT-0001) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="edit_employee_no" 
                            name="employee_no" 
                            required 
                            placeholder="e.g. HRMU-0001"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm font-mono text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white uppercase"
                        >
                    </div>

                    <div>
                        <label for="edit_office_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Assigned Office <span class="text-rose-500">*</span>
                        </label>
                        <select id="edit_office_id" name="office_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white cursor-pointer">
                            @foreach($offices as $off)
                                <option value="{{ $off->office_id }}">{{ $off->office_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit_position" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Position / Title <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="edit_position" 
                            name="position" 
                            required 
                            placeholder="e.g. Administrative Officer IV"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                        >
                    </div>

                    <div>
                        <label for="edit_employment_type" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Classification <span class="text-rose-500">*</span>
                        </label>
                        <select id="edit_employment_type" name="employment_type" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white cursor-pointer">
                            <option value="Permanent">Permanent</option>
                            <option value="Casual">Casual</option>
                            <option value="Elected">Elected</option>
                            <option value="Co-Terminous">Co-Terminous</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                    </div>

                    <div>
                        <label for="edit_employee_status" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Personnel Employment Status
                        </label>
                        <select id="edit_employee_status" name="employee_status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white cursor-pointer">
                            <option value="active">Active Service</option>
                            <option value="inactive">Inactive / On Leave</option>
                            <option value="retired">Retired</option>
                            <option value="transferred">Transferred</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="edit_date_hired" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Date of Employment / Hire Date
                        </label>
                        <input 
                            type="date" 
                            id="edit_date_hired" 
                            name="date_hired" 
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white cursor-pointer"
                        >
                    </div>
                </div>

                <div class="pt-1.5">
                    <button 
                        type="button" 
                        id="btnTriggerResetPasswordFromEdit"
                        onclick="closeEditRoleModal(); openResetPasswordModal(currentEditUserId, currentEditUsername, currentEditEmployeeName);"
                        class="w-full px-3 py-2.5 border border-amber-300 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
                    >
                        <i class="ph-bold ph-key text-sm"></i>
                        <span>Reset User Password</span>
                    </button>
                </div>
                </div>
            </div>

            <!-- Modal Action Buttons (Pinned) -->
            <div class="bg-slate-50 px-5 sm:px-6 py-3.5 border-t border-slate-200 flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-2.5 sm:gap-3 shrink-0">
                <button 
                    type="button" 
                    id="btnDeleteFromEditModal"
                    onclick="closeEditRoleModal(); openDeleteUserModal(currentEditUserId, currentEditUsername, currentEditEmployeeName);"
                    class="w-full sm:w-auto px-3.5 py-2.5 border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 hover:text-rose-800 rounded-lg text-xs font-bold transition flex items-center justify-center gap-1.5 cursor-pointer min-h-[40px]"
                >
                    <i class="ph-bold ph-trash text-sm"></i>
                    <span>Delete User</span>
                </button>

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                    <button type="button" onclick="closeEditRoleModal()" class="w-full sm:w-auto px-4 py-2.5 border border-slate-300 rounded-lg text-xs font-medium text-slate-700 hover:bg-slate-100 transition cursor-pointer min-h-[40px]">
                        Cancel
                    </button>
                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-gov-700 hover:bg-gov-800 text-white rounded-lg text-xs font-bold shadow-xs transition cursor-pointer min-h-[40px] flex items-center justify-center gap-1.5">
                        <i class="ph-bold ph-check text-base"></i>
                        <span>Save Personnel & User Updates</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Add Personnel & User Account -->
<div id="createUserModal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-200 overflow-hidden my-auto" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="bg-gov-navy text-white px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5 sm:gap-3">
                <div class="w-8 sm:w-9 h-8 sm:h-9 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-white text-base sm:text-lg shrink-0">
                    <i class="ph ph-user-plus"></i>
                </div>
                <div>
                    <h3 class="font-bold text-sm sm:text-base leading-tight">Register Municipal Personnel & User Account</h3>
                    <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">Civil Service employee master record and system RBAC credentials</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateUserModal()" class="text-slate-400 hover:text-white p-1 rounded-lg focus:outline-none focus:ring-2 focus:ring-gov-600 cursor-pointer" aria-label="Close modal">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.users.store') }}" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <div class="p-4 sm:p-6 space-y-4 sm:space-y-5 overflow-y-auto flex-1 min-h-0">

            <!-- Section 1: Employee Biodata -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-1.5">
                    <i class="ph ph-identification-card text-gov-700 text-sm"></i>
                    <span>1. Employee Master Details</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="create_full_name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Full Name (Last, First M.I.) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="create_full_name" 
                            name="full_name" 
                            required 
                            placeholder="e.g. Juanita R. Mendoza"
                            value="{{ old('full_name') }}"
                            class="w-full border @error('full_name') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                        >
                        @error('full_name')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="create_employee_no" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Employee ID (DEPT-0001) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="create_employee_no" 
                            name="employee_no" 
                            required 
                            placeholder="e.g. HRMU-0001"
                            value="{{ old('employee_no') }}"
                            oninput="this.value = this.value.toUpperCase()"
                            class="w-full border @error('employee_no') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-lg px-3 py-2 text-sm font-mono text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white uppercase"
                        >
                        @error('employee_no')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Row 2: Station Office, Position, Classification, Start Date (Clean 2x2 Grid) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="create_office_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Municipal Office <span class="text-rose-500">*</span>
                        </label>
                        <select id="create_office_id" name="office_id" required onchange="onOfficeChangeSuggestId(this, 'create_employee_no')" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white cursor-pointer">
                            <option value="" disabled selected>— Select Office —</option>
                            @foreach($offices as $off)
                                <option value="{{ $off->office_id }}">{{ $off->office_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="create_position" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Position / Title <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="create_position" 
                            name="position" 
                            required 
                            placeholder="e.g. Admin Aide IV"
                            class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                        >
                    </div>

                    <div>
                        <label for="create_employment_type" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Classification <span class="text-rose-500">*</span>
                        </label>
                        <select id="create_employment_type" name="employment_type" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white cursor-pointer">
                            <option value="Permanent" selected>Permanent</option>
                            <option value="Casual">Casual</option>
                            <option value="Elected">Elected</option>
                            <option value="Co-Terminous">Co-Terminous</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                    </div>

                    <div>
                        <label for="create_date_hired" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Date of Employment / Start Date
                        </label>
                        <input 
                            type="date" 
                            id="create_date_hired" 
                            name="date_hired" 
                            value="{{ old('date_hired') }}"
                            onchange="calculateStarlaTableIIPreview(this.value)"
                            class="w-full border @error('date_hired') border-rose-500 @else border-slate-300 @enderror rounded-lg px-3 py-2 text-xs text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white cursor-pointer"
                        >
                        @error('date_hired')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Starla Table II Accrual Calculation Info Card (Full Width) -->
                <div id="starlaTable2PreviewCard" class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl space-y-2.5 transition">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-gov-100 text-gov-700 flex items-center justify-center shrink-0">
                                <i class="ph-bold ph-table text-sm"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-800">CSC Starla Table II: Daily Service Accrual</h5>
                                <p class="text-[10px] text-slate-500 font-mono">Partial month service calculation (CSC MC No. 41, s. 1998)</p>
                            </div>
                        </div>
                        <span id="starlaDailyPreviewBadge" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gov-50 text-gov-800 font-mono font-bold text-xs border border-gov-200 w-fit">
                            <span class="w-1.5 h-1.5 rounded-full bg-gov-600"></span>
                            <span>Standard: 30 days = 1.250 VL & SL</span>
                        </span>
                    </div>

                    <div class="p-2.5 rounded-lg bg-white border border-slate-200 text-[11px] text-slate-600 leading-relaxed flex items-start gap-2" id="starlaDailyPreviewContainer">
                        <i class="ph-bold ph-info text-gov-600 text-sm shrink-0 mt-0.5"></i>
                        <p id="starlaDailyPreviewDesc">
                            When an employee starts mid-month, leave credits are earned based on days of service (<strong>30 − Start Day + 1</strong>). For example, starting on the <strong>26th</strong> equals <strong>5 days</strong> of service, earning exactly <strong class="text-emerald-700">0.208 VL</strong> and <strong class="text-emerald-700">0.208 SL</strong>.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section 2: User Login & Role Assignment -->
            <div class="space-y-3.5 pt-1">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-200 pb-1.5">
                    <i class="ph ph-key text-gov-700 text-sm"></i>
                    <span>2. User Account & Role Clearance (RBAC)</span>
                </h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="create_username" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Username <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="create_username" 
                            name="username" 
                            required 
                            placeholder="e.g. jmendoza"
                            value="{{ old('username') }}"
                            class="w-full border @error('username') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-lg px-3 py-2 text-xs sm:text-sm font-mono text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white lowercase"
                        >
                        @error('username')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="create_email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="create_email" 
                            name="email" 
                            required
                            placeholder="e.g. juanita@basud.gov.ph"
                            value="{{ old('email') }}"
                            class="w-full border @error('email') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                        >
                        @error('email')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="create_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            Temporary Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                id="create_password" 
                                name="password" 
                                required 
                                value="password123"
                                placeholder="Min. 6 chars"
                                class="w-full border @error('password') border-rose-500 ring-1 ring-rose-500 @else border-slate-300 @enderror rounded-lg pl-3 pr-9 py-2 text-xs sm:text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                            >
                            <button 
                                type="button" 
                                onclick="toggleCreatePasswordVisibility()" 
                                id="btnToggleCreatePassword"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-700 cursor-pointer focus:outline-none"
                                title="Show / Hide password"
                                aria-label="Toggle password visibility"
                            >
                                <i id="createPasswordEyeIcon" class="ph-bold ph-eye text-base"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-rose-600 font-medium mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="create_role" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                            RBAC Role Clearance <span class="text-rose-500">*</span>
                        </label>
                        <select id="create_role" name="role" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-xs sm:text-sm text-slate-800 font-semibold focus:ring-2 focus:ring-gov-600 bg-white cursor-pointer truncate">
                            <option value="employee" selected>Employee (Level 1)</option>
                            <option value="routing_clerk">Routing Clerk (Level 2)</option>
                            <option value="hrmu_staff">HR Records Staff (Level 3)</option>
                            <option value="admin">Administrator (Level 4)</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="create_is_active" name="is_active" value="1" checked class="rounded border-slate-300 text-gov-700 focus:ring-gov-600 w-4 h-4 cursor-pointer">
                    <label for="create_is_active" class="text-xs text-slate-700 font-medium cursor-pointer">
                        Account is Active and authorized to log in immediately
                    </label>
                </div>
            </div>

            <!-- Section 3: Certified Opening Balances -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center justify-between border-b border-slate-200 pb-1.5">
                    <span class="flex items-center gap-1.5">
                        <i class="ph ph-coins text-gov-700 text-sm"></i>
                        <span>3. Opening Certified Leave Balances (Year {{ $currentYear }})</span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-normal">Decimal Days (3 places)</span>
                </h4>

                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <label for="create_vl_balance" class="block text-[10px] font-bold text-slate-700 uppercase mb-1">
                            Vacation (VL)
                        </label>
                        <input 
                            type="number" 
                            step="0.001" 
                            min="0" 
                            max="999.999" 
                            id="create_vl_balance" 
                            name="vl_balance" 
                            value="0.000"
                            class="w-full border border-slate-300 rounded-lg px-2 py-1 text-xs font-mono font-bold text-slate-900 bg-white text-right focus:ring-2 focus:ring-gov-600"
                        >
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <label for="create_sl_balance" class="block text-[10px] font-bold text-slate-700 uppercase mb-1">
                            Sick (SL)
                        </label>
                        <input 
                            type="number" 
                            step="0.001" 
                            min="0" 
                            max="999.999" 
                            id="create_sl_balance" 
                            name="sl_balance" 
                            value="0.000"
                            class="w-full border border-slate-300 rounded-lg px-2 py-1 text-xs font-mono font-bold text-slate-900 bg-white text-right focus:ring-2 focus:ring-gov-600"
                        >
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <label for="create_fl_balance" class="block text-[10px] font-bold text-slate-700 uppercase mb-1">
                            Forced (FL)
                        </label>
                        <input 
                            type="number" 
                            step="1" 
                            min="0" 
                            max="5" 
                            id="create_fl_balance" 
                            name="fl_balance" 
                            value="5.0"
                            class="w-full border border-slate-300 rounded-lg px-2 py-1 text-xs font-mono font-bold text-slate-900 bg-white text-right focus:ring-2 focus:ring-gov-600"
                        >
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <label for="create_spl_balance" class="block text-[10px] font-bold text-slate-700 uppercase mb-1">
                            Special (SPL)
                        </label>
                        <input 
                            type="number" 
                            step="1" 
                            min="0" 
                            max="3" 
                            id="create_spl_balance" 
                            name="spl_balance" 
                            value="3.0"
                            class="w-full border border-slate-300 rounded-lg px-2 py-1 text-xs font-mono font-bold text-slate-900 bg-white text-right focus:ring-2 focus:ring-gov-600"
                        >
                    </div>

                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                        <label for="create_wl_balance" class="block text-[10px] font-bold text-slate-700 uppercase mb-1">
                            Wellness (WL)
                        </label>
                        <input 
                            type="number" 
                            step="1" 
                            min="0" 
                            max="5" 
                            id="create_wl_balance" 
                            name="wl_balance" 
                            value="5.0"
                            class="w-full border border-slate-300 rounded-lg px-2 py-1 text-xs font-mono font-bold text-slate-900 bg-white text-right focus:ring-2 focus:ring-gov-600"
                        >
                    </div>
                </div>
            </div>

            </div>

            <!-- Footer Buttons (Pinned) -->
            <div class="bg-slate-50 px-5 sm:px-6 py-3.5 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 shrink-0">
                <button 
                    type="button" 
                    onclick="closeCreateUserModal()" 
                    class="w-full sm:w-auto px-4 py-2.5 border border-slate-300 rounded-xl text-xs font-medium text-slate-700 hover:bg-slate-100 transition cursor-pointer min-h-[40px]"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-5 py-2.5 bg-gov-700 hover:bg-gov-800 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-gov-600 min-h-[40px]"
                >
                    <i class="ph ph-user-plus text-sm"></i>
                    <span>Register Personnel & User</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: CREATE ON-DEMAND SYSTEM BACKUP                                    -->
<!-- ======================================================================= -->
<div id="createBackupModal" onclick="if(event.target === this) closeCreateBackupModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full max-h-[92vh] sm:max-h-[88vh] shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col my-auto" onclick="event.stopPropagation()">
        <div class="bg-gov-navy text-white px-4 sm:px-6 py-3.5 sm:py-4 border-b border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-archive text-lg sm:text-xl text-amber-400"></i>
                <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider">Create System Backup Archive</h3>
            </div>
            <button type="button" onclick="closeCreateBackupModal()" class="text-slate-400 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-gov-600 rounded-lg p-1 cursor-pointer" aria-label="Close modal">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form id="createBackupForm" method="POST" action="{{ route('admin.backups.create') }}" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                <p class="text-xs text-slate-600 leading-relaxed">
                    Select the backup category to generate. Archives are created with a timestamped filename and SHA-256 integrity hash.
                </p>

                <div class="space-y-3">
                    <!-- Option 1: Database Only -->
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-slate-200 hover:border-gov-600 bg-slate-50/50 hover:bg-gov-50/20 cursor-pointer transition">
                        <input type="radio" name="type" value="db" checked class="mt-0.5 text-gov-700 focus:ring-gov-600">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-xs">Database Dump (.sql.gz)</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">Recommended</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                Complete SQL dump of all ~158 employee leave ledgers, certified balances, physical routing logs, Starla conversion tables, and audit logs. Fast (1-3 seconds).
                            </p>
                        </div>
                    </label>

                    <!-- Option 2: Full System Archive -->
                    <label class="flex items-start gap-3 p-3.5 rounded-xl border-2 border-slate-200 hover:border-gov-600 bg-slate-50/50 hover:bg-gov-50/20 cursor-pointer transition">
                        <input type="radio" name="type" value="full" class="mt-0.5 text-gov-700 focus:ring-gov-600">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-xs">Full System Snapshot (.zip)</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800 border border-purple-300">Complete Disaster Recovery</span>
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                                Bundles the complete database dump AND all uploaded document attachments (medical certificates, clearances) + system manifest. Complete recovery archive.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="bg-slate-50 px-5 sm:px-6 py-3.5 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 shrink-0">
                <button 
                    type="button" 
                    onclick="closeCreateBackupModal()" 
                    class="w-full sm:w-auto px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer min-h-[40px]"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    id="createBackupSubmitBtn"
                    class="w-full sm:w-auto px-5 py-2.5 bg-gov-700 hover:bg-gov-800 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-gov-600 min-h-[40px]"
                >
                    <i class="ph ph-archive text-base"></i>
                    <span>Generate & Save Archive</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL: DELETE BACKUP ARCHIVE CONFIRMATION                               -->
<!-- ======================================================================= -->
<div id="deleteBackupModal" onclick="if(event.target === this) closeDeleteBackupModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-md w-full max-h-[92vh] sm:max-h-[88vh] shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col my-auto" onclick="event.stopPropagation()">
        <div class="bg-rose-900 text-white px-4 sm:px-6 py-3.5 sm:py-4 border-b border-rose-950 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-warning text-lg sm:text-xl text-amber-300"></i>
                <h3 class="text-xs sm:text-sm font-bold uppercase tracking-wider">Confirm Backup Deletion</h3>
            </div>
            <button type="button" onclick="closeDeleteBackupModal()" class="text-rose-200 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-rose-500 rounded-lg p-1 cursor-pointer" aria-label="Close modal">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form id="deleteBackupForm" method="POST" action="" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            @method('DELETE')

            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                <p class="text-xs text-slate-600 leading-relaxed">
                    Are you sure you want to permanently delete this backup archive? This action is permanent and cannot be undone.
                </p>

                <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl">
                    <div class="text-[10px] uppercase font-bold text-rose-800 font-mono tracking-wider">Archive to Delete:</div>
                    <div id="deleteBackupFilenameDisplay" class="font-mono font-bold text-slate-900 text-xs mt-0.5 break-all">
                        clacts_backup.sql.gz
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 px-5 sm:px-6 py-3.5 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 shrink-0">
                <button 
                    type="button" 
                    onclick="closeDeleteBackupModal()" 
                    class="w-full sm:w-auto px-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold transition cursor-pointer min-h-[40px]"
                >
                    Cancel
                </button>
                <button 
                    type="submit" 
                    class="w-full sm:w-auto px-5 py-2.5 bg-rose-700 hover:bg-rose-800 text-white rounded-xl text-xs font-semibold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-rose-600 min-h-[40px]"
                >
                    <i class="ph ph-trash text-base"></i>
                    <span>Delete Archive</span>
                </button>
            </div>
        </form>
    </div>
</div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function switchAdminTab(tabName) {
        // Content blocks
        const users = document.getElementById('tabContentUsers');
        const starla = document.getElementById('tabContentStarla');
        const audit = document.getElementById('tabContentAudit');
        const backups = document.getElementById('tabContentBackups');

        // Buttons
        const btnUsers = document.getElementById('tabBtnUsers');
        const btnStarla = document.getElementById('tabBtnStarla');
        const btnAudit = document.getElementById('tabBtnAudit');
        const btnBackups = document.getElementById('tabBtnBackups');

        // Reset all
        if (users) users.classList.add('hidden');
        if (starla) starla.classList.add('hidden');
        if (audit) audit.classList.add('hidden');
        if (backups) backups.classList.add('hidden');

        const inactiveBtnClass = 'px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-t-lg transition border-b-2 border-transparent text-slate-600 hover:text-slate-900 flex items-center gap-1.5 sm:gap-2 cursor-pointer';
        const activeBtnClass = 'px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-t-lg transition border-b-2 border-gov-700 text-gov-700 bg-white font-bold flex items-center gap-1.5 sm:gap-2 cursor-pointer';

        if (btnUsers) btnUsers.className = inactiveBtnClass;
        if (btnStarla) btnStarla.className = inactiveBtnClass;
        if (btnAudit) btnAudit.className = inactiveBtnClass;
        if (btnBackups) btnBackups.className = inactiveBtnClass;

        if (tabName === 'starla') {
            if (starla) starla.classList.remove('hidden');
            if (btnStarla) btnStarla.className = activeBtnClass;
        } else if (tabName === 'audit') {
            if (audit) audit.classList.remove('hidden');
            if (btnAudit) btnAudit.className = activeBtnClass;
        } else if (tabName === 'backups') {
            if (backups) backups.classList.remove('hidden');
            if (btnBackups) btnBackups.className = activeBtnClass;
        } else {
            if (users) users.classList.remove('hidden');
            if (btnUsers) btnUsers.className = activeBtnClass;
        }
    }

    // Auto-switch to starla, audit, or backups tab if query parameter exists
    document.addEventListener('DOMContentLoaded', () => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'backups') {
            switchAdminTab('backups');
        } else if (urlParams.has('table_ref') || urlParams.has('starla_page')) {
            switchAdminTab('starla');
        } else if (urlParams.has('audit_page')) {
            switchAdminTab('audit');
        }
    });

    function openResetPasswordModal(userId, username, employeeName) {
        const modal = document.getElementById('resetPasswordModal');
        const form = document.getElementById('resetPasswordForm');
        form.action = `/admin/users/${userId}/reset-password`;

        document.getElementById('resetModalUsername').innerText = `@${username}`;
        document.getElementById('resetModalEmployeeName').innerText = employeeName || username;
        document.getElementById('admin_reset_new_password').value = 'password123';
        document.getElementById('admin_reset_new_password_confirm').value = 'password123';

        modal.classList.remove('hidden');
        document.getElementById('admin_reset_new_password').focus();
    }

    function closeResetPasswordModal() {
        document.getElementById('resetPasswordModal').classList.add('hidden');
    }

    function openDeleteUserModal(userId, username, employeeName) {
        const modal = document.getElementById('deleteUserModal');
        const form = document.getElementById('deleteUserForm');
        form.action = `/admin/users/${userId}`;

        document.getElementById('deleteModalUsername').innerText = `@${username}`;
        document.getElementById('deleteModalEmployeeName').innerText = employeeName || username;

        modal.classList.remove('hidden');
    }

    function closeDeleteUserModal() {
        document.getElementById('deleteUserModal').classList.add('hidden');
    }

    let currentEditUserId = null;
    let currentEditUsername = '';
    let currentEditEmployeeName = '';

    function openEditRoleModal(userId, username, employeeName, role, isActive, employeeNo, position, officeId, employmentType, employeeStatus, dateHired, email) {
        currentEditUserId = userId;
        currentEditUsername = username;
        currentEditEmployeeName = employeeName || username;

        const modal = document.getElementById('roleModal');
        const form = document.getElementById('editRoleForm');
        form.action = `/admin/users/${userId}/role`;

        document.getElementById('edit_username').value = username || '';
        document.getElementById('edit_email').value = email || '';
        document.getElementById('modalRoleSelect').value = role || 'employee';
        document.getElementById('modalIsActive').checked = isActive === 1;

        document.getElementById('edit_full_name').value = employeeName || '';
        document.getElementById('edit_employee_no').value = employeeNo || '';
        document.getElementById('edit_position').value = position || '';
        if (officeId) {
            document.getElementById('edit_office_id').value = officeId;
        }
        if (employmentType) {
            document.getElementById('edit_employment_type').value = employmentType;
        }
        if (employeeStatus) {
            document.getElementById('edit_employee_status').value = employeeStatus;
        }
        document.getElementById('edit_date_hired').value = dateHired || '';

        // Hide delete button if editing self or root admin
        const btnDelete = document.getElementById('btnDeleteFromEditModal');
        if (btnDelete) {
            const currentAuthId = {{ Auth::id() }};
            if (userId === currentAuthId || username === 'admin_hrmu') {
                btnDelete.classList.add('hidden');
            } else {
                btnDelete.classList.remove('hidden');
            }
        }

        modal.classList.remove('hidden');
        document.getElementById('edit_full_name').focus();
    }

    function closeEditRoleModal() {
        document.getElementById('roleModal').classList.add('hidden');
    }

    function openCreateUserModal() {
        document.getElementById('createUserModal').classList.remove('hidden');
        document.getElementById('create_full_name').focus();
    }

    function closeCreateUserModal() {
        document.getElementById('createUserModal').classList.add('hidden');
    }

    const officePrefixMap = {
        'human resource': 'HRMU',
        'mayor': 'MAYOR',
        'planning': 'MPDO',
        'treasur': 'MTO',
        'general service': 'GSO',
        'health': 'MHO',
        'engineer': 'MEO',
        'social welfare': 'MSWDO',
        'civil registrar': 'MCR',
        'agricultur': 'MAO',
        'accounting': 'ACCT',
        'assessor': 'ASSR',
        'budget': 'MBO',
        'disaster': 'MDRRO',
        'employment': 'PESO',
        'disability': 'PDAO',
        'environment': 'MENRO',
        'legal': 'LEGAL',
        'permit': 'BPLO',
        'library': 'LIB',
        'youth': 'LYDO',
        'cooperative': 'MCDO',
        'vice mayor': 'SB',
        'sangguniang': 'SB'
    };

    function onOfficeChangeSuggestId(selectEl, inputId) {
        const text = selectEl.options[selectEl.selectedIndex]?.text.toLowerCase() || '';
        let prefix = 'LGU';
        for (const [key, pfx] of Object.entries(officePrefixMap)) {
            if (text.includes(key)) {
                prefix = pfx;
                break;
            }
        }
        const empInput = document.getElementById(inputId);
        if (empInput) {
            empInput.placeholder = `e.g. ${prefix}-0001`;
            if (!empInput.value || /^[A-Z]+-?$/i.test(empInput.value.trim()) || /^\d+$/.test(empInput.value.trim())) {
                empInput.value = `${prefix}-`;
                empInput.focus();
            }
        }
    }

    // Official CSC Table II lookup table for daily leave credit accruals
    const starlaTableIIMap = {
        1: 0.042, 2: 0.083, 3: 0.125, 4: 0.167, 5: 0.208,
        6: 0.250, 7: 0.292, 8: 0.333, 9: 0.375, 10: 0.417,
        11: 0.458, 12: 0.500, 13: 0.542, 14: 0.583, 15: 0.625,
        16: 0.667, 17: 0.708, 18: 0.750, 19: 0.792, 20: 0.833,
        21: 0.875, 22: 0.917, 23: 0.958, 24: 1.000, 25: 1.042,
        26: 1.083, 27: 1.125, 28: 1.167, 29: 1.208, 30: 1.250
    };

    function calculateStarlaTableIIPreview(dateStr) {
        if (!dateStr) return;
        const parts = dateStr.split('-');
        if (parts.length < 3) return;
        const day = parseInt(parts[2], 10);
        if (isNaN(day) || day < 1 || day > 31) return;

        // Days of service in month under standard 30-day CSC baseline
        const daysOfService = Math.max(1, Math.min(30, 30 - day + 1));
        const credit = starlaTableIIMap[daysOfService] !== undefined ? starlaTableIIMap[daysOfService].toFixed(3) : (daysOfService * (1.25 / 30)).toFixed(3);

        const badge = document.getElementById('starlaDailyPreviewBadge');
        const desc = document.getElementById('starlaDailyPreviewDesc');

        if (badge && desc) {
            badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span><span>Table II: ${daysOfService} Days Service = ${credit} VL & SL</span>`;
            badge.className = "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-900 font-mono font-bold text-xs border border-emerald-300 w-fit";
            desc.innerHTML = `Starting on day <strong>${day}</strong> gives <strong>${daysOfService} day(s)</strong> of service for the initial month. Per <strong>CSC Table II</strong>, this employee earns exactly <strong class="text-emerald-700">${credit} VL</strong> and <strong class="text-emerald-700">${credit} SL</strong>.`;
        }

        // Auto-suggest opening balance based on employment status and Table II
        const empTypeSelect = document.getElementById('create_employment_type');
        const isJobOrder = empTypeSelect && empTypeSelect.value.toLowerCase() === 'job order';

        const vlInput = document.getElementById('create_vl_balance');
        const slInput = document.getElementById('create_sl_balance');
        const flInput = document.getElementById('create_fl_balance');
        const splInput = document.getElementById('create_spl_balance');
        const wlInput = document.getElementById('create_wl_balance');

        if (isJobOrder) {
            if (vlInput) vlInput.value = '0.000';
            if (slInput) slInput.value = '0.000';
            if (flInput) flInput.value = '0';
            if (splInput) splInput.value = '0';
            if (wlInput) wlInput.value = '0';
            if (desc) desc.innerHTML += `<br><span class="text-amber-800 font-bold">Note: Under CSC Rule XVI, Job Order personnel do not accrue leave credits.</span>`;
        } else {
            if (vlInput) vlInput.value = credit;
            if (slInput) slInput.value = credit;
        }
    }

    // React dynamically when Employment Classification changes (e.g. Job Order vs Permanent)
    document.addEventListener('DOMContentLoaded', function() {
        const empTypeSelect = document.getElementById('create_employment_type');
        const dateHiredInput = document.getElementById('create_date_hired');
        if (empTypeSelect) {
            empTypeSelect.addEventListener('change', function() {
                if (dateHiredInput && dateHiredInput.value) {
                    calculateStarlaTableIIPreview(dateHiredInput.value);
                } else if (this.value.toLowerCase() === 'job order') {
                    const vlInput = document.getElementById('create_vl_balance');
                    const slInput = document.getElementById('create_sl_balance');
                    const flInput = document.getElementById('create_fl_balance');
                    const splInput = document.getElementById('create_spl_balance');
                    const wlInput = document.getElementById('create_wl_balance');
                    if (vlInput) vlInput.value = '0.000';
                    if (slInput) slInput.value = '0.000';
                    if (flInput) flInput.value = '0';
                    if (splInput) splInput.value = '0';
                    if (wlInput) wlInput.value = '0';
                }
            });
        }
    });

    function toggleCreatePasswordVisibility() {
        const input = document.getElementById('create_password');
        const icon = document.getElementById('createPasswordEyeIcon');
        if (!input || !icon) return;

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('ph-eye');
            icon.classList.add('ph-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('ph-eye-slash');
            icon.classList.add('ph-eye');
        }
    }

    // Backup Modals & Actions
    function openCreateBackupModal() {
        const modal = document.getElementById('createBackupModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeCreateBackupModal() {
        const modal = document.getElementById('createBackupModal');
        if (modal) modal.classList.add('hidden');
    }

    function openDeleteBackupModal(filename) {
        const modal = document.getElementById('deleteBackupModal');
        const form = document.getElementById('deleteBackupForm');
        const display = document.getElementById('deleteBackupFilenameDisplay');
        if (form) form.action = `/admin/backups/${encodeURIComponent(filename)}`;
        if (display) display.textContent = filename;
        if (modal) modal.classList.remove('hidden');
    }

    function closeDeleteBackupModal() {
        const modal = document.getElementById('deleteBackupModal');
        if (modal) modal.classList.add('hidden');
    }

    function copyChecksum(hash) {
        if (!hash) return;
        navigator.clipboard.writeText(hash).then(() => {
            alert('Copied SHA-256 Checksum to clipboard:\n\n' + hash);
        }).catch(() => {
            prompt('Copy SHA-256 Checksum:', hash);
        });
    }

    // Dismiss modals on Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeDeleteUserModal();
            closeResetPasswordModal();
            closeEditRoleModal();
            closeCreateUserModal();
            closeCreateBackupModal();
            closeDeleteBackupModal();
        }
    });

    @if($errors->any() && (old('username') || old('employee_no') || old('full_name')))
        document.addEventListener('DOMContentLoaded', function() {
            openCreateUserModal();
        });
    @endif
</script>
@endsection
