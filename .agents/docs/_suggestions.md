# Architecture Suggestions & Domain Notes: C-LACTS (HRMU Basud)

This file tracks domain observations, architectural suggestions, and implementation refinements aligned with the approved **Centralized Leave Application and Credit Tracking System (C-LACTS)** proposal.

---

## 1. Scope Clarification & Retired Assumptions

Based on the approved Project Proposal and professor's review:
- ❌ **Retired (Out of Scope):** 
  - Biometric hardware integration and daily Form 48 timekeeping generation (DTRs remain strictly physical; HR encodes verified semi-monthly tardiness/absence summaries only).
  - Strategic Performance Management System (SPMS / IPCR).
  - Recruitment, Selection & Placement (RSP / Plantilla vacancies).
  - Housing & Resettlement Unit (the unit is definitively the Human Resource Management Unit).
  - Electronic digital signatures (municipal policy mandates physical wet signatures on paper forms).
- ✅ **Core Scope (C-LACTS):**
  - Leave administration and automated "Starla" credit accrual/deduction engine.
  - CSC Form 6 (Application for Leave) pre-filling and generation.
  - QR-based physical routing tracking with receiving clerk check-in.
  - Baseline migration & validation of ~158 employee records.
  - 4 specific user roles (Standard Employee, HR Staff, Routing/Receiving Clerk, System Administrator).

---

## 2. High-Value Suggestions for C-LACTS

### 2.1 PWA / HTML5 Camera Barcode Scanner for Receiving Clerks
- **Observation:** Receiving clerks in different department offices and the Mayor's Office may not have dedicated handheld barcode/QR scanners.
- **Suggestion:** Use a lightweight HTML5 camera scanner (e.g., via `@zxing/library` or native browser `BarcodeDetector` API) on the responsive web interface. This allows any clerk with a basic smartphone, tablet, or laptop webcam to scan the QR code in under 2 seconds. Provide a prominent manual alphanumeric input fallback.

### 2.2 Automated "Starla" Conversion Test Suite
- **Observation:** Condition 1 requires demonstrating mathematical accuracy under CSC rules.
- **Suggestion:** Implement a comprehensive Vitest/Jest unit test suite containing 100+ assertions that compare system outputs against the historical "Starla" binder for:
  - Exact fractional days for 1 to 60 minutes of tardiness (Table IV).
  - Partial month service calculations (Table II).
  - Leave without pay deductions (Table III).
  - Tardy deductions priority (deducted from VL first; conversion to LWOP when VL is exhausted).
- **Benefit:** Proves to the defense panel that the calculation engine is provably bug-free.

### 2.3 5-Working-Day Statutory SLA Visual Alerts
- **Observation:** Under Section 49, Rule XVI, leave applications not acted upon within 5 working days are legally deemed approved.
- **Suggestion:** Add an SLA badge to every routed application showing days elapsed at the current station. If an application stays at an office for $\ge 3$ days, display an amber badge; if $\ge 5$ days, display a red alert badge.

### 2.4 Terminal Leave & Monetization Estimation Preview
- **Observation:** Employees often inquire with HRMU about how much their accumulated leave credits are worth for planned retirement or monetization under CSC MC No. 02, s. 2016.
- **Suggestion:** Provide a read-only estimator tool on the Employee Dashboard and HR Admin view that computes estimated terminal leave value using the statutory formula:
  $$\text{Estimated Value} = \text{Highest Monthly Salary} \times (\text{VL} + \text{SL}) \times 0.0481927$$

### 2.5 Audit Log & Hash Chaining for Leave Ledger Entries
- **Observation:** In local government administration, leave ledger cards are financial and legal records that could be scrutinized in COA (Commission on Audit) or Civil Service audits.
- **Suggestion:** Store a SHA-256 hash for every ledger transaction (accruals, deductions, opening baseline balance) linking to the previous transaction hash (audit trail).
