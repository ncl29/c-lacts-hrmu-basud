# Employee Leave Record Ledger Card Specification

> **Document Class:** Official Primary Ledger Artifact & Digital Specification  
> **Target Scope:** ~158 Regular Employees & Officials of the Municipality of Basud  
> **Primary Source:** Scanned physical cardstock **"LEAVE RECORD"** ledger card utilized by HRMU Basud.

---

## 1. Physical Card Structure & Header Fields

The physical ledger card is a cardstock document filed in HRMU employee folders.

### Header Fields:
- **`NAME:`** Employee Full Name (Last, First, Middle)
- **`OFFICE:`** Department / Office Assignment (e.g. `Mayor's Office`, `HRMU`, `MTO`, `MPDO`, `RHU`)
- **`CONTROL NO.:`** Municipal Control Number (Strictly numbers only, e.g. `001`, `101`, `0501`)

---

## 2. Complete 11-Column Tabular Ledger Layout

The digital Leave Ledger table and export reports must replicate this exact 11-column structure:

| Col # | Column Header | Sub-Header | Data Type | Description / Posting Logic |
|:---:|---|---|:---:|---|
| **1** | `PERIOD` | — | `string` / `date` | Pay period or month (e.g. `Oct 1-15, 2026`, `Oct 16-31, 2026`, `October 2026`) |
| **2** | `PARTICULAR` | — | `string` | Transaction description (e.g., `Earned Credits`, `Tardiness/Absence`, `Vacation Leave`, `Sick Leave`, `Opening Balance`) |
| **3** | **VACATION LEAVE** | `EARNED` | `decimal(6,3)` | Credits earned for the period (standard $1.250$ per full month or fractional via Table II/III) |
| **4** | | `ABSENCE UNDERTIME W/ PAY` | `decimal(6,3)` | Deductions covered by available VL credits (e.g. tardiness converted via Table IV, or approved VL) |
| **5** | | `BALANCE` | `decimal(7,3)` | Running Vacation Leave balance after this transaction |
| **6** | | `ABSENCE UNDERTIME W/OUT PAY` | `decimal(6,3)` | Unexcused absences / tardiness incurred when VL balance is zero (LWOP) |
| **7** | **SICK LEAVE** | `EARNED` | `decimal(6,3)` | Sick leave credits earned for the period (standard $1.250$ or fractional via Table II/III) |
| **8** | | `ABSENCE UNDERTIME W/ PAY` | `decimal(6,3)` | Deductions covered by available SL credits (approved sick leave) |
| **9** | | `BALANCE` | `decimal(7,3)` | Running Sick Leave balance after this transaction |
| **10** | | `ABSENCE UNDERTIME W/OUT PAY` | `decimal(6,3)` | Sick leave taken without pay (when SL balance is zero) |
| **11** | `DATE & ACTION TAKEN ON APPLICATION FOR LEAVE` | — | `string` | Reference code, approval date, and action (e.g., `Approved by Mayor on 10/24/2026; Ref: LA-2026-000123`) |

### Footer:
- **`Total Carried Forwarded`**: Summary row showing cumulative balances forwarded to the next page or calendar year.

---

## 3. Transaction Posting Rules (Starla & Application Integration)

### Rule 1: Monthly Accruals (End of Month)
- When HR posts the monthly accrual:
  - `PARTICULAR` = `Earned for [Month Year]`
  - `VL EARNED` = $+1.250$ (or Table III value if on LWOP)
  - `SL EARNED` = $+1.250$ (or Table III value if on LWOP)
  - New `VL BALANCE` = Previous VL Balance + VL Earned
  - New `SL BALANCE` = Previous SL Balance + SL Earned

### Rule 2: Tardiness & Absences (Semi-Monthly Cut-off)
- When HR posts semi-monthly attendance deductions from physical DTRs:
  - Tardiness minutes converted to decimal days via Starla Table IV.
  - **Deduction Priority:** Deducted from **`VACATION LEAVE`** first.
  - If VL Balance $\ge$ deduction:
    - Post to `VL ABSENCE UNDERTIME W/ PAY`
    - Decrease `VL BALANCE`
  - If VL Balance $<$ deduction:
    - Deplete remaining VL Balance to $0.000$ under `W/ PAY`
    - Post excess to `VL ABSENCE UNDERTIME W/OUT PAY` (LWOP)

### Rule 3: Approved Leave Applications (CSC Form 6)
- When an application is returned approved by the Mayor:
  - For Vacation / Forced Leave: Debit from `VL ABSENCE UNDERTIME W/ PAY`
  - For Sick Leave: Debit from `SL ABSENCE UNDERTIME W/ PAY`
  - Record the tracking reference and approval note under Column 11: `DATE & ACTION TAKEN ON APPLICATION FOR LEAVE`

---

## 4. Relevance to Condition 3 (158-Employee Migration Protocol)

During the baseline migration of the ~158 employees:
1. The **Starting Baseline Values** are extracted directly from the latest row of the physical card:
   - `VL BALANCE` (Column 5)
   - `SL BALANCE` (Column 9)
2. The initial transaction entered into the digital database will be:
   - `PERIOD`: Certified Cut-off Date (e.g. `October 31, 2026`)
   - `PARTICULAR`: `CERTIFIED BASELINE OPENING BALANCE`
   - `VL BALANCE`: Certified balance from physical card
   - `SL BALANCE`: Certified balance from physical card
   - `AUDIT HASH`: SHA-256 seal signed by HRMU Head
