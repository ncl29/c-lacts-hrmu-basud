<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Leave Summary Table - {{ $employee->full_name }} (Control No. {{ $employee->control_no }})</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=JetBrains+Mono:wght@500;600;700;800&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #fff;
            color: #0f172a;
            font-size: 11px;
            padding: 24px 28px;
            line-height: 1.35;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
            font-variant-numeric: tabular-nums;
        }

        .table-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid #0f172a;
        }

        .table-title {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
        }

        .table-subtitle {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            margin-top: 2px;
        }

        .table-meta {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            text-align: right;
        }

        .year-label {
            font-weight: 800;
            font-size: 12px;
            margin-top: 14px;
            margin-bottom: 6px;
            text-transform: uppercase;
            color: #0f172a;
        }

        /* Monthly Summary Data Table */
        table.statement-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #0f172a;
            font-size: 10px;
            margin-bottom: 16px;
        }

        table.statement-table th,
        table.statement-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            vertical-align: top;
        }

        table.statement-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 9px;
            text-align: left;
        }

        table.statement-table th.text-right,
        table.statement-table td.text-right {
            text-align: right;
        }

        table.statement-table th.text-center,
        table.statement-table td.text-center {
            text-align: center;
        }

        .earned-val {
            font-weight: 700;
            color: #065f46;
        }

        .deduct-item {
            border-left: 2px solid #e11d48;
            padding-left: 5px;
            margin-bottom: 4px;
        }

        .deduct-val {
            font-weight: 700;
            color: #9f1239;
        }

        .total-cell {
            background-color: #f8fafc;
            font-weight: 900;
            color: #0f172a;
        }

        /* On-Screen Print Button */
        .print-btn-bar {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            gap: 8px;
        }

        .btn-print {
            background: #0f172a;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background: #1e293b;
        }

        @media print {
            body {
                padding: 0;
            }
            .print-btn-bar {
                display: none !important;
            }
            @page {
                size: portrait;
                margin: 12mm 10mm;
            }
            table.statement-table th {
                background-color: #0f172a !important;
                color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen floating action bar (hidden in print) -->
    <div class="print-btn-bar no-print">
        <button onclick="window.print()" class="btn-print">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <span>Print Leave Records</span>
        </button>
    </div>

    <!-- Minimal Table Header Identifying the Personnel & Table -->
    <div class="table-header-bar">
        <div>
            <div class="table-title">Monthly Leave Summary Table</div>
            <div class="table-subtitle">{{ $employee->full_name }} &bull; {{ $employee->position ?? 'Municipal Personnel' }} &bull; {{ $employee->office->office_name ?? 'LGU Basud' }}</div>
        </div>
        <div class="table-meta font-mono">
            Control No. {{ $employee->control_no }}
        </div>
    </div>

    @php
        function friendlyActivityName($particular) {
            $t = trim($particular ?? '');
            $lower = strtolower($t);
            if (str_contains($lower, 'opening balance') || str_contains($lower, 'certified baseline')) return 'Starting Balance';
            if (str_contains($lower, 'monthly accrual') || (str_contains($lower, 'accrual') && !str_contains($lower, 'prorated'))) return 'Monthly Earned Credit (+1.25 VL / +1.25 SL)';
            if (str_contains($lower, 'prorated')) return 'Prorated Monthly Credit';
            if (preg_match('/^l\s*&\s*ut\b/i', $t) || str_contains($lower, 'tardiness') || str_contains($lower, 'undertime') || str_contains($lower, 'late')) return 'Deduction for Late / Undertime';
            if (preg_match('/^vl\b/i', $t) || str_contains($lower, 'vacation leave')) return 'Vacation Leave';
            if (preg_match('/^sl\b/i', $t) || str_contains($lower, 'sick leave')) return 'Sick Leave';
            if (preg_match('/^spl\b/i', $t) || str_contains($lower, 'special privilege')) return 'Special Privilege Leave';
            if (preg_match('/^fl\b/i', $t) || str_contains($lower, 'forced leave') || str_contains($lower, 'mandatory')) return 'Mandatory 5-Day Forced Leave';
            if (str_contains($lower, 'wellness')) return 'Wellness Leave';
            if (str_contains($lower, 'solo parent')) return 'Solo Parent Leave';
            if (str_contains($lower, 'maternity')) return 'Maternity Leave';
            if (str_contains($lower, 'paternity')) return 'Paternity Leave';
            if (str_contains($lower, 'lwop') || str_contains($lower, 'without pay')) return 'Leave Without Pay';
            if (str_contains($lower, 'abs') || str_contains($lower, 'absent')) return 'Deduction for Absence';
            return $t !== '' ? $t : 'Leave Activity';
        }
    @endphp

    <!-- Monthly Summary Table -->
    @foreach($recordsByYear as $yr => $months)
        @if(count($recordsByYear) > 1)
            <div class="year-label">
                Calendar Year {{ $yr }}
            </div>
        @endif

        <table class="statement-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Month</th>
                    <th style="width: 20%;">Leave Added (+)</th>
                    <th style="width: 36%;">Leaves Taken & Deductions (-)</th>
                    <th style="width: 10%;" class="text-right">Vacation Left</th>
                    <th style="width: 10%;" class="text-right">Sick Left</th>
                    <th style="width: 10%;" class="text-right">Total Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($months as $sortKey => $m)
                    <tr>
                        <!-- Month -->
                        <td style="font-weight: 800; color: #0f172a;">
                            {{ $m['month_label'] }}
                        </td>

                        <!-- Added -->
                        <td>
                            @if($m['has_starting_balance'])
                                <div style="font-size: 9px; font-weight: 700; color: #1e3a8a; margin-bottom: 2px;">
                                    Starting Balance Carried Forward
                                </div>
                            @endif

                            @if($m['earned_vl'] > 0 || $m['earned_sl'] > 0)
                                <div class="font-mono earned-val">
                                    +{{ number_format($m['earned_vl'], 3) }} Vacation
                                </div>
                                <div class="font-mono earned-val" style="margin-top: 1px;">
                                    +{{ number_format($m['earned_sl'], 3) }} Sick
                                </div>
                            @elseif(!$m['has_starting_balance'])
                                <span style="color: #94a3b8; font-style: italic;">None added</span>
                            @endif
                        </td>

                        <!-- Leaves Taken -->
                        <td>
                            @if(!empty($m['leaves_taken_items']) && count($m['leaves_taken_items']) > 0)
                                @foreach($m['leaves_taken_items'] as $item)
                                    <div class="deduct-item">
                                        <div style="font-weight: 700; color: #0f172a;">
                                            {{ friendlyActivityName($item['particular']) }}
                                            @if(!empty($item['leave_dates']))
                                                <span style="font-size: 9px; font-weight: 500; color: #64748b;">(Leave: {{ $item['leave_dates'] }})</span>
                                            @endif
                                        </div>
                                        <div class="font-mono deduct-val" style="font-size: 9.5px; margin-top: 1px;">
                                            @if($item['vl_used'] > 0) -{{ number_format($item['vl_used'], 3) }} Vacation @endif
                                            @if($item['sl_used'] > 0) -{{ number_format($item['sl_used'], 3) }} Sick @endif
                                            @if($item['vl_lwop'] > 0) ({{ number_format($item['vl_lwop'], 3) }} LWOP) @endif
                                            @if(!empty($item['ref_code'])) <span style="color: #64748b; font-weight: normal;">• Ref: {{ $item['ref_code'] }}</span> @endif
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <span style="color: #94a3b8; font-style: italic;">No leaves taken</span>
                            @endif
                        </td>

                        <!-- Vacation Left -->
                        <td class="text-right font-mono" style="font-weight: 700;">
                            {{ $m['closing_vl'] }}
                        </td>

                        <!-- Sick Left -->
                        <td class="text-right font-mono" style="font-weight: 700;">
                            {{ $m['closing_sl'] }}
                        </td>

                        <!-- Total Balance -->
                        <td class="text-right font-mono total-cell">
                            {{ number_format((float)$m['closing_vl'] + (float)$m['closing_sl'], 3) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #94a3b8; padding: 12px;">
                            No leave activity recorded for year {{ $yr }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endforeach

</body>
</html>
