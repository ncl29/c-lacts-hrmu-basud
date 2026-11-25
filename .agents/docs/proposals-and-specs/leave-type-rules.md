# Comprehensive Civil Service Leave Type Rules & Policies

> **Document Class:** Regulatory Compliance Matrix & Business Logic  
> **Source Issuances:**  
> - Omnibus Rules on Leave (Rule XVI of Executive Order No. 292, CSC MC No. 41, s. 1998, as amended by CSC MC No. 14, s. 1999 and CSC MC No. 05, s. 2021)  
> - Republic Acts: RA 11210 (Maternity), RA 8187 (Paternity), RA 8972 / RA 11861 (Solo Parent), RA 9262 (VAWC), RA 9710 (Magna Carta of Women), RA 8552 (Adoption)  
> - CSC MC No. 01, s. 2026 / Res. No. 2501292 (Wellness Leave Policy)  
> - Instructions and Requirements printed on the back of CSC Form No. 6 (Revised 2020) of Basud LGU.

---

## 1. Complete Leave Classification Matrix

| # | Leave Type Name | Statutory Legal Basis | Annual Entitlement | Cumulative? | Commutable to Cash? | Filing Advance Notice | Mandatory Attachments / Proof |
|:---:|---|---|:---:|:---:|:---:|:---:|---|
| **1** | **Vacation Leave (VL)** | Sec. 51, Rule XVI, EO 292 | 15 days/yr (1.25/mo) | ✅ Yes | ✅ Yes | 5 days in advance | Travel authority if abroad; clearance if $\ge 30$ days |
| **2** | **Mandatory / Forced Leave (FL)** | Sec. 25, Rule XVI, EO 292 | 5 days/yr | ❌ No (Forfeited) | ❌ No | Scheduled annually | Required if $\ge 10$ days VL balance. Non-forfeited only if cancelled in exigency of service |
| **3** | **Sick Leave (SL)** | Sec. 43, Rule XVI, EO 292 | 15 days/yr (1.25/mo) | ✅ Yes | ✅ Yes | Upon return (or 5 days advance if planned) | Medical Certificate if $\ge 5$ days or in hospital; Affidavit if no consultation |
| **4** | **Special Privilege Leave (SPL)** | Sec. 21, Rule XVI, EO 292 | 3 days/yr | ❌ No (Forfeited) | ❌ No | 1 week prior | None (covers personal milestones, obligations, emergencies) |
| **5** | **Wellness Leave** | CSC MC No. 01, s. 2026 | 5 days/yr | ❌ No (Forfeited) | ❌ No | Advance notice | Strictly confidential under Data Privacy Act (no diagnosis required) |
| **6** | **Maternity Leave** | RA 11210 / CSC MC 05 s. 2021 | 105 days | ❌ No | ❌ No | 30 days prior | Proof of pregnancy (ultrasound), CS Form 6a (Notice of Allocation) |
| **7** | **Paternity Leave** | RA 8187 / CSC MC 71 s. 1998 | 7 working days | ❌ No | ❌ No | Advance or upon birth | Marriage contract, child's birth certificate / delivery proof |
| **8** | **Solo Parent Leave** | RA 8972 / RA 11861 | 7 working days/yr | ❌ No (Forfeited) | ❌ No | 5 days in advance | Valid Solo Parent ID issued by MSWDO; minimum 1 year service |
| **9** | **Special Emergency / Calamity Leave** | CSC MC No. 2, s. 2012 | Up to 5 days/yr | ❌ No (Forfeited) | ❌ No | Within 30 days of event | Declaration of State of Calamity / residence verification |
| **10** | **10-Day VAWC Leave** | RA 9262 / CSC MC 15 s. 2005 | 10 days/yr | ❌ No | ❌ No | Advance / upon return | Barangay Protection Order (BPO) or Court TPO/PPO; police report |
| **11** | **Special Leave Benefits for Women** | RA 9710 / CSC MC 25 s. 2010 | Up to 2 months | ❌ No | ❌ No | 5 days prior (or post-op) | Medical certificate, clinical summary, operative technique, histopath report |
| **12** | **Rehabilitation Privilege** | Sec. 55, Rule XVI, EO 292 | Up to 6 months | ❌ No | ❌ No | Within 1 week of injury | Police report, medical certificate, government physician concurrence |
| **13** | **Study Leave** | Sec. 68, Rule XVI, EO 292 | Up to 6 months | ❌ No | ❌ No | 1 semester prior | Agency contract, scholarship agreement, bar/board review notice |
| **14** | **Adoption Leave** | RA 8552 | 60 days | ❌ No | ❌ No | Upon placement | Authenticated Pre-Adoptive Placement Authority from DSWD |
| **15** | **Monetization of Leave Credits** | Sec. 22–24, Rule XVI | Up to 50% VL/SL | N/A | ✅ Yes (Cash) | Formal letter request | Must retain at least 5 days VL for forced leave |
| **16** | **Terminal Leave** | Sec. 40, Rule XVI / MC 02 s. 2016 | Total VL + SL balance | N/A | ✅ Yes (Separation) | Retirement / Resignation | Clearance from money, property, and accountability |

---

## 2. Core Algorithmic Business Logic

### 2.1 Accrual Rules (Starla Table II & Table III)
1. **Full Month Service:** An employee with full attendance earns $+1.250$ Vacation Leave and $+1.250$ Sick Leave.
2. **Partial Month Service (Table II):** Pro-rated daily credits for $1$ to $30$ days of service (e.g. $15\text{ days} = 0.625\text{ VL} + 0.625\text{ SL}$).
3. **Leave Without Pay Deductions (Table III):** If an employee has LWOP during the month, earnings decrease based on days present vs. days on LWOP.

### 2.2 Attendance Deduction Priority (Tardiness & Undertime)
1. Tardiness minutes are converted to decimal fractions of a day via **Starla Table IV**.
2. **Priority 1 (Deduct from VL):** The decimal fraction is deducted from the employee's **Vacation Leave** balance (`ABSENCE UNDERTIME W/ PAY`).
3. **Priority 2 (Conversion to LWOP):** If the Vacation Leave balance is insufficient or zero:
   - Remaining VL is reduced to $0.000$.
   - The excess tardiness/absence is posted to `ABSENCE UNDERTIME W/OUT PAY` (LWOP).
4. **Anti-Double Deduction Rule:** Days already covered by an approved leave application (Vacation, Sick, Forced, etc.) must NEVER be deducted again as an unexcused absence.

### 2.3 Mandatory Forced Leave (FL) Deduction Rule
- Any single day of Vacation Leave availed of by an employee who has $\ge 10$ days VL balance automatically counts towards satisfying their 5-day mandatory forced leave requirement for that year.
- Unused forced leave is automatically forfeited on December 31, unless the HRMU explicitly logs an agency head cancellation due to exigency of service.

### 2.4 Terminal Leave & Monetization Formula (CSC MC No. 02, s. 2016)
$$\text{Terminal Leave / Monetization} = \text{Highest Monthly Salary} \times (\text{VL Balance} + \text{SL Balance}) \times 0.0481927$$
*(The constant factor $0.0481927$ is derived from $\frac{12\text{ months}}{249\text{ working days/year}} = 0.0481927$).*
