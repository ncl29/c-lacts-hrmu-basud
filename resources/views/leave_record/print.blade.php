<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Record - {{ $employee->full_name }} (Control No. {{ $employee->control_no }})</title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=JetBrains+Mono:wght@500;600;700&display=swap');

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #fff;
            color: #000;
            font-size: 11px;
            padding: 20px;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .header-title {
            text-align: center;
            font-size: 26px;
            font-weight: 900;
            letter-spacing: 2px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 12px;
        }

        .meta-field {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .meta-line {
            border-bottom: 1.5px solid #000;
            min-width: 140px;
            padding: 0 4px 1px 4px;
            font-weight: 800;
        }

        table.ledger-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            font-size: 10.5px;
        }

        table.ledger-table th, 
        table.ledger-table td {
            border: 1px solid #000;
            padding: 4px 5px;
        }

        table.ledger-table th {
            font-weight: 800;
            text-align: center;
            vertical-align: middle;
            font-size: 9.5px;
            background-color: #f8fafc;
        }

        table.ledger-table td {
            height: 22px;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: 700; }
        .font-extrabold { font-weight: 800; }

        .carried-forward-row td {
            font-weight: 900;
            border-top: 2px solid #000;
            background: #f1f5f9;
        }

        .earned-row td {
            font-weight: 800 !important;
        }

        .earned-val {
            font-weight: 900 !important;
        }

        .deduct-row td {
            font-weight: 400 !important;
        }

        .deduct-val {
            font-weight: 600 !important;
        }

        .print-btn-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
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
            gap: 8px;
            z-index: 9999;
        }

        .print-btn-container:hover {
            background: #1e293b;
        }

        @media print {
            body {
                padding: 5px;
            }
            .print-btn-container {
                display: none !important;
            }
            @page {
                size: legal landscape;
                margin: 8mm 6mm;
            }
            table.ledger-table th {
                background-color: transparent !important;
            }
        }
    </style>
</head>
<body>

    <!-- Printable Header Matching Scanned Card -->
    <div class="header-title">LEAVE RECORD</div>

    <div class="meta-row">
        <div class="meta-field" style="flex: 1.5;">
            <span>NAME</span>
            <div class="meta-line" style="flex: 1;">{{ $employee->full_name }}</div>
        </div>
        <div class="meta-field" style="flex: 1.2; justify-content: center;">
            <span>OFFICE</span>
            <div class="meta-line" style="flex: 1; min-width: 160px;">{{ $employee->office->office_name ?? 'HRMU Basud' }}</div>
        </div>
        <div class="meta-field" style="flex: 1; justify-content: flex-end;">
            <span>CONTROL NO.</span>
            <div class="meta-line font-mono" style="min-width: 120px; text-align: center;">{{ $employee->control_no }}</div>
        </div>
    </div>

    <!-- Official 11-Column Tabular Ledger -->
    <table class="ledger-table">
        <thead>
            <!-- Row 1: Group Headers -->
            <tr>
                <th rowspan="2" style="width: 85px;">PERIOD</th>
                <th rowspan="2" style="width: 140px;">PARTICULAR</th>
                <th colspan="4" style="letter-spacing: 1px;">VACATION LEAVE</th>
                <th colspan="4" style="letter-spacing: 1px;">SICK LEAVE</th>
                <th rowspan="2">DATE & ACTION TAKEN ON<br>APPLICATION FOR LEAVE</th>
            </tr>
            <!-- Row 2: Sub-headers -->
            <tr>
                <!-- VL Subcolumns -->
                <th style="width: 60px;">EARNED</th>
                <th style="width: 85px;">ABSENCE<br>UNDERTIME<br>W/ PAY</th>
                <th style="width: 70px;">BALANCE</th>
                <th style="width: 85px;">ABSENCE<br>UNDERTIME<br>W/OUT PAY</th>

                <!-- SL Subcolumns -->
                <th style="width: 60px;">EARNED</th>
                <th style="width: 85px;">ABSENCE<br>UNDERTIME<br>W/ PAY</th>
                <th style="width: 70px;">BALANCE</th>
                <th style="width: 85px;">ABSENCE<br>UNDERTIME<br>W/OUT PAY</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $rec)
                @php
                    $p = strtoupper(trim((string)($rec->particular ?? '')));
                    $isEarned = str_contains($p, 'EARNED') || (!empty($rec->vl_earned) && empty($rec->vl_absence_with_pay) && empty($rec->sl_absence_with_pay));
                @endphp
                <tr class="{{ $isEarned ? 'earned-row' : '' }}">
                    <td class="font-bold text-left">{{ $rec->period }}</td>
                    <td class="font-semibold text-left">{{ $rec->particular }}</td>
                    
                    <!-- VL -->
                    <td class="font-mono text-center {{ $isEarned ? 'earned-val' : 'font-semibold' }}">{{ $rec->vl_earned ?? '' }}</td>
                    <td class="font-mono text-center {{ !empty($rec->vl_absence_with_pay) ? 'deduct-val' : 'font-bold' }}">{{ $rec->vl_absence_with_pay ?? '' }}</td>
                    <td class="font-mono text-center font-extrabold">{{ $rec->vl_balance ?? '' }}</td>
                    <td class="font-mono text-center">{{ $rec->vl_absence_without_pay ?? '' }}</td>

                    <!-- SL -->
                    <td class="font-mono text-center {{ $isEarned ? 'earned-val' : 'font-semibold' }}">{{ $rec->sl_earned ?? '' }}</td>
                    <td class="font-mono text-center {{ !empty($rec->sl_absence_with_pay) ? 'deduct-val' : 'font-bold' }}">{{ $rec->sl_absence_with_pay ?? '' }}</td>
                    <td class="font-mono text-center font-extrabold">{{ $rec->sl_balance ?? '' }}</td>
                    <td class="font-mono text-center">{{ $rec->sl_absence_without_pay ?? '' }}</td>

                    <td class="text-left" style="font-size: 9.5px;">{{ $rec->date_action_taken ?? '' }}</td>
                </tr>
            @endforeach

            <!-- Fill remaining blank lines to match physical cardstock paper (up to 20 lines) -->
            @php $blankLines = max(0, 18 - $records->count()); @endphp
            @for($i = 0; $i < $blankLines; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr class="carried-forward-row">
                <td colspan="2" class="text-left font-extrabold uppercase">
                    Total Carried Forwarded
                </td>
                <td class="text-center font-mono">—</td>
                <td class="text-center font-mono">—</td>
                <td class="text-center font-mono font-extrabold" style="font-size: 12px;">{{ $latestVlBalance }}</td>
                <td class="text-center font-mono">—</td>
                <td class="text-center font-mono">—</td>
                <td class="text-center font-mono">—</td>
                <td class="text-center font-mono font-extrabold" style="font-size: 12px;">{{ $latestSlBalance }}</td>
                <td class="text-center font-mono">—</td>
                <td class="text-left font-bold" style="font-size: 9px; color: #475569;">
                    Certified Municipal Balances
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- Quick Floating Print Button -->
    <button type="button" class="print-btn-container" onclick="window.print()">
        <span>Print Document</span>
    </button>

</body>
</html>
