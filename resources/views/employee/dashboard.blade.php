@extends('layouts.app')

@section('title', 'Employee Self-Service Dashboard')

@section('content')
<div class="space-y-6">

    @if(session('new_application_id'))
        <!-- New Application Success Banner & Print Action -->
        <div class="bg-gov-navy text-white rounded-xl shadow-xs border border-slate-800 p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-slate-800 text-white flex items-center justify-center text-2xl font-bold shadow-xs shrink-0">
                    <i class="ph ph-printer"></i>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs uppercase tracking-wider font-semibold text-slate-300">Application Filed Successfully</span>
                        <span class="text-xs px-2 py-0.5 rounded bg-white/10 font-mono font-bold">{{ session('new_reference_code') }}</span>
                    </div>
                    <h3 class="text-base font-bold text-white mt-0.5">Civil Service Form No. 6 (Revised 2020) Ready</h3>
                    <p class="text-xs text-slate-300 mt-0.5">Your official printable form with scannable QR code is pre-filled. Print and submit for physical signatures.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                <a href="{{ route('forms.form6.print', session('new_application_id')) }}" target="_blank" class="w-full sm:w-auto justify-center px-5 py-2 bg-gov-700 hover:bg-gov-800 text-white rounded-lg text-sm font-semibold shadow-xs flex items-center gap-2 transition min-h-11">
                    <i class="ph ph-printer text-base"></i>
                    <span>Open & Print CS Form 6</span>
                </a>
            </div>
        </div>
    @endif

    <!-- Profile Header & Switcher Strip -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-slate-100 border-2 border-slate-300 flex items-center justify-center text-slate-900 font-black text-xl sm:text-2xl shadow-xs shrink-0">
                {{ substr($employee->full_name, 0, 1) }}
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl sm:text-3xl font-black text-slate-950 truncate max-w-full">{{ $employee->full_name }}</h1>
                    <span class="inline-flex items-center px-2.5 sm:px-3 py-0.5 sm:py-1 rounded-full text-[11px] sm:text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-300 shrink-0">
                        {{ $employee->employment_type }}
                    </span>
                </div>
                <div class="text-xs sm:text-sm font-semibold text-slate-700 flex flex-wrap items-center gap-x-3 gap-y-1 mt-1">
                    <span class="flex items-center gap-1.5"><i class="ph ph-identification-badge text-slate-700 text-base sm:text-lg"></i> Emp ID <strong class="font-mono text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200">{{ $employee->employee_no }}</strong></span>
                    <span class="text-slate-400 hidden min-[360px]:inline">•</span>
                    <span class="flex items-center gap-1.5"><i class="ph ph-briefcase text-slate-700 text-base sm:text-lg"></i> {{ $employee->position }}</span>
                    <span class="text-slate-400 hidden min-[360px]:inline">•</span>
                    <span class="flex items-center gap-1.5"><i class="ph ph-buildings text-slate-700 text-base sm:text-lg"></i> {{ $employee->office->office_name ?? 'Municipal Office' }}</span>
                </div>
            </div>
        </div>

        <!-- Employee Switcher & Quick Actions -->
        <div class="flex flex-col gap-3 w-full lg:w-auto lg:items-end shrink-0 min-w-0">
            @if(Auth::user() && in_array(Auth::user()->role, ['admin', 'hrmu_staff']))
                <form method="GET" action="{{ route('employee.dashboard') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto min-w-0">
                    <label for="emp_switch" class="text-xs font-bold text-slate-700 whitespace-nowrap shrink-0">Review Ledger:</label>
                    <select id="emp_switch" name="employee_id" onchange="this.form.submit()" class="text-xs sm:text-sm border border-slate-300 rounded-lg px-3 py-2 bg-slate-50 text-slate-900 font-bold focus:ring-2 focus:ring-gov-600 w-full sm:flex-1 lg:w-72 truncate min-w-0 max-w-full min-h-10">
                        @foreach($allEmployees as $emp)
                            <option value="{{ $emp->employee_id }}" {{ $emp->employee_id == $employee->employee_id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->office->office_name ?? 'LGU' }})
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full lg:w-auto">
                <button type="button" onclick="openLimitsModal()" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-800 rounded-lg text-xs sm:text-sm font-bold shadow-2xs transition cursor-pointer border border-slate-300 min-h-10 sm:min-h-11 w-full sm:flex-1 lg:flex-initial lg:w-auto whitespace-nowrap">
                    <i class="ph ph-scales text-base sm:text-lg text-slate-700"></i>
                    <span>Check Limits</span>
                </button>

                <a href="{{ route('employee.leave-record') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 text-slate-800 rounded-lg text-xs sm:text-sm font-bold shadow-2xs transition cursor-pointer border border-slate-300 min-h-10 sm:min-h-11 w-full sm:flex-1 lg:flex-initial lg:w-auto whitespace-nowrap">
                    <i class="ph ph-notebook text-base sm:text-lg"></i>
                    <span>Leave Record</span>
                </a>

                <button type="button" onclick="openFilingOptionModal()" class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gov-700 hover:bg-gov-800 text-white rounded-lg text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-gov-600 min-h-10 sm:min-h-11 w-full sm:flex-1 lg:flex-initial lg:w-auto whitespace-nowrap">
                    <i class="ph ph-plus-circle text-base sm:text-lg"></i>
                    <span>File Application</span>
                </button>
            </div>
        </div>
    </div>

    @if(isset($reminders) && $reminders['total_count'] > 0)
        <!-- ======================================================================= -->
        <!-- ON-SCREEN ACTION REMINDERS: UPCOMING LEAVE & 7-WORKING-DAY CANCELLATION -->
        <!-- ======================================================================= -->
        <div id="employeeRemindersSection" class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs">
            <div id="remindersHeader" class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-200">
                <div class="flex items-start sm:items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-gov-100 text-gov-800 flex items-center justify-center font-bold shrink-0 mt-0.5 sm:mt-0">
                        <i class="ph ph-bell text-lg"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-sm font-bold text-slate-900">Active Leave Reminders & Notices</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-gov-100 text-gov-800 border border-gov-200">
                                {{ $reminders['total_count'] }} Active
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 flex items-center gap-1.5 flex-wrap mt-0.5">
                            <span>Upcoming leave dates & 7-working-day cancellation window</span>
                            <span class="text-slate-300">•</span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-800 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                <i class="ph ph-envelope-simple text-xs"></i> Scheduled Email Reminders Active (9:00 AM)
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <button 
                        type="button" 
                        onclick="toggleRemindersCollapse()" 
                        id="remindersToggleBtn"
                        class="text-xs font-semibold text-slate-700 hover:text-slate-900 px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 shadow-2xs transition cursor-pointer flex items-center gap-1.5"
                        title="Show or hide active leave reminders & notices"
                    >
                        <i id="remindersToggleIcon" class="ph ph-caret-up text-sm"></i>
                        <span id="remindersToggleText">Hide</span>
                    </button>
                </div>
            </div>

            <!-- Reminders Cards Container (Visible by default) -->
            <div id="remindersCardsList" class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-3">
                
                {{-- 1. UPCOMING LEAVE REMINDERS (Before Date of Leave) --}}
                @foreach($reminders['upcoming'] as $rem)
                    <div id="{{ $rem['id'] }}" class="relative rounded-lg p-3.5 border transition bg-white shadow-2xs {{ $rem['urgency'] === 'emerald' ? 'border-emerald-200' : ($rem['urgency'] === 'amber' ? 'border-amber-300 bg-amber-50/20' : 'border-sky-200 bg-sky-50/20') }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold {{ $rem['urgency'] === 'emerald' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : ($rem['urgency'] === 'amber' ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-sky-100 text-sky-800 border border-sky-200') }}">
                                        <i class="ph {{ $rem['icon'] }} text-xs"></i>
                                        <span>{{ $rem['badge'] }}</span>
                                    </span>
                                    <span class="font-mono text-xs font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">{{ $rem['reference_code'] }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-600 px-2 py-0.5 bg-slate-100 rounded shrink-0">
                                    {{ $rem['status'] }}
                                </span>
                            </div>

                            <h4 class="text-sm font-bold text-slate-900 leading-snug">
                                {{ $rem['headline'] }}
                            </h4>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                {{ $rem['message'] }}
                            </p>

                            @if(!empty($rem['can_cancel']))
                                <div class="mt-2.5 p-2 rounded bg-amber-50 border border-amber-200 flex items-start gap-1.5 text-xs text-amber-900">
                                    <i class="ph ph-info text-amber-700 text-sm mt-0.5 shrink-0"></i>
                                    <div>
                                        <strong class="font-semibold">Cancellation Window Active:</strong>
                                        <span>You can cancel until {{ $rem['cancel_deadline'] }} ({{ $rem['days_left'] }} working day{{ $rem['days_left'] > 1 ? 's' : '' }} remaining).</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center justify-between text-xs gap-2">
                            <span class="text-slate-500 font-medium truncate">
                                {{ $rem['inclusive_dates'] }} ({{ $rem['days_requested'] }}d)
                            </span>
                            <div class="flex items-center gap-2">
                                @if(!empty($rem['can_cancel']))
                                    <button 
                                        type="button" 
                                        onclick="openCancelModal('{{ $rem['application_id'] }}', '{{ $rem['reference_code'] }}', '{{ $rem['cancel_deadline'] }}', {{ $rem['days_left'] }}, '{{ $rem['status'] }}')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-rose-700 hover:text-rose-800 hover:bg-rose-50 border border-rose-300 rounded transition cursor-pointer shrink-0"
                                        title="Cancel application within the 7-working-day window"
                                    >
                                        <i class="ph ph-x-circle text-sm"></i>
                                        <span>Cancel</span>
                                    </button>
                                @endif
                                <a 
                                    href="{{ $rem['action_url'] }}" 
                                    target="_blank" 
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-gov-700 hover:text-gov-800 hover:bg-gov-50 rounded transition shrink-0"
                                >
                                    <i class="ph ph-printer text-sm"></i>
                                    <span>{{ $rem['action_label'] }}</span>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- 2. 7-WORKING-DAY CANCELLATION WINDOW REMINDERS (Before 7 Working Days End) --}}
                @foreach($reminders['cancellations'] as $rem)
                    <div id="{{ $rem['id'] }}" class="relative rounded-lg p-3.5 border transition bg-white shadow-2xs {{ $rem['urgency'] === 'rose' ? 'border-rose-300 bg-rose-50/30' : ($rem['urgency'] === 'amber' ? 'border-amber-300 bg-amber-50/30' : 'border-slate-200') }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold {{ $rem['urgency'] === 'rose' ? 'bg-rose-100 text-rose-800 border border-rose-200' : ($rem['urgency'] === 'amber' ? 'bg-amber-100 text-amber-900 border border-amber-200' : 'bg-slate-100 text-slate-700 border border-slate-200') }}">
                                        <i class="ph {{ $rem['icon'] }} text-xs"></i>
                                        <span>{{ $rem['badge'] }}</span>
                                    </span>
                                    <span class="font-mono text-xs font-semibold text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">{{ $rem['reference_code'] }}</span>
                                </div>
                                <span class="text-[11px] font-semibold text-slate-600 px-2 py-0.5 bg-slate-100 rounded shrink-0">
                                    {{ $rem['status'] }}
                                </span>
                            </div>

                            <h4 class="text-sm font-bold text-slate-900 leading-snug">
                                {{ $rem['headline'] }}
                            </h4>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                {{ $rem['message'] }}
                            </p>
                        </div>

                        <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs gap-2">
                            <span class="text-slate-500 font-medium truncate">
                                Deadline: <strong class="text-slate-700">{{ $rem['deadline'] }}</strong>
                            </span>
                            <button 
                                type="button" 
                                onclick="openCancelModal('{{ $rem['application_id'] }}', '{{ $rem['reference_code'] }}', '{{ $rem['cancel_deadline'] }}', {{ $rem['days_left'] }}, '{{ $rem['status'] }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-rose-700 hover:text-rose-800 hover:bg-rose-50 border border-rose-200 rounded transition cursor-pointer shrink-0"
                            >
                                <i class="ph ph-x-circle text-sm"></i>
                                <span>{{ $rem['action_label'] }}</span>
                            </button>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    @endif

    <!-- Top Section: Certified Leave Balances (3 Decimal Places) -->
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-3.5">
            <h2 class="text-lg sm:text-xl font-black text-slate-950 flex items-center gap-2">
                <i class="ph ph-wallet text-xl text-slate-800"></i>
                <span>Certified Leave Balances (Year {{ $currentYear }})</span>
            </h2>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Vacation Leave Card (Primary) -->
            <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 border-t-4 border-t-gov-700 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider font-mono truncate">Vacation Leave</span>
                        <span class="text-[9px] sm:text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-gov-50 text-gov-700 border border-gov-200 shrink-0">VL</span>
                    </div>
                    <div class="text-xl min-[400px]:text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 font-mono tracking-tight tabular-nums mt-1">
                        {{ number_format($balances->get('VL')?->balance ?? 0.000, 3) }}
                    </div>
                </div>
                <div class="text-[10px] min-[400px]:text-xs text-slate-500 mt-2 sm:mt-3 pt-2 sm:pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="font-medium truncate">Cumulative w/ Pay</span>
                    <span class="text-gov-700 font-bold font-mono shrink-0">+1.250/mo</span>
                </div>
            </div>

            <!-- Sick Leave Card (Primary) -->
            <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 border-t-4 border-t-emerald-600 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider font-mono truncate">Sick Leave</span>
                        <span class="text-[9px] sm:text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">SL</span>
                    </div>
                    <div class="text-xl min-[400px]:text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 font-mono tracking-tight tabular-nums mt-1">
                        {{ number_format($balances->get('SL')?->balance ?? 0.000, 3) }}
                    </div>
                </div>
                <div class="text-[10px] min-[400px]:text-xs text-slate-500 mt-2 sm:mt-3 pt-2 sm:pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="font-medium truncate">Medical / Illness</span>
                    <span class="text-emerald-700 font-bold font-mono shrink-0">+1.250/mo</span>
                </div>
            </div>

            <!-- Total Paid Leave Credits Card (VL + SL Combined) -->
            <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 border-t-4 border-t-slate-700 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider font-mono truncate">Total Paid Leave</span>
                        <span class="text-[9px] sm:text-[10px] font-mono font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 shrink-0">VL + SL</span>
                    </div>
                    <div class="text-xl min-[400px]:text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-900 font-mono tracking-tight tabular-nums mt-1">
                        {{ number_format($monetizationInfo['combined_total'] ?? ((float)($balances->get('VL')?->balance ?? 0) + (float)($balances->get('SL')?->balance ?? 0)), 3) }}
                    </div>
                </div>
                <div class="text-[10px] min-[400px]:text-xs text-slate-500 mt-2 sm:mt-3 pt-2 sm:pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="font-medium truncate">Combined Balance</span>
                    <span class="text-slate-700 font-bold font-mono shrink-0">+2.500/mo</span>
                </div>
            </div>

            <!-- Dedicated Monetization Limit Card (CSC Rule: Min 15 VL Days, Retain 5 VL Days) -->
            <div class="bg-white rounded-xl p-3 sm:p-4 border border-slate-200 border-t-4 {{ $monetizationInfo['is_eligible'] ? 'border-t-amber-500' : 'border-t-slate-400' }} shadow-xs flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1 mb-1">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider font-mono truncate">Monetization Limit</span>
                        <span class="text-[9px] sm:text-[10px] font-mono font-bold px-1.5 py-0.5 rounded {{ $monetizationInfo['is_eligible'] ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }} shrink-0">
                            {{ $monetizationInfo['is_eligible'] ? 'ELIGIBLE' : 'NOT ELIGIBLE' }}
                        </span>
                    </div>

                    <div class="text-xl min-[400px]:text-2xl sm:text-3xl lg:text-4xl font-extrabold {{ $monetizationInfo['is_eligible'] ? 'text-amber-600' : 'text-slate-400' }} font-mono tracking-tight tabular-nums mt-1">
                        {{ number_format($monetizationInfo['max_monetizable'], 3) }}
                        <span class="text-[10px] sm:text-xs font-semibold text-slate-500 font-sans tracking-normal">days</span>
                    </div>

                    <div class="mt-1 text-[10px] min-[400px]:text-[11px] sm:text-xs leading-snug">
                        @if($monetizationInfo['is_eligible'])
                            <p class="text-slate-600 font-medium line-clamp-2 sm:line-clamp-none">
                                Max allowable conversion (retains 5d buffer).
                            </p>
                        @else
                            <p class="text-rose-700 font-medium line-clamp-2 sm:line-clamp-none">
                                Need <strong class="font-mono text-rose-900 font-bold">{{ number_format($monetizationInfo['remaining_needed'] ?? $monetizationInfo['regular']['remaining_needed'] ?? 0.000, 3) }}</strong> more VL days.
                            </p>
                        @endif
                    </div>
                </div>

                <div class="mt-2 sm:mt-3 pt-2 sm:pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] min-[400px]:text-[11px] font-medium text-slate-400 font-mono truncate">CS Form 41</span>
                    @if($monetizationInfo['is_eligible'])
                        <span class="inline-flex items-center gap-1 text-[10px] min-[400px]:text-[11px] font-bold text-amber-800 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded font-mono shrink-0">
                            <i class="ph-bold ph-coins text-xs text-amber-600"></i>
                            <span>Max 30d/Yr</span>
                        </span>
                    @else
                        <span class="text-[10px] min-[400px]:text-[11px] font-bold text-slate-400 italic shrink-0">15.000 VL Req.</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Leave Applications & Physical Document Tracker (DGV) -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-black text-slate-950 flex items-center gap-2">
                    <i class="ph-bold ph-clock-counter-clockwise text-gov-700 text-xl"></i>
                    <span>Leave Applications & Physical Paper Movement</span>
                </h3>
            </div>
            <div class="text-xs sm:text-sm font-bold text-slate-700 font-mono bg-slate-100 px-3 py-1 rounded-lg border border-slate-200">
                Showing {{ $applications->total() }} record/s
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-800">
                <thead class="bg-slate-900 text-slate-200 text-xs font-bold uppercase tracking-wider font-mono border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-3 sm:px-4">Reference</th>
                        <th class="py-3 px-3 sm:px-4">Leave Type</th>
                        <th class="py-3 px-4 hidden sm:table-cell">Inclusive Dates</th>
                        <th class="py-3 px-4 text-center hidden md:table-cell">Days</th>
                        <th class="py-3 px-4 hidden lg:table-cell">Physical Location</th>
                        <th class="py-3 px-3 sm:px-4">Status</th>
                        <th class="py-3 px-3 sm:px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($applications as $app)
                        <tr 
                            onclick="openTrackerModal('{{ $app->application_id }}', '{{ $app->reference_code }}')" 
                            class="hover:bg-slate-100/70 transition cursor-pointer group"
                            title="Click to view physical routing timeline for {{ $app->reference_code }}"
                        >
                            <!-- Reference Code with scannable indicator -->
                            <td class="py-3 px-3 sm:px-4 font-mono font-bold text-gov-800 whitespace-nowrap text-xs sm:text-sm">
                                <div class="flex items-center gap-1.5">
                                    <i class="ph ph-qr-code text-base text-slate-400 group-hover:text-gov-700 transition"></i>
                                    <span class="group-hover:underline">{{ $app->reference_code }}</span>
                                </div>
                            </td>

                             <!-- Leave Type (With mobile subtext for dates & days) -->
                            <td class="py-3 px-3 sm:px-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="font-bold text-slate-900 text-xs sm:text-sm">{{ $app->leaveType->type_name }}</span>
                                    @if($app->attachments->count() > 0)
                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200" title="{{ $app->attachments->count() }} supporting document(s)">
                                            <i class="ph ph-paperclip text-xs"></i>
                                            <span>{{ $app->attachments->count() }}</span>
                                        </span>
                                    @endif
                                </div>
                                <span class="text-[11px] font-mono text-slate-500 block">({{ $app->leaveType->code }})</span>

                                <!-- Mobile Only Snapshot: Dates & Days -->
                                <div class="sm:hidden text-[11px] text-slate-500 font-medium mt-0.5">
                                    {{ $app->date_from->format('M d') }}–{{ $app->date_to->format('M d, Y') }} • <strong class="font-mono text-slate-800">{{ number_format($app->days_requested, 1) }}d</strong>
                                </div>
                            </td>

                            <!-- Inclusive Dates -->
                            <td class="py-3 px-4 whitespace-nowrap hidden sm:table-cell">
                                <div class="text-xs sm:text-sm text-slate-900 font-bold">
                                    {{ $app->date_from->format('M d, Y') }} — {{ $app->date_to->format('M d, Y') }}
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5 font-medium">Filed {{ $app->created_at->format('M d, Y') }}</div>
                            </td>

                            <!-- Number of Days -->
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-900 text-xs sm:text-sm hidden md:table-cell">
                                {{ number_format($app->days_requested, 3) }}
                            </td>

                            <!-- Current Physical Office Location -->
                            <td class="py-3 px-4 whitespace-nowrap hidden lg:table-cell">
                                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                                    <i class="ph ph-map-pin text-amber-600"></i>
                                    <span>{{ $app->currentLocation->office_name ?? 'In Transit' }}</span>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-3 px-3 sm:px-4 whitespace-nowrap">
                                @if($app->status === 'Approved')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs sm:text-sm font-bold bg-emerald-100 text-emerald-900 border border-emerald-300">
                                        <i class="ph-bold ph-check text-base"></i> Approved
                                    </span>
                                @elseif($app->status === 'Cancelled')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs sm:text-sm font-bold bg-slate-200 text-slate-800 border border-slate-300">
                                        <i class="ph-bold ph-prohibit text-base"></i> Cancelled
                                    </span>
                                @elseif($app->status === 'Disapproved')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs sm:text-sm font-bold bg-rose-100 text-rose-900 border border-rose-300">
                                        <i class="ph-bold ph-x text-base"></i> Disapproved
                                    </span>
                                @elseif(str_contains($app->status, 'Received'))
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs sm:text-sm font-bold bg-sky-100 text-sky-900 border border-sky-300">
                                        <i class="ph-bold ph-tray text-base"></i> {{ $app->status }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs sm:text-sm font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                        <i class="ph-bold ph-hourglass text-base"></i> {{ $app->status }}
                                    </span>
                                @endif
                            </td>

                            <!-- Action Buttons: Print Pre-Filled CS Form 6 & 7-Working-Day Cancellation -->
                            @php
                                $canCancel = false;
                                $cancelDeadline = null;
                                $workdaysRemainingToCancel = 0;
                                if ($app->status !== 'Cancelled' && $app->status !== 'Disapproved') {
                                    $filedAt = \Carbon\Carbon::parse($app->created_at ?? $app->date_of_filing);
                                    $deadline = \App\Services\WorkingDayService::getCancellationDeadline($filedAt, 7);
                                    if (\Carbon\Carbon::now()->lte($deadline)) {
                                        $canCancel = true;
                                        $cancelDeadline = $deadline->format('M d, Y h:i A');
                                        $workdaysRemainingToCancel = \App\Services\WorkingDayService::getRemainingWorkingDays($deadline);
                                    }
                                }
                            @endphp
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-2 justify-end">
                                    <a 
                                        href="{{ route('forms.form6.print', $app->application_id) }}" 
                                        target="_blank" 
                                        onclick="event.stopPropagation()"
                                        class="inline-flex items-center gap-1 px-3 py-2 bg-slate-50 hover:bg-slate-100 text-slate-800 border border-slate-300 rounded-lg text-xs sm:text-sm font-bold transition cursor-pointer min-h-10"
                                        title="Open printable CS Form 6"
                                    >
                                        <i class="ph ph-printer text-base"></i>
                                        <span>Form 6</span>
                                    </a>

                                    @if($canCancel)
                                        <button 
                                            type="button" 
                                            onclick="event.stopPropagation(); openCancelModal('{{ $app->application_id }}', '{{ $app->reference_code }}', '{{ $cancelDeadline }}', {{ $workdaysRemainingToCancel }}, '{{ $app->status }}')"
                                            class="inline-flex items-center gap-1 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-300 rounded-lg text-xs sm:text-sm font-bold transition cursor-pointer min-h-10"
                                            title="Cancel application ({{ $workdaysRemainingToCancel }} working day{{ $workdaysRemainingToCancel > 1 ? 's' : '' }} left in 7-working-day window{{ $app->status === 'Approved' ? ' • Currently Approved' : '' }})"
                                        >
                                            <i class="ph ph-x-circle text-base text-rose-700"></i>
                                            <span>Cancel</span>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500 font-bold">
                                <i class="ph ph-folder-open text-5xl mb-2 block text-slate-400"></i>
                                <span class="text-base font-bold">No leave applications found for this employee.</span>
                                <span class="block text-sm text-slate-500 mt-1">Click "File Application" above to begin.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
    <script type="application/json" id="clactsPreloadedTimelines">@json($preloadedTimelines ?? [])</script>
</div>

<!-- ======================================================================= -->
<!-- MODAL 0: APPLICATION TYPE SELECTION (LEAVE VS MONETIZATION)            -->
<!-- ======================================================================= -->
<div id="filingOptionModal" onclick="if(event.target === this) closeFilingOptionModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full max-h-[92vh] sm:max-h-[88vh] shadow-2xl border border-slate-200 overflow-hidden transform animate-in fade-in zoom-in-95 duration-200 flex flex-col my-auto">
        <!-- Header -->
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200/90 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-gov-700 text-white flex items-center justify-center font-bold shrink-0">
                    <i class="ph-bold ph-files text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Select Application Type</h3>
                </div>
            </div>
            <button type="button" onclick="closeFilingOptionModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-200 transition cursor-pointer" aria-label="Close modal">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Options Body (Scrollable within viewport) -->
        <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
            <!-- Option 1: Apply for Leave -->
            <button 
                type="button" 
                onclick="chooseApplicationType('leave')"
                class="w-full text-left p-4 rounded-2xl border-2 border-slate-200 hover:border-gov-600 hover:bg-gov-50/40 bg-white transition cursor-pointer group flex items-start gap-4 shadow-2xs"
            >
                <div class="w-12 h-12 rounded-2xl bg-gov-100 text-gov-800 flex items-center justify-center shrink-0 group-hover:scale-105 transition">
                    <i class="ph-bold ph-calendar-plus text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-900 group-hover:text-gov-800">Apply for Leave</span>
                        <i class="ph-bold ph-arrow-right text-slate-400 group-hover:text-gov-700 group-hover:translate-x-0.5 transition"></i>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                        Vacation, Sick, Mandatory Forced, Special Privilege, Maternity, Paternity, Solo Parent, or other Civil Service leave categories.
                    </p>
                </div>
            </button>

            <!-- Option 2: Monetization of Leave Credits -->
            <div class="border-2 rounded-2xl p-4 transition {{ $monetizationInfo['is_eligible'] ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200 bg-slate-50 opacity-80' }} space-y-3">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl {{ $monetizationInfo['is_eligible'] ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-500' }} flex items-center justify-center shrink-0">
                        <i class="ph-bold ph-coins text-2xl"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                                <span>Monetization of Leave Credits</span>
                                @if($monetizationInfo['is_eligible'])
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">ELIGIBLE</span>
                                @else
                                    <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600">NOT ELIGIBLE</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Sub-options: Regular vs Special Monetization -->
                <div class="grid grid-cols-1 gap-2.5 pt-1">
                    <!-- Sub-Option A: Regular Monetization -->
                    <div class="p-3 rounded-xl border bg-white transition {{ $monetizationInfo['regular']['is_eligible'] ? 'border-amber-200 hover:border-amber-400 hover:bg-amber-50/40 cursor-pointer shadow-2xs' : 'border-slate-200 opacity-70 hover:border-rose-300 cursor-pointer' }}"
                         onclick="chooseApplicationType('monetization_regular')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $monetizationInfo['regular']['is_eligible'] ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
                                <span class="text-xs font-bold text-slate-900">Regular Monetization (10 to 30 Days)</span>
                            </div>
                            @if($monetizationInfo['regular']['is_eligible'])
                                <span class="text-[10px] font-mono font-bold text-amber-800 bg-amber-100 px-2 py-0.5 rounded border border-amber-200">
                                    Up to {{ number_format($monetizationInfo['regular']['max_days'], 0) }} days
                                </span>
                            @else
                                <span class="text-[10px] font-mono text-slate-500">Min. 15.000 VL Req.</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed pl-4">
                            @if($monetizationInfo['regular']['is_eligible'])
                                Convert between 10 and 30 Vacation Leave days while retaining a mandatory 5-day VL buffer. Requires standard letter request.
                            @else
                                Requires holding at least 15.000 VL days. You currently hold <strong class="font-mono text-slate-700">{{ number_format($monetizationInfo['current_vl'], 3) }}</strong> VL days (need {{ number_format($monetizationInfo['regular']['remaining_needed'], 3) }} more).
                            @endif
                        </p>
                    </div>

                    <!-- Sub-Option B: Special Monetization (50% or more) -->
                    <div class="p-3 rounded-xl border bg-white transition {{ $monetizationInfo['special']['is_eligible'] ? 'border-gov-200 hover:border-gov-400 hover:bg-gov-50/40 cursor-pointer shadow-2xs' : 'border-slate-200 opacity-70 hover:border-rose-300 cursor-pointer' }}"
                         onclick="chooseApplicationType('monetization_special')">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $monetizationInfo['special']['is_eligible'] ? 'bg-gov-700' : 'bg-slate-300' }}"></span>
                                <span class="text-xs font-bold text-slate-900">Special Monetization (50% or more of VL & SL)</span>
                            </div>
                            @if($monetizationInfo['special']['is_eligible'])
                                <span class="text-[10px] font-mono font-bold text-gov-800 bg-gov-100 px-2 py-0.5 rounded border border-gov-200">
                                    {{ number_format($monetizationInfo['special']['min_days'], 0) }} – {{ number_format($monetizationInfo['special']['max_days'], 0) }} days
                                </span>
                            @else
                                <span class="text-[10px] font-mono text-slate-500">Total > 30.000 Req.</span>
                            @endif
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 leading-relaxed pl-4">
                            @if($monetizationInfo['special']['is_eligible'])
                                Convert 50% or more of combined VL & SL credits (Total: <strong class="font-mono text-slate-800">{{ number_format($monetizationInfo['combined_total'], 3) }}</strong> days). Enforces mandatory upload of proof for medical emergencies, calamity, or educational needs.
                            @else
                                Combined balance must strictly exceed 30.000 days. Current total: <strong class="font-mono text-slate-700">{{ number_format($monetizationInfo['combined_total'], 3) }}</strong> days (need {{ number_format($monetizationInfo['special']['remaining_needed'], 3) }} more).
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="px-6 py-3 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end shrink-0">
            <button type="button" onclick="closeFilingOptionModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-200 transition cursor-pointer">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL 1: FILE NEW LEAVE APPLICATION FORM                                -->
<!-- ======================================================================= -->
<div id="filingModal" onclick="if(event.target === this) closeFilingModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-2 sm:p-4">
    <div class="bg-white rounded-2xl max-w-xl sm:max-w-2xl w-full max-h-[92vh] sm:max-h-[88vh] shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col my-auto">
        <!-- Modal Header (Pinned) -->
        <div class="bg-slate-50 px-5 sm:px-6 py-4 border-b border-slate-200/90 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gov-700 text-white flex items-center justify-center font-bold shadow-2xs shrink-0">
                    <i class="ph-bold ph-note-pencil text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-snug">
                        Application for Leave
                    </h3>
                </div>
            </div>
            <button type="button" onclick="closeFilingModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-200 transition cursor-pointer" aria-label="Close filing modal">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <!-- Modal Form (Flex Column with scrollable body and pinned footer) -->
        <form method="POST" action="{{ route('employee.leave.store') }}" enctype="multipart/form-data" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">

            <!-- Scrollable Form Body -->
            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">

            <!-- Applicant Snapshot (Read-only) -->
            <div class="bg-slate-50 p-3.5 rounded-xl border-2 border-slate-200 text-sm flex justify-between">
                <div>
                    <span class="text-slate-600 font-semibold">Applicant:</span>
                    <strong class="text-slate-950 block text-base font-bold">{{ $employee->last_first_middle }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-slate-600 font-semibold">Office:</span>
                    <strong class="text-slate-950 block text-base font-bold">{{ $employee->office->office_name ?? 'LGU' }}</strong>
                </div>
            </div>

            <!-- Date of Filing (Box 3) -->
            <div>
                <label for="date_of_filing" class="block text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                    <i class="ph ph-calendar-check text-gov-700 text-sm"></i>
                    <span>Date of Filing (Box 3)</span>
                    <span class="text-rose-500">*</span>
                </label>
                <input type="date" id="date_of_filing" name="date_of_filing" value="{{ date('Y-m-d') }}" required class="w-full border-2 border-slate-300 rounded-xl px-3.5 py-2.5 text-sm sm:text-base font-bold text-slate-900 focus:border-gov-600 focus:ring-2 focus:ring-gov-600/20 bg-white min-h-[44px]">
            </div>

            <!-- Salary (Box 5) -->
            <div>
                <label for="declared_salary" class="block text-sm font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                    Monthly Salary (Box 5)
                </label>
                <input type="number" id="declared_salary" name="declared_salary" value="{{ old('declared_salary') }}" step="0.01" min="0" inputmode="decimal" placeholder="e.g. 25000.00 (Optional)" class="w-full border-2 border-slate-300 rounded-xl px-3.5 py-2.5 text-sm sm:text-base font-bold font-mono text-slate-900 focus:ring-2 focus:ring-gov-600 bg-white">
                @error('declared_salary')<span class="text-xs font-bold text-rose-600 mt-1 block">{{ $message }}</span>@enderror
            </div>

            <!-- Leave Type Dropdown (Box 6.A) -->
            <div>
                <label for="leave_type_id" class="block text-sm font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                    Type of Leave to be Availed (Box 6.A) <span class="text-rose-500">*</span>
                </label>
                <select id="leave_type_id" name="leave_type_id" required onchange="handleLeaveTypeChange()" onkeydown="if(this.dataset.locked==='true'){event.preventDefault();return false;}" class="w-full border-2 border-slate-300 rounded-xl px-3.5 py-2.5 text-sm sm:text-base font-bold text-slate-900 focus:ring-2 focus:ring-gov-600 bg-white transition">
                    <option value="" disabled selected>— Select Civil Service Leave Category —</option>
                    @foreach($leaveTypes as $type)
                        <option value="{{ $type->leave_type_id }}" data-code="{{ $type->code }}">
                            {{ $type->type_name }} ({{ $type->code }}) — {{ $type->annual_limit ? $type->annual_limit . ' days/yr' : 'Cumulative' }}
                        </option>
                    @endforeach
                </select>

                <!-- Remaining days reminder (revealed after a leave type is selected) -->
                <div id="leave_allowance_hint" class="hidden mt-2 p-3.5 rounded-xl border-2 text-sm font-semibold flex items-start gap-2" role="status">
                    <i id="leave_allowance_icon" class="ph ph-info text-lg shrink-0 mt-0.5"></i>
                    <span id="leave_allowance_text"></span>
                </div>

                <!-- Other Leave Specification (Revealed when 'Others' or 'OTHER' is selected) -->
                <div id="other_leave_container" class="hidden mt-2.5 p-3.5 bg-amber-50 rounded-xl border-2 border-amber-300">
                    <label for="other_leave_type" class="block text-xs sm:text-sm font-bold text-amber-950 uppercase tracking-wider mb-1.5">
                        Specify Other Type of Leave (Box 6.A "Others:") <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="other_leave_type" name="other_leave_type" placeholder="e.g. Wellness Leave, Quarantine Leave" class="w-full border-2 border-amber-300 rounded-xl px-3.5 py-2.5 text-sm sm:text-base font-semibold text-slate-900 focus:ring-2 focus:ring-gov-600 bg-white">
                </div>
            </div>

            <!-- Inclusive Dates Grid (Box 6.C) -->
            <div id="inclusive_dates_container" class="space-y-1.5">
                <label class="block text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider font-mono">
                    6.C Inclusive Dates of Leave <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-3.5">
                    <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-200">
                        <label for="date_from" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <i class="ph ph-calendar text-gov-700 text-sm"></i>
                            <span>Date From</span>
                            <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="date_from" name="date_from" required onchange="calculateDays()" class="w-full border-2 border-slate-300 rounded-xl px-3.5 py-2.5 text-sm sm:text-base font-bold text-slate-900 focus:border-gov-600 focus:ring-2 focus:ring-gov-600/20 bg-white min-h-[44px]">
                    </div>
                    <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-200">
                        <label for="date_to" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                            <i class="ph ph-calendar text-gov-700 text-sm"></i>
                            <span>Date To</span>
                            <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="date_to" name="date_to" required onchange="calculateDays()" class="w-full border-2 border-slate-300 rounded-xl px-3.5 py-2.5 text-sm sm:text-base font-bold text-slate-900 focus:border-gov-600 focus:ring-2 focus:ring-gov-600/20 bg-white min-h-[44px]">
                    </div>
                </div>
            </div>

            <!-- Contextual Monetization Notice (Revealed when monetization is selected) -->
            <div id="monetization_dates_notice" class="hidden p-3 bg-amber-50 rounded-xl border border-amber-300 text-xs text-amber-950 flex items-center gap-2">
                <i class="ph-bold ph-coins text-base text-amber-700 shrink-0"></i>
                <span>No absence dates required for monetization.</span>
            </div>

            <!-- Number of Days (Discrete with 3 decimal precision) -->
            <div>
                <label for="days_requested" id="days_requested_label" class="block text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider mb-1.5 flex items-center gap-1.5">
                    <i class="ph ph-clock text-gov-700 text-sm"></i>
                    <span>Number of Working Days Applied For (Box 6.C)</span>
                    <span class="text-rose-500">*</span>
                </label>
                <input type="number" step="0.125" min="0.500" max="120.000" id="days_requested" name="days_requested" placeholder="e.g. 3.000 or 0.500" required class="w-full border-2 border-slate-300 rounded-xl px-3.5 py-2.5 text-sm sm:text-base font-mono font-bold text-slate-900 focus:border-gov-600 focus:ring-2 focus:ring-gov-600/20 bg-white min-h-[44px]">
                <p id="days_requested_help" class="hidden text-xs text-slate-500 mt-1"></p>
            </div>

            <!-- 6.B Details of Leave Selector (Exact Match to CSC Form 6) -->
            <div class="space-y-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                        6.B Details of Leave <span class="text-rose-500">*</span>
                    </label>
                </div>

                <div>
                    <label for="details_type" class="block text-sm font-bold text-slate-800 mb-1.5">
                        Specification Category:
                    </label>
                    <select id="details_type" name="details_type" required onchange="handleDetailsTypeChange()" onkeydown="if(this.dataset.locked==='true'){event.preventDefault();return false;}" class="w-full border-2 border-slate-300 rounded-xl px-3.5 py-2.5 text-sm sm:text-base font-semibold text-slate-900 focus:ring-2 focus:ring-gov-600 bg-white transition">
                        <optgroup label="In case of Vacation / Special Privilege Leave:">
                            <option value="within_ph" selected>Within the Philippines (Specify Location)</option>
                            <option value="abroad">Abroad (Specify Country/Destination)</option>
                        </optgroup>
                        <optgroup label="In case of Sick Leave:">
                            <option value="in_hospital">In Hospital (Specify Illness & Hospital)</option>
                            <option value="out_patient">Out Patient (Specify Illness)</option>
                        </optgroup>
                        <optgroup label="In case of Special Leave Benefits for Women:">
                            <option value="women_illness">Special Leave for Women (Specify Illness/Procedure)</option>
                        </optgroup>
                        <optgroup label="In case of Study Leave:">
                            <option value="study_master">Completion of Master's Degree</option>
                            <option value="study_bar">BAR / Board Examination Review</option>
                        </optgroup>
                        <optgroup label="Other Purpose / Conversion:">
                            <option value="monetization">Regular Monetization of Leave Credits</option>
                            <option value="monetization_special">Special Monetization of Leave Credits (50% or more)</option>
                            <option value="terminal">Terminal Leave</option>
                        </optgroup>
                    </select>
                </div>

                <div id="details_specify_container">
                    <label for="details_specify" id="details_specify_label" class="block text-sm font-bold text-slate-800 mb-1.5">
                        Location / Hospital / Remarks:
                    </label>
                    <input type="text" id="details_specify" name="details_specify" value="Basud, Camarines Norte" placeholder="e.g. Basud, Camarines Norte" class="w-full border-2 border-slate-300 rounded-xl px-3.5 py-2.5 text-sm sm:text-base font-semibold text-slate-900 focus:ring-2 focus:ring-gov-600 bg-white">
                </div>
            </div>

            <!-- =============================================================== -->
            <!-- CONDITIONAL SUPPORTING DOCUMENT UPLOAD SECTION (CSC Form No. 6) -->
            <!-- =============================================================== -->
            <div id="supporting_documents_section" class="hidden p-3.5 sm:p-4 rounded-xl border-2 border-amber-300 bg-amber-50/70 space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-200 text-amber-950 border border-amber-400">
                            <i class="ph-bold ph-paperclip text-sm shrink-0"></i>
                            <span>Supporting Documents Required (CSC Form No. 6)</span>
                        </div>
                        <h4 class="text-sm sm:text-base font-extrabold text-slate-900 mt-1.5" id="req_docs_title">Required Attachments</h4>
                        <p class="text-xs sm:text-sm font-semibold text-slate-700 mt-0.5 leading-relaxed" id="req_docs_reason"></p>
                    </div>
                </div>

                <!-- Specific Required Documents List -->
                <div class="bg-white p-3.5 rounded-xl border border-amber-200 shadow-2xs">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800 block mb-1.5">Mandatory Supporting Document(s):</span>
                    <ul class="space-y-1.5 text-xs sm:text-sm font-bold text-slate-900 list-disc ml-5 leading-relaxed" id="req_docs_list">
                    </ul>
                    <div class="text-[11px] font-mono font-bold text-slate-500 mt-2" id="req_docs_legal"></div>
                </div>

                <!-- File Picker -->
                <div>
                    <label for="supporting_documents" class="block text-xs sm:text-sm font-extrabold text-slate-900 mb-1">
                        Upload Attachments (PDF, JPG, PNG) <span class="text-rose-600">*</span>
                    </label>
                    <input 
                        type="file" 
                        id="supporting_documents" 
                        name="attachments[]" 
                        multiple 
                        accept=".pdf,.jpg,.jpeg,.png"
                        onchange="handleFileSelection(event)"
                        class="w-full max-w-full text-xs sm:text-sm font-bold text-slate-800 border-2 border-amber-300 rounded-xl px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-gov-600 file:mr-2 sm:file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-black file:bg-gov-700 file:text-white hover:file:bg-gov-800 file:cursor-pointer cursor-pointer"
                    >
                    <span class="text-xs font-semibold text-slate-600 mt-1 block">
                        Accepted: <strong>PDF, JPG, PNG</strong> • Max: <strong>25MB</strong> (PDF), <strong>10MB</strong> (Images) • Multiple files allowed.
                    </span>
                </div>

                <!-- Live Selected Files Preview -->
                <div id="selected_files_container" class="hidden space-y-1.5 pt-1 border-t border-amber-200">
                    <div class="text-xs font-extrabold uppercase tracking-wider text-slate-800">Selected Files (<span id="selected_files_count">0</span>):</div>
                    <div id="selected_files_list" class="space-y-1.5 max-h-40 overflow-y-auto pr-1"></div>
                </div>
            </div>

            <!-- Commutation Request Checkbox (Box 6.D) -->
            <div class="flex items-start gap-2.5 pt-1.5">
                <input type="checkbox" id="commutation_requested" name="commutation_requested" value="1" class="mt-0.5 w-5 h-5 rounded border-2 border-slate-400 text-gov-700 focus:ring-gov-600 cursor-pointer">
                <label for="commutation_requested" class="text-sm text-slate-800 leading-snug cursor-pointer">
                    <strong>Request Commutation</strong>
                </label>
            </div>
        </div>

        <!-- Pinned Modal Footer -->
        <div class="bg-slate-50 px-5 sm:px-6 py-3.5 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 shrink-0">
            <button type="button" onclick="closeFilingModal()" class="w-full sm:w-auto px-5 py-2.5 border-2 border-slate-300 rounded-xl text-sm font-bold text-slate-700 hover:bg-slate-100 transition min-h-[44px] cursor-pointer">
                Cancel
            </button>
            <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-gov-700 hover:bg-gov-800 text-white rounded-xl text-sm sm:text-base font-bold shadow-sm transition flex items-center justify-center gap-2 min-h-[44px] cursor-pointer">
                <i class="ph-bold ph-paper-plane-tilt text-lg"></i>
                <span>Generate CS Form 6 & QR</span>
            </button>
        </div>
    </form>
</div>
</div>

<!-- ======================================================================= -->
<!-- MODAL 2: VISUAL ROUTING TIMELINE & SLA TRACKER                          -->
<!-- ======================================================================= -->
<div id="trackerModal" onclick="if(event.target === this) closeTrackerModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[92vh] sm:max-h-[88vh] shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col my-auto">
        <!-- Tracker Header (Pinned) -->
        <div class="bg-slate-900 text-white px-5 sm:px-6 py-4 border-b border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-gov-700 flex items-center justify-center text-white font-bold text-lg shrink-0">
                    <i class="ph-bold ph-path"></i>
                </div>
                <div>
                    <div class="text-xs text-slate-400">Routing Timeline</div>
                    <h3 class="text-base font-bold text-white font-mono" id="modalRefCode">LV-2026-XXXX</h3>
                </div>
            </div>
            <button type="button" onclick="closeTrackerModal()" class="text-slate-400 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-gov-600 rounded-lg p-1 cursor-pointer" aria-label="Close tracking modal">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>

        <!-- Tracker Body (Scrollable within viewport) -->
        <div class="p-4 sm:p-6 overflow-y-auto flex-1 min-h-0">
            <!-- Loading Indicator -->
            <div id="trackerLoading" class="py-12 text-center text-slate-500">
                <i class="ph ph-spinner animate-spin text-3xl text-gov-700 mb-2"></i>
                <div class="text-sm font-semibold">Loading...</div>
            </div>

            <!-- Content Area -->
            <div id="trackerContent" class="space-y-5 sm:space-y-6 hidden">
                <!-- Status & SLA Overview Banner -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 bg-slate-50 p-3.5 sm:p-4 rounded-xl border border-slate-200">
                    <div>
                        <div class="text-[11px] sm:text-xs text-slate-500 uppercase tracking-wider font-semibold">Current Physical Office</div>
                        <div class="text-sm sm:text-base font-bold text-slate-900 mt-0.5 flex items-center gap-1.5" id="modalLocation">
                            <i class="ph-fill ph-map-pin text-amber-600"></i>
                            <span>Office Location</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-[11px] sm:text-xs text-slate-500 uppercase tracking-wider font-semibold">Statutory SLA Compliance</div>
                        <div class="mt-0.5" id="modalSlaBadge">
                            <!-- Filled dynamically -->
                        </div>
                    </div>
                </div>

                <!-- Digital Supporting Documents Attachments -->
                <div id="modalAttachmentsCard" class="hidden bg-slate-50 p-3.5 sm:p-4 rounded-xl border border-slate-200">
                    <div class="flex items-center justify-between mb-2.5 pb-2 border-b border-slate-200">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                            <i class="ph-bold ph-paperclip text-base text-gov-700"></i>
                            <span>Attached Supporting Documents</span>
                        </h4>
                        <span class="text-xs font-bold text-slate-600 font-mono" id="modalAttachmentsCount"></span>
                    </div>
                    <div id="modalAttachmentsList" class="space-y-2"></div>
                </div>

                <!-- Visual Step-by-Step Chronological Timeline -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Routing Steps</h4>
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200" id="timelineList">
                        <!-- Filled dynamically -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Tracker Footer (Pinned) -->
        <div class="bg-slate-50 px-5 sm:px-6 py-3 border-t border-slate-200 flex justify-end items-center text-xs text-slate-500 shrink-0">
            <button type="button" onclick="closeTrackerModal()" class="w-full sm:w-auto px-4 py-2 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 font-bold text-slate-700 transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL 3: CIVIL SERVICE LEAVE ENTITLEMENTS & REMAINING LIMITS            -->
<!-- ======================================================================= -->
<div id="limitsModal" onclick="if(event.target === this) closeLimitsModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[88vh] shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col my-auto">
        <!-- Modal Header (Pinned) -->
        <div class="bg-gov-navy text-white px-5 py-3.5 border-b border-slate-800 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2">
                <i class="ph ph-scales text-xl text-slate-300"></i>
                <div>
                    <h3 class="text-base font-bold">Civil Service Leave Entitlements & Remaining Limits</h3>
                </div>
            </div>
            <button type="button" onclick="closeLimitsModal()" class="text-slate-400 hover:text-white transition focus:outline-none focus:ring-2 focus:ring-gov-600 rounded-lg p-1" aria-label="Close limits modal">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable, fits within viewport) -->
        <div class="p-5 overflow-y-auto flex-1 space-y-4">

            <div class="overflow-x-auto border border-slate-200 rounded-xl max-h-64 overflow-y-auto">
                <table class="w-full text-left text-sm text-slate-800">
                    <thead class="bg-slate-100 text-xs font-extrabold uppercase text-slate-800 border-b border-slate-300 sticky top-0 z-10 tracking-wider">
                        <tr>
                            <th class="py-3 px-3.5">Leave Category</th>
                            <th class="py-3 px-3.5">Statutory Limit</th>
                            <th class="py-3 px-3.5">Nature</th>
                            <th class="py-3 px-3.5 text-right">Used / Availed</th>
                            <th class="py-3 px-3.5 text-right">Remaining</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-mono">
                        @foreach($leaveAllowances as $typeId => $allowance)
                            @php
                                $rem = $allowance['remaining'];
                                $isCredit = $allowance['is_credit'];
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3 px-3.5 font-sans font-medium text-slate-950">
                                    <div class="font-bold text-sm sm:text-base">{{ $allowance['name'] }}</div>
                                    <div class="text-xs text-slate-600 font-mono font-bold">[{{ $allowance['code'] }}]</div>
                                </td>
                                <td class="py-3 px-3.5 whitespace-nowrap text-slate-900 font-sans font-bold text-sm">
                                    @if($allowance['limit'] !== null)
                                        {{ rtrim(rtrim(number_format($allowance['limit'], 3), '0'), '.') }} days
                                    @elseif($isCredit)
                                        15 days/yr
                                    @else
                                        As Specified
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 font-sans whitespace-nowrap text-xs">
                                    @if($isCredit)
                                        <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-900 font-bold border border-blue-300">Cumulative</span>
                                    @elseif($allowance['period'] === 'per event')
                                        <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-900 font-bold border border-amber-300">Per Event</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-slate-200 text-slate-900 font-bold border border-slate-300">Annually</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3.5 text-right text-slate-900 font-bold text-sm">
                                    {{ number_format($allowance['used'], 3) }}
                                </td>
                                <td class="py-3 px-3.5 text-right font-bold">
                                    @if($rem === null)
                                        <span class="text-slate-500 font-sans text-sm">—</span>
                                    @elseif($rem > 0)
                                        <span class="inline-flex items-center gap-1 text-emerald-900 bg-emerald-100 px-2.5 py-1 rounded-lg border border-emerald-300 font-extrabold text-sm">
                                            {{ number_format($rem, 3) }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-rose-900 bg-rose-100 px-2.5 py-1 rounded-lg border border-rose-300 font-extrabold text-sm">
                                            0.000
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="text-xs sm:text-sm text-slate-700 font-medium leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-200">
                <strong class="text-slate-900">Note on Vacation & Sick Leave:</strong> If your certified VL or SL credits are exhausted, applications are still accepted and recorded as <em>Absence/Undertime Without Pay</em> on your Leave Record Card.
            </div>

            <!-- Section 2: Civil Service Leave Monetization Limits & Conversion Thresholds -->
            <div class="pt-3 border-t border-slate-200">
                <div class="flex items-center justify-between mb-2.5">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                        <i class="ph-bold ph-coins text-base text-amber-600"></i>
                        <span>Leave Monetization Limits (Civil Service Commission Rule XVI)</span>
                    </h4>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <!-- Regular Monetization Card in Limits Modal -->
                    <div class="p-3.5 rounded-xl border bg-white {{ $monetizationInfo['regular']['is_eligible'] ? 'border-amber-300 ring-1 ring-amber-200 shadow-2xs' : 'border-slate-200 bg-slate-50/60' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $monetizationInfo['regular']['is_eligible'] ? 'bg-amber-500' : 'bg-slate-300' }}"></span>
                                    <span class="text-xs font-bold text-slate-900">Regular Monetization</span>
                                </div>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded {{ $monetizationInfo['regular']['is_eligible'] ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $monetizationInfo['regular']['is_eligible'] ? 'ELIGIBLE' : 'NOT ELIGIBLE' }}
                                </span>
                            </div>

                            <div class="space-y-1 text-xs">
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-500">Statutory Limit:</span>
                                    <span class="font-bold text-slate-800">10 to 30 whole days / year</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-500">Minimum VL Required:</span>
                                    <span class="font-mono font-bold text-slate-800">15.000 days</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-500">Current VL Balance:</span>
                                    <span class="font-mono font-bold text-slate-800">{{ number_format($monetizationInfo['current_vl'], 3) }} days</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-500">Mandatory Retained Buffer:</span>
                                    <span class="font-mono font-bold text-slate-800">5.000 VL days</span>
                                </div>
                                <div class="flex items-center justify-between py-1">
                                    <span class="text-slate-700 font-semibold">Allowable Days:</span>
                                    @if($monetizationInfo['regular']['is_eligible'])
                                        <span class="font-mono font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                            10 to {{ number_format($monetizationInfo['regular']['max_days'], 0) }} days
                                        </span>
                                    @else
                                        <span class="text-rose-600 font-medium">0 days (need {{ number_format($monetizationInfo['regular']['remaining_needed'], 3) }} more VL)</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($monetizationInfo['regular']['is_eligible'])
                            <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-end text-[11px]">
                                <button type="button" onclick="closeLimitsModal(); openMonetizationFilingModal('regular')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-amber-900 bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-lg transition cursor-pointer">
                                    <i class="ph-bold ph-coins text-sm"></i>
                                    <span>Apply Regular</span>
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- Special Monetization Card in Limits Modal -->
                    <div class="p-3.5 rounded-xl border bg-white {{ $monetizationInfo['special']['is_eligible'] ? 'border-gov-300 ring-1 ring-gov-200 shadow-2xs' : 'border-slate-200 bg-slate-50/60' }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full {{ $monetizationInfo['special']['is_eligible'] ? 'bg-gov-700' : 'bg-slate-300' }}"></span>
                                    <span class="text-xs font-bold text-slate-900">Special Monetization</span>
                                </div>
                                <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded {{ $monetizationInfo['special']['is_eligible'] ? 'bg-gov-100 text-gov-800 border border-gov-200' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $monetizationInfo['special']['is_eligible'] ? 'ELIGIBLE' : 'NOT ELIGIBLE' }}
                                </span>
                            </div>

                            <div class="space-y-1 text-xs">
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-500">Statutory Threshold:</span>
                                    <span class="font-bold text-slate-800">50% or more of VL + SL</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-500">Combined Total Balance:</span>
                                    <span class="font-mono font-bold text-slate-800">{{ number_format($monetizationInfo['combined_total'], 3) }} days</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-500">Minimum Balance Required:</span>
                                    <span class="font-mono font-bold text-slate-800">> 30.000 days</span>
                                </div>
                                <div class="flex items-center justify-between py-1 border-b border-slate-100">
                                    <span class="text-slate-500">Mandatory Retained Buffer:</span>
                                    <span class="font-mono font-bold text-slate-800">5.000 combined days</span>
                                </div>
                                <div class="flex items-center justify-between py-1">
                                    <span class="text-slate-700 font-semibold">Allowable Days:</span>
                                    @if($monetizationInfo['special']['is_eligible'])
                                        <span class="font-mono font-bold text-gov-700 bg-gov-50 px-2 py-0.5 rounded border border-gov-200">
                                            {{ number_format($monetizationInfo['special']['min_days'], 0) }} to {{ number_format($monetizationInfo['special']['max_days'], 0) }} days
                                        </span>
                                    @else
                                        <span class="text-rose-600 font-medium">0 days (need {{ number_format($monetizationInfo['special']['remaining_needed'], 3) }} more total)</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($monetizationInfo['special']['is_eligible'])
                            <div class="mt-3 pt-2 border-t border-slate-100 flex items-center justify-end text-[11px]">
                                <button type="button" onclick="closeLimitsModal(); openMonetizationFilingModal('special')" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-white bg-gov-700 hover:bg-gov-800 border border-gov-700 rounded-lg transition cursor-pointer">
                                    <i class="ph-bold ph-coins text-sm"></i>
                                    <span>Apply Special</span>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Section 3: All Leaves Taken by Employee -->
            <div class="pt-3 border-t border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                        <i class="ph-bold ph-calendar-check text-gov-700"></i>
                        <span>All Leaves Taken by {{ $employee->full_name }} ({{ count($takenItems) }} Records)</span>
                    </h4>
                </div>

                @if(count($takenItems) > 0)
                    <div class="border border-slate-200 rounded-xl overflow-hidden max-h-48 overflow-y-auto">
                        <table class="w-full text-left text-xs text-slate-700">
                            <thead class="bg-slate-50 text-[11px] font-semibold uppercase text-slate-500 border-b border-slate-200 sticky top-0 z-10 bg-slate-50 tracking-wider">
                                <tr>
                                    <th class="py-2.5 px-3">Leave Type</th>
                                    <th class="py-2.5 px-3">Period / Dates</th>
                                    <th class="py-2.5 px-3 text-right">Days Taken</th>
                                    <th class="py-2.5 px-3">Source & Reference</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-mono">
                                @foreach($takenItems as $item)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-2 px-3 font-sans font-medium text-slate-900">
                                            <span class="font-bold">{{ $item['leave_name'] }}</span>
                                            <span class="text-[10px] text-slate-400 font-mono block">[{{ $item['code'] }}]</span>
                                        </td>
                                        <td class="py-2 px-3 text-slate-700 font-sans whitespace-nowrap">
                                            {{ $item['period'] }}
                                        </td>
                                        <td class="py-2 px-3 text-right font-bold text-slate-900 whitespace-nowrap">
                                            {{ number_format($item['days'], 3) }} d
                                        </td>
                                        <td class="py-2 px-3 text-slate-500 font-sans text-[11px]">
                                            <span class="font-medium text-slate-700">{{ $item['source'] }}</span>
                                            @if(!empty($item['date_action']))
                                                <span class="text-slate-400 block text-[10px]">{{ $item['date_action'] }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 text-center text-xs text-slate-500">
                        No leaves have been taken or recorded for this employee yet.
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal Footer (Pinned) -->
        <div class="bg-slate-50 px-5 py-3 border-t border-slate-200 flex justify-end shrink-0">
            <button type="button" onclick="closeLimitsModal()" class="px-4 py-1.5 bg-white border border-slate-300 rounded-lg hover:bg-slate-100 font-medium text-slate-700 text-xs transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- MODAL 4: CANCEL LEAVE APPLICATION (7-DAY STATUTORY WINDOW)              -->
<!-- ======================================================================= -->
<div id="cancelModal" onclick="if(event.target === this) closeCancelModal()" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
    <div class="bg-white rounded-2xl max-w-md w-full max-h-[92vh] sm:max-h-[88vh] shadow-2xl border border-slate-200 overflow-hidden transform transition-all flex flex-col my-auto">
        <!-- Header (Pinned) -->
        <div class="bg-rose-50 px-5 sm:px-6 py-4 border-b border-rose-100 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5 text-rose-800">
                <div class="w-9 h-9 rounded-full bg-rose-100 flex items-center justify-center text-rose-700 shrink-0">
                    <i class="ph-bold ph-warning-octagon text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-rose-950">Cancel Leave Application</h3>
                </div>
            </div>
            <button type="button" onclick="closeCancelModal()" class="text-slate-500 hover:text-slate-800 transition p-1.5 rounded-lg cursor-pointer" aria-label="Close dialog">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>

        <!-- Form Body (Scrollable with pinned footer) -->
        <form id="cancelApplicationForm" method="POST" action="" class="flex flex-col flex-1 min-h-0 overflow-hidden">
            @csrf
            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
                <div class="text-sm font-semibold text-slate-800 leading-relaxed">
                    Are you sure you want to cancel leave application <strong id="cancelModalRefCode" class="font-mono text-base font-black text-slate-950">LV-2026-XXXX</strong>?
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-300 text-xs font-semibold text-amber-950 flex items-start gap-2">
                    <i class="ph ph-clock text-base text-amber-800 shrink-0 mt-0.5"></i>
                    <div id="cancelModalNotice" class="leading-relaxed">
                        Allowed within 7 working days of filing. Once cancelled, this cannot be undone.
                    </div>
                </div>

                <div>
                    <label for="cancellation_reason" class="block text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                        Reason for Cancellation <span class="text-slate-500 font-normal normal-case">(Optional)</span>
                    </label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="2" placeholder="e.g. Schedule changed, emergency resolved, personal reasons..." class="w-full border-2 border-slate-300 rounded-xl p-3 text-xs sm:text-sm font-semibold text-slate-900 focus:ring-2 focus:ring-rose-500 bg-white"></textarea>
                </div>
            </div>

            <!-- Modal Actions (Pinned) -->
            <div class="bg-slate-50 px-5 sm:px-6 py-3.5 border-t border-slate-200 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 shrink-0">
                <button type="button" onclick="closeCancelModal()" class="w-full sm:w-auto px-5 py-2.5 border-2 border-slate-300 rounded-xl text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-100 transition cursor-pointer min-h-[44px]">
                    Keep Application
                </button>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs sm:text-sm font-bold shadow-xs transition flex items-center justify-center gap-2 cursor-pointer min-h-[44px]">
                    <i class="ph-bold ph-trash text-base sm:text-lg"></i>
                    <span>Confirm Cancellation</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const leaveAllowances = @json($leaveAllowances);

    @if(session('new_application_id'))
        // Auto-open printable CS Form 6 in new window upon filing
        try {
            window.open("{{ route('forms.form6.print', session('new_application_id')) }}", "_blank");
        } catch (e) {
            console.log("Popup blocked:", e);
        }
    @endif

    function openLimitsModal() {
        document.getElementById('limitsModal').classList.remove('hidden');
    }

    function closeLimitsModal() {
        document.getElementById('limitsModal').classList.add('hidden');
    }

    function openCancelModal(appId, refCode, deadline, daysLeft, status) {
        document.getElementById('cancelModalRefCode').innerText = refCode;

        let notice = `Allowed until <strong>${deadline}</strong> (${daysLeft} working day${daysLeft > 1 ? 's' : ''} left).`;
        if (status === 'Approved') {
            notice += ` Deducted credits will be restored.`;
        }
        document.getElementById('cancelModalNotice').innerHTML = notice;
        document.getElementById('cancelApplicationForm').action = `/employee/leave/${appId}/cancel`;
        document.getElementById('cancellation_reason').value = '';
        document.getElementById('cancelModal').classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }

    function openFilingOptionModal() {
        document.getElementById('filingOptionModal').classList.remove('hidden');
    }

    function closeFilingOptionModal() {
        document.getElementById('filingOptionModal').classList.add('hidden');
    }

    function chooseApplicationType(type) {
        closeFilingOptionModal();
        if (type === 'monetization_regular') {
            openMonetizationFilingModal('regular');
        } else if (type === 'monetization_special') {
            openMonetizationFilingModal('special');
        } else if (type === 'monetization') {
            openMonetizationFilingModal('regular');
        } else {
            openFilingModal();
            // Reset to default leave type if was on monetization
            const leaveTypeSelect = document.getElementById('leave_type_id');
            if (leaveTypeSelect) {
                const currentOpt = leaveTypeSelect.options[leaveTypeSelect.selectedIndex];
                if (currentOpt && currentOpt.getAttribute('data-code') === 'MONETIZATION') {
                    for (let i = 0; i < leaveTypeSelect.options.length; i++) {
                        if (leaveTypeSelect.options[i].getAttribute('data-code') === 'VL') {
                            leaveTypeSelect.selectedIndex = i;
                            break;
                        }
                    }
                    handleLeaveTypeChange();
                }
            }
        }
    }

    function setSelectLocked(selectEl, isLocked) {
        if (!selectEl) return;
        if (isLocked) {
            selectEl.classList.add('pointer-events-none', 'bg-slate-100', 'text-slate-700', 'cursor-not-allowed', 'border-slate-300', 'select-none');
            selectEl.setAttribute('tabindex', '-1');
            selectEl.setAttribute('aria-readonly', 'true');
            selectEl.dataset.locked = 'true';
        } else {
            selectEl.classList.remove('pointer-events-none', 'bg-slate-100', 'text-slate-700', 'cursor-not-allowed', 'border-slate-300', 'select-none');
            selectEl.removeAttribute('tabindex');
            selectEl.removeAttribute('aria-readonly');
            delete selectEl.dataset.locked;
        }
    }

    function openFilingModal() {
        document.getElementById('filingModal').classList.remove('hidden');

        // Unlock Box 6.A and Box 6.B so they are fully interactive for normal leave filing
        const leaveTypeSelect = document.getElementById('leave_type_id');
        const detailsSelect = document.getElementById('details_type');
        if (leaveTypeSelect) setSelectLocked(leaveTypeSelect, false);
        if (detailsSelect) setSelectLocked(detailsSelect, false);

        // Restore normal leave filing view
        const datesContainer = document.getElementById('inclusive_dates_container');
        const monetizationNotice = document.getElementById('monetization_dates_notice');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        const daysInput = document.getElementById('days_requested');
        const daysHelp = document.getElementById('days_requested_help');
        const commutationCheckbox = document.getElementById('commutation_requested');

        if (datesContainer) datesContainer.classList.remove('hidden');
        if (monetizationNotice) monetizationNotice.classList.add('hidden');
        if (dateFromInput) dateFromInput.required = true;
        if (dateToInput) dateToInput.required = true;

        if (daysInput) {
            daysInput.step = '0.125';
            daysInput.min = '0.500';
            daysInput.max = '120.000';
            daysInput.placeholder = 'e.g. 3.000 or 0.500';
        }
        if (daysHelp) {
            daysHelp.innerText = 'Full days (1.000) or half-days (0.500). Starla fractional tables apply for tardiness/LWOP.';
            daysHelp.className = 'text-xs font-semibold text-slate-600 mt-1 block';
        }
        if (commutationCheckbox) {
            commutationCheckbox.checked = false;
        }
    }

    const monetizationData = @json($monetizationInfo);

    function openMonetizationFilingModal(subType = 'regular') {
        const regData = monetizationData.regular || {};
        const specData = monetizationData.special || {};

        // Automatic validation against Civil Service Commission rules before opening form
        if (subType === 'regular') {
            if (!regData.is_eligible) {
                if (typeof window.showClactsToast === 'function') {
                    window.showClactsToast(`Regular Monetization requires at least 15.000 VL days (need ${regData.remaining_needed.toFixed(3)} more).`, 'warning');
                }
                return;
            }
        } else if (subType === 'special') {
            if (!specData.is_eligible) {
                if (typeof window.showClactsToast === 'function') {
                    window.showClactsToast(`Special Monetization requires > 30.000 combined VL + SL days (need ${specData.remaining_needed.toFixed(3)} more).`, 'warning');
                }
                return;
            }
        }

        // Open CS Form No. 6 modal
        document.getElementById('filingModal').classList.remove('hidden');

        // Pre-fill filing date (defaults to today)
        const dateOfFiling = document.getElementById('date_of_filing');
        const todayStr = new Date().toISOString().split('T')[0];
        if (dateOfFiling && !dateOfFiling.value) {
            dateOfFiling.value = todayStr;
        }
        const filingDateVal = dateOfFiling ? dateOfFiling.value : todayStr;

        // Automatically hide Date From and Date To fields since monetization converts credits into cash without requiring an absence from work
        const datesContainer = document.getElementById('inclusive_dates_container');
        const monetizationNotice = document.getElementById('monetization_dates_notice');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');

        if (datesContainer) datesContainer.classList.add('hidden');
        if (monetizationNotice) monetizationNotice.classList.remove('hidden');
        if (dateFromInput) {
            dateFromInput.required = false;
            dateFromInput.value = filingDateVal;
        }
        if (dateToInput) {
            dateToInput.required = false;
            dateToInput.value = filingDateVal;
        }

        // Commutation is automatically marked as requested for monetization applications
        const commutationCheckbox = document.getElementById('commutation_requested');
        if (commutationCheckbox) {
            commutationCheckbox.checked = true;
        }

        // Select leave type: MONETIZATION and lock Box 6.A so it cannot be clicked
        const leaveTypeSelect = document.getElementById('leave_type_id');
        if (leaveTypeSelect) {
            let monetizationOption = null;
            for (let i = 0; i < leaveTypeSelect.options.length; i++) {
                const optCode = (leaveTypeSelect.options[i].getAttribute('data-code') || '').toUpperCase();
                const optText = (leaveTypeSelect.options[i].text || '').toUpperCase();
                if (optCode === 'MONETIZATION' || optText.includes('MONETIZATION')) {
                    monetizationOption = leaveTypeSelect.options[i];
                    break;
                }
            }
            if (monetizationOption) {
                leaveTypeSelect.value = monetizationOption.value;
            } else {
                for (let i = 0; i < leaveTypeSelect.options.length; i++) {
                    if (leaveTypeSelect.options[i].getAttribute('data-code') === 'VL') {
                        leaveTypeSelect.value = leaveTypeSelect.options[i].value;
                        break;
                    }
                }
            }
            handleLeaveTypeChange();
            // Lock Box 6.A dropdown so it cannot be clicked
            setSelectLocked(leaveTypeSelect, true);
        }

        // Set Specification Category in 6.B and lock so it cannot be clicked
        const detailsSelect = document.getElementById('details_type');
        if (detailsSelect) {
            const isSpecial = (subType === 'special');
            detailsSelect.value = isSpecial ? 'monetization_special' : 'monetization';
            handleDetailsTypeChange();
            // Lock Box 6.B dropdown so it cannot be clicked
            setSelectLocked(detailsSelect, true);
        }

        // Configure Number of Working Days Applied For input and contextual helper text
        const daysInput = document.getElementById('days_requested');
        const daysHelp = document.getElementById('days_requested_help');

        if (subType === 'regular') {
            const minDays = 10;
            const maxDays = Math.min(30, Math.floor(regData.max_days || 0));
            const defaultDays = Math.min(10, maxDays);

            if (daysInput) {
                daysInput.step = '1';
                daysInput.min = minDays;
                daysInput.max = maxDays;
                daysInput.value = defaultDays;
                daysInput.placeholder = `10 to ${maxDays}`;
            }
            if (daysHelp) {
                daysHelp.innerHTML = `<span class="text-amber-800 font-bold flex items-center gap-1.5"><i class="ph-bold ph-info text-sm shrink-0"></i><span>Enter <strong>whole days only</strong> (e.g. 10, 15, 20) between <strong>${minDays}</strong> and <strong>${maxDays}</strong> days. Capped at 30 days while retaining your mandatory 5.000-day Vacation Leave buffer.</span></span>`;
                daysHelp.className = 'text-xs mt-1 block';
            }
        } else {
            const minDays = Math.ceil(specData.min_days || (monetizationData.combined_total * 0.5));
            const maxDays = Math.floor(specData.max_days || (monetizationData.combined_total - 5.0));
            if (daysInput) {
                daysInput.step = '1';
                daysInput.min = minDays;
                daysInput.max = maxDays;
                daysInput.value = minDays;
                daysInput.placeholder = `${minDays} to ${maxDays}`;
            }
            if (daysHelp) {
                daysHelp.innerHTML = `<span class="text-purple-800 font-bold flex items-center gap-1.5"><i class="ph-bold ph-info text-sm shrink-0"></i><span>Special Monetization: Enter <strong>whole days only</strong> between <strong>${minDays}</strong> days (min 50% of ${monetizationData.combined_total.toFixed(3)} credits) and <strong>${maxDays}</strong> days (retaining mandatory 5.000 buffer).</span></span>`;
                daysHelp.className = 'text-xs mt-1 block';
            }
        }

        // Trigger supporting document rules evaluator
        evaluateSupportingDocumentRequirements();
    }

    function closeFilingModal() {
        document.getElementById('filingModal').classList.add('hidden');
    }

    function calculateDays() {
        const detailsSelect = document.getElementById('details_type');
        if (detailsSelect && (detailsSelect.value === 'monetization' || detailsSelect.value === 'monetization_special')) {
            return;
        }
        const fromInput = document.getElementById('date_from');
        const toInput = document.getElementById('date_to');
        const from = fromInput ? fromInput.value : '';
        const to = toInput ? toInput.value : '';

        // Dynamically enforce that date_to cannot precede date_from
        if (from && toInput) {
            toInput.min = from;
            if (to && to < from) {
                toInput.value = from;
            }
        }

        const effectiveTo = toInput ? toInput.value : to;
        if (from && effectiveTo) {
            const start = new Date(from);
            const end = new Date(effectiveTo);
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                const daysInput = document.getElementById('days_requested');
                if (daysInput) daysInput.value = diffDays.toFixed(3);
            }
        }
        evaluateSupportingDocumentRequirements();
    }

    function handleLeaveTypeChange() {
        const select = document.getElementById('leave_type_id');
        const selectedOpt = select.options[select.selectedIndex];
        const code = selectedOpt ? selectedOpt.getAttribute('data-code') : '';
        const detailsSelect = document.getElementById('details_type');
        const otherContainer = document.getElementById('other_leave_container');
        const otherInput = document.getElementById('other_leave_type');

        // Reveal Other Leave Specification field if 'OTHER' or 'WL' is chosen
        if (code === 'OTHER' || code === 'WL') {
            otherContainer.classList.remove('hidden');
            otherInput.required = true;
            if (code === 'WL' && !otherInput.value) {
                otherInput.value = 'Wellness Leave (CSC MC No. 01, s. 2026)';
            }
        } else {
            otherContainer.classList.add('hidden');
            otherInput.required = false;
        }

        if (code === 'MONETIZATION') {
            if (detailsSelect.value !== 'monetization_special') {
                detailsSelect.value = 'monetization';
            }
        } else if (code === 'TERMINAL') {
            detailsSelect.value = 'terminal';
        } else if (code === 'SLBW') {
            detailsSelect.value = 'women_illness';
        } else if (code === 'STUDY') {
            detailsSelect.value = 'study_master';
        } else if (code === 'SL') {
            if (detailsSelect.value !== 'in_hospital' && detailsSelect.value !== 'out_patient') {
                detailsSelect.value = 'out_patient';
            }
        } else if (code === 'VL' || code === 'FL' || code === 'SPL') {
            if (detailsSelect.value !== 'within_ph' && detailsSelect.value !== 'abroad' && detailsSelect.value !== 'monetization' && detailsSelect.value !== 'monetization_special') {
                detailsSelect.value = 'within_ph';
            }
        }

        // Dynamic Leave Allowance / Limit Hint
        const hintContainer = document.getElementById('leave_allowance_hint');
        const hintText = document.getElementById('leave_allowance_text');
        const hintIcon = document.getElementById('leave_allowance_icon');

        if (hintContainer && select.value && leaveAllowances[select.value]) {
            const allowance = leaveAllowances[select.value];
            hintContainer.classList.remove('hidden');

            if (allowance.is_credit) {
                // Vacation Leave or Sick Leave
                hintContainer.className = 'mt-2 p-3 rounded-lg border text-xs flex items-start gap-2 bg-slate-50 border-slate-200 text-slate-700';
                hintIcon.className = 'ph ph-info text-base text-gov-700 shrink-0';
                hintText.innerHTML = `<strong>${allowance.name} (${allowance.code}):</strong> Certified balance is <span class="font-mono font-bold text-slate-900">${allowance.remaining.toFixed(3)} day(s)</span>. <em>(Per CSC policy, if credits are exhausted or 0, applications are still accepted and recorded under Absence/Undertime Without Pay).</em>`;
            } else if (allowance.limit !== null) {
                const rem = allowance.remaining;
                if (rem > 0) {
                    hintContainer.className = 'mt-2 p-3 rounded-lg border text-xs flex items-start gap-2 bg-emerald-50 border-emerald-200 text-emerald-900';
                    hintIcon.className = 'ph ph-check-circle text-base text-emerald-600 shrink-0';
                    hintText.innerHTML = `<strong>${allowance.name} Limit Check:</strong> You have <span class="font-mono font-bold text-emerald-800">${rem.toFixed(3)} day(s) remaining</span> (out of ${allowance.limit.toFixed(3)} days ${allowance.period} limit). ${allowance.used > 0 ? `Already used/filed: <span class="font-mono font-semibold">${allowance.used.toFixed(3)} day(s)</span>.` : ''}`;
                } else {
                    hintContainer.className = 'mt-2 p-3 rounded-lg border text-xs flex items-start gap-2 bg-rose-50 border-rose-200 text-rose-900';
                    hintIcon.className = 'ph ph-warning-circle text-base text-rose-600 shrink-0';
                    hintText.innerHTML = `<strong>Limit Reached:</strong> You have <span class="font-mono font-bold text-rose-800">0.000 day(s) remaining</span> for ${allowance.name} (full ${allowance.limit.toFixed(3)} days ${allowance.period} limit used). Further applications will be blocked.`;
                }
            } else {
                hintContainer.className = 'mt-2 p-3 rounded-lg border text-xs flex items-start gap-2 bg-amber-50 border-amber-200 text-amber-900';
                hintIcon.className = 'ph ph-info text-base text-amber-600 shrink-0';
                hintText.innerHTML = `<strong>${allowance.name}:</strong> Subject to municipal office approval and necessary supporting requirements.`;
            }
        } else if (hintContainer) {
            hintContainer.classList.add('hidden');
        }

        handleDetailsTypeChange();
        evaluateSupportingDocumentRequirements();
    }

    function handleDetailsTypeChange() {
        const type = document.getElementById('details_type').value;
        const specifyInput = document.getElementById('details_specify');
        const specifyLabel = document.getElementById('details_specify_label');
        const container = document.getElementById('details_specify_container');

        const datesContainer = document.getElementById('inclusive_dates_container');
        const monetizationNotice = document.getElementById('monetization_dates_notice');
        const dateFromInput = document.getElementById('date_from');
        const dateToInput = document.getElementById('date_to');
        const dateOfFiling = document.getElementById('date_of_filing');
        const todayStr = new Date().toISOString().split('T')[0];
        const filingDateVal = dateOfFiling ? dateOfFiling.value : todayStr;
        const daysInput = document.getElementById('days_requested');
        const daysHelp = document.getElementById('days_requested_help');

        if (type === 'within_ph') {
            container.style.display = 'block';
            specifyLabel.innerText = 'Specify Destination / Town (Within Philippines):';
            specifyInput.placeholder = 'e.g. Basud, Camarines Norte';
            if (!specifyInput.value || specifyInput.value.includes('Illness') || specifyInput.value.includes('Hospital')) {
                specifyInput.value = 'Basud, Camarines Norte';
            }
        } else if (type === 'abroad') {
            container.style.display = 'block';
            specifyLabel.innerText = 'Specify Foreign Country / Destination (Abroad):';
            specifyInput.placeholder = 'e.g. Tokyo, Japan';
            if (specifyInput.value === 'Basud, Camarines Norte') specifyInput.value = '';
        } else if (type === 'in_hospital') {
            container.style.display = 'block';
            specifyLabel.innerText = 'Specify Illness & Hospital:';
            specifyInput.placeholder = 'e.g. In-hospital: Dengue Fever (Daet Provincial Hospital)';
            if (specifyInput.value === 'Basud, Camarines Norte') specifyInput.value = '';
        } else if (type === 'out_patient') {
            container.style.display = 'block';
            specifyLabel.innerText = 'Specify Illness (Out-Patient Consultation / Rest):';
            specifyInput.placeholder = 'e.g. Acute Bronchitis / Severe Flu (Home Medication)';
            if (specifyInput.value === 'Basud, Camarines Norte') specifyInput.value = '';
        } else if (type === 'women_illness') {
            container.style.display = 'block';
            specifyLabel.innerText = 'Specify Gynecological Disorder / Surgical Procedure:';
            specifyInput.placeholder = 'e.g. Post-operative recovery / Medical certification attached';
            if (specifyInput.value === 'Basud, Camarines Norte') specifyInput.value = '';
        } else if (type === 'study_master' || type === 'study_bar' || type === 'monetization' || type === 'monetization_special' || type === 'terminal') {
            container.style.display = 'none';
            specifyInput.value = '';
        }

        // Handle Monetization vs Standard Leave field visibility and limits
        if (type === 'monetization' || type === 'monetization_special') {
            const commutationCheckbox = document.getElementById('commutation_requested');
            if (commutationCheckbox) commutationCheckbox.checked = true;

            if (datesContainer) datesContainer.classList.add('hidden');
            if (monetizationNotice) monetizationNotice.classList.remove('hidden');
            if (dateFromInput) {
                dateFromInput.required = false;
                dateFromInput.value = filingDateVal;
            }
            if (dateToInput) {
                dateToInput.required = false;
                dateToInput.value = filingDateVal;
            }

            if (type === 'monetization') {
                const regData = monetizationData.regular || {};
                const minDays = 10;
                const maxDays = Math.min(30, Math.floor(regData.max_days || 0));
                if (daysInput) {
                    daysInput.step = '1';
                    daysInput.min = minDays;
                    daysInput.max = maxDays;
                    if (!daysInput.value || parseFloat(daysInput.value) < minDays || parseFloat(daysInput.value) > maxDays) {
                        daysInput.value = Math.min(10, maxDays);
                    }
                }
                if (daysHelp) {
                    daysHelp.innerHTML = `<span class="text-amber-800 font-bold flex items-center gap-1.5"><i class="ph-bold ph-info text-sm shrink-0"></i><span>Enter <strong>whole days only</strong> (e.g. 10, 15, 20) between <strong>${minDays}</strong> and <strong>${maxDays}</strong> days. Capped at 30 days while retaining your mandatory 5.000-day Vacation Leave buffer.</span></span>`;
                    daysHelp.className = 'text-xs mt-1 block';
                }
            } else {
                const specData = monetizationData.special || {};
                const minDays = Math.ceil(specData.min_days || (monetizationData.combined_total * 0.5));
                const maxDays = Math.floor(specData.max_days || (monetizationData.combined_total - 5.0));
                if (daysInput) {
                    daysInput.step = '1';
                    daysInput.min = minDays;
                    daysInput.max = maxDays;
                    if (!daysInput.value || parseFloat(daysInput.value) < minDays || parseFloat(daysInput.value) > maxDays) {
                        daysInput.value = minDays;
                    }
                }
                if (daysHelp) {
                    daysHelp.innerHTML = `<span class="text-purple-800 font-bold flex items-center gap-1.5"><i class="ph-bold ph-info text-sm shrink-0"></i><span>Special Monetization: Enter <strong>whole days only</strong> between <strong>${minDays}</strong> days (min 50% of ${monetizationData.combined_total.toFixed(3)} credits) and <strong>${maxDays}</strong> days (retaining mandatory 5.000 buffer).</span></span>`;
                    daysHelp.className = 'text-xs mt-1 block';
                }
            }
        } else {
            if (datesContainer) datesContainer.classList.remove('hidden');
            if (monetizationNotice) monetizationNotice.classList.add('hidden');
            if (dateFromInput) dateFromInput.required = true;
            if (dateToInput) dateToInput.required = true;

            if (daysInput) {
                daysInput.step = '0.125';
                daysInput.min = '0.500';
                daysInput.max = '120.000';
            }
            if (daysHelp) {
                daysHelp.innerText = 'Full days (1.000) or half-days (0.500). Starla fractional tables apply for tardiness/LWOP.';
                daysHelp.className = 'text-xs font-semibold text-slate-600 mt-1 block';
            }
        }

        evaluateSupportingDocumentRequirements();
    }

    // =========================================================================
    // CONDITIONAL SUPPORTING DOCUMENT EVALUATOR (CSC Form No. 6 Rules)
    // =========================================================================
    function evaluateSupportingDocumentRequirements() {
        const select = document.getElementById('leave_type_id');
        const selectedOpt = select.options[select.selectedIndex];
        const code = selectedOpt ? selectedOpt.getAttribute('data-code') : '';
        const detailsType = document.getElementById('details_type').value;
        const daysRequested = parseFloat(document.getElementById('days_requested').value) || 0;
        const dateFrom = document.getElementById('date_from').value;
        const dateOfFiling = document.getElementById('date_of_filing').value;
        const otherLeaveType = (document.getElementById('other_leave_type')?.value || '').toLowerCase();

        const section = document.getElementById('supporting_documents_section');
        const titleEl = document.getElementById('req_docs_title');
        const reasonEl = document.getElementById('req_docs_reason');
        const listEl = document.getElementById('req_docs_list');
        const legalEl = document.getElementById('req_docs_legal');
        if (!section) return;

        if (!code) {
            section.classList.add('hidden');
            return;
        }

        let required = false;
        let title = '';
        let reason = '';
        let docs = [];
        let legal = '';

        // 13. Monetization of Leave Credits (Special vs Regular)
        if (code === 'MONETIZATION' || detailsType === 'monetization' || detailsType === 'monetization_special' || otherLeaveType.includes('monetiz')) {
            required = true;
            if (detailsType === 'monetization_special' || otherLeaveType.includes('special')) {
                title = 'Special Monetization (50% or more of combined VL & SL)';
                reason = 'Special monetization of 50% or more requires written justification to the Municipal Mayor and mandatory evidence of medical emergency, calamity, or educational expenses.';
                docs = [
                    'Formal letter request addressed to the Municipal Mayor stating justifiable ground(s)',
                    'Mandatory Proof for Chosen Ground: Medical Certificate / Hospital Bill (Health/Medical), Barangay Calamity Certificate (Disaster/Calamity), or School Assessment / Enrollment Billing (Education)',
                    'Certified Summary of Leave Credit Balances (certified by HRMU)'
                ];
                legal = 'CSC Omnibus Rules on Leave (Rule XVI, Sec. 22-23) & CSC Form No. 6, Item 13';
            } else {
                title = 'Regular Monetization of Leave Credits (10 to 30 Days)';
                reason = 'Regular monetization requires a formal letter request addressed to the Municipal Mayor pursuant to CSC Omnibus Rules.';
                docs = [
                    'Formal letter request addressed to the Municipal Mayor requesting monetization of leave credits',
                    'Certified Summary of Leave Credit Balances (certified by HRMU)'
                ];
                legal = 'CSC Omnibus Rules on Leave (Rule XVI, Sec. 22-23) & CSC Form No. 6, Item 13';
            }
        }
        // 14. Terminal Leave
        else if (code === 'TERMINAL' || detailsType === 'terminal' || otherLeaveType.includes('terminal')) {
            required = true;
            title = 'Terminal Leave';
            reason = 'Terminal leave requires official proof of cessation of government service and full municipal clearance.';
            docs = [
                'Approved Letter of Resignation, Retirement Application, or Official Separation Order from service',
                'Municipal Certificate of Clearance (money, property, and administrative work accountabilities)'
            ];
            legal = 'CSC Omnibus Rules on Leave (Rule XVI, Sec. 35) & CSC Form No. 6, Item 14';
        }
        // 1. Vacation Leave
        else if (code === 'VL') {
            if (detailsType === 'abroad') {
                required = true;
                title = 'Vacation Leave (Abroad)';
                reason = 'Vacation leave spent abroad requires securing official foreign travel authority and accountability clearance.';
                docs = [
                    'Formal Letter Request for Foreign Travel Authority addressed to the Municipal Mayor',
                    'Certificate of Clearance from Money and Property Accountabilities'
                ];
                legal = 'CSC Omnibus Rules on Leave (Sec. 51, Rule XVI) & CSC Form No. 6, Item 1';
            }
        }
        // 2. Mandatory / Forced Leave
        else if (code === 'FL') {
            required = false; // No supporting documents required
        }
        // 3. Sick Leave
        else if (code === 'SL') {
            const isInAdvance = (dateFrom && dateOfFiling) ? (new Date(dateFrom) > new Date(dateOfFiling)) : false;
            const isExceeding5 = daysRequested > 5.0;
            const isInHospital = (detailsType === 'in_hospital');

            if (isExceeding5 || isInAdvance || isInHospital) {
                required = true;
                title = 'Sick Leave';
                const triggers = [];
                if (isExceeding5) triggers.push(`exceeding 5 working days (${daysRequested.toFixed(1)} days)`);
                if (isInAdvance) triggers.push('filed in advance of medical leave');
                if (isInHospital) triggers.push('hospital confinement');
                reason = `Application requires medical certification due to: ${triggers.join(', ')}.`;
                docs = [
                    'Medical Certificate filled out and signed by attending physician (with license and PTR number)',
                    'Affidavit executed by applicant (strictly if medical consultation was not availed of, pursuant to CSC instructions)'
                ];
                if (isInHospital) {
                    docs.push('Hospital Discharge Order / Clinical Summary (for in-hospital confinement)');
                }
                legal = 'CSC Omnibus Rules on Leave (Sec. 43, Rule XVI) & CSC Form No. 6, Item 3';
            }
        }
        // 4. Maternity Leave
        else if (code === 'ML') {
            required = true;
            title = 'Maternity Leave (105 Days)';
            reason = 'Mandatory proof of pregnancy and allocation notice under RA 11210.';
            docs = [
                'Proof of Pregnancy (Ultrasound report or Doctor’s medical certificate on expected date of delivery)',
                'Accomplished Notice of Allocation of Maternity Leave Credits (CS Form No. 6a, if applicable)'
            ];
            legal = 'R.A. No. 11210 / CSC MC No. 005, s. 2019 & CSC Form No. 6, Item 4';
        }
        // 5. Paternity Leave
        else if (code === 'PL') {
            required = true;
            title = 'Paternity Leave (7 Days)';
            reason = 'Mandatory proof of child delivery and marriage contract under RA 8187.';
            docs = [
                'Proof of child’s delivery (authenticated Birth Certificate or Hospital Delivery Certificate)',
                'Marriage Contract (certifying legitimate marriage with lawful cohabiting spouse)'
            ];
            legal = 'R.A. No. 8187 / CSC MC No. 71, s. 1998 & CSC Form No. 6, Item 5';
        }
        // 6. Special Privilege Leave
        else if (code === 'SPL') {
            if (detailsType === 'abroad') {
                required = true;
                title = 'Special Privilege Leave (Abroad)';
                reason = 'Special privilege leave spent abroad requires securing official foreign travel authority.';
                docs = [
                    'Formal Letter Request for Foreign Travel Authority addressed to the Municipal Mayor',
                    'Certificate of Clearance from Money and Property Accountabilities'
                ];
                legal = 'CSC Omnibus Rules on Leave (Sec. 21, Rule XVI) & CSC Form No. 6, Item 6';
            }
        }
        // 7. Solo Parent Leave
        else if (code === 'SOLO') {
            required = true;
            title = 'Solo Parent Leave (7 Days)';
            reason = 'Mandatory validation of solo parent eligibility under RA 8972 / RA 11861.';
            docs = [
                'Valid / Updated Solo Parent Identification Card (issued by MSWDO)'
            ];
            legal = 'R.A. No. 8972 / R.A. No. 11861 & CSC Form No. 6, Item 7';
        }
        // 8. Study Leave
        else if (code === 'STUDY') {
            required = true;
            title = 'Study Leave (Up to 6 Months)';
            reason = 'Requires approved municipal study contract and enrollment credentials.';
            docs = [
                'Approved Study Leave Contract / Agreement between the Municipal Mayor and the employee',
                'Proof of Enrollment, Acceptance, or Admission in Master’s Degree program or Bar/Board Examination Review Center'
            ];
            legal = 'CSC Omnibus Rules on Leave (Sec. 68, Rule XVI) & CSC Form No. 6, Item 8';
        }
        // 9. 10-Day VAWC Leave
        else if (code === 'VAWC') {
            required = true;
            title = '10-Day VAWC Leave (RA 9262)';
            reason = 'Mandatory protective order or police report verification pursuant to CSC MC No. 15, s. 2005.';
            docs = [
                'Barangay Protection Order (BPO) obtained from the barangay, OR',
                'Temporary/Permanent Protection Order (TPO/PPO) issued by the court, OR',
                'Certification of filed protection order from Barangay / Prosecutor / Court, OR',
                'Police Blotter/Report specifying occurrence of violence accompanied by Medical Certificate'
            ];
            legal = 'R.A. No. 9262 / CSC MC No. 15, s. 2005 & CSC Form No. 6, Item 9';
        }
        // 10. Rehabilitation Leave
        else if (code === 'REHAB') {
            required = true;
            title = 'Rehabilitation Privilege (Up to 6 Months)';
            reason = 'Work-connected disability/injury requires incident reports and physician concurrence.';
            docs = [
                'Formal letter request supported by Police Report or Incident Report on the work-connected accident',
                'Medical Certificate specifying nature of injuries, course of treatment, and recuperation period',
                'Written concurrence of government physician'
            ];
            legal = 'CSC Omnibus Rules on Leave (Sec. 55, Rule XVI) & CSC Form No. 6, Item 10';
        }
        // 11. Special Leave Benefits for Women
        else if (code === 'SLBW') {
            required = true;
            title = 'Special Leave Benefits for Women (Up to 2 Months)';
            reason = 'Magna Carta of Women requires surgical clinical summary and histopathological findings.';
            docs = [
                'Medical Certificate filled out by attending surgeon / gynecologist',
                'Clinical Summary reflecting gynecological disorder addressed by the surgery',
                'Histopathological Report and Operative Confinement Summary',
                'Estimated period of recuperation certified by attending surgeon'
            ];
            legal = 'R.A. No. 9710 / CSC MC No. 25, s. 2010 & CSC Form No. 6, Item 11';
        }
        // 12. Special Emergency (Calamity) Leave
        else if (code === 'CALAMITY') {
            required = true;
            title = 'Special Emergency / Calamity Leave (Up to 5 Days)';
            reason = 'Requires proof that employee’s residence was directly affected within a declared calamity zone.';
            docs = [
                'Official Declaration of State of Calamity (Presidential Proclamation, NDRRMC, or LGU Resolution)',
                'Barangay Certification of Affected Residence / Photos of damaged property'
            ];
            legal = 'CSC MC No. 2, s. 2012, as amended & CSC Form No. 6, Item 12';
        }
        // 15. Adoption Leave
        else if (code === 'ADOPT') {
            required = true;
            title = 'Adoption Leave (Up to 60 Days)';
            reason = 'Mandatory proof of DSWD pre-adoptive placement authority under RA 8552 / RA 11642.';
            docs = [
                'Authenticated copy of the Pre-Adoptive Placement Authority issued by DSWD'
            ];
            legal = 'R.A. No. 8552 / R.A. No. 11642 & CSC Form No. 6, Item 15';
        }

        if (required) {
            section.classList.remove('hidden');
            titleEl.textContent = title;
            reasonEl.textContent = reason;
            legalEl.textContent = legal;
            listEl.innerHTML = docs.map(d => `<li>${d}</li>`).join('');
        } else {
            section.classList.add('hidden');
        }
    }

    function handleFileSelection(event) {
        const input = event.target;
        const container = document.getElementById('selected_files_container');
        const list = document.getElementById('selected_files_list');
        const countEl = document.getElementById('selected_files_count');

        if (!input.files || input.files.length === 0) {
            container.classList.add('hidden');
            list.innerHTML = '';
            return;
        }

        container.classList.remove('hidden');
        countEl.textContent = input.files.length;
        list.innerHTML = '';

        const maxDocBytes = 25 * 1024 * 1024; // 25MB
        const maxImgBytes = 10 * 1024 * 1024; // 10MB

        Array.from(input.files).forEach((file) => {
            const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
            const limit = isPdf ? maxDocBytes : maxImgBytes;
            const limitLabel = isPdf ? '25 MB' : '10 MB';
            const isOver = file.size > limit;
            const isEmpty = file.size === 0;

            const sizeFormatted = (file.size >= 1048576)
                ? (file.size / 1048576).toFixed(2) + ' MB'
                : (file.size / 1024).toFixed(1) + ' KB';

            const item = document.createElement('div');
            item.className = 'flex items-center justify-between gap-2 p-2 rounded-lg border text-xs font-semibold ' +
                (isOver || isEmpty ? 'bg-rose-50 border-rose-300 text-rose-900' : 'bg-slate-50 border-slate-200 text-slate-800');

            item.innerHTML = `
                <div class="flex items-center gap-2 min-w-0 flex-1 truncate">
                    <i class="${isPdf ? 'ph-bold ph-file-pdf text-rose-600 text-base' : 'ph-bold ph-file-image text-blue-600 text-base'} shrink-0"></i>
                    <span class="truncate font-bold">${file.name}</span>
                    <span class="text-[11px] text-slate-500 font-mono shrink-0">(${sizeFormatted})</span>
                </div>
                ${isOver ? `<span class="text-rose-600 font-bold shrink-0 text-[11px]">Exceeds ${limitLabel}!</span>` : ''}
                ${isEmpty ? `<span class="text-rose-600 font-bold shrink-0 text-[11px]">Empty file!</span>` : ''}
            `;
            list.appendChild(item);
        });
    }

    window.activeTrackerAppId = null;

    function getPreloadedTimeline(applicationId) {
        try {
            const el = document.getElementById('clactsPreloadedTimelines');
            if (!el) return null;
            const map = JSON.parse(el.textContent || '{}');
            return map[String(applicationId)] || map[applicationId] || null;
        } catch (e) {
            return null;
        }
    }

    async function fetchAndRenderTimeline(applicationId) {
        const response = await fetch(`/api/routing/${applicationId}/timeline`, { cache: 'no-store' });
        const data = await response.json();

        if (data.success) {
            renderTimeline(data.data);
        } else {
            if (typeof window.showClactsToast === 'function') {
                window.showClactsToast(data.message || 'Could not load routing timeline.', 'error');
            }
            closeTrackerModal();
        }
    }

    window.refreshActiveTrackerModal = async function() {
        const modal = document.getElementById('trackerModal');
        if (!window.activeTrackerAppId || !modal || modal.classList.contains('hidden')) return;
        try {
            const response = await fetch(`/api/routing/${window.activeTrackerAppId}/timeline`, { cache: 'no-store' });
            const data = await response.json();
            if (data.success) {
                renderTimeline(data.data);
            }
        } catch (err) {}
    };

    async function openTrackerModal(applicationId, refCode) {
        window.activeTrackerAppId = applicationId;
        document.getElementById('modalRefCode').innerText = refCode;
        document.getElementById('trackerModal').classList.remove('hidden');

        const preloaded = getPreloadedTimeline(applicationId);
        if (preloaded) {
            renderTimeline(preloaded);
            return;
        }

        document.getElementById('trackerLoading').classList.remove('hidden');
        document.getElementById('trackerContent').classList.add('hidden');

        try {
            await fetchAndRenderTimeline(applicationId);
        } catch (err) {
            console.error(err);
            if (typeof window.showClactsToast === 'function') {
                window.showClactsToast('Failed to load routing timeline.', 'error');
            }
            closeTrackerModal();
        }
    }

    function closeTrackerModal() {
        window.activeTrackerAppId = null;
        document.getElementById('trackerModal').classList.add('hidden');
    }

    function renderTimeline(info) {
        document.getElementById('trackerLoading').classList.add('hidden');
        document.getElementById('trackerContent').classList.remove('hidden');

        // Location
        document.getElementById('modalLocation').innerHTML = `
            <i class="ph-fill ph-map-pin text-amber-600"></i>
            <span>${info.current_location ? info.current_location.office_name : 'In Transit'}</span>
        `;

        // SLA Badge
        const sla = info.sla_tracker;
        let slaHtml = '';
        if (sla.sla_status === 'BREACHED') {
            slaHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                <i class="ph-bold ph-warning"></i> Breached (${sla.working_days_elapsed} / ${sla.statutory_limit_days} days) — Deemed Approved
            </span>`;
        } else if (sla.sla_status === 'WARNING') {
            slaHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                <i class="ph-bold ph-hourglass"></i> SLA Approaching (${sla.working_days_elapsed} / ${sla.statutory_limit_days} days)
            </span>`;
        } else {
            slaHtml = `<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                <i class="ph-bold ph-check"></i> On Time (${sla.working_days_elapsed} / ${sla.statutory_limit_days} days)
            </span>`;
        }
        document.getElementById('modalSlaBadge').innerHTML = slaHtml;

        // Render Digital Supporting Document Attachments in Tracker
        const attCard = document.getElementById('modalAttachmentsCard');
        const attList = document.getElementById('modalAttachmentsList');
        const attCountEl = document.getElementById('modalAttachmentsCount');
        if (info.attachments && info.attachments.length > 0) {
            attCard.classList.remove('hidden');
            attCountEl.textContent = `${info.attachments.length} document(s)`;
            attList.innerHTML = info.attachments.map(att => {
                return `
                    <div class="bg-white p-3 rounded-xl border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                        <div class="min-w-0 flex-1">
                            <span class="text-xs font-black uppercase tracking-wider text-slate-800">${att.document_label}</span>
                            <div class="text-xs text-slate-600 truncate mt-1 flex items-center gap-1.5">
                                <i class="${att.is_pdf ? 'ph-bold ph-file-pdf text-rose-600' : 'ph-bold ph-file-image text-blue-600'} text-base shrink-0"></i>
                                <span class="font-bold text-slate-900 truncate">${att.original_filename}</span>
                                <span class="text-slate-400 font-mono shrink-0">(${att.size_formatted})</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <a href="${att.view_url}" target="_blank" class="p-2 text-slate-700 hover:text-gov-800 hover:bg-slate-100 rounded-lg border border-slate-300 transition inline-flex items-center justify-center" title="View Document">
                                <i class="ph ph-eye text-base"></i>
                            </a>
                            <a href="${att.download_url}" class="p-2 text-slate-700 hover:text-gov-800 hover:bg-slate-100 rounded-lg border border-slate-300 transition inline-flex items-center justify-center" title="Download Document">
                                <i class="ph ph-download-simple text-base"></i>
                            </a>
                        </div>
                    </div>
                `;
            }).join('');
        } else {
            attCard.classList.add('hidden');
            attList.innerHTML = '';
        }

        // Timeline Items
        const listContainer = document.getElementById('timelineList');
        listContainer.innerHTML = '';

        info.timeline.forEach((step, idx) => {
            const isLast = (idx === info.timeline.length - 1);
            const iconBg = step.action.includes('APPROVED') ? 'bg-emerald-600 text-white' : (isLast ? 'bg-gov-700 text-white' : 'bg-slate-300 text-slate-800');

            const itemHtml = `
                <div class="relative">
                    <div class="absolute -left-6 mt-1 w-6 h-6 rounded-full ${iconBg} flex items-center justify-center text-xs shadow-xs font-bold">
                        ${idx + 1}
                    </div>
                    <div class="bg-white p-4 rounded-xl border-2 border-slate-200 shadow-xs">
                        <div class="flex items-center justify-between text-sm mb-1.5 flex-wrap gap-2">
                            <span class="font-extrabold text-slate-950">${step.title}</span>
                            <span class="text-slate-600 font-mono text-xs font-bold">${step.timestamp} (${step.time_ago})</span>
                        </div>
                        <p class="text-sm text-slate-800 font-medium leading-relaxed">${step.description}</p>
                        ${step.remarks ? `<div class="mt-2 text-xs font-semibold bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-slate-700 italic">“${step.remarks}”</div>` : ''}
                        <div class="mt-2.5 text-xs font-bold text-slate-700 flex items-center gap-3">
                            <span><i class="ph ph-user text-sm"></i> ${step.handler}</span>
                            <span class="text-slate-400">•</span>
                            <span><i class="ph ph-buildings text-sm"></i> ${step.office}</span>
                        </div>
                    </div>
                </div>
            `;
            listContainer.insertAdjacentHTML('beforeend', itemHtml);
        });
    }

    // Toggle collapse/expand of on-screen reminders panel (default: hidden)
    function toggleRemindersCollapse() {
        const list = document.getElementById('remindersCardsList');
        const header = document.getElementById('remindersHeader');
        const icon = document.getElementById('remindersToggleIcon');
        const text = document.getElementById('remindersToggleText');
        if (!list) return;

        if (list.classList.contains('hidden')) {
            list.classList.remove('hidden');
            if (header) {
                header.classList.add('pb-3', 'border-b', 'border-slate-200');
            }
            if (icon) icon.className = 'ph ph-caret-up text-sm';
            if (text) text.textContent = 'Hide';
        } else {
            list.classList.add('hidden');
            if (header) {
                header.classList.remove('pb-3', 'border-b', 'border-slate-200');
            }
            if (icon) icon.className = 'ph ph-caret-down text-sm';
            if (text) text.textContent = 'Show';
        }
    }

    // Dismiss modals when Escape key is pressed
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeFilingModal();
            closeTrackerModal();
            closeLimitsModal();
            closeCancelModal();
        }
    });
</script>
@endsection
