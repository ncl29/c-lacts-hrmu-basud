<?php

namespace App\Services;

use Carbon\Carbon;

class LeaveDocumentRuleService
{
    /**
     * Determine whether supporting documents are required based on CSC Form No. 6 rules.
     *
     * @param string $leaveCode Leave type code (e.g. VL, SL, ML, PL, etc.)
     * @param float $daysRequested Number of working days applied for
     * @param string|null $dateFrom Inclusive start date (Y-m-d)
     * @param string|null $dateOfFiling Filing date (Y-m-d)
     * @param string|null $detailsType Specification type (e.g. within_ph, abroad, in_hospital, out_patient, monetization, terminal)
     * @param string|null $otherLeaveType Free-text other leave specification
     * @return array [
     *     'required' => bool,
     *     'reason' => string,
     *     'documents' => array of strings (specific required documents),
     *     'legal_basis' => string
     * ]
     */
    public static function evaluateRequirements(
        string $leaveCode,
        float $daysRequested = 1.0,
        ?string $dateFrom = null,
        ?string $dateOfFiling = null,
        ?string $detailsType = null,
        ?string $otherLeaveType = null
    ): array {
        $code = strtoupper(trim($leaveCode));
        $details = strtolower(trim((string) $detailsType));
        $otherText = strtolower(trim((string) $otherLeaveType));

        // Check if monetization or terminal leave is indicated in details_type or otherLeaveType
        if ($code === 'MONETIZATION' || $details === 'monetization' || $details === 'monetization_special' || str_contains($details, 'monetiz') || str_contains($otherText, 'monetiz')) {
            $isSpecial = str_contains($otherText, 'special') || $details === 'monetization_special' || str_contains($details, 'special');
            if ($isSpecial) {
                return [
                    'required' => true,
                    'leave_title' => 'Special Monetization (50% or More of VL & SL)',
                    'reason' => 'Special monetization of 50% or more requires mandatory justification letter and valid proof of health/medical emergency, calamity, or educational need.',
                    'documents' => [
                        'Formal letter request addressed to the Municipal Mayor stating valid and justifiable grounds (medical emergency, disaster repair, tuition expenses)',
                        'Mandatory supporting evidence for chosen ground: Medical Certificate/Hospital Billing (Health/Medical), Barangay Calamity Certification (Calamity), or School Assessment/Enrollment Assessment Form (Educational Needs)',
                        'Certified Summary of Leave Credit Balances (certified by HRMU)',
                    ],
                    'legal_basis' => 'CSC Omnibus Rules on Leave (Rule XVI, Sec. 22-23) & CSC Form No. 6, Item 13',
                ];
            }

            return [
                'required' => true,
                'leave_title' => 'Regular Monetization of Leave Credits (10 to 30 Days)',
                'reason' => 'Regular monetization requires a formal letter request addressed to the Municipal Mayor pursuant to CSC Omnibus Rules.',
                'documents' => [
                    'Formal letter request addressed to the Municipal Mayor requesting monetization of leave credits',
                    'Certified Summary of Leave Credit Balances (certified by HRMU)',
                ],
                'legal_basis' => 'CSC Omnibus Rules on Leave (Rule XVI, Sec. 22-23) & CSC Form No. 6, Item 13',
            ];
        }

        if ($code === 'TERMINAL' || $details === 'terminal' || str_contains($otherText, 'terminal')) {
            return [
                'required' => true,
                'leave_title' => 'Terminal Leave',
                'reason' => 'Terminal leave requires proof of cessation of government service and full municipal clearance.',
                'documents' => [
                    'Approved Letter of Resignation, Retirement Application, or Official Separation Order from service',
                    'Municipal Certificate of Clearance (money, property, and administrative work accountabilities)',
                    'Ombudsman Clearance (for retiring/resigning officials/plantilla personnel)',
                ],
                'legal_basis' => 'CSC Omnibus Rules on Leave (Rule XVI, Sec. 35) & CSC Form No. 6, Item 14',
            ];
        }

        switch ($code) {
            case 'VL': // 1. Vacation Leave
                if ($details === 'abroad') {
                    return [
                        'required' => true,
                        'leave_title' => 'Vacation Leave (Abroad)',
                        'reason' => 'Vacation leave spent abroad requires securing official travel authority and accountability clearance.',
                        'documents' => [
                            'Formal Letter Request for Foreign Travel Authority addressed to the Municipal Mayor',
                            'Certificate of Clearance from Money and Property Accountabilities',
                            'Certificate of No Pending Administrative Case',
                        ],
                        'legal_basis' => 'CSC Omnibus Rules on Leave (Sec. 51, Rule XVI) & CSC Form No. 6, Item 1',
                    ];
                }
                return [
                    'required' => false,
                    'leave_title' => 'Vacation Leave (Within Philippines)',
                    'reason' => 'Standard domestic vacation leave within the Philippines does not require supporting attachments.',
                    'documents' => [],
                    'legal_basis' => 'CSC Omnibus Rules on Leave (Sec. 51, Rule XVI) & CSC Form No. 6, Item 1',
                ];

            case 'FL': // 2. Mandatory / Forced Leave
                return [
                    'required' => false,
                    'leave_title' => 'Mandatory / Forced Leave',
                    'reason' => 'Annual 5-day mandatory vacation leave forfeited if not scheduled; no supporting documents required.',
                    'documents' => [],
                    'legal_basis' => 'CSC Omnibus Rules on Leave (Sec. 25, Rule XVI) & CSC Form No. 6, Item 2',
                ];

            case 'SL': // 3. Sick Leave
                $isInAdvance = false;
                if ($dateFrom && $dateOfFiling) {
                    try {
                        $from = Carbon::parse($dateFrom)->startOfDay();
                        $filing = Carbon::parse($dateOfFiling)->startOfDay();
                        $isInAdvance = $from->gt($filing);
                    } catch (\Throwable $e) {}
                }

                $isExceeding5Days = $daysRequested > 5.0;
                $isInHospital = ($details === 'in_hospital');

                if ($isExceeding5Days || $isInAdvance || $isInHospital) {
                    $triggers = [];
                    if ($isExceeding5Days) $triggers[] = 'exceeding 5 working days (' . number_format($daysRequested, 1) . ' days applied)';
                    if ($isInAdvance) $triggers[] = 'filed in advance of medical leave';
                    if ($isInHospital) $triggers[] = 'confinement in hospital';
                    $triggerStr = implode(', ', $triggers);

                    return [
                        'required' => true,
                        'leave_title' => 'Sick Leave',
                        'reason' => "Application is accompanied by medical certificate due to: {$triggerStr}.",
                        'documents' => array_values(array_filter([
                            'Medical Certificate filled out and signed by attending physician (with PTR/license number)',
                            'Affidavit executed by applicant (only if medical consultation was not availed of, pursuant to CSC instructions)',
                            $isInHospital ? 'Hospital Discharge Order / Clinical Summary (for in-hospital confinement)' : null,
                        ])),
                        'legal_basis' => 'CSC Omnibus Rules on Leave (Sec. 43, Rule XVI) & CSC Form No. 6, Item 3',
                    ];
                }

                return [
                    'required' => false,
                    'leave_title' => 'Sick Leave (Routine Out-Patient <= 5 Days)',
                    'reason' => 'Routine out-patient sick leave of 5 days or fewer filed upon return does not strictly mandate a medical certificate under CSC Form 6 instructions (optional doctor slip may be uploaded if available).',
                    'documents' => [],
                    'legal_basis' => 'CSC Omnibus Rules on Leave (Sec. 43, Rule XVI) & CSC Form No. 6, Item 3',
                ];

            case 'ML': // 4. Maternity Leave
                return [
                    'required' => true,
                    'leave_title' => 'Maternity Leave (105 Days)',
                    'reason' => 'Mandatory proof of pregnancy and allocation notice under RA 11210.',
                    'documents' => [
                        'Proof of Pregnancy (e.g. Ultrasound report or Doctor’s medical certificate specifying expected date of delivery / delivery report)',
                        'Accomplished Notice of Allocation of Maternity Leave Credits (CS Form No. 6a, if allocating up to 7 days to child’s father or alternate caregiver)',
                    ],
                    'legal_basis' => 'R.A. No. 11210 / CSC MC No. 005, s. 2019 & CSC Form No. 6, Item 4',
                ];

            case 'PL': // 5. Paternity Leave
                return [
                    'required' => true,
                    'leave_title' => 'Paternity Leave (7 Days)',
                    'reason' => 'Mandatory proof of legitimate delivery under RA 8187.',
                    'documents' => [
                        'Proof of child’s delivery (authenticated Child’s Birth Certificate or Hospital Medical Certificate of Delivery)',
                        'Marriage Contract (certifying legitimate marriage with the lawful cohabiting spouse)',
                    ],
                    'legal_basis' => 'R.A. No. 8187 / CSC MC No. 71, s. 1998 & CSC Form No. 6, Item 5',
                ];

            case 'SPL': // 6. Special Privilege Leave
                if ($details === 'abroad') {
                    return [
                        'required' => true,
                        'leave_title' => 'Special Privilege Leave (Abroad)',
                        'reason' => 'Special privilege leave spent abroad requires securing official travel authority and clearance.',
                        'documents' => [
                            'Formal Letter Request for Foreign Travel Authority addressed to the Municipal Mayor',
                            'Certificate of Clearance from Money and Property Accountabilities',
                        ],
                        'legal_basis' => 'CSC Omnibus Rules on Leave (Sec. 21, Rule XVI) & CSC Form No. 6, Item 6',
                    ];
                }
                return [
                    'required' => false,
                    'leave_title' => 'Special Privilege Leave (Within Philippines)',
                    'reason' => 'Special privilege leave within the Philippines for personal milestones/emergencies does not require supporting attachments.',
                    'documents' => [],
                    'legal_basis' => 'CSC Omnibus Rules on Leave (Sec. 21, Rule XVI) & CSC Form No. 6, Item 6',
                ];

            case 'SOLO': // 7. Solo Parent Leave
                return [
                    'required' => true,
                    'leave_title' => 'Solo Parent Leave (7 Days)',
                    'reason' => 'Mandatory validation of solo parent eligibility under RA 8972 / RA 11861.',
                    'documents' => [
                        'Valid / Updated Solo Parent Identification Card (issued by Municipal Social Welfare and Development Office - MSWDO)',
                    ],
                    'legal_basis' => 'R.A. No. 8972 / R.A. No. 11861 & CSC Form No. 6, Item 7',
                ];

            case 'STUDY': // 8. Study Leave
                return [
                    'required' => true,
                    'leave_title' => 'Study Leave (Up to 6 Months)',
                    'reason' => 'Requires approved municipal study contract and enrollment credentials.',
                    'documents' => [
                        'Approved Study Leave Contract / Agreement between the Municipal Mayor and the employee',
                        'Proof of Enrollment, Acceptance, or Admission in Master’s Degree program or Bar/Board Examination Review Center',
                    ],
                    'legal_basis' => 'CSC Omnibus Rules on Leave (Sec. 68, Rule XVI) & CSC Form No. 6, Item 8',
                ];

            case 'VAWC': // 9. 10-Day VAWC Leave
                return [
                    'required' => true,
                    'leave_title' => '10-Day VAWC Leave (RA 9262)',
                    'reason' => 'Mandatory protective order or police report verification pursuant to CSC MC No. 15, s. 2005.',
                    'documents' => [
                        'Any ONE of the following valid documents:',
                        '• Barangay Protection Order (BPO) obtained from the barangay, OR',
                        '• Temporary/Permanent Protection Order (TPO/PPO) issued by the court, OR',
                        '• Certification from Barangay / Prosecutor / Court Clerk of pending protection order application, OR',
                        '• Police Blotter/Report specifying occurrence of violence accompanied by Medical Certificate',
                    ],
                    'legal_basis' => 'R.A. No. 9262 / CSC MC No. 15, s. 2005 & CSC Form No. 6, Item 9',
                ];

            case 'REHAB': // 10. Rehabilitation Privilege
                return [
                    'required' => true,
                    'leave_title' => 'Rehabilitation Privilege (Up to 6 Months)',
                    'reason' => 'Work-connected disability/injury requires incident reports and physician concurrence.',
                    'documents' => [
                        'Formal letter request supported by Police Report or Incident Report on the work-connected accident',
                        'Medical Certificate specifying nature of injuries, course of treatment, and recuperation period',
                        'Written concurrence of government physician (if attending doctor is a private practitioner)',
                    ],
                    'legal_basis' => 'CSC Omnibus Rules on Leave (Sec. 55, Rule XVI) & CSC Form No. 6, Item 10',
                ];

            case 'SLBW': // 11. Special Leave Benefits for Women
                return [
                    'required' => true,
                    'leave_title' => 'Special Leave Benefits for Women (Up to 2 Months)',
                    'reason' => 'Magna Carta of Women requires surgical clinical summary and histopathological findings.',
                    'documents' => [
                        'Medical Certificate filled out by attending surgeon / gynecologist',
                        'Clinical Summary reflecting the gynecological disorder addressed by the surgery',
                        'Histopathological Report and Operative Technique / Confinement Summary',
                        'Estimated period of recuperation certified by attending surgeon',
                    ],
                    'legal_basis' => 'R.A. No. 9710 / CSC MC No. 25, s. 2010 & CSC Form No. 6, Item 11',
                ];

            case 'CALAMITY': // 12. Special Emergency (Calamity) Leave
                return [
                    'required' => true,
                    'leave_title' => 'Special Emergency / Calamity Leave (Up to 5 Days)',
                    'reason' => 'Requires proof that employee’s residence was directly affected within a declared calamity zone.',
                    'documents' => [
                        'Official Declaration of State of Calamity (Presidential Proclamation, NDRRMC, or Sangguniang Bayan / Panlalawigan Resolution)',
                        'Barangay Certification of Affected Residence / Photos of damaged property',
                    ],
                    'legal_basis' => 'CSC MC No. 2, s. 2012, as amended & CSC Form No. 6, Item 12',
                ];

            case 'ADOPT': // 15. Adoption Leave
                return [
                    'required' => true,
                    'leave_title' => 'Adoption Leave (Up to 60 Days)',
                    'reason' => 'Mandatory proof of DSWD pre-adoptive placement authority under RA 8552 / RA 11642.',
                    'documents' => [
                        'Authenticated copy of the Pre-Adoptive Placement Authority issued by the Department of Social Welfare and Development (DSWD)',
                    ],
                    'legal_basis' => 'R.A. No. 8552 / R.A. No. 11642 & CSC Form No. 6, Item 15',
                ];

            case 'WL': // Wellness Leave (CSC MC No. 01, s. 2026)
                return [
                    'required' => false,
                    'leave_title' => 'Wellness Leave (CSC MC No. 01, s. 2026)',
                    'reason' => 'Annual wellness privilege for mental/physical well-being does not require medical diagnosis.',
                    'documents' => [],
                    'legal_basis' => 'CSC MC No. 01, s. 2026 / Res. No. 2501292',
                ];

            case 'OTHER': // Others
            default:
                if (!empty($otherText)) {
                    if (str_contains($otherText, 'monetiz')) {
                        return self::evaluateRequirements('MONETIZATION', $daysRequested, $dateFrom, $dateOfFiling, $detailsType, $otherLeaveType);
                    }
                    if (str_contains($otherText, 'terminal')) {
                        return self::evaluateRequirements('TERMINAL', $daysRequested, $dateFrom, $dateOfFiling, $detailsType, $otherLeaveType);
                    }
                }
                return [
                    'required' => false,
                    'leave_title' => 'Other Leave Category',
                    'reason' => 'Optional supporting attachments may be uploaded if relevant to your application.',
                    'documents' => [],
                    'legal_basis' => 'CSC Omnibus Rules on Leave (Rule XVI)',
                ];
        }
    }

