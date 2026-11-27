@extends('layouts.app')

@section('title', 'My Leave Record - C-LACTS HRMU Basud')

@section('content')
@php
    // Helper function to translate complex HR particulars into plain, everyday language
    function friendlyActivityName($particular) {
        $t = trim($particular ?? '');
        $lower = strtolower($t);

        if (str_contains($lower, 'opening balance') || str_contains($lower, 'certified baseline')) {
            return 'Starting Balance';
        }
        if (str_contains($lower, 'monthly accrual') || (str_contains($lower, 'accrual') && !str_contains($lower, 'prorated'))) {
            return 'Monthly Earned Credit';
        }
        if (str_contains($lower, 'prorated')) {
            return 'Prorated Monthly Credit';
        }
        if (preg_match('/^l\s*&\s*ut\b/i', $t) || str_contains($lower, 'tardiness') || str_contains($lower, 'undertime') || str_contains($lower, 'late')) {
            return 'Deduction for Late / Undertime';
        }
        if (preg_match('/^vl\b/i', $t) || str_contains($lower, 'vacation leave')) {
            return 'Vacation Leave';
        }
        if (preg_match('/^sl\b/i', $t) || str_contains($lower, 'sick leave')) {
            return 'Sick Leave';
        }
        if (preg_match('/^spl\b/i', $t) || str_contains($lower, 'special privilege')) {
            return 'Special Privilege Leave (Birthday / Emergency)';
        }
        if (preg_match('/^fl\b/i', $t) || str_contains($lower, 'forced leave') || str_contains($lower, 'mandatory')) {
            return 'Mandatory 5-Day Forced Leave';
        }
        if (str_contains($lower, 'wellness')) {
            return 'Wellness Leave';
        }
        if (str_contains($lower, 'solo parent')) {
            return 'Solo Parent Leave';
        }
        if (str_contains($lower, 'maternity')) {
            return 'Maternity Leave';
        }
        if (str_contains($lower, 'paternity')) {
            return 'Paternity Leave';
        }
        if (str_contains($lower, 'lwop') || str_contains($lower, 'without pay')) {
            return 'Leave Without Pay (Unpaid)';
        }
        if (str_contains($lower, 'abs') || str_contains($lower, 'absent')) {
            return 'Deduction for Unexcused Absence';
        }
        return $t !== '' ? $t : 'Leave Activity';
    }
@endphp

