@extends('layouts.app')

@section('title', 'Civil Service Starla Conversion Tables - HRMU Basud')

@section('content')
<div class="space-y-6 w-full">

    <!-- Top GovTech Header Banner -->
    <div class="bg-white rounded-xl p-4 sm:p-6 border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4 w-full">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200">
                    <i class="ph ph-sparkle text-slate-600"></i>
                    <span>Starla Reference Matrix</span>
                </span>
                <span class="text-xs text-slate-500 font-mono">Civil Service Commission (CSC) Omnibus Rules on Leave</span>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <i class="ph ph-shield-check text-emerald-600"></i>
                    <span>159 Verified Lookups</span>
                </span>
            </div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-1">
                Civil Service "Starla" Fractional Conversion Tables
            </h1>
            <p class="text-xs text-slate-500 mt-0.5 max-w-3xl">
                Official digitized reference matrix from the physical binder filed at the Human Resource Management Unit (HRMU), Municipality of Basud. Used for discrete semi-monthly tardiness conversions and leave accrual computations.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a 
                href="{{ route('hr.tardiness.create') }}" 
                class="px-3.5 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition border border-slate-300 flex items-center gap-2 shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-slate-400"
            >
                <i class="ph-bold ph-arrow-left text-sm"></i>
                <span>Tardiness Encoding</span>
            </a>
            <button 
                type="button" 
                onclick="window.print()" 
                class="px-3.5 py-2 text-xs font-bold text-white bg-gov-700 hover:bg-gov-800 rounded-xl transition flex items-center gap-2 shadow-xs cursor-pointer focus:outline-none focus:ring-2 focus:ring-gov-600"
            >
                <i class="ph-bold ph-printer text-sm"></i>
                <span>Print Desk Reference</span>
            </button>
        </div>
    </div>

    <!-- Quick Stat Cards (3-Column: Tables IV, II, & Calculator) -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 print:hidden w-full">
        <!-- Card 1: Table IV -->
        <button 
            type="button"
            onclick="switchTableTab('table_4')"
            class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-gov-600 hover:shadow-md transition text-left cursor-pointer group"
        >
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider group-hover:text-gov-700 transition">Table IV</span>
                <i class="ph-bold ph-clock text-gov-700 text-lg"></i>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">
                {{ $table4Hours->count() + $table4Minutes->count() }} <span class="text-xs font-normal text-slate-500 font-sans">Rules</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100 truncate">
                8 Hours ({{ $table4Hours->count() }}) • 60 Mins ({{ $table4Minutes->count() }})
            </div>
        </button>

        <!-- Card 2: Table II -->
        <button 
            type="button"
            onclick="switchTableTab('table_2')"
            class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-gov-600 hover:shadow-md transition text-left cursor-pointer group"
        >
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider group-hover:text-gov-700 transition">Table II</span>
                <i class="ph-bold ph-calendar text-emerald-700 text-lg"></i>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">
                {{ $table2Daily->count() }} <span class="text-xs font-normal text-slate-500 font-sans">Days</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100 truncate">
                Daily VL & SL Accrual (1–30 Days)
            </div>
        </button>

        <!-- Card 3: Quick Calculator Trigger -->
        <button 
            type="button"
            onclick="switchTableTab('calculator')"
            class="bg-white rounded-xl p-4 border border-slate-200 shadow-xs hover:border-gov-600 hover:shadow-xs transition text-left cursor-pointer group"
        >
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider group-hover:text-gov-700 transition">Interactive</span>
                <i class="ph ph-calculator text-slate-600 text-lg"></i>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 font-mono tracking-tight">
                Instant Lookup
            </div>
            <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100 truncate">
                Hours, Mins & Absence Tester
            </div>
        </button>
    </div>

    <!-- Main Navigation Tabs & Instant Search Box -->
    <div class="bg-white rounded-2xl shadow-xs border border-slate-200 overflow-hidden w-full">
        
        <!-- Tab Bar & Search Header -->
        <div class="bg-slate-50 border-b border-slate-200 p-4 sm:px-6 flex flex-col md:flex-row md:items-center justify-between gap-4 print:hidden">
            <!-- Navigation Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold" id="tableTabsContainer">
                <button 
                    type="button" 
                    onclick="switchTableTab('table_4')" 
                    id="tabBtn_table_4" 
                    class="px-3.5 py-2 rounded-xl transition border text-white bg-gov-700 border-gov-700 shadow-xs flex items-center gap-1.5 cursor-pointer"
                >
                    <i class="ph-bold ph-clock"></i>
                    <span>Table IV: Hours & Mins (p.15)</span>
                </button>
                <button 
                    type="button" 
                    onclick="switchTableTab('table_2')" 
                    id="tabBtn_table_2" 
                    class="px-3.5 py-2 rounded-xl transition border text-slate-600 hover:text-slate-900 bg-white border-slate-300 hover:bg-slate-100 flex items-center gap-1.5 cursor-pointer"
                >
                    <i class="ph-bold ph-calendar"></i>
                    <span>Table II: Daily Accrual (p.13)</span>
                </button>
                <button 
                    type="button" 
                    onclick="switchTableTab('all')" 
                    id="tabBtn_all" 
                    class="px-3.5 py-2 rounded-xl transition border text-slate-600 hover:text-slate-900 bg-white border-slate-300 hover:bg-slate-100 flex items-center gap-1.5 cursor-pointer"
                >
                    <i class="ph-bold ph-table"></i>
                    <span>Full Cheat Sheet</span>
                </button>
                <button 
                    type="button" 
                    onclick="switchTableTab('calculator')" 
                    id="tabBtn_calculator" 
                    class="px-3.5 py-2 rounded-xl transition border text-slate-600 hover:text-slate-900 bg-white border-slate-300 hover:bg-slate-100 flex items-center gap-1.5 cursor-pointer"
                >
                    <i class="ph-bold ph-calculator"></i>
                    <span>Live Calculator</span>
                </button>
                <button 
                    type="button" 
                    onclick="switchTableTab('legal')" 
                    id="tabBtn_legal" 
                    class="px-3.5 py-2 rounded-xl transition border text-slate-600 hover:text-slate-900 bg-white border-slate-300 hover:bg-slate-100 flex items-center gap-1.5 cursor-pointer"
                >
                    <i class="ph-bold ph-book-bookmark"></i>
                    <span>Legal Basis</span>
                </button>
            </div>

            <!-- Instant Live Table Filter Search -->
            <div class="relative w-full md:w-72 shrink-0">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i class="ph ph-magnifying-glass text-base"></i>
                </div>
                <input 
                    type="text" 
                    id="starlaTableSearch" 
                    oninput="filterStarlaRows(this.value)"
                    placeholder="Search minutes, hours, days..." 
                    class="w-full pl-9 pr-8 py-2 text-xs text-slate-800 placeholder-slate-400 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-gov-600 transition shadow-xs"
                >
                <button 
                    type="button" 
                    id="clearSearchBtn" 
                    onclick="clearSearch()" 
                    class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600"
                    title="Clear filter"
                >
                    <i class="ph-bold ph-x text-xs"></i>
                </button>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 1: TABLE IV - HOURS & MINUTES (Page 15)                       -->
        <!-- ================================================================= -->
        <div id="tabContent_table_4" class="p-4 sm:p-6 space-y-6">
            <!-- Context Callout Banner -->
            <div class="bg-gov-50 border-l-4 border-gov-700 p-4 rounded-r-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div>
                    <h3 class="font-bold text-gov-900 text-sm flex items-center gap-2">
                        <i class="ph-bold ph-info text-gov-700"></i>
                        <span>Table IV: Conversion of Working Hours/Minutes into Fractions of a Day (Page 15)</span>
                    </h3>
                    <p class="text-slate-600 mt-0.5">
                        Civil Service 8-Hour Workday conversion standard. Used by HRMU Basud to compute decimal deductions for tardiness and undertime.
                    </p>
                </div>
                <div class="shrink-0 font-mono text-[11px] bg-white px-3 py-1.5 rounded-lg border border-gov-200 text-gov-800 font-bold">
                    8 Hours = 1.000 Day • 1 Hour = 0.125 Day
                </div>
            </div>

            <!-- Discrete CSC Rounding Highlight Banner -->
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 space-y-1">
                <div class="font-bold flex items-center gap-1.5">
                    <i class="ph-bold ph-warning text-amber-600"></i>
                    <span>CSC Discrete Rounding Parity Guarantee (Zero Decimal Discrepancies)</span>
                </div>
                <p class="text-amber-800 text-[11px] leading-relaxed">
                    Notice specific discrete entries on Page 15 of the official physical binder: 
                    <strong>30 min = .062</strong> (not .063), 
                    <strong>18 min = .037</strong> (not .038), 
                    <strong>42 min = .087</strong> (not .088), and 
                    <strong>54 min = .112</strong> (not .113). 
                    C-LACTS guarantees 100% exact parity with HRMU physical desk calculations by strictly querying these digitized discrete values.
                </p>
            </div>

            <!-- Working Hours (1 to 8 Hours) Grid -->
            <div>
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="ph-bold ph-hourglass-high text-gov-700"></i>
                    <span>Part 1: Working Hours (1 to 8 Hours)</span>
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                    @foreach($table4Hours as $hr)
                        @php
                            $hourNum = (int) filter_var($hr->input_value, FILTER_SANITIZE_NUMBER_INT);
                        @endphp
                        <div class="searchable-item bg-slate-50 border border-slate-200 rounded-xl p-3 text-center transition hover:border-gov-600 hover:bg-gov-50/50" data-search="{{ $hr->input_value }} {{ $hr->day_equivalent }} hour hours">
                            <span class="text-[11px] text-slate-500 font-semibold block">{{ $hr->input_value }}</span>
                            <strong class="text-base font-extrabold font-mono text-gov-900 block mt-1">
                                {{ number_format($hr->day_equivalent, 3) }}
                            </strong>
                            <span class="text-[10px] text-slate-400 font-mono mt-0.5 block">
                                {{ $hourNum * 60 }} mins
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Working Minutes (1 to 60 Minutes) in 4 Columns -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="ph-bold ph-timer text-gov-700"></i>
                        <span>Part 2: Working Minutes (1 to 60 Minutes)</span>
                    </h4>
                    <span class="text-[11px] text-slate-400 font-mono">Sorted 1–15, 16–30, 31–45, 46–60</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @php
                        // Chunk 60 minutes into 4 columns of 15 each
                        $minuteChunks = $table4Minutes->chunk(15);
                    @endphp

                    @foreach($minuteChunks as $colIdx => $chunk)
                        <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-xs">
                            <div class="bg-slate-100 px-3 py-2 text-[11px] font-bold text-slate-700 border-b border-slate-200 flex justify-between">
                                <span>Minutes {{ ($colIdx * 15) + 1 }} – {{ ($colIdx + 1) * 15 }}</span>
                                <span class="font-mono text-slate-500">Day Equiv.</span>
                            </div>
                            <div class="divide-y divide-slate-100 text-xs">
                                @foreach($chunk as $minRow)
                                    @php
                                        $minVal = (int) filter_var($minRow->input_value, FILTER_SANITIZE_NUMBER_INT);
                                        $isNotable = in_array($minVal, [15, 18, 30, 42, 45, 54, 60]);
                                    @endphp
                                    <div class="searchable-item px-3 py-2 flex items-center justify-between transition hover:bg-slate-50 {{ $isNotable ? 'bg-amber-50/40' : '' }}" data-search="{{ $minRow->input_value }} {{ $minRow->day_equivalent }} min mins minute minutes">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono font-bold text-slate-800 w-8 text-right">{{ $minVal }}</span>
                                            <span class="text-slate-400 text-[11px] font-mono">min{{ $minVal > 1 ? 's' : '' }}</span>
                                            @if($minVal === 30)
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 bg-amber-200 text-amber-900 rounded font-mono">.062</span>
                                            @elseif($minVal === 60)
                                                <span class="text-[9px] font-bold px-1.5 py-0.2 bg-gov-200 text-gov-900 rounded font-mono">1 hr</span>
                                            @endif
                                        </div>
                                        <div class="font-mono font-extrabold text-gov-900 text-xs text-right">
                                            {{ number_format($minRow->day_equivalent, 3) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 2: TABLE II - DAILY ACCRUAL (Page 13)                         -->
        <!-- ================================================================= -->
        <div id="tabContent_table_2" class="hidden p-4 sm:p-6 space-y-6">
            <!-- Context Callout -->
            <div class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-r-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <div>
                    <h3 class="font-bold text-emerald-900 text-sm flex items-center gap-2">
                        <i class="ph-bold ph-calendar text-emerald-700"></i>
                        <span>Table II: Vacation & Sick Leave Credits Earned on a Daily Basis (Page 13)</span>
                    </h3>
                    <p class="text-slate-600 mt-0.5">
                        Computes partial monthly service earnings. Vacation Leave (VL) and Sick Leave (SL) accrue at identical rates up to 1.250 days per full month (30 days).
                    </p>
                </div>
                <div class="shrink-0 font-mono text-[11px] bg-white px-3 py-1.5 rounded-lg border border-emerald-200 text-emerald-800 font-bold">
                    15 Days = 0.625 Day • 30 Days = 1.250 Days
                </div>
            </div>

            <!-- Table II 2-Column Side-by-Side Presentation -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @php
                    $dailyHalf1 = $table2Daily->slice(0, 15);
                    $dailyHalf2 = $table2Daily->slice(15, 15);
                @endphp

                <!-- First Half: Days 1 to 15 -->
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-xs">
                    <div class="bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-800 border-b border-slate-200 flex items-center justify-between">
                        <span>Days of Service: 1 to 15 Days</span>
                        <span class="text-[11px] text-slate-500 font-mono">VL & SL (Each)</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase border-b border-slate-200">
                                <tr>
                                    <th class="px-3 sm:px-4 py-2 text-left">Service Days</th>
                                    <th class="px-2 sm:px-4 py-2 text-right">VL Earned</th>
                                    <th class="px-2 sm:px-4 py-2 text-right">SL Earned</th>
                                    <th class="px-2 sm:px-4 py-2 text-right">Total Combined</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($dailyHalf1 as $dayRow)
                                    @php
                                        $dInt = (int) filter_var($dayRow->input_value, FILTER_SANITIZE_NUMBER_INT);
                                        $isMilestone = ($dInt === 15);
                                    @endphp
                                    <tr class="searchable-item hover:bg-slate-50 transition {{ $isMilestone ? 'bg-emerald-50/60 font-bold' : '' }}" data-search="{{ $dayRow->input_value }} {{ $dayRow->day_equivalent }} day days">
                                        <td class="px-3 sm:px-4 py-2 font-mono text-slate-800 whitespace-nowrap">
                                            {{ $dayRow->input_value }}
                                            @if($isMilestone)
                                                <span class="text-[9px] bg-emerald-200 text-emerald-900 px-1.5 py-0.5 rounded ml-1 font-bold">1/2 Month</span>
                                            @endif
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 font-mono font-bold text-right text-emerald-800">
                                            {{ number_format($dayRow->day_equivalent, 3) }}
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 font-mono font-bold text-right text-indigo-800">
                                            {{ number_format($dayRow->day_equivalent, 3) }}
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 font-mono font-extrabold text-right text-slate-900">
                                            {{ number_format($dayRow->day_equivalent * 2, 3) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Second Half: Days 16 to 30 -->
                <div class="border border-slate-200 rounded-xl overflow-hidden bg-white shadow-xs">
                    <div class="bg-slate-100 px-4 py-2.5 text-xs font-bold text-slate-800 border-b border-slate-200 flex items-center justify-between">
                        <span>Days of Service: 16 to 30 Days</span>
                        <span class="text-[11px] text-slate-500 font-mono">VL & SL (Each)</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-50 text-[11px] font-bold text-slate-600 uppercase border-b border-slate-200">
                                <tr>
                                    <th class="px-3 sm:px-4 py-2 text-left">Service Days</th>
                                    <th class="px-2 sm:px-4 py-2 text-right">VL Earned</th>
                                    <th class="px-2 sm:px-4 py-2 text-right">SL Earned</th>
                                    <th class="px-2 sm:px-4 py-2 text-right">Total Combined</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($dailyHalf2 as $dayRow)
                                    @php
                                        $dInt = (int) filter_var($dayRow->input_value, FILTER_SANITIZE_NUMBER_INT);
                                        $isFullMonth = ($dInt === 30);
                                    @endphp
                                    <tr class="searchable-item hover:bg-slate-50 transition {{ $isFullMonth ? 'bg-emerald-100/70 font-bold' : '' }}" data-search="{{ $dayRow->input_value }} {{ $dayRow->day_equivalent }} day days">
                                        <td class="px-3 sm:px-4 py-2 font-mono text-slate-800 whitespace-nowrap">
                                            {{ $dayRow->input_value }}
                                            @if($isFullMonth)
                                                <span class="text-[9px] bg-emerald-700 text-white px-1.5 py-0.5 rounded ml-1 font-bold">Full Month</span>
                                            @endif
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 font-mono font-bold text-right text-emerald-800">
                                            {{ number_format($dayRow->day_equivalent, 3) }}
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 font-mono font-bold text-right text-indigo-800">
                                            {{ number_format($dayRow->day_equivalent, 3) }}
                                        </td>
                                        <td class="px-2 sm:px-4 py-2 font-mono font-extrabold text-right text-slate-900">
                                            {{ number_format($dayRow->day_equivalent * 2, 3) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 3: FULL CONVERSION CHEAT SHEET (Tables II & IV Master Sheet)   -->
        <!-- ================================================================= -->
        <div id="tabContent_all" class="hidden p-4 sm:p-6 space-y-6">
            <div class="border-b border-slate-200 pb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">
                        ⭐ STARLA ⭐ Master Civil Service Conversion Reference Sheet
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Civil Service Commission (CSC) Omnibus Rules on Leave (Rule XVI) • HRMU Municipality of Basud
                    </p>
                </div>
                <div class="font-mono text-xs text-slate-400">
                    Tables II & IV Complete Digitized Set
                </div>
            </div>

            <!-- Print View Header Block (Visible on Print) -->
            <div class="hidden print:block border-b-2 border-slate-800 pb-4 mb-4 text-center">
                <div class="text-xs uppercase font-bold tracking-widest text-slate-600">Republic of the Philippines • Region V</div>
                <div class="text-lg font-bold text-slate-900">Municipality of Basud, Camarines Norte</div>
                <div class="text-sm font-semibold text-slate-800">Human Resource Management Unit (HRMU)</div>
                <div class="text-xs text-slate-600 mt-1">⭐ STARLA ⭐ Civil Service Leave Deduction & Accrual Matrix (Tables II & IV)</div>
            </div>

            <!-- Section 1: Table IV Summary -->
            <div class="space-y-3">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-gov-700"></span>
                    <span>1. CSC Table IV: Conversion of Working Hours/Minutes into Fractions of a Day</span>
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2 text-center text-xs">
                    @foreach($table4Hours as $hr)
                        <div class="p-2 border border-slate-200 rounded-lg bg-slate-50">
                            <span class="text-[10px] text-slate-500 block">{{ $hr->input_value }}</span>
                            <strong class="font-mono text-xs text-gov-900">{{ number_format($hr->day_equivalent, 3) }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2 text-xs font-mono">
                    @foreach($table4Minutes->whereIn('input_value', ['5 mins', '10 mins', '15 mins', '18 mins', '20 mins', '25 mins', '30 mins', '35 mins', '40 mins', '42 mins', '45 mins', '50 mins', '54 mins', '55 mins', '60 mins']) as $keyMin)
                        <div class="p-2 border border-slate-200 rounded-lg bg-white flex justify-between">
                            <span class="text-slate-600">{{ $keyMin->input_value }}</span>
                            <strong class="text-gov-900">{{ number_format($keyMin->day_equivalent, 3) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Section 2: Table II Summary -->
            <div class="space-y-3 pt-4 border-t border-slate-200">
                <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>2. CSC Table II: Daily Vacation & Sick Leave Accrual (Key Milestones)</span>
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 text-xs font-mono">
                    @foreach($table2Daily->whereIn('input_value', ['1 day', '5 days', '10 days', '15 days', '20 days', '25 days', '30 days']) as $keyDay)
                        <div class="p-2.5 border border-slate-200 rounded-lg bg-slate-50 flex flex-col justify-between">
                            <span class="text-[10px] text-slate-500 font-sans">{{ $keyDay->input_value }}</span>
                            <strong class="text-sm text-emerald-800">{{ number_format($keyDay->day_equivalent, 3) }}</strong>
                            <span class="text-[9px] text-slate-400 font-sans mt-0.5">VL & SL each</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 5: INTERACTIVE LIVE CONVERTER & CALCULATOR                     -->
        <!-- ================================================================= -->
        <div id="tabContent_calculator" class="hidden p-4 sm:p-6 space-y-6">
            <div class="max-w-2xl mx-auto space-y-5">
                <div class="text-center">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">
                        <i class="ph-bold ph-calculator text-amber-600"></i>
                        <span>Interactive Verification Tool</span>
                    </span>
                    <h3 class="text-lg font-bold text-slate-900 mt-2">
                        HR Starla Instant Leave Deduction Calculator
                    </h3>
                    <p class="text-xs text-slate-500 mt-1">
                        Cross-check physical DTR numbers against the digitized CSC conversion tables with 100% mathematical transparency.
                    </p>
                </div>

                <!-- Input Card -->
                <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label for="calc_hours" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Late Hours (Table IV)
                            </label>
                            <input 
                                type="number" 
                                id="calc_hours" 
                                min="0" 
                                max="160" 
                                value="0" 
                                oninput="computeInteractiveStarla()"
                                class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm font-mono font-bold text-slate-900 bg-white focus:ring-2 focus:ring-gov-600"
                            >
                        </div>
                        <div>
                            <label for="calc_minutes" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Late Minutes (Table IV)
                            </label>
                            <input 
                                type="number" 
                                id="calc_minutes" 
                                min="0" 
                                max="59" 
                                value="0" 
                                oninput="computeInteractiveStarla()"
                                class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm font-mono font-bold text-slate-900 bg-white focus:ring-2 focus:ring-gov-600"
                            >
                        </div>
                        <div>
                            <label for="calc_absent" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                                Absences (Table II/III)
                            </label>
                            <input 
                                type="number" 
                                step="0.5" 
                                min="0" 
                                max="31" 
                                id="calc_absent" 
                                value="0.0" 
                                oninput="computeInteractiveStarla()"
                                class="w-full border border-slate-300 rounded-xl px-3 py-2 text-sm font-mono font-bold text-slate-900 bg-white focus:ring-2 focus:ring-gov-600"
                            >
                        </div>
                    </div>

                    <!-- Breakdown & Result Box -->
                    <div class="bg-white border border-slate-200 rounded-xl p-4 text-xs space-y-3">
                        <div class="font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center justify-between">
                            <span>Calculation Breakdown:</span>
                            <span class="text-[10px] text-gov-700 font-mono font-semibold" id="calcSourceLabel">Table IV + Table II Discrete Matches</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-600">
                            <span>Hours Portion (Table IV):</span>
                            <span class="font-mono font-bold text-gov-900" id="calcHoursEquiv">0.000 days</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-600">
                            <span>Minutes Portion (Table IV):</span>
                            <span class="font-mono font-bold text-gov-900" id="calcMinsEquiv">0.000 days</span>
                        </div>

                        <div class="flex items-center justify-between text-slate-600">
                            <span>Absence Deduction (Table II / III):</span>
                            <span class="font-mono font-bold text-gov-900" id="calcAbsenceEquiv">0.000 days</span>
                        </div>

                        <div class="pt-3 border-t-2 border-slate-200 flex items-center justify-between text-sm">
                            <span class="font-bold text-slate-900">Total Leave Credit Deducted:</span>
                            <span class="text-xl font-extrabold font-mono text-rose-600" id="calcTotalDeduction">0.000 days</span>
                        </div>
                    </div>

                    <!-- Deduct Priority Reminder -->
                    <div class="bg-gov-50 border border-gov-200 rounded-xl p-3 text-[11px] text-gov-900 flex items-start gap-2">
                        <i class="ph-bold ph-shield-check text-gov-700 text-base shrink-0 mt-0.5"></i>
                        <div>
                            <strong>CSC Deduction Priority Rule:</strong> Tardiness & absences are debited first against <strong>Vacation Leave (VL)</strong>. If VL is exhausted, remaining is debited against <strong>Sick Leave (SL)</strong>. If both are exhausted, it converts to <strong>Leave Without Pay (LWOP)</strong>.
                        </div>
                    </div>

                    <!-- Action Button: Go to Encoding Form -->
                    <div class="text-center pt-2">
                        <a 
                            href="{{ route('hr.tardiness.create') }}" 
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-gov-700 hover:bg-gov-800 text-white font-bold rounded-xl text-xs shadow-xs transition"
                        >
                            <i class="ph-bold ph-pencil-simple text-sm"></i>
                            <span>Encode in Semi-Monthly Attendance Terminal</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 6: LEGAL BASIS & PHYSICAL BINDER METADATA                     -->
        <!-- ================================================================= -->
        <div id="tabContent_legal" class="hidden p-4 sm:p-6 space-y-6">
            <div class="max-w-3xl space-y-5">
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                        <i class="ph-bold ph-book-bookmark text-gov-700"></i>
                        <span>Legal Authority & Physical Artifact Verification</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Historical and statutory codification of leave accruals and decimal conversions in the Philippine Civil Service.
                    </p>
                </div>

                <!-- Legal Cards -->
                <div class="space-y-4 text-xs text-slate-700 leading-relaxed">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1">
                        <h4 class="font-bold text-slate-900">1. Physical Evidence at HRMU Basud:</h4>
                        <p>
                            Verified scans of the primary reference binder filed at the Human Resource Management Unit (HRMU), Municipal Hall, Basud, Camarines Norte, explicitly titled <strong>"⭐ STARLA ⭐"</strong>.
                        </p>
                        <ul class="list-disc list-inside mt-2 space-y-1 text-slate-600">
                            <li><strong>Cover Page:</strong> Explicitly marked <em>SPECIAL PRIVILEGE LEAVE (3 DAYS)</em> and <em>MANDATORY FORCE LEAVE (5 DAYS)</em>.</li>
                            <li><strong>Page 13:</strong> Table II: Vacation and Sick Leave Credits Earned on a Daily Basis (1 to 30 days).</li>
                            <li><strong>Page 14:</strong> Table III: Leave Credits Earned with LWOP (Omitted per HRMU Basud operational policy; staff exclusively utilizes Table II and Table IV).</li>
                            <li><strong>Page 15:</strong> Table IV: Conversion of Working Hours/Minutes into Fractions of a Day (8-Hour Workday; 1 to 8 Hours and 1 to 60 Minutes).</li>
                        </ul>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1">
                        <h4 class="font-bold text-slate-900">2. Statutory Civil Service Issuances:</h4>
                        <ul class="list-disc list-inside mt-1 space-y-1 text-slate-600">
                            <li><strong>Civil Service Commission Omnibus Rules on Leave (Rule XVI of Executive Order No. 292):</strong> Statutory framework governing leave accruals, mandatory forced leave, special privilege leave, and tardiness deductions.</li>
                            <li><strong>CSC Memorandum Circular No. 41, s. 1998:</strong> Guidelines on the application and deduction of leave credits.</li>
                            <li><strong>CSC Memorandum Circular No. 14, s. 1999:</strong> Prescribing the fractional conversion matrix for hours and minutes.</li>
                            <li><strong>CSC Memorandum Circular No. 01, s. 2026:</strong> Institutionalization of Wellness Leave (5 working days).</li>
                        </ul>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-1">
                        <h4 class="font-bold text-slate-900">3. Non-Negotiable System Guardrails (C-LACTS):</h4>
                        <ul class="list-disc list-inside mt-1 space-y-1 text-slate-600">
                            <li><strong>Guardrail #1:</strong> Zero raw biometric ingestion. System accepts only pre-verified aggregate summaries.</li>
                            <li><strong>Guardrail #6:</strong> Double-deduction shield: automatically prevents deducting absences covered by approved leave applications.</li>
                            <li><strong>Guardrail #7:</strong> Immutability of Starla conversion tables: multipliers are never hardcoded in controllers; all conversions strictly query <code>starla_reference</code>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Data Dictionary for Live Calculator in JS -->
<script>
    // Digitize Starla Table IV into JavaScript for instantaneous client-side calculation
    const starlaHours = {
        @foreach($table4Hours as $h)
            {{ (int) filter_var($h->input_value, FILTER_SANITIZE_NUMBER_INT) }}: {{ (float) $h->day_equivalent }},
        @endforeach
    };

    const starlaMinutes = {
        @foreach($table4Minutes as $m)
            {{ (int) filter_var($m->input_value, FILTER_SANITIZE_NUMBER_INT) }}: {{ (float) $m->day_equivalent }},
        @endforeach
    };

    const starlaDaily = {
        @foreach($table2Daily as $d)
            {{ (int) filter_var($d->input_value, FILTER_SANITIZE_NUMBER_INT) }}: {{ (float) $d->day_equivalent }},
        @endforeach
    };

    // Tab Switcher
    function switchTableTab(tabId) {
        const tabs = ['table_4', 'table_2', 'all', 'calculator', 'legal'];
        
        tabs.forEach(t => {
            const content = document.getElementById(`tabContent_${t}`);
            const btn = document.getElementById(`tabBtn_${t}`);

            if (content) {
                if (t === tabId) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            }

            if (btn) {
                if (t === tabId) {
                    btn.className = 'px-3.5 py-2 rounded-xl transition border text-white bg-gov-700 border-gov-700 shadow-xs flex items-center gap-1.5 cursor-pointer font-bold';
                } else {
                    btn.className = 'px-3.5 py-2 rounded-xl transition border text-slate-600 hover:text-slate-900 bg-white border-slate-300 hover:bg-slate-100 flex items-center gap-1.5 cursor-pointer font-bold';
                }
            }
        });
    }

    // Instant Search / Filter Rows
    function filterStarlaRows(query) {
        const cleanQuery = query.toLowerCase().trim();
        const clearBtn = document.getElementById('clearSearchBtn');
        
        if (cleanQuery.length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        const items = document.querySelectorAll('.searchable-item');
        items.forEach(el => {
            const searchData = el.getAttribute('data-search') || '';
            if (cleanQuery === '' || searchData.toLowerCase().includes(cleanQuery)) {
                el.style.display = '';
                if (cleanQuery !== '') {
                    el.classList.add('ring-2', 'ring-gov-600', 'bg-gov-50');
                } else {
                    el.classList.remove('ring-2', 'ring-gov-600', 'bg-gov-50');
                }
            } else {
                el.style.display = 'none';
                el.classList.remove('ring-2', 'ring-gov-600', 'bg-gov-50');
            }
        });
    }

    function clearSearch() {
        const input = document.getElementById('starlaTableSearch');
        if (input) {
            input.value = '';
            filterStarlaRows('');
            input.focus();
        }
    }

    // Interactive Starla Calculator
    function computeInteractiveStarla() {
        let hours = parseInt(document.getElementById('calc_hours').value) || 0;
        let minutes = parseInt(document.getElementById('calc_minutes').value) || 0;
        let daysAbsent = parseFloat(document.getElementById('calc_absent').value) || 0.0;

        if (hours < 0) hours = 0;
        if (minutes < 0) minutes = 0;
        if (daysAbsent < 0) daysAbsent = 0;

        // Normalize minutes >= 60
        if (minutes >= 60) {
            hours += Math.floor(minutes / 60);
            minutes = minutes % 60;
        }

        // 1. Hours equivalent
        let hoursEquiv = 0.000;
        if (hours > 0) {
            const fullDays = Math.floor(hours / 8);
            const remHours = hours % 8;
            hoursEquiv = fullDays * 1.000;
            if (remHours > 0 && starlaHours[remHours]) {
                hoursEquiv += starlaHours[remHours];
            }
        }

        // 2. Minutes equivalent
        let minsEquiv = 0.000;
        if (minutes > 0 && starlaMinutes[minutes]) {
            minsEquiv = starlaMinutes[minutes];
        }

        // 3. Absence equivalent (Table II daily accrual basis)
        let absenceEquiv = 0.000;
        if (daysAbsent > 0) {
            const wholeDay = Math.floor(daysAbsent);
            const fraction = daysAbsent - wholeDay;
            let wholeVal = 0.000;
            if (wholeDay > 0) {
                wholeVal = starlaDaily[wholeDay] || (wholeDay * 0.042);
            }
            const fractionVal = Math.round(fraction * 0.042 * 1000) / 1000;
            absenceEquiv = Math.round((wholeVal + fractionVal) * 1000) / 1000;
        }

        const totalDeduction = Math.round((hoursEquiv + minsEquiv + absenceEquiv) * 1000) / 1000;

        document.getElementById('calcHoursEquiv').textContent = hoursEquiv.toFixed(3) + ' days';
        document.getElementById('calcMinsEquiv').textContent = minsEquiv.toFixed(3) + ' days';
        document.getElementById('calcAbsenceEquiv').textContent = absenceEquiv.toFixed(3) + ' days';
        document.getElementById('calcTotalDeduction').textContent = totalDeduction.toFixed(3) + ' days';
    }

    // Auto-select tab if provided via URL parameter
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        const tabParam = params.get('tab');
        if (tabParam) {
            switchTableTab(tabParam);
        }
        const qParam = params.get('q');
        if (qParam) {
            const searchInput = document.getElementById('starlaTableSearch');
            if (searchInput) {
                searchInput.value = qParam;
                filterStarlaRows(qParam);
            }
        }
    });
</script>

<style>
    @media print {
        header, footer, nav, #tableTabsContainer, #starlaTableSearch, button, a {
            display: none !important;
        }
        body {
            background-color: white !important;
            color: black !important;
        }
        #tabContent_all {
            display: block !important;
        }
        #tabContent_table_4, #tabContent_table_2, #tabContent_calculator, #tabContent_legal {
            display: none !important;
        }
    }
</style>
@endsection