    /**
     * Get complete rule definition map for client-side JavaScript auto-evaluation.
     */
    public static function getClientRulesMap(): array
    {
        return [
            'VL' => [
                'conditional_field' => 'details_type',
                'require_if_abroad' => true,
                'abroad_docs' => [
                    'Formal Letter Request for Foreign Travel Authority addressed to the Municipal Mayor',
                    'Certificate of Clearance from Money and Property Accountabilities',
                ],
            ],
            'FL' => [
                'required' => false,
            ],
            'SL' => [
                'conditional' => true,
                'max_days_without_cert' => 5.0,
                'require_if_in_advance' => true,
                'require_if_in_hospital' => true,
                'docs' => [
                    'Medical Certificate filled out and signed by attending physician (with PTR/license number)',
                    'Affidavit executed by applicant (strictly if medical consultation was not availed of)',
                ],
                'hospital_docs' => [
                    'Medical Certificate filled out and signed by attending physician',
                    'Hospital Discharge Order / Clinical Summary (for in-hospital confinement)',
                ],
            ],
            'ML' => [
                'required' => true,
                'docs' => [
                    'Proof of Pregnancy (Ultrasound report or Doctor’s medical certificate specifying expected date of delivery)',
                    'Accomplished Notice of Allocation of Maternity Leave Credits (CS Form No. 6a, if applicable)',
                ],
            ],
            'PL' => [
                'required' => true,
                'docs' => [
                    'Proof of child’s delivery (authenticated Birth Certificate or Hospital Delivery Certificate)',
                    'Marriage Contract (certifying legitimate marriage with spouse pursuant to RA 8187)',
                ],
            ],
            'SPL' => [
                'conditional_field' => 'details_type',
                'require_if_abroad' => true,
                'abroad_docs' => [
                    'Formal Letter Request for Foreign Travel Authority addressed to the Municipal Mayor',
                    'Certificate of Clearance from Money and Property Accountabilities',
                ],
            ],
            'SOLO' => [
                'required' => true,
                'docs' => [
                    'Valid / Updated Solo Parent Identification Card (issued by MSWDO under RA 8972 / RA 11861)',
                ],
            ],
            'STUDY' => [
                'required' => true,
                'docs' => [
                    'Approved Study Leave Contract / Agreement between the Municipal Mayor and the employee',
                    'Proof of Enrollment or Acceptance in Master’s Degree program or Bar/Board Review Center',
                ],
            ],
            'VAWC' => [
                'required' => true,
                'docs' => [
                    'Barangay Protection Order (BPO), Court TPO/PPO, Prosecutor/Barangay Certification, or Police Blotter Report with Medical Certificate',
                ],
            ],
            'REHAB' => [
                'required' => true,
                'docs' => [
                    'Letter request to Agency Head supported by Police Report or Incident Report on work accident',
                    'Medical Certificate specifying nature of injuries, treatment course, and recuperation period',
                    'Written concurrence of government physician',
                ],
            ],
            'SLBW' => [
                'required' => true,
                'docs' => [
                    'Medical Certificate by attending surgeon, Clinical Summary of gynecological disorder/surgery, and Histopathological report',
                ],
            ],
            'CALAMITY' => [
                'required' => true,
                'docs' => [
                    'Proof of residence covered in official State of Calamity declaration (Resolution) or Barangay Certificate of disaster damage',
                ],
            ],
            'MONETIZATION' => [
                'required' => true,
                'docs' => [
                    'Formal letter request addressed to the Municipal Mayor stating valid and justifiable reason(s)',
                    'Certified Summary of Leave Credit Balances',
                ],
            ],
            'TERMINAL' => [
                'required' => true,
                'docs' => [
                    'Approved Letter of Resignation, Retirement Application, or Official Separation Order from service',
                    'Municipal Certificate of Clearance (money, property, and administrative work accountabilities)',
                ],
            ],
            'ADOPT' => [
                'required' => true,
                'docs' => [
                    'Authenticated copy of Pre-Adoptive Placement Authority issued by DSWD (RA 8552 / RA 11642)',
                ],
            ],
            'WL' => [
                'required' => false,
            ],
            'OTHER' => [
                'required' => false,
            ],
        ];
    }
}

