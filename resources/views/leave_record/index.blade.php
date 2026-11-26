@extends('layouts.app')

@section('title', 'Civil Service Leave Record - HRMU Basud')

@section('content')
<div class="space-y-6">

    <!-- Top Action Bar & Employee Selector -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 p-4 sm:p-5 w-full">
        <!-- Row 1: Employee Selector & Primary Action Group -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            
            <!-- Employee Selector & Badges -->
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-700 flex items-center justify-center shrink-0">
                    <i class="ph ph-identification-card text-2xl"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Leave Record Management</span>
                    </div>
                    <form id="employeeSelectForm" method="GET" action="{{ route('hr.leave-records.index') }}" class="flex flex-wrap items-center gap-2 mt-1">
                        <!-- Hidden input containing selected employee ID -->
                        <input type="hidden" name="employee_id" id="selected_employee_id" value="{{ $employee->employee_id }}">

                        <!-- Typeable Searchable Employee Combobox -->
                        <div class="relative min-w-0 w-full sm:w-80 md:w-[440px]" id="employeeComboboxWrapper">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="ph ph-magnifying-glass text-sm"></i>
                                </div>
                                <input 
                                    type="text" 
                                    id="employeeSearchInput" 
                                    autocomplete="off"
                                    spellcheck="false"
                                    value="{{ $employee->full_name }} ({{ $employee->control_no }}) - {{ $employee->office->office_name ?? 'LGU Basud' }}"
                                    placeholder="Type employee name, badge #, or office..." 
                                    class="w-full pl-9 pr-14 py-1.5 text-xs sm:text-sm font-semibold text-slate-900 bg-slate-50 hover:bg-white focus:bg-white border border-slate-300 focus:border-gov-600 rounded-xl focus:ring-2 focus:ring-gov-600/20 focus:outline-none transition shadow-2xs cursor-text"
                                >
                                <div class="absolute inset-y-0 right-0 pr-1.5 flex items-center gap-0.5">
                                    <button 
                                        type="button" 
                                        id="clearEmployeeSearchBtn" 
                                        class="p-1 text-slate-400 hover:text-slate-600 rounded-md transition focus:outline-none cursor-pointer"
                                        title="Clear to type a new search"
                                    >
                                        <i class="ph ph-x text-xs"></i>
                                    </button>
                                    <button 
                                        type="button" 
                                        id="toggleEmployeeDropdownBtn" 
                                        class="p-1 text-slate-400 hover:text-slate-600 rounded-md transition focus:outline-none cursor-pointer"
                                        title="Show all municipal personnel"
                                    >
                                        <i class="ph ph-caret-down text-xs transition-transform duration-150" id="employeeCaretIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Filterable Floating Autocomplete Dropdown List -->
                            <div 
                                id="employeeDropdownList" 
                                class="hidden absolute z-50 left-0 right-0 mt-1 max-h-72 overflow-y-auto bg-white rounded-xl shadow-2xl border border-slate-200 divide-y divide-slate-100 text-xs text-slate-700"
                            ></div>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-semibold text-slate-500 bg-slate-100 rounded-full border border-slate-200 font-mono">
                            {{ $records->count() }} entries
                        </span>
                        
                        <!-- Selected Employee Monthly Accrual Status -->
                        @if($isCurrentMonthAccrued)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-300" title="{{ $currentMonthDate->format('F Y') }} monthly earned credits already posted">
                                <i class="ph ph-check-circle text-emerald-600"></i>
                                <span>{{ $currentMonthDate->format('M Y') }} Accrued</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-300" title="Awaiting {{ $currentMonthDate->format('F Y') }} 1.25 VL/SL credit">
                                <i class="ph ph-clock text-amber-600"></i>
                                <span>Pending {{ $currentMonthDate->format('M Y') }}</span>
                            </span>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Primary Actions for Current Employee -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 shrink-0 w-full sm:w-auto">
                <!-- Print Official Cardstock -->
                <a 
                    href="{{ route('hr.leave-records.print', $employee->employee_id) }}" 
                    target="_blank" 
                    class="px-3.5 py-2 text-xs font-semibold rounded-xl bg-slate-800 hover:bg-slate-900 text-white transition flex items-center justify-center gap-2 shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-700 flex-1 sm:flex-none min-h-[38px]"
                    title="Print Civil Service Form No. 41 Official Cardstock"
                >
                    <i class="ph ph-printer text-sm"></i>
                    <span>Print Cardstock</span>
                </a>


            </div>
        </div>

        <!-- Row 2: Ledger Actions Toolbar -->
        <div class="pt-3 flex flex-wrap items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <!-- Partial Month / Prorated Entry (Table II) -->
                <button 
                    type="button" 
                    onclick="openModal('proratedModal')" 
                    class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 transition flex items-center gap-1.5 shadow-2xs cursor-pointer" 
                    title="Credit earned leave for employees who started mid-month or rendered partial service (CSC Table II)"
                >
                    <i class="ph ph-calendar-plus text-sm text-slate-600"></i>
                    <span>+ Partial Month (Prorated)</span>
                </button>

                <!-- Days of Service (Table II Reference) -->
                <button 
                    type="button" 
                    onclick="openModal('tableIIModal')" 
                    class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 transition flex items-center gap-1.5 shadow-2xs cursor-pointer" 
                    title="View CSC Table II: Daily Leave Credits Earned (1-30 days of service)"
                >
                    <i class="ph ph-table text-sm text-slate-600"></i>
                    <span>Days of Service (Table II)</span>
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Recalculate Balances -->
                <form id="recalculateForm" method="POST" action="{{ route('hr.leave-records.recalculate', $employee->employee_id) }}" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        id="btnRecalculate"
                        class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 transition flex items-center gap-1.5 border border-slate-300 cursor-pointer shadow-2xs disabled:opacity-75 disabled:cursor-not-allowed" 
                        title="Re-computes all subsequent balances in chronological order"
                    >
                        <i id="recalculateIcon" class="ph ph-arrows-clockwise text-sm text-slate-600"></i>
                        <span id="recalculateLabel">Recalculate</span>
                    </button>
                </form>

                <!-- Update All Leave Records (Earned at start of month) -->
                <form method="POST" action="{{ route('hr.leave-records.update-all') }}" class="inline" onsubmit="return confirm('Update all municipal personnel leave records to ensure every month begins with an Earned row (1.25 VL / 1.25 SL) in chronological sequence?');">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">
                    <button 
                        type="submit" 
                        class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 transition flex items-center gap-1.5 border border-slate-300 cursor-pointer shadow-2xs" 
                        title="Ensures all months begin with 'Earned' in Particular and 1.25 in Earned days across all employees"
                    >
                        <i class="ph ph-calendar-check text-sm text-slate-600"></i>
                        <span>Update All</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Official Card Container (Civil Service Form 41) -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        
        <!-- Card Identification Bar -->
        <div class="bg-slate-50 border-b border-slate-200 px-4 sm:px-6 py-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-semibold text-slate-600 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 uppercase tracking-wider font-bold">NAME:</span>
                        <span class="text-slate-900 font-bold uppercase">{{ $employee->full_name }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 uppercase tracking-wider font-bold">OFFICE:</span>
                        <span class="text-slate-900 font-bold uppercase">{{ $employee->office->office_name ?? 'HRMU Basud' }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 uppercase tracking-wider font-bold">CONTROL NO.:</span>
                        <span class="font-mono text-slate-900 font-bold">{{ $employee->control_no }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <div class="px-3 py-1 rounded-lg bg-white border border-slate-200 text-right">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase block">VL Balance</span>
                        <span class="text-sm font-bold font-mono text-slate-900">{{ $latestVlBalance }}</span>
                    </div>
                    <div class="px-3 py-1 rounded-lg bg-white border border-slate-200 text-right">
                        <span class="text-[10px] text-slate-500 font-semibold uppercase block">SL Balance</span>
                        <span class="text-sm font-bold font-mono text-slate-900">{{ $latestSlBalance }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 11-Column Tabular Ledger (Two-Level Header Structure) -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <!-- Level 1 Header -->
                    <tr class="bg-slate-900 text-slate-100 border-b border-slate-800 text-center font-bold font-mono">
                        <th rowspan="2" class="px-3 py-3 border-r border-slate-800 min-w-[100px] text-left uppercase tracking-wider text-xs">PERIOD</th>
                        <th rowspan="2" class="px-3 py-3 border-r border-slate-800 min-w-[140px] text-left uppercase tracking-wider text-xs">PARTICULAR</th>
                        
                        <!-- Vacation Leave Group -->
                        <th colspan="4" class="px-4 py-2 border-r border-slate-800 text-white uppercase tracking-wider text-xs font-extrabold bg-slate-950/60">
                            VACATION LEAVE
                        </th>

                        <!-- Sick Leave Group -->
                        <th colspan="4" class="px-4 py-2 border-r border-slate-800 text-white uppercase tracking-wider text-xs font-extrabold bg-slate-950/60">
                            SICK LEAVE
                        </th>

                        <th rowspan="2" class="px-3 py-3 border-r border-slate-800 min-w-[200px] text-left uppercase tracking-wider text-xs">
                            DATE & ACTION TAKEN ON APPLICATION FOR LEAVE
                        </th>
                        <th rowspan="2" class="px-2 py-3 min-w-[80px] uppercase tracking-wider text-center no-print text-xs">
                            ACTIONS
                        </th>
                    </tr>

                    <!-- Level 2 Header (Subcolumns) -->
                    <tr class="bg-slate-800 text-slate-200 text-center text-[10px] font-bold border-b border-slate-700 font-mono">
                        <!-- VL Subcolumns -->
                        <th class="px-2 py-2 border-r border-slate-700 text-slate-200 min-w-[70px]">EARNED</th>
                        <th class="px-2 py-2 border-r border-slate-700 text-slate-200 min-w-[95px]">ABSENCE / UT W/ PAY</th>
                        <th class="px-2 py-2 border-r border-slate-700 text-amber-300 min-w-[75px] font-extrabold">BALANCE</th>
                        <th class="px-2 py-2 border-r border-slate-800 text-slate-300 min-w-[95px]">ABSENCE W/OUT PAY</th>

                        <!-- SL Subcolumns -->
                        <th class="px-2 py-2 border-r border-slate-700 text-slate-200 min-w-[70px]">EARNED</th>
                        <th class="px-2 py-2 border-r border-slate-700 text-slate-200 min-w-[95px]">ABSENCE / UT W/ PAY</th>
                        <th class="px-2 py-2 border-r border-slate-700 text-emerald-300 min-w-[75px] font-extrabold">BALANCE</th>
                        <th class="px-2 py-2 border-r border-slate-800 text-slate-300 min-w-[95px]">ABSENCE W/OUT PAY</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($records as $rec)
                        @php
$partUpper = strtoupper(trim((string)($rec->particular ?? '')));
                            $isBaseline = str_contains($partUpper, 'BASELINE') || str_contains($partUpper, 'OPENING');
                            $isEarned = str_contains($partUpper, 'EARNED') || (!empty($rec->vl_earned) && empty($rec->vl_absence_with_pay) && empty($rec->sl_absence_with_pay));
                            $isPendingApp = $rec->application_id && ($rec->vl_absence_with_pay === null && $rec->sl_absence_with_pay === null) && str_contains($rec->date_action_taken ?? '', 'Pending');
@endphp
                        <tr class="transition font-sans hover:bg-slate-50 {{ $isEarned || $isBaseline ? 'border-t border-slate-300' : '' }} {{ $loop->iteration % 2 === 0 ? 'bg-slate-50/40' : 'bg-white' }}">
                            
                            <!-- Col 1: Period -->
                            <td class="px-3 py-2.5 border-r border-slate-200 whitespace-nowrap {{ $isEarned || $isBaseline ? 'font-black text-slate-950 text-xs sm:text-sm font-mono' : 'font-normal text-slate-700 pl-4 sm:pl-5 text-xs font-mono' }}">
                                {{ $rec->period }}

                            </td>

                            <!-- Col 2: Particular -->
                            <td class="px-3 py-2.5 border-r border-slate-200 whitespace-nowrap text-xs">
                                @if($isEarned || $isBaseline)
                                    <span class="font-black text-slate-950 uppercase tracking-wide">
                                        {{ $rec->particular }}

                                    </span>
                                @elseif($isPendingApp)
                                    <span class="inline-flex items-center gap-1 font-medium text-slate-700">
                                        <i class="ph ph-hourglass text-slate-500"></i>
                                        <span>{{ $rec->particular }}</span>
                                    </span>
                                @else
                                    <span class="font-normal text-slate-700">
                                        {{ $rec->particular }}

                                    </span>
                                @endif
                            </td>

                            <!-- Col 3: VL Earned -->
                            <td class="px-2 py-2.5 text-center font-mono border-r border-slate-200 {{ $isEarned ? 'font-black text-slate-950 text-sm' : 'text-slate-300 font-normal' }}">
                                {{ !empty($rec->vl_earned) ? $rec->vl_earned : '—' }}

                            </td>

                            <!-- Col 4: VL Absence w/ Pay -->
                            <td class="px-2 py-2.5 text-center font-mono font-medium border-r border-slate-200 {{ !empty($rec->vl_absence_with_pay) ? 'font-semibold text-slate-900' : 'text-slate-300' }}">
                                @if($isPendingApp)
                                    <span class="text-slate-400 italic text-[10px]" title="Application pending physical Mayor approval; credits not prematurely deducted">—</span>
                                @else
                                    {{ !empty($rec->vl_absence_with_pay) ? $rec->vl_absence_with_pay : '—' }}

                                @endif
                            </td>

                            <!-- Col 5: VL Balance -->
                            <td class="px-2 py-2.5 text-center font-mono border-r border-slate-200 text-sm {{ $isEarned || $isBaseline ? 'font-black text-slate-950' : 'font-bold text-slate-900' }}">
                                {{ $rec->vl_balance ?? '—' }}

                            </td>

                            <!-- Col 6: VL Absence w/out Pay -->
                            <td class="px-2 py-2.5 text-center font-mono border-r border-slate-200 {{ !empty($rec->vl_absence_without_pay) ? 'font-semibold text-slate-800' : 'text-slate-300 font-normal' }}">
                                {{ !empty($rec->vl_absence_without_pay) ? $rec->vl_absence_without_pay : '—' }}

                            </td>

                            <!-- Col 7: SL Earned -->
                            <td class="px-2 py-2.5 text-center font-mono border-r border-slate-200 {{ $isEarned ? 'font-black text-slate-950 text-sm' : 'text-slate-300 font-normal' }}">
                                {{ !empty($rec->sl_earned) ? $rec->sl_earned : '—' }}

                            </td>

                            <!-- Col 8: SL Absence w/ Pay -->
                            <td class="px-2 py-2.5 text-center font-mono font-medium border-r border-slate-200 {{ !empty($rec->sl_absence_with_pay) ? 'font-semibold text-slate-900' : 'text-slate-300' }}">
                                @if($isPendingApp)
                                    <span class="text-slate-400 italic text-[10px]" title="Application pending physical Mayor approval; credits not prematurely deducted">—</span>
                                @else
                                    {{ !empty($rec->sl_absence_with_pay) ? $rec->sl_absence_with_pay : '—' }}

                                @endif
                            </td>

                            <!-- Col 9: SL Balance -->
                            <td class="px-2 py-2.5 text-center font-mono border-r border-slate-200 text-sm {{ $isEarned || $isBaseline ? 'font-black text-slate-950' : 'font-bold text-slate-900' }}">
                                {{ $rec->sl_balance ?? '—' }}

                            </td>

                            <!-- Col 10: SL Absence w/out Pay -->
                            <td class="px-2 py-2.5 text-center font-mono border-r border-slate-200 {{ !empty($rec->sl_absence_without_pay) ? 'font-semibold text-slate-800' : 'text-slate-300 font-normal' }}">
                                {{ !empty($rec->sl_absence_without_pay) ? $rec->sl_absence_without_pay : '—' }}

                            </td>

                            <!-- Col 11: Date & Action Taken -->
                            <td class="px-3 py-2.5 text-[11px] border-r border-slate-200 {{ $isEarned || $isBaseline ? 'font-bold text-slate-950' : 'font-normal text-slate-600' }}">
                                @if($isPendingApp)
                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-amber-50 text-amber-900 border border-amber-300" title="Application filed online; awaiting Mayor physical wet signature">
                                        <i class="ph-bold ph-hourglass text-amber-600 shrink-0"></i>
                                        <span>{{ $rec->date_action_taken }}</span>
                                    </div>
                                @elseif($rec->application_id && str_contains($rec->date_action_taken ?? '', 'Approved'))
                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-[11px] font-semibold bg-emerald-50 text-emerald-900 border border-emerald-300">
                                        <i class="ph-bold ph-check-circle text-emerald-600 shrink-0"></i>
                                        <span>{{ $rec->date_action_taken }}</span>
                                    </div>
                                @else
                                    {{ $rec->date_action_taken ?? '' }}

                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="px-2 py-2 text-center whitespace-nowrap no-print">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Edit Trigger -->
                                    <button type="button" onclick="openEditModal({{ json_encode($rec) }})" class="p-1 rounded text-slate-500 hover:text-amber-600 hover:bg-slate-100" title="Edit row">
                                        <i class="ph ph-pencil-simple text-sm"></i>
                                    </button>
                                    
                                    <!-- Delete Row -->
                                    <form method="POST" action="{{ route('hr.leave-records.destroy', $rec->record_id) }}" onsubmit="return confirm('Remove row #{{ $rec->row_order }} ({{ $rec->particular }})? Subsequent balances will automatically be recalculated.');" class="inline">
                                        @csrf
                                        @php
echo method_field('DELETE');
@endphp
                                        <button type="submit" class="p-1 rounded text-slate-500 hover:text-rose-600 hover:bg-slate-100" title="Delete row">
                                            <i class="ph ph-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-6 py-12 text-center text-slate-500">
                                <i class="ph ph-files text-3xl text-slate-400 block mb-2"></i>
                                <span class="font-bold text-sm text-slate-700">No Leave Record Entries Found</span>
                                <p class="text-xs text-slate-500 mt-1">Click "Add Record Entry" above to post the first certified transaction or monthly accrual.</p>
                            </td>
                        </tr>
                    @endif
                </tbody>
                <!-- Footer: Total Carried Forwarded (Matches Scanned Card) -->
                <tfoot>
                    <tr class="bg-slate-100 font-extrabold text-slate-900 border-t-2 border-slate-400 text-xs">
                        <td colspan="2" class="px-4 py-3 uppercase tracking-wider text-slate-700 border-r border-slate-300">
                            Total Carried Forwarded
                        </td>
                        <td class="px-2 py-3 text-center font-mono text-emerald-800 border-r border-slate-300">
                            —
                        </td>
                        <td class="px-2 py-3 text-center font-mono text-rose-700 border-r border-slate-300">
                            —
                        </td>
                        <td class="px-2 py-3 text-center font-mono text-emerald-900 bg-emerald-100/60 border-r border-slate-300 text-sm">
                            {{ $latestVlBalance }}

                        </td>
                        <td class="px-2 py-3 text-center font-mono border-r border-slate-300">
                            —
                        </td>
                        <td class="px-2 py-3 text-center font-mono text-sky-800 border-r border-slate-300">
                            —
                        </td>
                        <td class="px-2 py-3 text-center font-mono text-rose-700 border-r border-slate-300">
                            —
                        </td>
                        <td class="px-2 py-3 text-center font-mono text-sky-900 bg-sky-100/60 border-r border-slate-300 text-sm">
                            {{ $latestSlBalance }}

                        </td>
                        <td class="px-2 py-3 text-center font-mono border-r border-slate-300">
                            —
                        </td>
                        <td colspan="2" class="px-3 py-3 text-slate-500 text-[11px] font-normal italic">
                            Certified Balances as of latest posting date.
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
     


<!-- Floating Recalculation Success Dialog -->
@if(session('recalculate_success'))
    @php
        $recalcInfo = session('recalculate_success');
    @endphp
    <div 
        id="recalcSuccessDialog" 
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="recalcSuccessTitle"
        onclick="if (event.target === this) { const d = document.getElementById('recalcSuccessDialog'); if(d) d.remove(); }"
        class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4 transition-all duration-200"
    >
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border-t-4 border-t-emerald-500 border-x border-b border-slate-200 overflow-hidden transform animate-in fade-in zoom-in-95 duration-200 relative z-10" onclick="event.stopPropagation()">
            <!-- Header -->
            <div class="bg-slate-50 px-5 py-3.5 border-b border-slate-200/90 flex items-center justify-end">
                <button 
                    type="button" 
                    id="btnCloseRecalcDialogHeader"
                    onclick="(function(){ const d = document.getElementById('recalcSuccessDialog'); if(d) d.remove(); })()"
                    class="text-slate-400 hover:text-slate-700 p-1.5 rounded-xl hover:bg-slate-200 transition cursor-pointer focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    aria-label="Close dialog"
                >
                    <i class="ph ph-x text-lg pointer-events-none"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-5 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 border-4 border-emerald-200 shadow-xs">
                    <i class="ph-bold ph-check text-3xl"></i>
                </div>

                <div>
                    <h3 id="recalcSuccessTitle" class="text-xl font-black text-slate-900 tracking-tight">
                        Recalculation Successful!
                    </h3>
                    <p class="text-xs text-slate-600 mt-1.5 font-medium leading-relaxed">
                        All leave ledger entries for <strong class="text-slate-900 font-bold">{{ $recalcInfo['employee_name'] ?? $employee->full_name }}</strong> were verified and re-computed in strict chronological order.
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3.5 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end">
                <button 
                    type="button" 
                    id="btnDoneRecalcDialog"
                    onclick="(function(){ const d = document.getElementById('recalcSuccessDialog'); if(d) d.remove(); })()"
                    class="w-full sm:w-auto px-6 py-2 rounded-xl bg-gov-700 hover:bg-gov-800 text-white text-xs font-bold shadow-xs transition cursor-pointer flex items-center justify-center min-h-[38px] focus:outline-none focus:ring-2 focus:ring-gov-600"
                >
                    <span>OK, Done</span>
                </button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const d = document.getElementById('recalcSuccessDialog');
                if (d) d.remove();
            }
        });
    </script>
@endif

<!-- Fullscreen Recalculation Loading Backdrop -->
<div 
    id="recalculateLoadingBackdrop" 
    class="fixed inset-0 z-50 hidden bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4 transition-all duration-300"
    role="status"
    aria-live="polite"
>
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 sm:p-8 shadow-2xl border border-slate-200 text-center space-y-4 animate-in fade-in zoom-in-95 duration-200">
        <div class="relative inline-flex items-center justify-center">
            <div class="w-16 h-16 rounded-full border-4 border-slate-100 border-t-gov-700 animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center text-gov-700">
                <i class="ph-bold ph-arrows-clockwise text-xl animate-pulse"></i>
            </div>
        </div>
        <div>
            <h4 class="text-base font-black text-slate-900 tracking-tight">Recalculating Ledger Balances</h4>
            <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                Applying Table II & Table IV conversion formulas in chronological sequence...
            </p>
        </div>
        <div class="pt-1 flex items-center justify-center gap-1.5 text-[11px] font-mono text-slate-400">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
            <span>Processing Civil Service Form 41</span>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 1. ADD RECORD ROW MODAL                                                   -->



<!-- ========================================================================= -->
<div id="addRowModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full max-h-[90vh] flex flex-col p-4 sm:p-6 space-y-4 my-auto overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 shrink-0">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-plus-circle text-amber-500 text-xl"></i>
                <h3 class="text-base font-bold text-slate-900">Add Leave Record Row</h3>
            </div>
            <button type="button" onclick="closeModal('addRowModal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('hr.leave-records.store') }}" class="space-y-4 overflow-y-auto flex-1 pr-1" id="addRowForm">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">

            <!-- Switch to DTR Starla Engine Banner -->
            <div class="bg-gov-50 border border-gov-200 rounded-xl p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                <div class="flex items-center gap-2 text-gov-900 font-semibold">
                    <i class="ph-bold ph-calculator text-gov-700 text-base"></i>
                    <span>Need to encode semi-monthly DTR tardiness, undertime, or unexcused absences?</span>
                </div>
                <a href="{{ route('hr.tardiness.create') }}" class="px-2.5 py-1 text-xs font-bold rounded bg-gov-700 hover:bg-gov-800 text-white transition whitespace-nowrap">
                    Go to Starla DTR Engine →
                </a>
            </div>

            <!-- Quick Presets -->
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Quick Presets</label>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="setPreset('Earned', '1.25', '1.25')" class="px-2.5 py-1 text-xs font-bold rounded bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 cursor-pointer">
                        + Earned 1.25 (Both)
                    </button>
                    <button type="button" onclick="setPreset('FL (1)')" class="px-2.5 py-1 text-xs font-bold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 cursor-pointer">
                        FL (1)
                    </button>
                    <button type="button" onclick="setPreset('FL (2)')" class="px-2.5 py-1 text-xs font-bold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 cursor-pointer">
                        FL (2)
                    </button>
                    <button type="button" onclick="setPreset('SL (2)')" class="px-2.5 py-1 text-xs font-bold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 cursor-pointer">
                        SL (2)
                    </button>
                    <button type="button" onclick="setPreset('SPL (3)')" class="px-2.5 py-1 text-xs font-bold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 cursor-pointer">
                        SPL (3)
                    </button>
                    <button type="button" onclick="setPreset('WL (1)')" class="px-2.5 py-1 text-xs font-bold rounded bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 cursor-pointer">
                        WL (1)
                    </button>
                    <button type="button" onclick="setPreset('L (1 hr and 3 mins)')" class="px-2.5 py-1 text-xs font-bold rounded bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 font-mono cursor-pointer">
                        L (1 hr and 3 mins)
                    </button>
                    <button type="button" onclick="setPreset('UT (2 hrs and 30 mins)')" class="px-2.5 py-1 text-xs font-bold rounded bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200 font-mono cursor-pointer">
                        UT (2 hrs and 30 mins)
                    </button>
                </div>
            </div>

            <!-- Period & Particular Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Period <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="period" id="add_period" value="{{ Carbon\Carbon::now()->format('F Y') }}" required class="w-full text-sm font-semibold text-slate-900 border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none" placeholder="e.g. July, August 2026">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Particular <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="particular" id="add_particular" oninput="checkParticular(this.value)" required class="w-full text-sm font-bold text-slate-900 border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-amber-500 focus:outline-none" placeholder="e.g. FL (2), SL (3), L (1 hr and 3 mins)">
                </div>
            </div>

            <!-- Live Parser Feedback Badge -->
            <div id="particularFeedback" class="hidden text-xs rounded-lg p-2.5 border"></div>

            <!-- Column Values Grid -->
            <div class="border border-slate-200 rounded-xl p-3 sm:p-4 bg-slate-50 space-y-3">
                <div class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center justify-between">
                    <span>Ledger Columns (Leave blank to auto-fill)</span>
                    <span class="text-[10px] text-amber-700 font-semibold">Exact Decimals</span>
                </div>

                <!-- Vacation Leave Sub-columns -->
                <div class="bg-white p-3 rounded-lg border border-slate-200 space-y-2">
                    <div class="text-[11px] font-extrabold text-emerald-800 flex items-center gap-1.5">
                        <i class="ph-fill ph-circle text-[8px] text-emerald-600"></i>
                        <span>VACATION LEAVE COLUMNS</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">VL Earned</label>
                            <input type="text" name="vl_earned" id="add_vl_earned" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5" placeholder="e.g. 1.25">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Absence w/ Pay</label>
                            <input type="text" name="vl_absence_with_pay" id="add_vl_absence_with_pay" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5" placeholder="e.g. 0.1256">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Absence w/out Pay</label>
                            <input type="text" name="vl_absence_without_pay" id="add_vl_absence_without_pay" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5" placeholder="LWOP">
                        </div>
                    </div>
                </div>

                <!-- Sick Leave Sub-columns -->
                <div class="bg-white p-3 rounded-lg border border-slate-200 space-y-2">
                    <div class="text-[11px] font-extrabold text-sky-800 flex items-center gap-1.5">
                        <i class="ph-fill ph-circle text-[8px] text-sky-600"></i>
                        <span>SICK LEAVE COLUMNS</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">SL Earned</label>
                            <input type="text" name="sl_earned" id="add_sl_earned" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5" placeholder="e.g. 1.25">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Absence w/ Pay</label>
                            <input type="text" name="sl_absence_with_pay" id="add_sl_absence_with_pay" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5" placeholder="e.g. 2">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Absence w/out Pay</label>
                            <input type="text" name="sl_absence_without_pay" id="add_sl_absence_without_pay" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5" placeholder="LWOP">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date and Action Taken -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Date & Action Taken on Application for Leave</label>
                <input type="text" name="date_action_taken" id="add_date_action_taken" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2" placeholder="e.g. Approved by Mayor on Aug 18, 2026; Ref: LV-2026-0012">
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 pt-3 border-t border-slate-200 shrink-0">
                <button type="button" onclick="closeModal('addRowModal')" class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer min-h-[40px]">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold rounded-lg bg-amber-500 hover:bg-amber-600 text-white transition shadow-sm cursor-pointer min-h-[40px]">
                    Post to Leave Record
                </button>
            </div>
        </form>
    </div>
</div>






<!-- ========================================================================= -->
<!-- 2. EDIT RECORD ROW MODAL                                                  -->



<!-- ========================================================================= -->
<div id="editRowModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full max-h-[90vh] flex flex-col p-4 sm:p-6 space-y-4 my-auto overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 shrink-0">
            <div class="flex items-center gap-2">
                <i class="ph-bold ph-pencil-simple text-amber-500 text-xl"></i>
                <h3 class="text-base font-bold text-slate-900">Edit Leave Record Row</h3>
            </div>
            <button type="button" onclick="closeModal('editRowModal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form method="POST" id="editRowForm" class="space-y-4 overflow-y-auto flex-1 pr-1">
            @csrf
            @php
echo method_field('PUT');
@endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Period</label>
                    <input type="text" name="period" id="edit_period" required class="w-full text-sm font-semibold text-slate-900 border border-slate-300 rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Particular</label>
                    <input type="text" name="particular" id="edit_particular" required class="w-full text-sm font-bold text-slate-900 border border-slate-300 rounded-lg px-3 py-2">
                </div>
            </div>

            <!-- Column Values Grid -->
            <div class="border border-slate-200 rounded-xl p-3 sm:p-4 bg-slate-50 space-y-3">
                <!-- Vacation Leave -->
                <div class="bg-white p-3 rounded-lg border border-slate-200 space-y-2">
                    <div class="text-[11px] font-extrabold text-emerald-800">VACATION LEAVE COLUMNS</div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">VL Earned</label>
                            <input type="text" name="vl_earned" id="edit_vl_earned" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Absence w/ Pay</label>
                            <input type="text" name="vl_absence_with_pay" id="edit_vl_absence_with_pay" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Absence w/out Pay</label>
                            <input type="text" name="vl_absence_without_pay" id="edit_vl_absence_without_pay" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5">
                        </div>
                    </div>
                </div>

                <!-- Sick Leave -->
                <div class="bg-white p-3 rounded-lg border border-slate-200 space-y-2">
                    <div class="text-[11px] font-extrabold text-sky-800">SICK LEAVE COLUMNS</div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">SL Earned</label>
                            <input type="text" name="sl_earned" id="edit_sl_earned" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Absence w/ Pay</label>
                            <input type="text" name="sl_absence_with_pay" id="edit_sl_absence_with_pay" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5">
                        </div>
                        <div>
                            <label class="block text-[10px] font-semibold text-slate-500 mb-0.5">Absence w/out Pay</label>
                            <input type="text" name="sl_absence_without_pay" id="edit_sl_absence_without_pay" class="w-full text-xs font-mono font-bold border border-slate-300 rounded px-2 py-1.5">
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Date & Action Taken on Application for Leave</label>
                <input type="text" name="date_action_taken" id="edit_date_action_taken" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2">
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 pt-3 border-t border-slate-200 shrink-0">
                <button type="button" onclick="closeModal('editRowModal')" class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer min-h-[40px]">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm cursor-pointer min-h-[40px]">
                    Update Row & Recalculate
                </button>
            </div>
        </form>
    </div>
</div>




<!-- ========================================================================= -->
<!-- 3. TABLE II REFERENCE MODAL (Daily Accruals)                              -->



<!-- ========================================================================= -->
<div id="tableIIModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full max-h-[90vh] flex flex-col p-4 sm:p-6 space-y-4 my-auto overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 shrink-0">
            <div>
                <h3 class="text-base font-bold text-slate-900">Table II: Daily Leave Credits Earned</h3>
            </div>
            <button type="button" onclick="closeModal('tableIIModal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <div class="overflow-y-auto flex-1 border border-slate-200 rounded-xl">
            <table class="w-full text-xs">
                <thead class="bg-slate-100 text-slate-700 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left font-bold border-b">Days of Service</th>
                        <th class="px-3 py-2 text-right font-bold border-b">VL Earned</th>
                        <th class="px-3 py-2 text-right font-bold border-b">SL Earned</th>
                        <th class="px-3 py-2 text-center font-bold border-b">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($tableII as $item)
                        @php
$daysNum = preg_replace('/[^0-9]/', '', $item->input_value);
@endphp
                        <tr class="hover:bg-sky-50 transition group">
                            <td class="px-3 py-1.5 text-slate-800 font-sans font-semibold">
                                {{ $item->input_value }}

                                @if($daysNum == '15')
                                    <span class="text-[10px] text-sky-700 bg-sky-100 px-1.5 py-0.2 rounded font-sans ml-1">Half Month</span>
                                @elseif($daysNum == '30')
                                    <span class="text-[10px] text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded font-sans ml-1">Full Month</span>
                                @endif
                            </td>
                            <td class="px-3 py-1.5 text-right font-bold text-emerald-700">{{ $item->day_equivalent }}</td>
                            <td class="px-3 py-1.5 text-right font-bold text-sky-700">{{ $item->day_equivalent }}</td>
                            <td class="px-3 py-1.5 text-center font-sans">
                                <button type="button" onclick="openProratedForRate('{{ $daysNum }}', '{{ $item->day_equivalent }}')" class="px-2 py-0.5 rounded bg-sky-100 group-hover:bg-sky-600 group-hover:text-white text-sky-800 text-[10px] font-bold transition cursor-pointer">
                                    Use
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-right pt-2 shrink-0">
            <button type="button" onclick="closeModal('tableIIModal')" class="px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>




<!-- ========================================================================= -->
<!-- 3.1. MONTHLY ACCRUAL BATCH MODAL (1st of Every Month Rule)                -->



<!-- ========================================================================= -->
<!-- 3.2. TABLE II PRORATED ACCRUAL MODAL (For Partway Regular / Newly Eligible) -->



<!-- ========================================================================= -->
<div id="proratedModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full max-h-[90vh] flex flex-col p-4 sm:p-6 space-y-4 my-auto overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-800 flex items-center justify-center shrink-0">
                    <i class="ph-bold ph-scales text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Partial Month Earned Credits (Prorated)</h3>
                </div>
            </div>
            <button type="button" onclick="closeModal('proratedModal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('hr.leave-records.prorated-accrual') }}" class="space-y-4 overflow-y-auto flex-1 pr-1">
            @csrf
            <input type="hidden" name="employee_id" value="{{ $employee->employee_id }}">
            <input type="hidden" name="days_of_service" id="prorated_days_of_service" value="15">

            <!-- Employee & Period -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Employee</label>
                    <input type="text" readonly value="{{ $employee->full_name }}" class="w-full text-xs font-bold text-slate-700 bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 cursor-not-allowed">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Control No. {{ $employee->control_no }}</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Period Covered <span class="text-rose-500">*</span></label>
                    <input type="text" name="period" id="prorated_period" value="{{ $currentMonthDate->format('F Y') }}" required class="w-full text-xs font-bold text-slate-900 border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="e.g. October 2026 or 10/16-31/2026">
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Month and year of service</span>
                </div>
            </div>

            <!-- Quick Table II Days Selector -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                    Days of Service in Month (Table II Quick Lookup)
                </label>
                <select id="tableIISelector" onchange="onTableIISelect(this)" class="w-full text-xs font-semibold border border-slate-300 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-sky-500">
                    <option value="">-- Select days of service or enter manual below --</option>
                    @foreach($tableII as $item)
                        @php
$daysNum = preg_replace('/[^0-9]/', '', $item->input_value);
@endphp
                        <option value="{{ $item->day_equivalent }}" data-days="{{ $daysNum }}" {{ $daysNum == '15' ? 'selected' : '' }}>
                            {{ $item->input_value }} of service = {{ $item->day_equivalent }} days earned {{ $daysNum == '15' ? '(Half Month)' : ($daysNum == '30' ? '(Full Month)' : '') }}

                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Exact Earned Decimal Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                <div>
                    <label class="block text-xs font-bold text-emerald-800 mb-1">
                        VL Earned <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="vl_earned" id="prorated_vl_earned" value="0.625" required class="w-full text-sm font-mono font-bold text-slate-900 border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="e.g. 0.625">
                    <span class="text-[10px] text-slate-500 mt-0.5 block">Exact decimal (e.g. 0.625, 0.417)</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-sky-800 mb-1">
                        SL Earned <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="sl_earned" id="prorated_sl_earned" value="0.625" required class="w-full text-sm font-mono font-bold text-slate-900 border border-slate-300 rounded-lg px-3 py-2 bg-white" placeholder="e.g. 0.625">
                    <span class="text-[10px] text-slate-500 mt-0.5 block">Exact decimal (e.g. 0.625, 0.417)</span>
                </div>
            </div>

            <!-- Particular Description -->
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Particular Label</label>
                <input type="text" name="particular" id="prorated_particular" value="Earned (Table II: 15 days)" class="w-full text-xs font-bold text-slate-900 border border-slate-300 rounded-lg px-3 py-2 bg-white">
            </div>

            <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 sm:gap-3 pt-3 border-t border-slate-200 shrink-0">
                <button type="button" onclick="closeModal('proratedModal')" class="w-full sm:w-auto px-4 py-2.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition cursor-pointer min-h-[40px]">
                    Cancel
                </button>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 text-xs font-bold rounded-lg bg-sky-600 hover:bg-sky-700 text-white transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer min-h-[40px]">
                    <i class="ph-bold ph-plus-circle text-sm"></i>
                    <span>Post Table II Prorated Credits</span>
                </button>
            </div>
        </form>
    </div>
</div>




<!-- ========================================================================= -->
<!-- 4. TABLE IV REFERENCE MODAL (L/UT Fractional Conversion)                  -->



<!-- ========================================================================= -->
<div id="tableIVModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-2xl w-full max-h-[92vh] sm:max-h-[88vh] flex flex-col p-4 sm:p-6 space-y-4 my-auto overflow-hidden">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200 shrink-0">
            <div>
                <h3 class="text-base font-bold text-slate-900">Table IV: Working Hours / Minutes Conversion</h3>
                <p class="text-xs text-slate-500">Official Civil Service Conversion into Fractions of a Day (8-Hour Workday)</p>
            </div>
            <button type="button" onclick="closeModal('tableIVModal')" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg cursor-pointer">
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 flex-1 min-h-0 overflow-y-auto">
            <!-- Hours -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-amber-50 px-3 py-2 text-xs font-bold text-amber-900 border-b border-amber-200">
                    Hours (1 to 8 Hours)
                </div>
                <table class="w-full text-xs font-mono">
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tableIVHours as $h)
                            <tr class="hover:bg-slate-50">
                                <td class="px-3 py-1.5 font-sans font-semibold text-slate-800">{{ $h->input_value }}</td>
                                <td class="px-3 py-1.5 text-right font-bold text-slate-900">{{ $h->day_equivalent }} day</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Minutes -->
            <div class="border border-slate-200 rounded-xl overflow-hidden">
                <div class="bg-sky-50 px-3 py-2 text-xs font-bold text-sky-900 border-b border-sky-200">
                    Minutes (Sample Discrete Lookups)
                </div>
                <div class="max-h-[300px] overflow-y-auto">
                    <table class="w-full text-xs font-mono">
                        <tbody class="divide-y divide-slate-100">
                            @foreach($tableIVMinutes as $m)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-3 py-1 font-sans text-slate-700">{{ $m->input_value }}</td>
                                    <td class="px-3 py-1 text-right font-bold text-rose-700">{{ $m->day_equivalent }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="text-right shrink-0 pt-2">
            <button type="button" onclick="closeModal('tableIVModal')" class="w-full sm:w-auto px-4 py-2 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer min-h-[40px]">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function setPreset(particular, vlEarned = '', slEarned = '') {
        document.getElementById('add_particular').value = particular;
        document.getElementById('add_vl_earned').value = vlEarned;
        document.getElementById('add_sl_earned').value = slEarned;
        checkParticular(particular);
    }

    let timeout = null;
    function checkParticular(val) {
        clearTimeout(timeout);
        const fb = document.getElementById('particularFeedback');

        if (!val || val.trim().length === 0) {
            fb.classList.add('hidden');
            return;
        }

        timeout = setTimeout(() => {
            fetch(`/api/leave-records/preview-particular?particular=${encodeURIComponent(val)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.parsed) {
                        const p = data.parsed;
                        fb.classList.remove('hidden');

                        if (p.type === 'SPECIAL_PRIVILEGE_LEAVE' || p.type === 'WELLNESS_LEAVE') {
                            fb.className = 'text-xs rounded-lg p-2.5 bg-sky-50 border-sky-200 text-sky-800';
                            fb.innerHTML = `<strong>${p.description}</strong>`;
                            document.getElementById('add_sl_absence_with_pay').value = p.value;
                            document.getElementById('add_vl_absence_with_pay').value = '';
                        } else if (p.target_column === 'vl_absence_with_pay') {
                            fb.className = 'text-xs rounded-lg p-2.5 bg-emerald-50 border-emerald-200 text-emerald-800 font-mono';
                            fb.innerHTML = `<strong>${p.description}</strong>`;
                            document.getElementById('add_vl_absence_with_pay').value = p.value;
                            document.getElementById('add_sl_absence_with_pay').value = '';
                        } else if (p.target_column === 'sl_absence_with_pay') {
                            fb.className = 'text-xs rounded-lg p-2.5 bg-sky-50 border-sky-200 text-sky-800 font-mono';
                            fb.innerHTML = `<strong>${p.description}</strong>`;
                            document.getElementById('add_sl_absence_with_pay').value = p.value;
                            document.getElementById('add_vl_absence_with_pay').value = '';
                        } else if (p.type === 'EARNED') {
                            fb.className = 'text-xs rounded-lg p-2.5 bg-amber-50 border-amber-200 text-amber-800';
                            fb.innerHTML = `<strong>${p.description}</strong>`;
                        } else {
                            fb.className = 'text-xs rounded-lg p-2.5 bg-slate-50 border-slate-200 text-slate-700';
                            fb.innerHTML = `<span>${p.description}</span>`;
                        }
                    } else {
                        fb.classList.add('hidden');
                    }
                })
                .catch(() => fb.classList.add('hidden'));
        }, 150);
    }

    function openEditModal(record) {
        document.getElementById('edit_period').value = record.period || '';
        document.getElementById('edit_particular').value = record.particular || '';
        document.getElementById('edit_vl_earned').value = record.vl_earned || '';
        document.getElementById('edit_vl_absence_with_pay').value = record.vl_absence_with_pay || '';
        document.getElementById('edit_vl_absence_without_pay').value = record.vl_absence_without_pay || '';
        document.getElementById('edit_sl_earned').value = record.sl_earned || '';
        document.getElementById('edit_sl_absence_with_pay').value = record.sl_absence_with_pay || '';
        document.getElementById('edit_sl_absence_without_pay').value = record.sl_absence_without_pay || '';
        document.getElementById('edit_date_action_taken').value = record.date_action_taken || '';

        const form = document.getElementById('editRowForm');
        form.action = `/hr/leave-records/${record.record_id}`;

        openModal('editRowModal');
    }

    function onTableIISelect(sel) {
        const val = sel.value;
        const opt = sel.options[sel.selectedIndex];
        const days = opt ? opt.getAttribute('data-days') : '';
        if (val) {
            document.getElementById('prorated_vl_earned').value = val;
            document.getElementById('prorated_sl_earned').value = val;
            document.getElementById('prorated_days_of_service').value = days;
            document.getElementById('prorated_particular').value = days ? `Earned (Table II: ${days} days)` : 'Earned (Prorated)';
        }
    }

    function openProratedForRate(days, rate) {
        closeModal('tableIIModal');
        document.getElementById('prorated_vl_earned').value = rate;
        document.getElementById('prorated_sl_earned').value = rate;
        document.getElementById('prorated_days_of_service').value = days;
        document.getElementById('prorated_particular').value = days ? `Earned (Table II: ${days} days)` : 'Earned (Prorated)';

        const selector = document.getElementById('tableIISelector');
        if (selector) {
            for (let i = 0; i < selector.options.length; i++) {
                if (selector.options[i].getAttribute('data-days') === days) {
                    selector.selectedIndex = i;
                    break;
                }
            }
        }

        openModal('proratedModal');
    }

    // -------------------------------------------------------------------------
    // Searchable Employee Combobox with Live Filter & Keyboard Navigation
    // -------------------------------------------------------------------------
    document.addEventListener('DOMContentLoaded', function() {
        const municipalPersonnel = [
            @foreach($allEmployees as $emp)
                {
                    id: {{ $emp->employee_id }},
                    name: @php
echo json_encode($emp->full_name, 15, 512)
@endphp,
                    no: @php
echo json_encode($emp->control_no, 15, 512)
@endphp,
                    office: @php
echo json_encode($emp->office->office_name ?? 'LGU Basud', 15, 512)
@endphp,
                    position: @php
echo json_encode($emp->position ?? '', 15, 512)
@endphp
                },
            @endforeach
        ];

        const currentEmpId = {{ $employee->employee_id }};
        const currentEmpLabel = @php
echo json_encode($employee->full_name . ' (' . $employee->control_no . ') - ' . ($employee->office->office_name ?? 'LGU Basud'), 15, 512)
@endphp;
        
        const searchInput = document.getElementById('employeeSearchInput');
        const hiddenIdInput = document.getElementById('selected_employee_id');
        const dropdownList = document.getElementById('employeeDropdownList');
        const caretIcon = document.getElementById('employeeCaretIcon');
        const clearBtn = document.getElementById('clearEmployeeSearchBtn');
        const toggleBtn = document.getElementById('toggleEmployeeDropdownBtn');
        const form = document.getElementById('employeeSelectForm');

        if (!searchInput || !dropdownList || !form) return;

        let highlightedIndex = -1;
        let filteredEmployees = [...municipalPersonnel];

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
            return escapedText.replace(regex, '<mark class="bg-amber-100 text-amber-900 font-bold px-0.5 rounded-xs">$1</mark>');
        }

        function getInitials(name) {
            if (!name) return '??';
            const parts = name.trim().split(/\s+/).filter(Boolean);
            if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        }

        function renderDropdown(items, query = '') {
            dropdownList.innerHTML = '';
            highlightedIndex = -1;

            if (items.length === 0) {
                dropdownList.innerHTML = `
                    <div class="p-4 text-center text-slate-400">
                        <i class="ph-bold ph-user-circle-minus text-2xl mb-1 block text-slate-300"></i>
                        <p class="font-medium">No personnel found</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Try searching another name, badge #, or office</p>
                    </div>
                `;
                openDropdown();
                return;
            }

            const fragment = document.createDocumentFragment();
            items.forEach((emp, idx) => {
                const itemEl = document.createElement('div');
                itemEl.className = 'px-3 py-2 flex items-center justify-between hover:bg-gov-50 cursor-pointer transition select-none ' + 
                    (emp.id === currentEmpId ? 'bg-amber-50/70 font-semibold' : '');
                itemEl.setAttribute('data-index', idx);
                itemEl.setAttribute('data-id', emp.id);

                const isCurrent = (emp.id === currentEmpId);

                itemEl.innerHTML = `
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-7 h-7 rounded-lg ${isCurrent ? 'bg-amber-600 text-white' : 'bg-slate-100 text-slate-700'} font-bold flex items-center justify-center shrink-0 text-[10px]">
                            ${getInitials(emp.name)}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-900 truncate">
                                ${highlightMatch(emp.name, query)}
                            </div>
                            <div class="text-[11px] text-slate-500 truncate flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono text-slate-700 bg-slate-100 px-1.5 py-0.2 rounded font-semibold text-[10px]">
                                    ${highlightMatch(emp.no, query)}
                                </span>
                                <span>•</span>
                                <span class="truncate">${highlightMatch(emp.office, query)}</span>
                            </div>
                        </div>
                    </div>
                    ${isCurrent ? '<span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 shrink-0 ml-2">Viewing</span>' : ''}
                `;

                itemEl.addEventListener('click', (e) => {
                    e.stopPropagation();
                    selectEmployee(emp);
                });

                fragment.appendChild(itemEl);
            });

            dropdownList.appendChild(fragment);
            openDropdown();
        }

        function openDropdown() {
            dropdownList.classList.remove('hidden');
            if (caretIcon) caretIcon.classList.add('rotate-180');
        }

        function closeDropdown() {
            dropdownList.classList.add('hidden');
            if (caretIcon) caretIcon.classList.remove('rotate-180');
            highlightedIndex = -1;
        }

        function selectEmployee(emp) {
            hiddenIdInput.value = emp.id;
            searchInput.value = `${emp.name} (${emp.no}) - ${emp.office}`;
            closeDropdown();
            // Automatically submit the form to navigate to the selected employee's leave record
            form.submit();
        }

        function updateHighlightedItem() {
            const items = dropdownList.querySelectorAll('[data-index]');
            items.forEach((el, i) => {
                if (i === highlightedIndex) {
                    el.classList.add('bg-gov-50', 'ring-1', 'ring-inset', 'ring-gov-600');
                    el.scrollIntoView({ block: 'nearest' });
                } else {
                    el.classList.remove('bg-gov-50', 'ring-1', 'ring-inset', 'ring-gov-600');
                }
            });
        }

        // Input Events
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            
            if (query.length > 0) {
                if (clearBtn) clearBtn.classList.remove('hidden');
                filteredEmployees = municipalPersonnel.filter(e => {
                    return e.name.toLowerCase().includes(query) ||
                           e.no.toLowerCase().includes(query) ||
                           e.office.toLowerCase().includes(query) ||
                           (e.position && e.position.toLowerCase().includes(query));
                });
            } else {
                if (clearBtn) clearBtn.classList.add('hidden');
                filteredEmployees = [...municipalPersonnel];
            }

            renderDropdown(filteredEmployees, query);
        });

        searchInput.addEventListener('focus', function() {
            // Select all text on focus so user can immediately type over it or see the full list
            this.select();
            if (clearBtn) clearBtn.classList.remove('hidden');
            filteredEmployees = [...municipalPersonnel];
            renderDropdown(filteredEmployees, '');
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                searchInput.value = '';
                clearBtn.classList.add('hidden');
                searchInput.focus();
                filteredEmployees = [...municipalPersonnel];
                renderDropdown(filteredEmployees, '');
            });
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (dropdownList.classList.contains('hidden')) {
                    searchInput.focus();
                    filteredEmployees = [...municipalPersonnel];
                    renderDropdown(filteredEmployees, '');
                } else {
                    closeDropdown();
                }
            });
        }

        // Keyboard navigation (ArrowDown, ArrowUp, Enter, Escape)
        searchInput.addEventListener('keydown', function(e) {
            if (dropdownList.classList.contains('hidden')) {
                if (e.key === 'ArrowDown' || e.key === 'Enter') {
                    e.preventDefault();
                    filteredEmployees = [...municipalPersonnel];
                    renderDropdown(filteredEmployees, this.value.trim());
                }
                return;
            }

            const itemsCount = filteredEmployees.length;
            if (itemsCount === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlightedIndex = (highlightedIndex + 1) % itemsCount;
                updateHighlightedItem();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlightedIndex = (highlightedIndex - 1 + itemsCount) % itemsCount;
                updateHighlightedItem();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (highlightedIndex >= 0 && highlightedIndex < itemsCount) {
                    selectEmployee(filteredEmployees[highlightedIndex]);
                } else if (itemsCount === 1) {
                    selectEmployee(filteredEmployees[0]);
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closeDropdown();
                searchInput.value = currentEmpLabel;
                if (clearBtn) clearBtn.classList.add('hidden');
            }
        });

        // Close on click outside
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('employeeComboboxWrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                closeDropdown();
                if (clearBtn) clearBtn.classList.add('hidden');
                // Restore active employee label if input was left dirty without selection
                if (hiddenIdInput.value == currentEmpId) {
                    searchInput.value = currentEmpLabel;
                }
            }
        });
    });

    
    
    // Recalculation Dialog Dismissal Controller
    function dismissRecalcSuccessDialog() {
        const dialog = document.getElementById('recalcSuccessDialog');
        if (dialog) {
            dialog.style.opacity = '0';
            dialog.style.pointerEvents = 'none';
            setTimeout(() => {
                if (dialog && dialog.parentNode) {
                    dialog.parentNode.removeChild(dialog);
                }
            }, 180);
        }
    }

    document.addEventListener('click', function(e) {
        if (e.target.closest('#btnDoneRecalcDialog') || e.target.closest('#btnCloseRecalcDialogHeader')) {
            e.preventDefault();
            e.stopPropagation();
            dismissRecalcSuccessDialog();
            return;
        }

        const dialog = document.getElementById('recalcSuccessDialog');
        if (dialog && e.target === dialog) {
            dismissRecalcSuccessDialog();
            return;
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.getElementById('recalcSuccessDialog')) {
            dismissRecalcSuccessDialog();
        }
    });

    // Recalculation Form Submit Loader
    document.addEventListener('DOMContentLoaded', function() {
        const recalcForm = document.getElementById('recalculateForm');
        const btnRecalculate = document.getElementById('btnRecalculate');
        const recalcIcon = document.getElementById('recalculateIcon');
        const recalcLabel = document.getElementById('recalculateLabel');
        const loadingBackdrop = document.getElementById('recalculateLoadingBackdrop');

        if (recalcForm && btnRecalculate) {
            recalcForm.addEventListener('submit', function() {
                btnRecalculate.disabled = true;
                if (recalcIcon) {
                    recalcIcon.className = 'ph ph-spinner text-sm text-gov-700 animate-spin';
                }
                if (recalculateLabel) {
                    recalculateLabel.textContent = 'Recalculating...';
                }
                if (loadingBackdrop) {
                    loadingBackdrop.classList.remove('hidden');
                }
            });
        }
    });
</script>
@endsection
