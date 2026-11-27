@extends('layouts.app')

@section('title', 'HR Attendance & Starla Deduction Terminal')

@section('content')
<div class="space-y-6 w-full">

    <!-- Header Banner -->
    <div class="bg-white rounded-xl p-4 sm:p-6 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-lg sm:text-xl font-bold text-slate-900">Semi-Monthly Attendance & Tardiness Encoding</h1>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 w-full sm:w-auto">
            <a 
                href="{{ route('hr.starla.tables') }}" 
                class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-2xs transition flex items-center justify-center gap-2 shrink-0 cursor-pointer min-h-[40px]"
                title="View digitized CSC Starla conversion matrices (Tables II & IV)"
            >
                <i class="ph ph-table text-slate-600 text-sm"></i>
                <span>View Starla Tables</span>
            </a>
        </div>
    </div>

    <!-- Main 2-Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Column 1: The Starla Computation Input Form (5 Cols) -->
        <div class="lg:col-span-5 bg-white rounded-2xl shadow-xs border border-slate-200 p-4 sm:p-6 space-y-4 sm:space-y-5">
            <div class="border-b border-slate-200 pb-3 flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <i class="ph-bold ph-calculator text-gov-700 text-base"></i>
                    <span>DTR Summary Input</span>
                </h2>
            </div>

            <form method="POST" action="{{ route('hr.tardiness.store') }}" id="starlaForm" class="space-y-4">
                @csrf

                <!-- Municipal Office / Department Filter (Placed First to Avoid Clutter) -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between mb-1">
                        <label for="office_filter" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ph-bold ph-buildings text-gov-700"></i>
                            <span>Municipal Office / Department</span>
                        </label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="ph ph-buildings text-base"></i>
                        </div>
                        <select 
                            id="office_filter" 
                            name="office_id_filter"
                            class="w-full pl-9 pr-8 py-2 text-sm text-slate-800 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent transition shadow-xs cursor-pointer"
                        >
                            <option value="">— All Municipal Offices ({{ $employees->count() }} personnel) —</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->office_id }}" {{ old('office_id_filter') == $office->office_id ? 'selected' : '' }}>
                                    {{ $office->office_name }} ({{ $office->employees_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Employee Selection Searchable Combobox -->
                <div class="relative" id="employeeComboboxContainer">
                    <div class="flex items-center justify-between mb-1">
                        <label for="employee_search_input" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                            Select Employee <span class="text-rose-500">*</span>
                        </label>
                    </div>

                    <!-- Hidden Input holding the submitted ID -->
                    <input type="hidden" name="employee_id" id="employee_id" value="{{ old('employee_id') }}">

                    <!-- Typeable Search Input -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <i class="ph ph-magnifying-glass text-base"></i>
                        </div>
                        <input 
                            type="text" 
                            id="employee_search_input" 
                            autocomplete="off"
                            spellcheck="false"
                            placeholder="Type employee name, badge # (e.g. 101), or office..." 
                            class="w-full pl-9 pr-14 py-2 text-sm text-slate-800 placeholder-slate-400 bg-white border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gov-600 focus:border-transparent transition shadow-xs {{ (isset($errors) && $errors->has('employee_id')) ? 'border-rose-500 ring-1 ring-rose-500' : '' }}"
                        >
                        <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-0.5">
                            <button 
                                type="button" 
                                id="clearEmployeeBtn" 
                                class="hidden p-1 text-slate-400 hover:text-slate-600 rounded-md transition focus:outline-none"
                                title="Clear search"
                            >
                                <i class="ph-bold ph-x text-sm"></i>
                            </button>
                            <button 
                                type="button" 
                                id="toggleEmployeeDropdownBtn" 
                                class="p-1 text-slate-400 hover:text-slate-600 rounded-md transition focus:outline-none"
                                title="Show all municipal employees"
                            >
                                <i class="ph-bold ph-caret-down text-sm transition-transform duration-150" id="employeeCaretIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Filterable Dropdown List -->
                    <div 
                        id="employeeDropdownList" 
                        class="hidden absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-white rounded-xl shadow-xl border border-slate-200 divide-y divide-slate-100 text-xs text-slate-700"
                    ></div>

                    <!-- Client/Server Error Message -->
                    <p id="employeeSelectionError" class="hidden text-xs text-rose-600 mt-1 flex items-center gap-1 font-medium">
                        <i class="ph-bold ph-warning-circle"></i>
                        <span>Please select a municipal employee from the list.</span>
                    </p>
                    @if(isset($errors) && $errors->has('employee_id'))
                        <p class="text-xs text-rose-600 mt-1 flex items-center gap-1 font-medium">
                            <i class="ph-bold ph-warning-circle"></i>
                            <span>{{ $errors->first('employee_id') }}</span>
                        </p>
                    @endif

                    <!-- Selected Employee Confirmation Card -->
                    <div id="selectedEmployeeCard" class="hidden mt-2 p-2.5 bg-gov-50/90 border border-gov-200 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-3 text-xs shadow-xs transition">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div id="cardAvatar" class="w-7 h-7 rounded-full bg-gov-700 text-white font-bold flex items-center justify-center text-[11px] shrink-0 font-mono">
                                --
                            </div>
                            <div class="min-w-0">
                                <div id="cardEmpName" class="font-bold text-slate-900 truncate text-xs leading-tight"></div>
                                <div id="cardEmpDetails" class="text-[10px] text-slate-500 font-mono truncate leading-tight mt-0.5"></div>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 text-[10px] font-mono">
                            <span id="cardVlBadge" class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-semibold border border-emerald-200" title="Certified Vacation Leave balance">VL: 0.000</span>
                            <span id="cardSlBadge" class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 font-semibold border border-indigo-200" title="Certified Sick Leave balance">SL: 0.000</span>
                        </div>
                    </div>

                    <!-- Graceful No-JS Fallback -->
                    <noscript>
                        <select name="employee_id" required class="w-full mt-1 border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 bg-white">
                            <option value="" disabled selected>— Choose Municipal Employee —</option>
                            @foreach($offices as $off)
                                <optgroup label="{{ $off->office_name }} ({{ $off->employees_count }})">
                                    @foreach($employees->where('office_id', $off->office_id) as $emp)
                                        <option value="{{ $emp->employee_id }}">{{ $emp->full_name }} ({{ $emp->employee_no }})</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </noscript>
                </div>

                <!-- Pay Period Dropdown -->
                <div>
                    <label for="pay_period_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Semi-Monthly Pay Period <span class="text-rose-500">*</span>
                    </label>
                    <select id="pay_period_id" name="pay_period_id" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white">
                        @foreach($payPeriods as $period)
                            <option value="{{ $period->pay_period_id }}" {{ (old('pay_period_id') ?? $selectedPeriodId) == $period->pay_period_id ? 'selected' : '' }}>
                                Month {{ $period->month }} ({{ $period->half == 1 ? '1st Half: 1-15' : '2nd Half: 16-End' }}) • {{ Carbon\Carbon::parse($period->start_date)->format('M d') }} - {{ Carbon\Carbon::parse($period->end_date)->format('M d, Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Aggregate Tardiness Grid (Hours & Minutes) -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                    <div class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                        <span>Tardiness & Undertime (Table IV)</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="late_hours" class="block text-[11px] font-semibold text-slate-600 uppercase mb-1">
                                Late Hours
                            </label>
                            <input 
                                type="number" 
                                id="late_hours" 
                                name="late_hours" 
                                min="0" 
                                max="160" 
                                value="{{ old('late_hours', 0) }}" 
                                oninput="triggerLivePreview()"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono font-bold text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                            >
                        </div>
                        <div>
                            <label for="late_minutes" class="block text-[11px] font-semibold text-slate-600 uppercase mb-1">
                                Late Minutes
                            </label>
                            <input 
                                type="number" 
                                id="late_minutes" 
                                name="late_minutes" 
                                min="0" 
                                max="59" 
                                value="{{ old('late_minutes', 0) }}" 
                                oninput="triggerLivePreview()"
                                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono font-bold text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                            >
                        </div>
                    </div>
                </div>

                <!-- Unexcused Absences -->
                <div>
                    <label for="days_absent" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        Unexcused Absences (Days)
                    </label>
                    <input 
                        type="number" 
                        step="0.5" 
                        min="0" 
                        max="31" 
                        id="days_absent" 
                        name="days_absent" 
                        value="{{ old('days_absent', 0.0) }}" 
                        oninput="triggerLivePreview()"
                        placeholder="e.g. 0.00, 1.00, 2.50" 
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono font-bold text-slate-800 focus:ring-2 focus:ring-gov-600 bg-white"
                    >
                </div>

                <!-- Live Mathematical Preview Card (Instant Starla Lookup - Totaled Separately) -->
                <div class="bg-gov-50 border border-gov-200 rounded-xl p-4 text-xs space-y-3">
                    <div class="font-bold text-gov-900 flex items-center justify-between">
                        <span class="flex items-center gap-1.5">
                            <i class="ph-bold ph-scales text-gov-700"></i>
                            <span>Separate Leave Record Deductions:</span>
                        </span>
                        <span id="previewTag" class="text-[10px] bg-white border border-gov-300 text-gov-800 px-2 py-0.5 rounded font-mono">
                            Discrete Lookups
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <!-- 1. Tardiness & Undertime Total -->
                        <div class="bg-white p-2.5 rounded-lg border border-gov-200">
                            <span class="text-[11px] font-bold text-slate-700 block">Tardiness & Undertime</span>
                            <span class="text-[10px] text-slate-400 block mb-1">CSC Table IV Total</span>
                            <strong class="font-mono text-base text-gov-900" id="prevTardy">0.000</strong>
                        </div>

                        <!-- 2. Unexcused Absences Total -->
                        <div class="bg-white p-2.5 rounded-lg border border-gov-200">
                            <span class="text-[11px] font-bold text-slate-700 block">Unexcused Absences</span>
                            <span class="text-[10px] text-slate-400 block mb-1">1:1 Direct Total</span>
                            <strong class="font-mono text-base text-gov-900" id="prevAbsence">0.000</strong>
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gov-200 flex justify-between items-center text-xs">
                        <span class="font-semibold text-slate-600">Combined Total:</span>
                        <span class="font-mono font-bold text-sm text-gov-950" id="prevTotal">0.000</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit" 
                    class="w-full py-3 bg-gov-700 hover:bg-gov-800 active:bg-gov-900 text-white font-bold rounded-xl text-sm shadow-xs transition flex items-center justify-center gap-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-gov-600 focus:ring-offset-2"
                >
                    <i class="ph-bold ph-calculator text-lg"></i>
                    <span>Compute & Deduct (Starla)</span>
                </button>
            </form>
        </div>

        <!-- Column 2: Recent Ledger Audit Records (7 Cols) -->
        <div class="lg:col-span-7 space-y-4">
            <!-- Calculation Result Callout (if just submitted) -->
            @if(session('calculation_result'))
                @php $res = session('calculation_result'); @endphp
                <div class="bg-white rounded-xl p-4 sm:p-5 border-2 border-emerald-500 shadow-xs space-y-3">
                    <div class="flex items-center gap-2 text-emerald-800 font-bold">
                        <i class="ph-fill ph-check-circle text-xl"></i>
                        <span>Separate Entries Posted to Digital Leave Ledger (CS Form 41)</span>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-center text-xs">
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                            <span class="text-slate-500 block text-[11px]">Tardiness Total</span>
                            <strong class="font-mono text-sm text-rose-600">{{ number_format($res['tardiness_deduction'], 3) }}</strong>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                            <span class="text-slate-500 block text-[11px]">Absence Total</span>
                            <strong class="font-mono text-sm text-rose-600">{{ number_format($res['absence_deduction'], 3) }}</strong>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                            <span class="text-slate-500 block text-[11px]">New VL Balance</span>
                            <strong class="font-mono text-sm text-gov-800">{{ number_format($res['new_vl_balance'], 3) }}</strong>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                            <span class="text-slate-500 block text-[11px]">New SL Balance</span>
                            <strong class="font-mono text-sm text-emerald-800">{{ number_format($res['new_sl_balance'], 3) }}</strong>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2 border-t border-emerald-100 text-[11px] text-slate-500">
                        <div>
                            Recorded separately: <strong>{{ $res['leave_record_particular'] }}</strong>. Priority applied: VL debited <strong>{{ $res['vl_debited'] }}</strong>; SL debited <strong>{{ $res['sl_debited'] }}</strong>; LWOP <strong>{{ $res['lwop_incurred'] }}</strong>.
                        </div>
                        @if(isset($res['employee_id']))
                            <a href="{{ route('hr.leave-records.index', ['employee_id' => $res['employee_id']]) }}" class="inline-flex items-center gap-1 font-bold text-gov-700 hover:text-gov-900 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 px-3 py-1.5 rounded-lg transition shrink-0 cursor-pointer">
                                <span>Open Employee Leave Card (CS Form 41)</span>
                                <i class="ph-bold ph-arrow-square-out text-xs"></i>
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Digital Ledger History Table -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Recent Attendance Deductions</h3>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">{{ $recentDeductions->total() }} recorded</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-50 text-[11px] font-semibold uppercase text-slate-500 border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-3">Employee</th>
                                <th class="py-3 px-3">Period</th>
                                <th class="py-3 px-3 text-center hidden md:table-cell">Tardiness</th>
                                <th class="py-3 px-3 text-center hidden md:table-cell">Absence</th>
                                <th class="py-3 px-3 text-right">Deducted</th>
                                <th class="py-3 px-3 hidden sm:table-cell">Encoded</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($recentDeductions as $ded)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-3 font-semibold text-slate-900">
                                        {{ $ded->employee->full_name }}
                                        <span class="block text-[10px] text-slate-400 font-mono">{{ $ded->employee->employee_no }} • {{ $ded->employee->office->office_name ?? 'LGU' }}</span>
                                        <div class="text-[10px] text-slate-500 font-mono md:hidden mt-0.5">
                                            {{ $ded->late_hours }}h {{ $ded->late_minutes }}m tardy • {{ number_format($ded->days_absent, 2) }}d abs
                                        </div>
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap">
                                        Month {{ $ded->payPeriod->month }} (H{{ $ded->payPeriod->half }})
                                        <span class="block text-[10px] text-slate-400">{{ Carbon\Carbon::parse($ded->payPeriod->start_date)->format('M d') }} - {{ Carbon\Carbon::parse($ded->payPeriod->end_date)->format('d') }}</span>
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono hidden md:table-cell">
                                        {{ $ded->late_hours }}h {{ $ded->late_minutes }}m
                                    </td>
                                    <td class="py-3 px-3 text-center font-mono hidden md:table-cell">
                                        {{ number_format($ded->days_absent, 2) }}d
                                    </td>
                                    <td class="py-3 px-3 text-right font-mono font-bold text-rose-600 whitespace-nowrap">
                                        -{{ number_format($ded->days_deducted, 3) }}
                                    </td>
                                    <td class="py-3 px-3 whitespace-nowrap text-slate-500 text-[11px] hidden sm:table-cell">
                                        {{ $ded->encoded_at->format('M d, h:i A') }}
                                        <span class="block text-[10px] text-slate-400">by {{ $ded->encoder?->employee?->full_name ?? $ded->encoder?->employee?->employee_no ?? 'HR' }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-slate-400">
                                        <i class="ph ph-calendar-blank text-3xl mb-1 block text-slate-300"></i>
                                        No attendance deductions recorded yet. Enter a record on the left to compute via Starla.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($recentDeductions->hasPages())
                    <div class="p-3 border-t border-slate-200">
                        {{ $recentDeductions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@php
    $employeeData = $employees->map(function($e) {
        $vl = $e->balances->firstWhere('leaveType.code', 'VL')?->balance ?? '0.000';
        $sl = $e->balances->firstWhere('leaveType.code', 'SL')?->balance ?? '0.000';
        return [
            'id' => $e->employee_id,
            'no' => $e->employee_no,
            'name' => $e->full_name,
            'office_id' => $e->office_id,
            'office' => $e->office->office_name ?? 'LGU Basud',
            'position' => $e->position,
            'vl' => (string) $vl,
            'sl' => (string) $sl,
        ];
    });
@endphp
<script>
    // -------------------------------------------------------------------------
    // 1. Employee Searchable Combobox & Office Filtering Logic
    // -------------------------------------------------------------------------
    const municipalEmployees = @json($employeeData);
    const officeFilter = document.getElementById('office_filter');
    const employeeCounter = document.getElementById('employeeCounter');
    const searchInput = document.getElementById('employee_search_input');
    const hiddenIdInput = document.getElementById('employee_id');
    const dropdown = document.getElementById('employeeDropdownList');
    const caretIcon = document.getElementById('employeeCaretIcon');
    const clearBtn = document.getElementById('clearEmployeeBtn');
    const toggleBtn = document.getElementById('toggleEmployeeDropdownBtn');
    const selectionError = document.getElementById('employeeSelectionError');
    const card = document.getElementById('selectedEmployeeCard');
    const cardAvatar = document.getElementById('cardAvatar');
    const cardEmpName = document.getElementById('cardEmpName');
    const cardEmpDetails = document.getElementById('cardEmpDetails');
    const cardVlBadge = document.getElementById('cardVlBadge');
    const cardSlBadge = document.getElementById('cardSlBadge');

    let highlightedIndex = -1;
    let filteredEmployees = [...municipalEmployees];
    let selectedEmployee = null;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/[&<>'"]/g, tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag));
    }

    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        const escapedText = escapeHtml(text);
        const escapedQuery = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp(`(${escapedQuery})`, 'gi');
        return escapedText.replace(regex, '<mark class="bg-amber-200 text-amber-950 font-bold px-0.5 rounded-xs">$1</mark>');
    }

    function getInitials(name) {
        if (!name) return '??';
        const parts = name.trim().split(/\s+/).filter(Boolean);
        if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    function getOfficeFilteredEmployees() {
        const selectedOfficeId = officeFilter ? officeFilter.value : '';
        if (!selectedOfficeId) {
            return municipalEmployees;
        }
        return municipalEmployees.filter(emp => String(emp.office_id) === String(selectedOfficeId));
    }

    function updateOfficeFilterState() {
        const selectedOfficeId = officeFilter ? officeFilter.value : '';
        const selectedOfficeText = (officeFilter && officeFilter.selectedIndex >= 0) 
            ? officeFilter.options[officeFilter.selectedIndex].text.replace(/\s*\(\d+\)$/, '').trim() 
            : '';
        const pool = getOfficeFilteredEmployees();

        if (selectedOfficeId) {
            if (employeeCounter) {
                employeeCounter.innerText = `${pool.length} personnel in office`;
            }
            if (searchInput) {
                searchInput.placeholder = `Search personnel in ${selectedOfficeText}...`;
            }
        } else {
            if (employeeCounter) {
                employeeCounter.innerText = `Type to search (${municipalEmployees.length})`;
            }
            if (searchInput) {
                searchInput.placeholder = "Type employee name, badge # (e.g. 101), or office...";
            }
        }

        // If currently selected employee does not belong to this office, clear selection
        if (selectedEmployee && selectedOfficeId && String(selectedEmployee.office_id) !== String(selectedOfficeId)) {
            clearSelection(false);
        }

        // Re-apply filter if dropdown is open
        if (!dropdown.classList.contains('hidden')) {
            applySearch(searchInput.value.trim());
        }
    }

    function applySearch(query = '') {
        const pool = getOfficeFilteredEmployees();
        if (!query) {
            renderDropdown(pool, '');
            return;
        }

        const q = query.toLowerCase();
        const matches = pool.filter(emp => {
            return emp.name.toLowerCase().includes(q) ||
                   emp.no.toLowerCase().includes(q) ||
                   emp.office.toLowerCase().includes(q) ||
                   (emp.position && emp.position.toLowerCase().includes(q));
        });

        renderDropdown(matches, query);
    }

    function renderDropdown(items, query = '') {
        filteredEmployees = items;
        highlightedIndex = -1;

        if (items.length === 0) {
            const isOfficeFiltered = officeFilter && officeFilter.value;
            dropdown.innerHTML = `
                <div class="p-4 text-center text-slate-400">
                    <i class="ph ph-${isOfficeFiltered ? 'user-minus' : 'magnifying-glass'} text-2xl mb-1 text-slate-300 block"></i>
                    <div class="text-xs font-semibold text-slate-600">${isOfficeFiltered ? 'No personnel found in this office' : 'No personnel found'}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">
                        ${query ? `No match for "${escapeHtml(query)}"` : 'Try selecting "All Municipal Offices" or choose another office'}
                    </div>
                </div>
            `;
            dropdown.classList.remove('hidden');
            caretIcon.classList.add('rotate-180');
            return;
        }

        let html = '';
        items.forEach((emp, index) => {
            const isSelected = selectedEmployee && selectedEmployee.id === emp.id;
            html += `
                <div 
                    class="employee-item px-3 py-2.5 cursor-pointer transition flex items-center justify-between gap-3 ${isSelected ? 'bg-gov-50 text-gov-900 font-semibold border-l-4 border-gov-600' : 'hover:bg-slate-50'}"
                    data-index="${index}"
                    role="option"
                >
                    <div class="min-w-0">
                        <div class="text-xs font-bold text-slate-900 truncate">
                            ${highlightMatch(emp.name, query)}
                        </div>
                        <div class="text-[10px] text-slate-500 font-mono truncate mt-0.5">
                            <span class="text-gov-700 font-semibold">${highlightMatch(emp.no, query)}</span>
                            <span class="text-slate-300 mx-1">•</span>
                            <span>${highlightMatch(emp.office, query)}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0 text-[10px] font-mono">
                        <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">VL ${emp.vl}</span>
                        <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200">SL ${emp.sl}</span>
                    </div>
                </div>
            `;
        });

        dropdown.innerHTML = html;
        dropdown.classList.remove('hidden');
        caretIcon.classList.add('rotate-180');

        // Attach item click listeners
        dropdown.querySelectorAll('.employee-item').forEach(el => {
            el.addEventListener('click', () => {
                const index = parseInt(el.getAttribute('data-index'), 10);
                selectEmployee(filteredEmployees[index]);
            });
        });
    }

    function openDropdown() {
        applySearch(searchInput.value.trim());
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        caretIcon.classList.remove('rotate-180');
        highlightedIndex = -1;
    }

    function selectEmployee(emp) {
        selectedEmployee = emp;
        hiddenIdInput.value = emp.id;
        searchInput.value = emp.name;
        clearBtn.classList.remove('hidden');

        // Auto-sync office filter dropdown if currently not set to that office
        if (officeFilter && (!officeFilter.value || officeFilter.value != emp.office_id)) {
            officeFilter.value = emp.office_id;
            updateOfficeFilterState();
        }

        // Update Confirmation Card
        cardAvatar.innerText = getInitials(emp.name);
        cardEmpName.innerText = emp.name;
        cardEmpDetails.innerText = `${emp.no} • ${emp.office}`;
        cardVlBadge.innerText = `VL: ${emp.vl}`;
        cardSlBadge.innerText = `SL: ${emp.sl}`;
        card.classList.remove('hidden');

        // Clear error styling
        searchInput.classList.remove('border-rose-500', 'ring-1', 'ring-rose-500', 'ring-2');
        selectionError.classList.add('hidden');

        closeDropdown();
    }

    function clearSelection(openAgain = true) {
        selectedEmployee = null;
        hiddenIdInput.value = '';
        searchInput.value = '';
        clearBtn.classList.add('hidden');
        card.classList.add('hidden');
        filteredEmployees = getOfficeFilteredEmployees();
        if (openAgain) {
            searchInput.focus();
            openDropdown();
        }
    }

    // Office filter change listener
    if (officeFilter) {
        officeFilter.addEventListener('change', function() {
            updateOfficeFilterState();
            if (this.value) {
                searchInput.focus();
                openDropdown();
            }
        });
    }

    // Filter on typing
    searchInput.addEventListener('input', function() {
        clearBtn.classList.toggle('hidden', this.value.length === 0);

        if (selectedEmployee && selectedEmployee.name.toLowerCase() !== this.value.trim().toLowerCase()) {
            hiddenIdInput.value = '';
            card.classList.add('hidden');
            selectedEmployee = null;
        }

        applySearch(this.value.trim());
    });

    // Focus opens dropdown & selects text for quick replacement
    searchInput.addEventListener('focus', function() {
        if (selectedEmployee) {
            this.select();
        }
        applySearch(this.value.trim());
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (dropdown.classList.contains('hidden')) {
            if (e.key === 'ArrowDown' || e.key === 'Enter') {
                e.preventDefault();
                openDropdown();
                return;
            }
        }

        const items = dropdown.querySelectorAll('.employee-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            highlightedIndex = (highlightedIndex + 1) % items.length;
            updateHighlight(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            highlightedIndex = (highlightedIndex - 1 + items.length) % items.length;
            updateHighlight(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (highlightedIndex >= 0 && highlightedIndex < filteredEmployees.length) {
                selectEmployee(filteredEmployees[highlightedIndex]);
            } else if (filteredEmployees.length === 1) {
                selectEmployee(filteredEmployees[0]);
            }
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    function updateHighlight(items) {
        items.forEach((item, idx) => {
            if (idx === highlightedIndex) {
                item.classList.add('bg-gov-100', 'text-gov-900', 'border-l-4', 'border-gov-600');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('bg-gov-100', 'text-gov-900', 'border-l-4', 'border-gov-600');
            }
        });
    }

    clearBtn.addEventListener('click', () => clearSelection(true));

    toggleBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        if (dropdown.classList.contains('hidden')) {
            searchInput.focus();
            openDropdown();
        } else {
            closeDropdown();
        }
    });

    // Close on click outside
    document.addEventListener('click', function(e) {
        const container = document.getElementById('employeeComboboxContainer');
        if (container && !container.contains(e.target) && (!officeFilter || !officeFilter.contains(e.target))) {
            closeDropdown();
            if (selectedEmployee) {
                searchInput.value = selectedEmployee.name;
            } else if (!hiddenIdInput.value) {
                searchInput.value = '';
                clearBtn.classList.add('hidden');
            }
        }
    });

    // Form submission validation check
    const starlaForm = document.getElementById('starlaForm');
    if (starlaForm) {
        starlaForm.addEventListener('submit', function(e) {
            if (!hiddenIdInput.value) {
                e.preventDefault();
                searchInput.classList.add('border-rose-500', 'ring-2', 'ring-rose-500');
                selectionError.classList.remove('hidden');
                searchInput.focus();
                openDropdown();
            }
        });
    }

    // Restore previous selection on initial load (e.g. from validation errors or old input)
    const initialEmpId = {{ old('employee_id') ? old('employee_id') : 'null' }};
    if (initialEmpId) {
        const found = municipalEmployees.find(e => e.id == initialEmpId);
        if (found) {
            if (officeFilter && found.office_id) {
                officeFilter.value = found.office_id;
            }
            selectEmployee(found);
        }
    } else if (officeFilter && officeFilter.value) {
        updateOfficeFilterState();
    }

    // -------------------------------------------------------------------------
    // 2. Starla Live Preview Calculation
    // -------------------------------------------------------------------------
    let previewDebounce = null;

    function triggerLivePreview() {
        clearTimeout(previewDebounce);
        previewDebounce = setTimeout(fetchPreview, 250);
    }

    async function fetchPreview() {
        const hours = parseInt(document.getElementById('late_hours').value, 10) || 0;
        const minutes = parseInt(document.getElementById('late_minutes').value, 10) || 0;
        const days = parseFloat(document.getElementById('days_absent').value) || 0.0;

        try {
            const res = await fetch(`/api/starla/preview?late_hours=${hours}&late_minutes=${minutes}&days_absent=${days}`);
            const data = await res.json();

            if (data.success) {
                document.getElementById('prevTardy').innerText = Number(data.tardiness_deduction).toFixed(3);
                document.getElementById('prevAbsence').innerText = Number(data.absence_deduction).toFixed(3);
                document.getElementById('prevTotal').innerText = Number(data.total_deduction).toFixed(3);
            }
        } catch (e) {
            console.error(e);
        }
    }

    // Run preview once on page load
    document.addEventListener('DOMContentLoaded', fetchPreview);
</script>
@endsection