<div class="space-y-6">

    <!-- Top Navigation Header: Clean GovTech Profile & Actions -->
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-4 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider">
                <span>Personal Leave Record</span>
                <span>•</span>
                <span id="headerModeBadge" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-gov-100 text-gov-800 border border-gov-200">
                    MONTHLY STATEMENT VIEW
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-950 mt-1 truncate">{{ $employee->full_name }}</h1>
            <p class="text-xs sm:text-sm text-slate-600 mt-0.5">
                {{ $employee->position ?? 'Municipal Personnel' }} • {{ $employee->office->office_name ?? 'HRMU Basud' }}
            </p>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2.5 sm:gap-3 w-full md:w-auto">
            <!-- Clearly Visible "View Original Format" Button -->
            <button 
                type="button" 
                id="btnToggleRecordView" 
                onclick="toggleRecordView()" 
                class="inline-flex items-center justify-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 bg-gov-700 hover:bg-gov-800 text-white rounded-lg text-xs sm:text-sm font-bold shadow-xs transition cursor-pointer border-2 border-gov-700 hover:border-gov-800 focus:outline-none focus:ring-2 focus:ring-gov-600 min-h-[40px] sm:min-h-[44px] w-full sm:w-auto"
                title="Toggle between simplified monthly table and official 11-column CSC Form 41 ledger"
            >
                <i id="toggleViewIcon" class="ph ph-file-text text-base sm:text-lg"></i>
                <span id="toggleViewBtnLabel">View Original Format (CSC Form 41)</span>
            </button>

            <!-- Context-Aware Print Leave Records Button -->
            <button 
                type="button" 
                id="btnPrintLeaveRecords" 
                onclick="printCurrentRecordView()" 
                class="inline-flex items-center justify-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 bg-white hover:bg-slate-50 text-slate-800 rounded-lg text-xs sm:text-sm font-bold shadow-2xs transition cursor-pointer border-2 border-slate-300 min-h-[40px] sm:min-h-[44px] w-full sm:w-auto" 
                title="Print leave records in currently active view"
            >
                <i class="ph ph-printer text-base sm:text-lg"></i>
                <span id="printBtnLabel">Print Leave Records</span>
            </button>
        </div>
    </div>

    <!-- ======================================================================= -->
    <!-- VIEW OPTION 1: SIMPLIFIED MONTHLY SUMMARY (WHOLE YEAR IN ONE TABLE)     -->
    <!-- ======================================================================= -->
    <div id="simplifiedLedgerView" class="space-y-6">

        <!-- 1. Balances at a Glance (Plain Language) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            
            <!-- Vacation Leave Card -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 border-t-4 border-t-gov-700 p-3 sm:p-5 space-y-2 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-700 uppercase tracking-wider block font-mono truncate">Vacation Leave</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-bold bg-gov-50 text-gov-700 border border-gov-200 font-mono shrink-0">VL</span>
                    </div>
                    <div class="mt-1">
                        <div class="text-[10px] sm:text-xs text-slate-500 font-medium">Available Balance</div>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-xl min-[400px]:text-2xl sm:text-3xl font-black font-mono text-slate-950 tabular-nums">
                                {{ number_format((float) $latestVlBalance, 3) }}
                            </span>
                            <span class="text-[10px] sm:text-xs font-bold text-slate-500">days</span>
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] min-[400px]:text-xs font-mono">
                    <span class="text-emerald-700 font-bold" title="Total earned credits">+{{ number_format((float) ($totalVlEarned ?? 0), 3) }}</span>
                    <span class="text-rose-700 font-bold" title="Total used credits">-{{ number_format((float) ($totalVlUsed ?? 0), 3) }}</span>
                </div>
            </div>

            <!-- Sick Leave Card -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 border-t-4 border-t-emerald-600 p-3 sm:p-5 space-y-2 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] sm:text-xs font-bold text-emerald-800 uppercase tracking-wider block font-mono truncate">Sick Leave</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 font-mono shrink-0">SL</span>
                    </div>
                    <div class="mt-1">
                        <div class="text-[10px] sm:text-xs text-slate-500 font-medium">Available Balance</div>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-xl min-[400px]:text-2xl sm:text-3xl font-black font-mono text-emerald-800 tabular-nums">
                                {{ number_format((float) $latestSlBalance, 3) }}
                            </span>
                            <span class="text-[10px] sm:text-xs font-bold text-slate-500">days</span>
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] min-[400px]:text-xs font-mono">
                    <span class="text-emerald-700 font-bold" title="Total earned credits">+{{ number_format((float) ($totalSlEarned ?? 0), 3) }}</span>
                    <span class="text-rose-700 font-bold" title="Total used credits">-{{ number_format((float) ($totalSlUsed ?? 0), 3) }}</span>
                </div>
            </div>

            <!-- Total Available Balance Card -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 border-t-4 border-t-slate-700 p-3 sm:p-5 space-y-2 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] sm:text-xs font-bold text-slate-800 uppercase tracking-wider block font-mono truncate">Total Paid Leave</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-bold bg-slate-100 text-slate-800 border border-slate-200 font-mono shrink-0">VL + SL</span>
                    </div>
                    <div class="mt-1">
                        <div class="text-[10px] sm:text-xs text-slate-500 font-medium">Combined Balance</div>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-xl min-[400px]:text-2xl sm:text-3xl font-black font-mono text-slate-900 tabular-nums">
                                {{ number_format((float) ($totalAvailableBalance ?? 0), 3) }}
                            </span>
                            <span class="text-[10px] sm:text-xs font-bold text-slate-500">days</span>
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] min-[400px]:text-xs font-mono">
                    <span class="text-emerald-700 font-bold">+{{ number_format((float) (($totalVlEarned ?? 0) + ($totalSlEarned ?? 0)), 3) }}</span>
                    <span class="text-rose-700 font-bold">-{{ number_format((float) (($totalVlUsed ?? 0) + ($totalSlUsed ?? 0)), 3) }}</span>
                </div>
            </div>

            <!-- Total Used Credits Card -->
            <div class="bg-white rounded-xl shadow-xs border border-slate-200 border-t-4 border-t-rose-600 p-3 sm:p-5 space-y-2 flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between gap-1">
                        <span class="text-[10px] sm:text-xs font-bold text-rose-900 uppercase tracking-wider block font-mono truncate">Leave Utilized</span>
                        <span class="px-1.5 py-0.5 rounded text-[9px] sm:text-[10px] font-bold bg-rose-50 text-rose-800 border border-rose-200 font-mono shrink-0">USED</span>
                    </div>
                    <div class="mt-1">
                        <div class="text-[10px] sm:text-xs text-slate-500 font-medium">Days Taken (YTD)</div>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-xl min-[400px]:text-2xl sm:text-3xl font-black font-mono text-rose-900 tabular-nums">
                                {{ number_format((float) (($totalVlUsed ?? 0) + ($totalSlUsed ?? 0)), 3) }}
                            </span>
                            <span class="text-[10px] sm:text-xs font-bold text-slate-500">days</span>
                        </div>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] min-[400px]:text-xs font-mono">
                    <span class="text-slate-600 font-medium">VL: -{{ number_format((float) ($totalVlUsed ?? 0), 3) }}</span>
                    <span class="text-slate-600 font-medium">SL: -{{ number_format((float) ($totalSlUsed ?? 0), 3) }}</span>
                </div>
            </div>

        </div>

        <!-- 2. The Master Monthly Table (The Whole Year in One Table) -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            
            <!-- Table Header Toolbar -->
            <div class="p-4 bg-slate-50/90 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-gov-100 text-gov-800 flex items-center justify-center font-bold text-xs shrink-0">
                        <i class="ph ph-calendar-check text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Monthly Leave Summary Table</h3>
                    </div>
                </div>

                <!-- Year Tab Selector -->
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-slate-700 uppercase font-mono">Year:</span>
                    <div class="inline-flex items-center rounded-lg bg-slate-200/90 p-1 border border-slate-300 gap-1" role="tablist">
                        @foreach($availableYears as $yr)
                            <button 
                                type="button" 
                                onclick="switchYear('{{ $yr }}')" 
                                id="yearTabBtn_{{ $yr }}" 
                                class="year-tab-btn inline-flex items-center justify-center px-3 py-2 text-xs font-bold rounded-md transition cursor-pointer min-h-[36px] sm:min-h-[40px] {{ $loop->first ? 'bg-white text-gov-800 shadow-xs border border-slate-300/80' : 'text-slate-600 hover:text-slate-900 hover:bg-white/60' }}"
                            >
                                {{ $yr }}
                            </button>
                        @endforeach
                        @if(count($availableYears) > 1)
                            <button 
                                type="button" 
                                onclick="switchYear('all')" 
                                id="yearTabBtn_all" 
                                class="year-tab-btn inline-flex items-center justify-center px-3 py-2 text-xs font-bold rounded-md transition cursor-pointer min-h-[36px] sm:min-h-[40px] text-slate-600 hover:text-slate-900 hover:bg-white/60"
                            >
                                All Years
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ONE Master Table per Year -->
            @forelse($recordsByYear as $yr => $months)
                <div id="yearGroup_{{ $yr }}" class="year-content-group {{ $loop->first ? '' : 'hidden' }}">
                    
                    <!-- Desktop / Tablet: The One Unified Monthly Table -->
                    <div class="hidden sm:block overflow-x-auto">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900 text-slate-200 uppercase font-mono font-bold tracking-wider text-[11px] border-b border-slate-800">
                                    <th class="px-4 py-3 w-40">Month</th>
                                    <th class="px-4 py-3 w-48">Leave Added (+)</th>
                                    <th class="px-4 py-3 min-w-[240px]">Leaves Taken & Deductions (-)</th>
                                    <th class="px-4 py-3 text-right w-36">Vacation Left</th>
                                    <th class="px-4 py-3 text-right w-36">Sick Left</th>
                                    <th class="px-4 py-3 text-right w-36 bg-slate-950/80 text-white font-extrabold">Total Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse($months as $sortKey => $m)
                                    <tr class="hover:bg-slate-50 transition {{ $loop->iteration % 2 === 0 ? 'bg-slate-50/40' : '' }}">
                                        
                                        <!-- 1. Month -->
                                        <td class="px-4 py-3.5 align-top whitespace-nowrap">
                                            <div class="font-bold text-slate-950 text-sm">
                                                {{ $m['month_label'] }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                                Month {{ (int)$m['month_num'] }}
                                            </div>
                                        </td>

                                        <!-- 2. Leave Added (+) -->
                                        <td class="px-4 py-3.5 align-top">
                                            @if($m['has_starting_balance'])
                                                <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-gov-100 text-gov-800 border border-gov-200 mb-1">
                                                    <i class="ph-bold ph-check text-xs"></i> Starting Balance
                                                </div>
                                            @endif

                                            @if($m['earned_vl'] > 0 || $m['earned_sl'] > 0)
                                                <div class="text-xs font-bold text-emerald-800 font-mono">
                                                    +{{ number_format($m['earned_vl'], 3) }} Vacation
                                                </div>
                                                <div class="text-xs font-bold text-emerald-800 font-mono mt-0.5">
                                                    +{{ number_format($m['earned_sl'], 3) }} Sick
                                                </div>
                                                <div class="text-[10px] text-emerald-700 font-semibold mt-1">
                                                    (+{{ number_format($m['earned_vl'] + $m['earned_sl'], 3) }} total added)
                                                </div>
                                            @elseif(!$m['has_starting_balance'])
                                                <span class="text-slate-400 text-xs italic font-sans">None added</span>
                                            @endif
                                        </td>

                                        <!-- 3. Leaves Taken & Deductions (-) -->
                                        <td class="px-4 py-3.5 align-top">
                                            @if(!empty($m['leaves_taken_items']) && count($m['leaves_taken_items']) > 0)
                                                <div class="space-y-2">
                                                    @foreach($m['leaves_taken_items'] as $item)
                                                        @php
                                                            $name = friendlyActivityName($item['particular']);
                                                        @endphp
                                                        <div class="text-xs border-l-2 border-rose-300 pl-2 py-0.5">
                                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                                <span class="font-bold text-slate-900">{{ $name }}</span>
                                                                @if(!empty($item['leave_dates']))
                                                                    <span class="text-slate-500 font-medium text-[11px]">(Leave: {{ $item['leave_dates'] }})</span>
                                                                @endif
                                                                
                                                                @if($item['vl_used'] > 0)
                                                                    <span class="text-rose-700 font-bold font-mono">-{{ number_format($item['vl_used'], 3) }} Vacation</span>
                                                                @endif
                                                                @if($item['sl_used'] > 0)
                                                                    <span class="text-rose-700 font-bold font-mono">-{{ number_format($item['sl_used'], 3) }} Sick</span>
                                                                @endif
                                                                @if($item['vl_lwop'] > 0)
                                                                    <span class="text-amber-800 font-bold font-mono">{{ number_format($item['vl_lwop'], 3) }} Vac (Unpaid)</span>
                                                                @endif
                                                                @if($item['sl_lwop'] > 0)
                                                                    <span class="text-amber-800 font-bold font-mono">{{ number_format($item['sl_lwop'], 3) }} Sick (Unpaid)</span>
                                                                @endif
                                                                @if(empty($item['vl_used']) && empty($item['sl_used']) && empty($item['vl_lwop']) && empty($item['sl_lwop']))
                                                                    <span class="text-slate-500 font-semibold text-[11px]">(0 days deducted)</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                                                                @if($item['ref_code'])
                                                                    <span class="font-mono font-bold text-slate-700">Ref: {{ $item['ref_code'] }}</span>
                                                                @endif
                                                                @if($item['date_action_taken'])
                                                                    <span>• {{ $item['date_action_taken'] }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="flex items-center gap-1.5 text-slate-400 text-xs italic">
                                                    <i class="ph ph-check text-emerald-600 font-bold text-sm"></i>
                                                    <span>No leaves taken this month (0 days used)</span>
                                                </div>
                                            @endif
                                        </td>

                                        <!-- 4. Vacation Left -->
                                        <td class="px-4 py-3.5 text-right whitespace-nowrap align-top font-mono">
                                            <span class="font-black text-slate-950 text-sm sm:text-base">{{ $m['closing_vl'] }}</span>
                                            <span class="text-xs text-slate-500 font-sans font-medium block">days</span>
                                        </td>

                                        <!-- 5. Sick Left -->
                                        <td class="px-4 py-3.5 text-right whitespace-nowrap align-top font-mono">
                                            <span class="font-black text-emerald-800 text-sm sm:text-base">{{ $m['closing_sl'] }}</span>
                                            <span class="text-xs text-slate-500 font-sans font-medium block">days</span>
                                        </td>

                                        <!-- 6. Total Balance -->
                                        <td class="px-4 py-3.5 text-right whitespace-nowrap align-top font-mono bg-slate-50/80">
                                            <span class="font-black text-gov-900 text-sm sm:text-base">
                                                {{ number_format((float)$m['closing_vl'] + (float)$m['closing_sl'], 3) }}
                                            </span>
                                            <span class="text-xs text-slate-500 font-sans font-bold block">total days</span>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-slate-500 text-xs">
                                            No monthly records found for year {{ $yr }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile (< 640px): Stacked Cards per Month (Zero Side-Scroll) -->
                    <div class="block sm:hidden divide-y divide-slate-100">
                        @forelse($months as $sortKey => $m)
                            <div class="p-4 space-y-2.5">
                                <!-- Month Title & Total Balance Badge -->
                                <div class="flex items-center justify-between gap-2">
                                    <div class="font-bold text-slate-900 text-sm">
                                        {{ $m['month_label'] }}
                                    </div>
                                    <div class="text-right font-mono">
                                        <span class="font-black text-gov-900 text-sm">
                                            {{ number_format((float)$m['closing_vl'] + (float)$m['closing_sl'], 3) }}
                                        </span>
                                        <span class="text-[10px] text-slate-500 font-sans">total days</span>
                                    </div>
                                </div>

                                <!-- Added -->
                                <div class="bg-emerald-50/70 p-2.5 rounded-lg border border-emerald-100 text-xs text-emerald-950 space-y-0.5">
                                    <span class="font-bold text-emerald-900 block text-[11px] uppercase tracking-wider">Leave Added This Month:</span>
                                    @if($m['has_starting_balance'])
                                        <span class="font-bold text-gov-800 block">Starting Certified Balance</span>
                                    @endif
                                    @if($m['earned_vl'] > 0 || $m['earned_sl'] > 0)
                                        <span class="font-mono font-bold">+{{ number_format($m['earned_vl'], 3) }} Vacation • +{{ number_format($m['earned_sl'], 3) }} Sick</span>
                                    @elseif(!$m['has_starting_balance'])
                                        <span class="text-slate-400 italic">None added</span>
                                    @endif
                                </div>

                                <!-- Used -->
                                <div class="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs text-slate-800 space-y-1">
                                    <span class="font-bold text-slate-700 block text-[11px] uppercase tracking-wider">Leaves Taken This Month:</span>
                                    @if(!empty($m['leaves_taken_items']) && count($m['leaves_taken_items']) > 0)
                                        @foreach($m['leaves_taken_items'] as $item)
                                            <div class="border-l-2 border-rose-400 pl-2 py-0.5">
                                                <div class="font-bold text-slate-900">
                                                    {{ friendlyActivityName($item['particular']) }}
                                                    @if(!empty($item['leave_dates']))
                                                        <span class="text-slate-500 font-medium text-[10px]">({{ $item['leave_dates'] }})</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px] font-mono text-rose-700 font-bold">
                                                    @if($item['vl_used'] > 0) -{{ number_format($item['vl_used'], 3) }} Vacation @endif
                                                    @if($item['sl_used'] > 0) -{{ number_format($item['sl_used'], 3) }} Sick @endif
                                                </div>
                                                @if($item['ref_code'])
                                                    <div class="text-[10px] text-slate-500 font-mono">Ref: {{ $item['ref_code'] }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-slate-400 italic">No leaves taken this month</span>
                                    @endif
                                </div>

                                <!-- Remaining Balances Strip -->
                                <div class="pt-1 flex items-center justify-between text-xs font-mono">
                                    <span class="text-slate-500 font-sans">Your Balance:</span>
                                    <span class="font-bold text-slate-900">
                                        {{ $m['closing_vl'] }} Vacation • {{ $m['closing_sl'] }} Sick
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-slate-400 text-xs">
                                No activity recorded for year {{ $yr }}.
                            </div>
                        @endforelse
                    </div>

                </div>
            @empty
                <div class="p-8 text-center text-slate-500">
                    <i class="ph ph-receipt text-3xl text-slate-400 block mb-2"></i>
                    <span class="font-bold text-base text-slate-800">No Leave Record Entries Recorded</span>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">Your starting balances and approved applications will be reflected here.</p>
                </div>
            @endforelse

        </div>

    </div>

    <!-- ======================================================================= -->
    <!-- VIEW OPTION 2: ORIGINAL OFFICIAL FORMAT                                  -->
    <!-- ======================================================================= -->
    <div id="originalLedgerView" class="hidden space-y-4">

        <!-- Official 11-Column Tabular Ledger Container -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
            
            <div class="p-3 bg-slate-900 text-white text-xs font-bold font-mono flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <i class="ph ph-buildings text-amber-400"></i>
                    <span>HRMU Basud • Control No. {{ $employee->control_no }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        <!-- Level 1 Header -->
                        <tr class="bg-slate-900 text-slate-100 border-b border-slate-800 text-center font-bold font-mono">
                            <th rowspan="2" class="sticky left-0 z-10 bg-slate-900 px-3 py-3 border-r border-slate-800 min-w-[100px] text-left uppercase tracking-wider text-xs">PERIOD</th>
                            <th rowspan="2" class="px-3 py-3 border-r border-slate-800 min-w-[140px] text-left uppercase tracking-wider text-xs">PARTICULAR</th>
                            
                            <!-- Vacation Leave Group -->
                            <th colspan="4" class="px-4 py-2 border-r border-slate-800 text-white uppercase tracking-wider text-xs font-extrabold bg-slate-950/60">
                                VACATION LEAVE
                            </th>

                            <!-- Sick Leave Group -->
                            <th colspan="4" class="px-4 py-2 border-r border-slate-800 text-white uppercase tracking-wider text-xs font-extrabold bg-slate-950/60">
                                SICK LEAVE
                            </th>

                            <th rowspan="2" class="px-3 py-3 min-w-[240px] text-left uppercase tracking-wider text-xs">
                                DATE & ACTION TAKEN ON APPLICATION FOR LEAVE
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
                                $isDeduction = !$isBaseline && !$isEarned;
                            @endphp
                            <tr class="transition font-sans hover:bg-slate-50 {{ $isEarned || $isBaseline ? 'border-t border-slate-300' : '' }} {{ $loop->iteration % 2 === 0 ? 'bg-slate-50/40' : 'bg-white' }}">
                                
                                <!-- Col 1: Period -->
                                <td class="sticky left-0 z-10 bg-white px-3 py-2 border-r border-slate-200 whitespace-nowrap text-xs sm:text-sm font-mono {{ $isEarned || $isBaseline ? 'font-bold text-slate-950' : 'font-normal text-slate-600 pl-5' }}">
                                    {{ $rec->period }}
                                </td>

                                <!-- Col 2: Particular -->
                                <td class="px-3 py-2.5 border-r border-slate-200 whitespace-nowrap text-xs">
                                    @if($isEarned || $isBaseline)
                                        <span class="font-black text-slate-950 uppercase tracking-wide">
                                            {{ $rec->particular }}
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
                                <td class="px-2 py-2.5 text-center font-mono border-r border-slate-200 {{ !empty($rec->vl_absence_with_pay) ? 'font-semibold text-slate-900' : 'text-slate-300 font-normal' }}">
                                    {{ !empty($rec->vl_absence_with_pay) ? $rec->vl_absence_with_pay : '—' }}
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
                                <td class="px-2 py-2.5 text-center font-mono border-r border-slate-200 {{ !empty($rec->sl_absence_with_pay) ? 'font-semibold text-slate-900' : 'text-slate-300 font-normal' }}">
                                    {{ !empty($rec->sl_absence_with_pay) ? $rec->sl_absence_with_pay : '—' }}
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
                                <td class="px-3 py-2.5 text-xs border-slate-200 {{ $isEarned || $isBaseline ? 'font-bold text-slate-950' : 'font-normal text-slate-600' }}">
                                    {{ $rec->date_action_taken ?? '' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-12 text-center text-slate-500">
                                    <i class="ph ph-files text-3xl text-slate-400 block mb-2"></i>
                                    <span class="font-bold text-base text-slate-800">No Leave Record Entries Recorded</span>
                                    <p class="text-xs sm:text-sm text-slate-600 mt-1 font-medium">Your certified opening balances and monthly accruals will be posted by HRMU.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <!-- Footer: Total Carried Forwarded -->
                    <tfoot>
                        <tr class="bg-slate-100 font-extrabold text-slate-900 border-t-2 border-slate-400 text-xs sm:text-sm">
                            <td colspan="2" class="px-4 py-3 uppercase tracking-wider text-slate-800 border-r border-slate-300">
                                Total Carried Forwarded
                            </td>
                            <td class="px-2 py-3 text-center font-mono text-emerald-800 border-r border-slate-300">
                                —
                            </td>
                            <td class="px-2 py-3 text-center font-mono text-rose-700 border-r border-slate-300">
                                —
                            </td>
                            <td class="px-2 py-3 text-center font-mono text-emerald-950 bg-emerald-100/70 border-r border-slate-300 text-base font-black">
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
                            <td class="px-2 py-3 text-center font-mono text-sky-950 bg-sky-100/70 border-r border-slate-300 text-base font-black">
                                {{ $latestSlBalance }}
                            </td>
                            <td class="px-2 py-3 text-center font-mono border-r border-slate-300">
                                —
                            </td>
                            <td class="px-3 py-3 text-slate-700 text-xs font-semibold italic">
                                Certified Municipal Balances as of latest cut-off.
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- Interactive View Switching & Year Scripts -->
<script>
    function setRecordView(mode) {
        const simplifiedView = document.getElementById('simplifiedLedgerView');
        const originalView = document.getElementById('originalLedgerView');
        const badge = document.getElementById('headerModeBadge');
        const label = document.getElementById('toggleViewBtnLabel');
        const icon = document.getElementById('toggleViewIcon');
        const btn = document.getElementById('btnToggleRecordView');

        if (!simplifiedView || !originalView) return;

        if (mode === 'original') {
            simplifiedView.classList.add('hidden');
            originalView.classList.remove('hidden');

            if (badge) {
                badge.textContent = 'OFFICIAL LEAVE LEDGER';
                badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300';
            }
            if (label) label.textContent = 'View Simple Format';
            if (icon) icon.className = 'ph ph-table text-base sm:text-lg';
            if (btn) {
                btn.classList.remove('bg-gov-700', 'border-gov-700', 'hover:bg-gov-800', 'hover:border-gov-800');
                btn.classList.add('bg-slate-900', 'border-slate-900', 'hover:bg-slate-800', 'hover:border-slate-800');
            }

            try { localStorage.setItem('clacts_record_view', 'original'); } catch (e) {}
        } else {
            originalView.classList.add('hidden');
            simplifiedView.classList.remove('hidden');

            if (badge) {
                badge.textContent = 'MONTHLY STATEMENT VIEW';
                badge.className = 'inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-gov-100 text-gov-800 border border-gov-200';
            }
            if (label) label.textContent = 'View Original Format (CSC Form 41)';
            if (icon) icon.className = 'ph ph-file-text text-base sm:text-lg';
            if (btn) {
                btn.classList.remove('bg-slate-900', 'border-slate-900', 'hover:bg-slate-800', 'hover:border-slate-800');
                btn.classList.add('bg-gov-700', 'border-gov-700', 'hover:bg-gov-800', 'hover:border-gov-800');
            }

            try { localStorage.setItem('clacts_record_view', 'simplified'); } catch (e) {}
        }
    }

    function toggleRecordView() {
        const simplifiedView = document.getElementById('simplifiedLedgerView');
        if (!simplifiedView) return;
        const isCurrentlySimplified = !simplifiedView.classList.contains('hidden');
        setRecordView(isCurrentlySimplified ? 'original' : 'simplified');
    }

    function printCurrentRecordView() {
        const simplifiedView = document.getElementById('simplifiedLedgerView');
        const isSimplified = simplifiedView && !simplifiedView.classList.contains('hidden');

        if (isSimplified) {
            // Print the simplified monthly statement
            window.open("{{ route('employee.leave-record.print') }}?format=simple", '_blank');
        } else {
            // Print the official 11-column CS Form 41 ledger cardstock
            window.open("{{ route('employee.leave-record.print') }}?format=original", '_blank');
        }
    }

    function switchYear(selectedYear) {
        const yearGroups = document.querySelectorAll('.year-content-group');
        const tabBtns = document.querySelectorAll('.year-tab-btn');

        tabBtns.forEach(btn => {
            btn.classList.remove('bg-white', 'text-gov-800', 'shadow-xs', 'border', 'border-slate-300/80');
            btn.classList.add('text-slate-600', 'hover:text-slate-900', 'hover:bg-white/60');
        });

        const activeBtn = document.getElementById('yearTabBtn_' + selectedYear);
        if (activeBtn) {
            activeBtn.classList.add('bg-white', 'text-gov-800', 'shadow-xs', 'border', 'border-slate-300/80');
            activeBtn.classList.remove('text-slate-600', 'hover:text-slate-900', 'hover:bg-white/60');
        }

        yearGroups.forEach(group => {
            if (selectedYear === 'all') {
                group.classList.remove('hidden');
            } else {
                const targetId = 'yearGroup_' + selectedYear;
                if (group.id === targetId) {
                    group.classList.remove('hidden');
                } else {
                    group.classList.add('hidden');
                }
            }
        });

        try { localStorage.setItem('clacts_record_year', selectedYear); } catch (e) {}
    }

    // Initialize view & year according to URL parameters or saved preferences
    document.addEventListener('DOMContentLoaded', function() {
        try {
            const urlParams = new URLSearchParams(window.location.search);
            const viewParam = urlParams.get('view');
            const savedView = localStorage.getItem('clacts_record_view');

            if (viewParam === 'original' || (viewParam === null && savedView === 'original')) {
                setRecordView('original');
            } else {
                setRecordView('simplified');
            }

            const yearParam = urlParams.get('year');
            const savedYear = localStorage.getItem('clacts_record_year');
            const targetYear = yearParam || savedYear;
            if (targetYear && document.getElementById('yearTabBtn_' + targetYear)) {
                switchYear(targetYear);
            }
        } catch (e) {}
    });
</script>
@endsection
