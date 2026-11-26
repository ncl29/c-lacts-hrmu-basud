@php
    $isMonetization = ($application->leaveType->code ?? '') === 'MONETIZATION'
        || in_array($application->details_type ?? '', ['monetization', 'monetization_special'], true)
        || str_contains(strtolower($application->other_leave_type ?? ''), 'monetiz')
        || str_contains(strtolower($application->details_of_leave ?? ''), 'monetiz');

    // Ensure Section 7.A is always calculated with latest balances and correct deductions
    $sec7AService = app(\App\Services\LeaveRecordService::class);
    $sec7AData = $sec7AService->calculateSection7AData($application);
    $isSection7AFilled = true;
    $certifiedAsOf = $certifiedAsOf ?? $sec7AData['as_of_formatted'];
    $vlBalance = (float)$sec7AData['vl_total_earned'];
    $vlLess = (float)$sec7AData['vl_less'];
    $vlRemain = (float)$sec7AData['vl_balance'];
    $slBalance = (float)$sec7AData['sl_total_earned'];
    $slLess = (float)$sec7AData['sl_less'];
    $slRemain = (float)$sec7AData['sl_balance'];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Civil Service Form No. 6 (Revised 2020) - {{ $application->reference_code }}</title>
    <style>
        /* =====================================================================
           A4 EXACT PAPER & PRINT CALIBRATION (210mm x 297mm)
           Official Civil Service Form No. 6 (Revised 2020) - Municipality of Basud
           Page 1: Exact Front (Application for Leave) — Identical to Scanned Form
           Page 2: Exact Back (Instructions and Requirements) — Identical to Scanned Form
           Page 3: C-LACTS QR Tracking & Stapling Cutout Slip
           ===================================================================== */
        @page {
            size: A4 portrait;
            margin: 6mm 8mm 6mm 8mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            margin: 0;
            padding: 20px 0;
            background-color: #cbd5e1;
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 8pt;
            line-height: 1.15;
            color: #000;
        }

        /* Screen Print Toolbar */
        .no-print {
            max-width: 194mm;
            margin: 0 auto 16px auto;
            background: #ffffff;
            padding: 12px 18px;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease-in-out;
            border: none;
        }

        .btn-back {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-back:hover { background: #e2e8f0; }

        .btn-print {
            background: #0f4c81;
            color: #ffffff;
        }
        .btn-print:hover { background: #0a365c; }

        .btn-sub {
            background: #ffffff;
            color: #334155;
            border: 1px solid #94a3b8;
            font-size: 12px;
            padding: 5px 10px;
        }
        .btn-sub:hover { background: #f8fafc; border-color: #64748b; }

        /* A4 Page Container (Exactly 194mm x 284mm inside printable margin) */
        .a4-page {
            width: 194mm;
            min-height: 284mm;
            max-height: 284mm;
            margin: 0 auto 24px auto;
            background: #ffffff;
            border: 1.5px solid #000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.18);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }

        .page-badge-screen {
            max-width: 194mm;
            margin: 0 auto 6px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: system-ui, -apple-system, sans-serif;
            font-size: 11px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Layout Tables & Borders */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        .border-b { border-bottom: 1px solid #000; }
        .border-t { border-top: 1px solid #000; }
        .border-r { border-right: 1px solid #000; }
        .border-l { border-left: 1px solid #000; }

        .section-header {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            letter-spacing: 0.5px;
            padding: 2.5px 0;
            border-top: 1.5px solid #000;
            border-bottom: 1px solid #000;
            background: #fff;
            text-transform: uppercase;
        }

        .chk-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 9.5px;
            height: 9.5px;
            min-width: 9.5px;
            border: 1px solid #000;
            margin-right: 4px;
            font-size: 8pt;
            font-weight: bold;
            line-height: 1;
            vertical-align: middle;
            background: #fff;
        }

        .fill-line {
            display: inline-block;
            border-bottom: 1px solid #000;
            min-height: 12px;
            vertical-align: bottom;
            padding: 0 4px;
        }

        /* Credits Table */
        .credits-table {
            width: 100%;
            border: 1px solid #000;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .credits-table th, .credits-table td {
            border: 1px solid #000;
            padding: 2px 4px;
        }

        /* Cutout dashed styles for Page 3 */
        .cutout-slip-card {
            border: 2px dashed #000;
            padding: 10px 14px;
            background: #ffffff;
            position: relative;
        }

        /* Screen Responsive Styles */
        @media screen and (max-width: 800px) {
            body {
                padding: 10px 8px;
                overflow-x: auto;
            }
            .no-print {
                width: 100%;
                max-width: 100%;
                padding: 10px;
                margin-bottom: 12px;
            }
            .no-print .btn {
                padding: 6px 10px;
                font-size: 11px;
            }
            .page-badge-screen {
                max-width: 100%;
                font-size: 10px;
            }
        }

        /* Print Media Styles */
        @media print {
            body {
                background: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print, .page-badge-screen {
                display: none !important;
            }
            .a4-page {
                box-shadow: none !important;
                margin: 0 !important;
                width: 194mm !important;
                height: 284mm !important;
                max-height: 284mm !important;
                border: 1.5px solid #000 !important;
                page-break-after: always !important;
                break-after: page !important;
            }
            .a4-page:last-child {
                page-break-after: auto !important;
                break-after: auto !important;
            }

            /* Selective print classes */
            body.print-form-only .page-qr-slip {
                display: none !important;
            }
            body.print-slip-only .page-form-front,
            body.print-slip-only .page-form-back {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- =======================================================================
         SCREEN PRINT CONTROL BAR (HIDDEN IN PRINT)
         ======================================================================= -->
    <div class="no-print">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <button onclick="window.history.back()" class="btn btn-back">
                    ← Back
                </button>
                <div style="font-size: 14px; font-weight: bold; color: #0f172a;">
                    Civil Service Form No. 6 (Revised 2020)
                </div>
                <span style="font-family: monospace; font-size: 12px; background: #e0f2fe; padding: 3px 8px; border-radius: 4px; font-weight: bold; color: #0369a1; border: 1px solid #bae6fd;">
                    REF: {{ $application->reference_code }}
                </span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <button onclick="printFormOnly()" class="btn btn-sub" title="Prints Page 1 (Front) & Page 2 (Back Instructions) for duplex printing">
                    📄 Print Form 6 Only (Pages 1 & 2)
                </button>
                <button onclick="printSlipOnly()" class="btn btn-sub" title="Prints Page 3 QR Tracking Stapling Slip only">
                    ✂️ Print QR Staple Slip Only (Page 3)
                </button>
                <button onclick="printAll()" class="btn btn-print" title="Prints all 3 pages">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    <span>Print All (3 Pages)</span>
                </button>
            </div>
        </div>

        <!-- Instructional Banner -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #334155;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 16px;">📌</span>
                <span><strong>Legal Compliance Notice:</strong> By Civil Service law, the official layout of CS Form No. 6 on <strong>Page 1 (Front)</strong> and <strong>Page 2 (Back)</strong> is 100% untampered and identical to the official scanned version. The dynamic QR code is generated on <strong>Page 3</strong> — cut the QR slip along the dashed line and staple it to the upper right corner of Form 6.</span>
            </div>
            <span style="font-size: 11px; color: #64748b; font-weight: 500; white-space: nowrap; margin-left: 12px;">Standard A4 / Folio</span>
        </div>
    </div>


    <!-- =======================================================================
         PAGE 1: OFFICIAL CIVIL SERVICE FORM NO. 6 (FRONT - APPLICATION FOR LEAVE)
         EXACT IDENTICAL REPLICA OF THE SCANNED ORIGINAL
         ======================================================================= -->
    <div class="page-badge-screen">
        <span>Page 1 of 3: Civil Service Form No. 6 (Front — Application for Leave)</span>
        <span style="color: #64748b; font-weight: normal;">Untampered Official Civil Service Template</span>
    </div>

    <div class="a4-page page-form-front">

        <!-- Header -->
        <div style="padding: 4px 6px 6px 6px; display: flex; align-items: flex-start; justify-content: space-between;">
            
            <!-- Left: Form Revision Label -->
            <div style="width: 42mm; padding-top: 2px;">
                <div style="font-size: 7.5pt; font-style: italic; font-weight: bold; line-height: 1.15; font-family: Arial, sans-serif;">
                    Civil Service Form No. 6
                </div>
                <div style="font-size: 7pt; font-style: italic; line-height: 1.1; font-family: Arial, sans-serif;">
                    Revised 2020
                </div>
            </div>

            <!-- Center: Republic & Municipality Header with Official Basud Seal -->
            <div style="flex: 1; text-align: center; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: -2px;">
                <img src="{{ asset('images/basud-logo.png') }}" alt="Seal of the Municipality of Basud" style="width: 48px; height: 48px; object-fit: contain;">
                <div style="text-align: center;">
                    <div style="font-size: 8pt; line-height: 1.2; font-family: Arial, sans-serif;">Republic of the Philippines</div>
                    <div style="font-size: 8.5pt; font-weight: bold; line-height: 1.2; font-family: Arial, sans-serif;">Province of Camarines Norte</div>
                    <div style="font-size: 9pt; font-weight: bold; line-height: 1.2; text-transform: uppercase; font-family: Arial, sans-serif;">Municipality of Basud</div>
                    <div style="font-size: 11pt; font-weight: bold; margin-top: 3px; letter-spacing: 0.5px; text-transform: uppercase; font-family: Arial, sans-serif;">
                        APPLICATION FOR LEAVE
                    </div>
                </div>
            </div>

            <!-- Right: Stamp of Date of Receipt (Identical to Scanned Form - NO BOX BORDER) -->
            <div style="width: 42mm; text-align: right; padding-top: 4px; padding-right: 2px;">
                <div style="font-size: 6.8pt; font-family: Arial, sans-serif; white-space: nowrap;">
                    Stamp of Date of Receipt
                </div>
            </div>

        </div>

        <!-- ===================================================================
             BOXES 1 TO 5: EMPLOYEE & OFFICE PROFILE
             Row 1: 1. Office / Department (Colspan 1) | 2. Name (Colspan 2)
             Row 2: 3. Date of Filing | 4. Position | 5. Salary (3 Columns)
             =================================================================== -->
        <table style="border-top: 1.5px solid #000; border-bottom: 1px solid #000; font-size: 7.5pt;">
            <tr>
                <td style="width: 44%; border-right: 1px solid #000; padding: 2px 5px; vertical-align: top;">
                    <div style="font-size: 6.8pt; font-weight: bold; text-transform: uppercase;">1. OFFICE/DEPARTMENT</div>
                    <div style="font-weight: bold; font-size: 8pt; margin-top: 2px; text-transform: uppercase;">
                        {{ $application->employee->office->office_name ?? 'LGU BASUD' }}
                    </div>
                </td>
                <td colspan="2" style="width: 56%; padding: 2px 5px; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                        <tr>
                            <td style="width: 17%; font-size: 6.8pt; font-weight: bold; padding: 0; vertical-align: top; white-space: nowrap;">2. NAME :</td>
                            <td style="width: 32%; text-align: center; font-size: 6.8pt; font-weight: normal; padding: 0; vertical-align: top;">(Last)</td>
                            <td style="width: 33%; text-align: center; font-size: 6.8pt; font-weight: normal; padding: 0; vertical-align: top;">(First)</td>
                            <td style="width: 18%; text-align: center; font-size: 6.8pt; font-weight: normal; padding: 0; vertical-align: top;">(Middle)</td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="text-align: center; font-weight: bold; font-size: 8pt; text-transform: uppercase; padding-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $application->employee->last_name }}
                            </td>
                            <td style="text-align: center; font-weight: bold; font-size: 8pt; text-transform: uppercase; padding-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $application->employee->first_name }}
                            </td>
                            <td style="text-align: center; font-weight: bold; font-size: 8pt; text-transform: uppercase; padding-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $application->employee->middle_name ?? '' }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr style="border-top: 1px solid #000;">
                <!-- 3. DATE OF FILING -->
                <td style="width: 35%; border-right: 1px solid #000; padding: 3px 5px; vertical-align: middle;">
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span style="font-size: 6.8pt; font-weight: bold; white-space: nowrap;">3. DATE OF FILING</span>
                        <span style="border-bottom: 1px solid #000; flex: 1; font-weight: bold; font-size: 7.5pt; padding-left: 3px; min-height: 12px;">
                            {{ $application->date_of_filing ? $application->date_of_filing->format('F d, Y') : $application->created_at->format('F d, Y') }}
                        </span>
                    </div>
                </td>
                <!-- 4. POSITION -->
                <td style="width: 38%; border-right: 1px solid #000; padding: 3px 5px; vertical-align: middle;">
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span style="font-size: 6.8pt; font-weight: bold; white-space: nowrap;">4. POSITION</span>
                        <span style="border-bottom: 1px solid #000; flex: 1; font-weight: bold; font-size: 7pt; padding-left: 3px; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-height: 12px;">
                            {{ $application->employee->position }}
                        </span>
                    </div>
                </td>
                <!-- 5. SALARY -->
                <td style="width: 27%; padding: 3px 5px; vertical-align: middle;">
                    <div style="display: flex; align-items: baseline; gap: 4px;">
                        <span style="font-size: 6.8pt; font-weight: bold; white-space: nowrap;">5. SALARY</span>
                        <span style="border-bottom: 1px solid #000; flex: 1; font-size: 7pt; font-weight: bold; padding-left: 3px; white-space: nowrap; min-height: 12px;">
                            {{ $application->declared_salary ? '₱ ' . number_format($application->declared_salary, 2) : '—' }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- ===================================================================
             BOX 6: DETAILS OF APPLICATION
             =================================================================== -->
        <div class="section-header">
            6. DETAILS OF APPLICATION
        </div>

        <div style="display: flex; flex: 1; border-bottom: 1px solid #000; min-height: 124mm;">

            <!-- 6.A & 6.C: LEFT COLUMN -->
            <div style="width: 50%; border-right: 1px solid #000; display: flex; flex-direction: column; justify-content: space-between;">
                
                <!-- 6.A TYPE OF LEAVE -->
                <div style="padding: 3px 5px;">
                    <div style="font-size: 7pt; font-weight: bold; text-transform: uppercase; margin-bottom: 3px;">
                        6.A TYPE OF LEAVE TO BE AVAILED OF
                    </div>

                    <div style="font-size: 7.2pt; line-height: 1.25;">
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ (!$isMonetization && $application->leaveType->code === 'VL') ? '✔' : '' }}</span>
                            <span>Vacation Leave <span style="font-size: 6.5pt;">(Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'FL' ? '✔' : '' }}</span>
                            <span>Mandatory/Forced Leave<span style="font-size: 6.5pt;">(Sec. 25, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'SL' ? '✔' : '' }}</span>
                            <span>Sick Leave <span style="font-size: 6.5pt;">(Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'ML' ? '✔' : '' }}</span>
                            <span>Maternity Leave <span style="font-size: 6.5pt;">(R.A. No. 11210 / IRR issued by CSC, DOLE and SSS)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'PL' ? '✔' : '' }}</span>
                            <span>Paternity Leave <span style="font-size: 6.5pt;">(R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'SPL' ? '✔' : '' }}</span>
                            <span>Special Privilege Leave <span style="font-size: 6.5pt;">(Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'SOLO' ? '✔' : '' }}</span>
                            <span>Solo Parent Leave <span style="font-size: 6.5pt;">(RA No. 8972 / CSC MC No. 8, s. 2004)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'STUDY' ? '✔' : '' }}</span>
                            <span>Study Leave <span style="font-size: 6.5pt;">(Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'VAWC' ? '✔' : '' }}</span>
                            <span>10-Day VAWC Leave <span style="font-size: 6.5pt;">(RA No. 9262 / CSC MC No. 15, s. 2005)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'REHAB' ? '✔' : '' }}</span>
                            <span>Rehabilitation Privilege <span style="font-size: 6.5pt;">(Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'SLBW' ? '✔' : '' }}</span>
                            <span>Special Leave Benefits for Women <span style="font-size: 6.5pt;">(RA No. 9710 / CSC MC No. 25, s. 2010)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'CALAMITY' ? '✔' : '' }}</span>
                            <span>Special Emergency (Calamity) Leave <span style="font-size: 6.5pt;">(CSC MC No. 2, s. 2012, as amended)</span></span>
                        </div>
                        <div style="margin-bottom: 1.5px; display: flex; align-items: flex-start;">
                            <span class="chk-box">{{ $application->leaveType->code === 'ADOPT' ? '✔' : '' }}</span>
                            <span>Adoption Leave <span style="font-size: 6.5pt;">(R.A. No. 8552)</span></span>
                        </div>
                        <div style="margin-top: 2px; font-weight: bold; display: flex; align-items: baseline;">
                            <span class="chk-box" style="margin-right: 3px;">{{ (!$isMonetization && ($application->other_leave_type || in_array($application->leaveType->code, ['OTHER', 'WL']) || !in_array($application->leaveType->code, ['VL','FL','SL','ML','PL','SPL','SOLO','STUDY','VAWC','REHAB','SLBW','CALAMITY','ADOPT']))) ? '✔' : '' }}</span>
                            <span style="white-space: nowrap; margin-right: 4px;">Others:</span>
                            <span style="border-bottom: 1px solid #000; flex: 1; font-weight: bold; font-size: 7.2pt; padding: 0 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-height: 11px;">
                                {{ $isMonetization ? '' : ($application->other_leave_type ?? ($application->leaveType->code === 'WL' ? 'Wellness Leave (CSC MC No. 01, s. 2026)' : (!in_array($application->leaveType->code, ['VL','FL','SL','ML','PL','SPL','SOLO','STUDY','VAWC','REHAB','SLBW','CALAMITY','ADOPT']) ? $application->leaveType->type_name : ''))) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 6.C NUMBER OF WORKING DAYS APPLIED FOR -->
                <div style="border-top: 1px solid #000; padding: 4px 5px;">
                    <div style="font-size: 7pt; font-weight: bold; text-transform: uppercase;">
                        6.C NUMBER OF WORKING DAYS APPLIED FOR
                    </div>
                    <div style="margin-top: 2px; margin-bottom: 4px;">
                        <span style="border-bottom: 1px solid #000; display: block; font-weight: bold; font-size: 8pt; padding: 1px 4px; min-height: 14px;">
                            {{ number_format($application->days_requested, 3) }} DAY(S)
                        </span>
                    </div>

                    <div style="font-size: 7pt; font-weight: bold; text-transform: uppercase; margin-top: 2px;">
                        INCLUSIVE DATES
                    </div>
                    <div style="margin-top: 2px;">
                        <span style="border-bottom: 1px solid #000; display: block; font-weight: bold; font-size: 7.5pt; padding: 1px 4px; min-height: 14px;">
                            {{ $isMonetization ? 'N/A' : ($application->date_from->format('F d, Y') . ' to ' . $application->date_to->format('F d, Y')) }}
                        </span>
                    </div>
                </div>

            </div>

            <!-- 6.B & 6.D: RIGHT COLUMN -->
            <div style="width: 50%; display: flex; flex-direction: column; justify-content: space-between;">
                
                <!-- 6.B DETAILS OF LEAVE -->
                <div style="padding: 3px 6px;">
                    <div style="font-size: 7pt; font-weight: bold; text-transform: uppercase; margin-bottom: 3px;">
                        6.B DETAILS OF LEAVE
                    </div>

                    <div style="font-size: 7pt; line-height: 1.25;">
                        <!-- Vacation / SPL Details -->
                        <div style="font-style: italic; font-weight: bold; margin-bottom: 1px;">
                            In case of Vacation/Special Privilege Leave:
                        </div>
                        <div style="padding-left: 6px; margin-bottom: 2px;">
                            <div style="display: flex; align-items: baseline; gap: 4px;">
                                <span class="chk-box">{{ ($application->details_type === 'within_ph' || (!$application->details_type && in_array($application->leaveType->code, ['VL', 'SPL', 'FL']) && !str_contains(strtolower($application->details_of_leave ?? ''), 'abroad'))) ? '✔' : '' }}</span>
                                <span style="white-space: nowrap;">Within the Philippines</span>
                                <span style="border-bottom: 1px solid #000; flex: 1; font-weight: bold; padding: 0 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-height: 11px;">
                                    {{ ($application->details_type === 'within_ph' || (!$application->details_type && in_array($application->leaveType->code, ['VL', 'SPL', 'FL']) && !str_contains(strtolower($application->details_of_leave ?? ''), 'abroad'))) ? ($application->details_specify ?? $application->details_of_leave ?? '') : '' }}
                                </span>
                            </div>
                            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 2px;">
                                <span class="chk-box">{{ ($application->details_type === 'abroad' || (!$application->details_type && in_array($application->leaveType->code, ['VL', 'SPL', 'FL']) && str_contains(strtolower($application->details_of_leave ?? ''), 'abroad'))) ? '✔' : '' }}</span>
                                <span style="white-space: nowrap;">Abroad (Specify)</span>
                                <span style="border-bottom: 1px solid #000; flex: 1; font-weight: bold; padding: 0 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-height: 11px;">
                                    {{ ($application->details_type === 'abroad' || (!$application->details_type && in_array($application->leaveType->code, ['VL', 'SPL', 'FL']) && str_contains(strtolower($application->details_of_leave ?? ''), 'abroad'))) ? ($application->details_specify ?? $application->details_of_leave ?? '') : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Sick Leave Details -->
                        <div style="font-style: italic; font-weight: bold; margin-top: 4px; margin-bottom: 1px;">
                            In case of Sick Leave:
                        </div>
                        <div style="padding-left: 6px; margin-bottom: 2px;">
                            <div style="display: flex; align-items: baseline; gap: 4px;">
                                <span class="chk-box">{{ ($application->details_type === 'in_hospital' || (!$application->details_type && $application->leaveType->code === 'SL' && str_contains(strtolower($application->details_of_leave ?? ''), 'hospital'))) ? '✔' : '' }}</span>
                                <span style="white-space: nowrap;">In Hospital (Specify Illness)</span>
                                <span style="border-bottom: 1px solid #000; flex: 1; font-weight: bold; padding: 0 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-height: 11px;">
                                    {{ ($application->details_type === 'in_hospital' || (!$application->details_type && $application->leaveType->code === 'SL' && str_contains(strtolower($application->details_of_leave ?? ''), 'hospital'))) ? ($application->details_specify ?? $application->details_of_leave ?? '') : '' }}
                                </span>
                            </div>
                            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 2px;">
                                <span class="chk-box">{{ ($application->details_type === 'out_patient' || (!$application->details_type && $application->leaveType->code === 'SL' && !str_contains(strtolower($application->details_of_leave ?? ''), 'hospital'))) ? '✔' : '' }}</span>
                                <span style="white-space: nowrap;">Out Patient (Specify Illness)</span>
                                <span style="border-bottom: 1px solid #000; flex: 1; font-weight: bold; padding: 0 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-height: 11px;">
                                    {{ ($application->details_type === 'out_patient' || (!$application->details_type && $application->leaveType->code === 'SL' && !str_contains(strtolower($application->details_of_leave ?? ''), 'hospital'))) ? ($application->details_specify ?? $application->details_of_leave ?? '') : '' }}
                                </span>
                            </div>
                        </div>

                        <!-- Special Leave for Women -->
                        <div style="font-style: italic; font-weight: bold; margin-top: 4px; margin-bottom: 1px;">
                            In case of Special Leave Benefits for Women:
                        </div>
                        <div style="padding-left: 6px;">
                            <div style="display: flex; align-items: baseline; gap: 4px;">
                                <span style="white-space: nowrap;">(Specify Illness)</span>
                                <span style="border-bottom: 1px solid #000; flex: 1; font-weight: bold; padding: 0 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-height: 11px;">
                                    {{ ($application->details_type === 'women_illness' || (!$application->details_type && $application->leaveType->code === 'SLBW')) ? ($application->details_specify ?? $application->details_of_leave ?? '') : '' }}
                                </span>
                            </div>
                            <div style="border-bottom: 1px solid #000; width: 100%; height: 12px; margin-top: 2px;"></div>
                        </div>

                        <!-- Study Leave Details -->
                        <div style="font-style: italic; font-weight: bold; margin-top: 4px; margin-bottom: 1px;">
                            In case of Study Leave:
                        </div>
                        <div style="padding-left: 6px;">
                            <div style="display: flex; align-items: center; margin-bottom: 2px;">
                                <span class="chk-box">{{ ($application->details_type === 'study_master' || (!$application->details_type && $application->leaveType->code === 'STUDY' && str_contains(strtolower($application->details_of_leave ?? ''), 'master'))) ? '✔' : '' }}</span>
                                <span>Completion of Master's Degree</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span class="chk-box">{{ ($application->details_type === 'study_bar' || (!$application->details_type && $application->leaveType->code === 'STUDY' && !str_contains(strtolower($application->details_of_leave ?? ''), 'master'))) ? '✔' : '' }}</span>
                                <span>BAR/Board Examination Review</span>
                            </div>
                        </div>

                        <!-- Other Purposes -->
                        <div style="font-style: italic; font-weight: bold; margin-top: 4px; margin-bottom: 1px;">
                            Other purpose:
                        </div>
                        <div style="padding-left: 6px;">
                            <div style="display: flex; align-items: center; margin-bottom: 2px;">
                                <span class="chk-box">{{ ($application->details_type === 'monetization' || $application->details_type === 'monetization_special' || (!$application->details_type && str_contains(strtolower($application->details_of_leave ?? ''), 'monetiz'))) ? '✔' : '' }}</span>
                                <span>Monetization of Leave Credits</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span class="chk-box">{{ ($application->details_type === 'terminal' || (!$application->details_type && str_contains(strtolower($application->details_of_leave ?? ''), 'terminal'))) ? '✔' : '' }}</span>
                                <span>Terminal Leave</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6.D COMMUTATION & APPLICANT SIGNATURE (Identical to Image 1: Signature above line, label below) -->
                <div style="border-top: 1px solid #000; padding: 4px 6px;">
                    <div style="font-size: 7pt; font-weight: bold; text-transform: uppercase;">
                        6.D COMMUTATION
                    </div>
                    <div style="display: flex; align-items: center; gap: 16px; margin-top: 2px; font-size: 7.2pt;">
                        <div style="display: flex; align-items: center;">
                            <span class="chk-box">{{ !$application->commutation_requested ? '✔' : '' }}</span>
                            <span>Not Requested</span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span class="chk-box">{{ $application->commutation_requested ? '✔' : '' }}</span>
                            <span>Requested</span>
                        </div>
                    </div>

                    <!-- Signature of Applicant Line -->
                    <div style="margin-top: 12px; text-align: center;">
                        <div style="border-bottom: 1px solid #000; width: 85%; margin: 0 auto; height: 10px;"></div>
                        <div style="font-size: 6.8pt; margin-top: 1px; font-family: Arial, sans-serif;">(Signature of Applicant)</div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ===================================================================
             BOX 7: DETAILS OF ACTION ON APPLICATION
             =================================================================== -->
        <div class="section-header">
            7. DETAILS OF ACTION ON APPLICATION
        </div>

        <!-- 7.A & 7.B Split Row -->
        <div style="display: flex; border-bottom: 1px solid #000; min-height: 44mm;">
            
            <!-- 7.A CERTIFICATION OF LEAVE CREDITS -->
            <div style="width: 50%; border-right: 1px solid #000; padding: 4px 6px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 7pt; font-weight: bold; text-transform: uppercase;">
                        7.A CERTIFICATION OF LEAVE CREDITS
                    </div>
                    <div style="font-size: 6.5pt; margin-top: 2px; margin-bottom: 3px;">
                        As of <span class="fill-line" style="width: 65%; font-weight: bold; font-size: 7.5pt;">{{ $isSection7AFilled ? $certifiedAsOf : '' }}</span>
                    </div>

                    <!-- Certified Balance Table -->
                    <table class="credits-table">
                        <thead>
                            <tr style="text-align: center; font-weight: bold;">
                                <th style="width: 44%; text-align: left; padding-left: 4px;"></th>
                                <th style="width: 28%;">Vacation Leave</th>
                                <th style="width: 28%;">Sick Leave</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-weight: bold;">Total Earned</td>
                                <td style="text-align: center; font-weight: bold; font-family: monospace;">
                                    {{ number_format($vlBalance, 3) }}
                                </td>
                                <td style="text-align: center; font-weight: bold; font-family: monospace;">
                                    {{ number_format($slBalance, 3) }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold;">Less this application</td>
                                <td style="text-align: center; font-weight: bold; font-family: monospace;">
                                    {{ ($vlLess !== null && $vlLess > 0) ? number_format($vlLess, 3) : '—' }}
                                </td>
                                <td style="text-align: center; font-weight: bold; font-family: monospace;">
                                    {{ ($slLess !== null && $slLess > 0) ? number_format($slLess, 3) : '—' }}
                                </td>
                            </tr>
                            <tr style="background: #fafafa;">
                                <td style="font-weight: bold;">Balance</td>
                                <td style="text-align: center; font-weight: bold; font-family: monospace;">
                                    {{ number_format($vlRemain, 3) }}
                                </td>
                                <td style="text-align: center; font-weight: bold; font-family: monospace;">
                                    {{ number_format($slRemain, 3) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    @php
                        $isNonVlSlLeave = !$isMonetization && !in_array($application->leaveType->code ?? '', ['VL', 'FL', 'SL']);
                    @endphp
                    @if($isNonVlSlLeave)
                        <div style="font-size: 5.8pt; color: #475569; font-style: italic; margin-top: 2px; line-height: 1.15;">
                            * {{ $application->leaveType->code ?? 'Special' }} Leave ({{ (float)$application->days_requested }} day(s) applied): statutory leave benefit not deductible from VL/SL balances.
                        </div>
                    @endif
                </div>


                <!-- Signatory: Mrs. Vicky C. Alvaro (With Underline Line Matching Image 1) -->
                <div style="text-align: center; margin-top: 6px; margin-bottom: 2px;">
                    <div style="border-bottom: 1px solid #000; width: 85%; margin: 0 auto; height: 10px;"></div>
                    <div style="font-weight: bold; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px;">
                        MRS. VICKY C. ALVARO
                    </div>
                    <div style="font-size: 6.5pt;">
                        Administrative Officer IV - HRMU II
                    </div>
                </div>
            </div>

            <!-- 7.B RECOMMENDATION -->
            <div style="width: 50%; padding: 4px 6px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 7pt; font-weight: bold; text-transform: uppercase;">
                        7.B RECOMMENDATION
                    </div>
                    <div style="margin-top: 3px; font-size: 7.2pt;">
                        <div style="display: flex; align-items: center; margin-bottom: 2px;">
                            <span class="chk-box"></span>
                            <span>For approval</span>
                        </div>
                        <div style="display: flex; align-items: baseline; gap: 4px;">
                            <span class="chk-box"></span>
                            <span style="white-space: nowrap;">For disapproval due to</span>
                            <span style="border-bottom: 1px solid #000; flex: 1; height: 11px;"></span>
                        </div>
                        <div style="border-bottom: 1px solid #000; width: 100%; height: 12px; margin-top: 3px;"></div>
                        <div style="border-bottom: 1px solid #000; width: 100%; height: 12px; margin-top: 3px;"></div>
                        <div style="border-bottom: 1px solid #000; width: 100%; height: 12px; margin-top: 3px;"></div>
                    </div>
                </div>

                <!-- Signatory: Authorized Officer -->
                <div style="text-align: center; margin-top: 6px; margin-bottom: 2px;">
                    <div style="border-bottom: 1px solid #000; width: 75%; margin: 0 auto; height: 10px;"></div>
                    <div style="font-size: 6.5pt; margin-top: 1px;">
                        (Authorized Officer)
                    </div>
                </div>
            </div>

        </div>

        <!-- 7.C & 7.D Approval / Disapproval & Mayor Signatory -->
        <div style="padding: 4px 6px 6px 6px; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
            
            <div style="display: flex;">
                <!-- 7.C APPROVED FOR -->
                <div style="width: 50%; padding-right: 8px;">
                    <div style="font-size: 7pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">
                        7.C APPROVED FOR:
                    </div>
                    <div style="font-size: 7pt; line-height: 1.35; padding-left: 8px;">
                        <div><span style="border-bottom: 1px solid #000; display: inline-block; width: 35px;"></span> days with pay</div>
                        <div><span style="border-bottom: 1px solid #000; display: inline-block; width: 35px;"></span> days without pay</div>
                        <div><span style="border-bottom: 1px solid #000; display: inline-block; width: 35px;"></span> others (Specify)</div>
                    </div>
                </div>

                <!-- 7.D DISAPPROVED DUE TO -->
                <div style="width: 50%;">
                    <div style="font-size: 7pt; font-weight: bold; text-transform: uppercase; margin-bottom: 2px;">
                        7.D DISAPPROVED DUE TO:
                    </div>
                    <div style="border-bottom: 1px solid #000; width: 100%; height: 12px;"></div>
                    <div style="border-bottom: 1px solid #000; width: 100%; height: 12px; margin-top: 2px;"></div>
                    <div style="border-bottom: 1px solid #000; width: 100%; height: 12px; margin-top: 2px;"></div>
                </div>
            </div>

            <!-- Municipal Mayor Signatory (Centered across Box 7) -->
            <div style="text-align: center; margin-top: 8px; margin-bottom: 2px;">
                <div style="font-weight: bold; font-size: 9pt; text-transform: uppercase; letter-spacing: 0.5px;">
                    HON. SHUNN FREDERICK S. DAVOCO
                </div>
                <div style="font-size: 7.5pt; font-weight: bold; text-transform: uppercase;">
                    MUNICIPAL MAYOR
                </div>
            </div>

        </div>

    </div>


    <!-- =======================================================================
         PAGE 2: INSTRUCTIONS AND REQUIREMENTS (BACK OF APPLICATION FOR LEAVE)
         EXACT IDENTICAL REPLICA OF THE SCANNED ORIGINAL
         ======================================================================= -->
    <div class="page-badge-screen">
        <span>Page 2 of 3: Instructions and Requirements (Back of CS Form No. 6)</span>
        <span style="color: #64748b; font-weight: normal;">Official Back Page for Duplex Printing</span>
    </div>

    <div class="a4-page page-form-back">
        
        <!-- Header Box -->
        <div style="padding: 10px 14px 6px 14px; text-align: center;">
            <div style="display: inline-block; border: 1.5px solid #000; padding: 4px 28px; font-weight: bold; font-size: 9.5pt; letter-spacing: 0.8px; text-transform: uppercase;">
                INSTRUCTIONS AND REQUIREMENTS
            </div>
            <div style="font-size: 7.8pt; line-height: 1.35; margin-top: 6px; text-align: justify;">
                Application for any type of leave shall be made on this Form and <u>to be accomplished at least in duplicate</u> with documentary requirements, as follows:
            </div>
        </div>

        <!-- Two-Column Layout matching the Scanned Back Sheet -->
        <div style="display: flex; flex: 1; padding: 0 12px 6px 12px; gap: 14px; font-size: 7pt; line-height: 1.25;">
            
            <!-- Left Column: Items 1 to 9 -->
            <div style="width: 50%; display: flex; flex-direction: column; justify-content: space-between;">
                
                <!-- 1. Vacation leave* -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">1. Vacation leave*</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        It shall be filed five (5) days in advance, whenever possible, of the effective date of such leave. Vacation leave within the Philippines or abroad shall be indicated in the form for purposes of securing travel authority and completing clearance from money and work accountabilities.
                    </div>
                </div>

                <!-- 2. Mandatory/Forced leave -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">2. Mandatory/Forced leave</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        Annual five-day vacation leave shall be forfeited if not taken during the year. In case the scheduled leave has been cancelled in the exigency of the service by the head of agency, it shall no longer be deducted from the accumulated vacation leave. Availment of one (1) day or more Vacation Leave (VL) shall be considered for complying the mandatory/forced leave subject to the conditions under Section 25, Rule XVI of the Omnibus Rules Implementing E.O. No. 292.
                    </div>
                </div>

                <!-- 3. Sick leave* -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">3. Sick leave*</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        • It shall be filed immediately upon employee's return from such leave.<br>
                        • If filed in advance or exceeding five (5) days, application shall be accompanied by a <u>medical certificate</u>. In case medical consultation was not availed of, an <u>affidavit</u> should be executed by an applicant.
                    </div>
                </div>

                <!-- 4. Maternity leave* – 105 days -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">4. Maternity leave* – 105 days</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        • Proof of pregnancy e.g. ultrasound, doctor's certificate on the expected date of delivery<br>
                        • Accomplished Notice of Allocation of Maternity Leave Credits (CS Form No. 6a), if needed<br>
                        • Seconded female employees shall enjoy maternity leave with full pay in the recipient agency.
                    </div>
                </div>

                <!-- 5. Paternity leave – 7 days -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">5. Paternity leave – 7 days</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        Proof of child's delivery e.g. birth certificate, medical certificate and marriage contract
                    </div>
                </div>

                <!-- 6. Special Privilege leave – 3 days -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">6. Special Privilege leave – 3 days</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        It shall be filed/approved for at least one (1) week prior to availment, except on emergency cases. Special privilege leave within the Philippines or abroad shall be indicated in the form for purposes of securing travel authority and completing clearance from money and work accountabilities.
                    </div>
                </div>

                <!-- 7. Solo Parent leave – 7 days -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">7. Solo Parent leave – 7 days</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        It shall be filed in advance or whenever possible five (5) days before going on such leave with updated Solo Parent Identification Card.
                    </div>
                </div>

                <!-- 8. Study leave* – up to 6 months -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">8. Study leave* – up to 6 months</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        • Shall meet the agency's internal requirements, if any;<br>
                        • Contract between the agency head or authorized representative and the employee concerned.
                    </div>
                </div>

                <!-- 9. VAWC leave – 10 days -->
                <div>
                    <div style="font-weight: bold; font-size: 7.5pt;">9. VAWC leave – 10 days</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        • It shall be filed in advance or immediately upon the woman employee's return from such leave.<br>
                        • It shall be accompanied by any of the following supporting documents:<br>
                        <span style="padding-left: 6px; display: block;">a. Barangay Protection Order (BPO) obtained from the barangay;</span>
                        <span style="padding-left: 6px; display: block;">b. Temporary/Permanent Protection Order (TPO/PPO) obtained from the court;</span>
                        <span style="padding-left: 6px; display: block;">c. If the protection order is not yet issued by the barangay or the court, a certification issued by the Punong Barangay/Kagawad or Prosecutor or the Clerk of Court that the application for the BPO, TPO or PPO has been filed with the said office shall be sufficient to support the application for the ten-day leave; or</span>
                        <span style="padding-left: 6px; display: block;">d. In the absence of the BPO/TPO/PPO or the certification, a police report specifying the details of the occurrence of violence on the victim and a medical certificate may be considered, at the discretion of the immediate supervisor of the woman employee concerned.</span>
                    </div>
                </div>

            </div>

            <!-- Right Column: Items 10 to 15 -->
            <div style="width: 50%; display: flex; flex-direction: column; justify-content: space-between;">
                
                <!-- 10. Rehabilitation leave* – up to 6 months -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">10. Rehabilitation leave* – up to 6 months</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        • Application shall be made within one (1) week from the time of the accident except when a longer period is warranted.<br>
                        • Letter request supported by relevant reports such as the police report, if any,<br>
                        • Medical certificate on the nature of the injuries, the course of treatment involved, and the need to undergo rest, recuperation, and rehabilitation, as the case may be.<br>
                        • Written concurrence of a government physician should be obtained relative to the recommendation for rehabilitation if the attending physician is a private practitioner, particularly on the duration of the period of rehabilitation.
                    </div>
                </div>

                <!-- 11. Special leave benefits for women* – up to 2 months -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">11. Special leave benefits for women* – up to 2 months</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        • The application may be filed in advance, that is, at least five (5) days prior to the scheduled date of the gynecological surgery that will be undergone by the employee. In case of emergency, the application for special leave shall be filed immediately upon employee's return but during confinement the agency shall be notified of said surgery.<br>
                        • The application shall be accompanied by a medical certificate filled out by the proper medical authorities, e.g. the attending surgeon accompanied by a clinical summary reflecting the gynecological disorder which shall be addressed or was addressed by the said surgery; the histopathological report; the operative technique used for the surgery; the duration of the surgery including the peri-operative period (period of confinement around surgery); as well as the employees estimated period of recuperation for the same.
                    </div>
                </div>

                <!-- 12. Special Emergency (Calamity) leave – up to 5 days -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">12. Special Emergency (Calamity) leave – up to 5 days</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        • The special emergency leave can be applied for a maximum of five (5) straight working days or staggered basis within thirty (30) days from the actual occurrence of the natural calamity/disaster. Said privilege shall be enjoyed once a year, not in every instance of calamity or disaster.<br>
                        • The head of office shall take full responsibility for the grant of special emergency leave and verification of the employee's eligibility to be granted thereof. Said verification shall include: validation of place of residence based on latest available records of the affected employee; verification that the place of residence is covered in the declaration of calamity area by the proper government agency; and such other proofs as may be necessary.
                    </div>
                </div>

                <!-- 13. Monetization of leave credits -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">13. Monetization of leave credits</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        Application for monetization of fifty percent (50%) or more of the accumulated leave credits shall be accompanied by letter request to the head of the agency stating the valid and justifiable reasons.
                    </div>
                </div>

                <!-- 14. Terminal leave* -->
                <div style="margin-bottom: 6px;">
                    <div style="font-weight: bold; font-size: 7.5pt;">14. Terminal leave*</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        Proof of employee's resignation or retirement or separation from the service.
                    </div>
                </div>

                <!-- 15. Adoption Leave -->
                <div>
                    <div style="font-weight: bold; font-size: 7.5pt;">15. Adoption Leave</div>
                    <div style="text-align: justify; margin-top: 1px;">
                        • Application for adoption leave shall be filed with an authenticated copy of the Pre-Adoptive Placement Authority issued by the Department of Social Welfare and Development (DSWD).
                    </div>
                </div>

            </div>

        </div>

        <!-- Footer Asterisk Note -->
        <div style="border-top: 1px solid #000; padding: 6px 14px; font-size: 6.8pt; line-height: 1.25; text-align: justify;">
            * For leave of absence for thirty (30) calendar days or more and terminal leave, application shall be accompanied by a <u>clearance from money, property and work-related accountabilities</u> (pursuant to CSC Memorandum Circular No. 2, s. 1985).
        </div>

    </div>


    <!-- =======================================================================
         PAGE 3: C-LACTS QR TRACKING & STAPLING CUTOUT SLIP
         INNOVATIVE ZERO-ALTERATION PHYSICAL ROUTING ATTACHMENT
         ======================================================================= -->
    <div class="page-badge-screen">
        <span>Page 3 of 3: C-LACTS QR Document Tracking Slip (Cut & Staple to Upper-Right of Form 6)</span>
        <span style="color: #64748b; font-weight: normal;">Hybrid Physical Routing Terminal Attachment</span>
    </div>

    <div class="a4-page page-qr-slip" style="padding: 14px 16px; justify-content: space-between;">
        
        <!-- Header with Official Basud Seal -->
        <div style="border-bottom: 1.5px solid #000; padding-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <img src="{{ asset('images/basud-logo.png') }}" alt="Seal of Basud" style="width: 48px; height: 48px; object-fit: contain;">
                <div>
                    <div style="font-size: 8pt; line-height: 1.2;">Republic of the Philippines • Province of Camarines Norte</div>
                    <div style="font-size: 10pt; font-weight: bold; line-height: 1.2; text-transform: uppercase;">Municipality of Basud</div>
                    <div style="font-size: 8.5pt; font-weight: bold; color: #1e3a8a; line-height: 1.2;">
                        Human Resource Management Unit (HRMU)
                    </div>
                </div>
            </div>
            <div style="text-align: right;">
                <div style="font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">C-LACTS Physical Routing</div>
                <div style="font-size: 6.8pt; color: #475569; font-mono;">CSC Rule XVI • RA 10173 Compliant</div>
            </div>
        </div>

        <!-- Purpose & Legal Notice Banner -->
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; margin-top: 6px; font-size: 7.5pt; line-height: 1.35; text-align: justify;">
            <strong>MUNICIPAL POLICY & LEGAL RETENTION COMPLIANCE:</strong><br>
            In strict compliance with Civil Service Commission (CSC) laws and local audit rules, <strong>Civil Service Form No. 6 must maintain its official legal format without alteration</strong>. To facilitate real-time digital tracking across municipal stations (Department Offices, HRMU, and Office of the Municipal Mayor), please cut out the tracking slip below and staple it securely to the top-right corner of the physical CS Form No. 6.
        </div>

        <!-- ===================================================================
             THE CUTOUT QR TRACKING SLIP (DIMENSIONED TO FIT TOP-RIGHT CORNER)
             =================================================================== -->
        <div style="margin: 12px 0;">
            
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; font-size: 7.5pt; font-weight: bold;">
                <span style="display: flex; align-items: center; gap: 4px;">
                    <span>✂️</span>
                    <span>CUT ALONG THE DASHED BORDER BELOW AND STAPLE TO CS FORM NO. 6</span>
                </span>
                <span style="font-family: monospace; color: #475569;">TARGET: UPPER RIGHT CORNER ("STAMP OF DATE OF RECEIPT")</span>
            </div>

            <!-- Cutout Container -->
            <div class="cutout-slip-card" style="width: 100%; border: 2px dashed #000; padding: 12px 16px; background: #fff; position: relative;">
                
                <!-- Staple Target Indicator in Corner -->
                <div style="position: absolute; top: 8px; right: 8px; border: 1px dashed #64748b; background: #f8fafc; padding: 3px 6px; border-radius: 3px; font-size: 6.5pt; font-weight: bold; text-transform: uppercase; color: #334155;">
                    📎 STAPLE HERE
                </div>

                <div style="display: flex; align-items: center; gap: 16px;">
                    
                    <!-- QR Code (Crisp 110x110) -->
                    <div style="text-align: center; border: 1px solid #000; padding: 4px; background: #fff; border-radius: 4px; shrink-0;">
                        <img 
                            src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode($application->reference_code) }}" 
                            alt="Scannable QR Tracking Payload" 
                            style="width: 24mm; height: 24mm; display: block;"
                        >
                        <div style="font-size: 5.5pt; font-weight: bold; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.3px;">
                            SCAN FOR CUSTODY
                        </div>
                    </div>

                    <!-- Metadata -->
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; align-items: baseline; justify-content: space-between; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 5px;">
                            <div>
                                <span style="font-size: 7pt; font-weight: bold; text-transform: uppercase; color: #475569;">C-LACTS REFERENCE:</span>
                                <span style="font-size: 13pt; font-weight: 900; font-family: monospace; color: #000; margin-left: 4px; letter-spacing: 0.5px;">
                                    {{ $application->reference_code }}
                                </span>
                            </div>
                            <span style="font-size: 7pt; font-weight: bold; background: #f1f5f9; padding: 2px 6px; border-radius: 3px; border: 1px solid #cbd5e1;">
                                {{ $application->leaveType->code }} • {{ number_format($application->days_requested, 1) }} DAY(S)
                            </span>
                        </div>

                        <table style="width: 100%; font-size: 7.5pt; line-height: 1.3;">
                            <tr>
                                <td style="width: 22%; font-weight: bold; text-transform: uppercase;">Employee:</td>
                                <td style="width: 78%; font-weight: bold; text-transform: uppercase;">
                                    {{ $application->employee->last_first_middle }}
                                    <span style="font-weight: normal; font-family: monospace; font-size: 6.8pt; color: #475569;">({{ $application->employee->employee_no }})</span>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-transform: uppercase;">Office / Dept:</td>
                                <td style="font-weight: bold; text-transform: uppercase;">
                                    {{ $application->employee->office->office_name ?? 'LGU BASUD' }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-transform: uppercase;">Leave Type:</td>
                                <td>
                                    {{ $application->leaveType->type_name }}
                                </td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-transform: uppercase;">Inclusive:</td>
                                <td>
                                    {{ $application->date_from->format('M d, Y') }} to {{ $application->date_to->format('M d, Y') }}
                                </td>
                            </tr>
                        </table>

                        <div style="margin-top: 5px; font-size: 6.5pt; color: #475569; display: flex; align-items: center; justify-content: space-between;">
                            <span>Filing Date: {{ $application->date_of_filing ? $application->date_of_filing->format('M d, Y') : $application->created_at->format('M d, Y') }}</span>
                            <span style="font-style: italic;">Privacy Protected (Data Privacy Act of 2012)</span>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        <!-- ===================================================================
             INSTRUCTIONS FOR EMPLOYEE & RECEIVING CLERKS
             =================================================================== -->
        <div style="display: flex; gap: 12px; margin-top: 2px;">
            
            <!-- 4-Step Instructions -->
            <div style="width: 55%; border: 1px solid #000; border-radius: 4px; padding: 8px 10px;">
                <div style="font-size: 7.5pt; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                    <span>Step-by-Step Operator Guide</span>
                    <span style="font-weight: normal; font-size: 6.8pt; color: #475569;">Municipal Physical Routing</span>
                </div>

                <div style="font-size: 7pt; line-height: 1.35; space-y-2;">
                    <div style="margin-bottom: 4px;">
                        <strong>1. Cut the QR Slip:</strong> Using scissors, carefully cut along the dashed rectangle above.
                    </div>
                    <div style="margin-bottom: 4px;">
                        <strong>2. Staple to Form 6:</strong> Attach the cut slip to the upper-right corner of Page 1 (over or beside the <em>Stamp of Date of Receipt</em> text).
                    </div>
                    <div style="margin-bottom: 4px;">
                        <strong>3. Physical Signatures:</strong> Complete handwritten wet signatures on CS Form No. 6:
                        <ul style="margin: 2px 0 2px 14px; padding: 0; font-size: 6.8pt;">
                            <li>Box 6.D: Applicant Signature</li>
                            <li>Box 7.B: Department Head Recommendation</li>
                            <li>Box 7.C: Municipal Mayor Approval</li>
                        </ul>
                    </div>
                    <div>
                        <strong>4. Municipal Receiver Check-in:</strong> Station receivers at Department Offices, HRMU, and Mayor's Office will scan the stapled QR code (or enter the reference code) to update transit location without writing on the official form.
                    </div>
                </div>
            </div>

            <!-- Application Routing Record Summary -->
            <div style="width: 45%; border: 1px solid #000; border-radius: 4px; padding: 8px 10px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 7.5pt; font-weight: bold; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 6px;">
                        Application Summary & Verification
                    </div>

                    <table style="width: 100%; font-size: 6.8pt; line-height: 1.35;">
                        <tr>
                            <td style="color: #475569; width: 42%;">Reference Code:</td>
                            <td style="font-weight: bold; font-family: monospace;">{{ $application->reference_code }}</td>
                        </tr>
                        <tr>
                            <td style="color: #475569;">Working Days:</td>
                            <td style="font-weight: bold;">{{ number_format($application->days_requested, 3) }} Day(s)</td>
                        </tr>
                        <tr>
                            <td style="color: #475569;">Commutation:</td>
                            <td style="font-weight: bold;">{{ $application->commutation_requested ? 'Requested' : 'Not Requested' }}</td>
                        </tr>
                        <tr>
                            <td style="color: #475569;">Certified VL Balance:</td>
                            <td style="font-weight: bold; font-family: monospace;">{{ number_format($vlBalance, 3) }}</td>
                        </tr>
                        <tr>
                            <td style="color: #475569;">Certified SL Balance:</td>
                            <td style="font-weight: bold; font-family: monospace;">{{ number_format($slBalance, 3) }}</td>
                        </tr>
                        <tr>
                            <td style="color: #475569;">Statutory Target:</td>
                            <td style="font-weight: bold; color: #166534;">5 Working Days SLA</td>
                        </tr>
                    </table>
                </div>

                <!-- Certification Seal Line -->
                <div style="border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 6px; text-align: center; font-size: 6.5pt; color: #475569;">
                    <div>C-LACTS Verification System • HRMU Basud</div>
                    <div style="font-mono; font-size: 5.8pt; margin-top: 1px;">Generated on: {{ now()->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>

        </div>

        <!-- Bottom Certification Footer -->
        <div style="border-top: 1px solid #000; padding-top: 6px; display: flex; align-items: center; justify-content: space-between; font-size: 6.8pt; color: #334155;">
            <div>
                <strong>Centralized Leave Application and Credit Tracking System (C-LACTS)</strong> • HRMU Basud, Camarines Norte
            </div>
            <div style="font-family: monospace;">
                GOVTECH-IT116 • FORM 6 PRE-FILLING ENGINE
            </div>
        </div>

    </div>

    <!-- Print Control Scripts -->
    <script>
        function printAll() {
            document.body.className = '';
            window.print();
        }

        function printFormOnly() {
            document.body.className = 'print-form-only';
            window.print();
            setTimeout(() => { document.body.className = ''; }, 1000);
        }

        function printSlipOnly() {
            document.body.className = 'print-slip-only';
            window.print();
            setTimeout(() => { document.body.className = ''; }, 1000);
        }
    </script>
</body>
</html>
