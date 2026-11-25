# Centralized Leave Application & Credit Tracking System (C-LACTS) - HRMU Basud

## Project Overview
The **Centralized Leave Application and Credit Tracking System (C-LACTS)** is a specialized GovTech web application for the **Human Resource Management Unit (HRMU) of the Municipality of Basud, Camarines Norte, Philippines**. Developed for IT 116 (System Analysis and Design), the system digitizes and automates Civil Service Commission (CSC) leave administration, automates localized "Starla" fractional accrual/deduction computations, generates pre-filled CSC Form 6 documents, tracks physical paper routing between municipal offices via QR codes, and manages the leave ledger baseline for approximately 158 municipal personnel.

---

## Core System Modules & Scope Boundaries

### 1. Employee Master & Baseline Leave Ledger (~158 Personnel)
- Digital profile of regular employees and elected officials.
- Certified opening balances for Vacation Leave (VL), Sick Leave (SL), Mandatory Forced Leave (FL), Special Privilege Leave (SPL), and Wellness Leave (CSC MC No. 01, s. 2026).
- Audit trail transaction history with SHA-256 audit hashing.

### 2. Automated "Starla" Computation Engine
- Full digitization of CSC fractional conversion tables:
  - **Table II:** Daily VL & SL earnings (1-30 days of service; 15 days = 0.625, 30 days = 1.250 days) and absence deductions.
  - **Table IV:** Fractional day conversions for hours (1-8 hrs) and minutes (1-60 mins) for tardiness and undertime.
  - *(Note: CSC Table III for LWOP was removed per HRMU Basud operational policy; staff exclusively utilizes Table II and Table IV).*
- Semi-monthly encoding of HR-verified tardiness and unexcused absences.
- Auto-deduction priority (tardiness deducted from VL first; conversion to LWOP if VL is exhausted).
- Guaranteed prevention of double deduction for days covered by approved leaves.

### 3. Leave Application Filing & Form Generation
- Employee self-service dashboard to check certified balances and request leave.
- Automatic generation of printable, pre-filled **Civil Service Form No. 6 (Revised 2020)**.
- Embedding of unique, scannable QR Code and alphanumeric reference code (e.g. `LV-2026-0042`).

### 4. Hybrid Physical Routing & QR Traceability Module
- In compliance with municipal policy, physical wet signatures on paper forms are retained.
- Real-time status update interface for receiving clerks stationed at Department Offices, HRMU, and Mayor's Office.
- 1-click QR code scanning or alphanumeric reference lookup to mark documents as "Received" or "Dispatched".
- Visual SLA tracker monitoring the statutory 5-working-day deemed approval rule (Section 49, Rule XVI).

### 5. Four User Roles & Access Control (RBAC)
1. **Standard Employee (View & Track Level):** View personal balance dashboard, file leave requests to generate pre-filled Form 6 with QR, track physical routing status.
2. **HR Staff / Record Keeper (Processing Level):** Encode verified semi-monthly tardiness/absence summaries, trigger Starla computations, manage digital ledgers, generate official reports.
3. **Routing/Receiving Clerk (Status Updater Level):** Functional role accessible to any authenticated personnel on duty at Department Head, HRMU, and Mayor's offices; allows any staff member in the office to scan/enter QR codes and update physical routing status (logging their specific `user_id` and `office_id`), while keeping Section 7.A credit certification and ledger adjustments strictly restricted to `hrmu_staff` and `admin`.
4. **System Administrator / HR Head (Configuration Level):** Full audit trail oversight, user account management, and Starla conversion matrix adjustments.

---

## Strict Negative Constraints & Guardrails (Non-Negotiable)

See [`.agents/rules/c-lacts-operational-guardrails.md`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/rules/c-lacts-operational-guardrails.md) for full details:
1. **STRICTLY NO RAW DTR / BIOMETRIC INGESTION:** No biometric hardware integration, daily punch tables, or DTR clock-ins. The system accepts only pre-verified aggregate summaries in `ATTENDANCE_DEDUCTIONS`.
2. **STRICTLY NO DIGITAL / ELECTRONIC SIGNATURES:** Physical wet signatures remain mandatory. The system tracks document physical routing via `ROUTING_LOGS`, not electronic legal signatures.
3. **MANDATORY ZERO-HARDWARE FALLBACK:** Unified input accepting USB scanner, mobile camera, or manual keyboard entry of alphanumeric reference codes (e.g., `LV-2026-0104`).
4. **DATA PRIVACY ACT OF 2012 COMPLIANCE:** QR code payload encodes strictly the unique reference identifier (`LV-2026-XXXX`). Never encode names, medical diagnoses, or personal details.
5. **STRICTLY NO PAYROLL CALCULATIONS OR MONETIZATION:** Exclusively an attendance-deduction and leave-credit tool. All values are in **days** (3 decimal places). No basic salaries or currency calculations in the schema.
6. **DOUBLE-DEDUCTION PREVENTION LOGIC:** Automatic cross-validation against `LEAVE_APPLICATIONS` prevents deducting absences already covered by approved leaves.
7. **IMMUTABILITY OF "STARLA" COMPUTATION ENGINE:** Never hardcode multipliers in controllers. All conversions query `STARLA_REFERENCE` (Tables II, III, IV), and only `admin` can calibrate them.

---

## Authority & Precedence Hierarchy

When making architectural, schema, or functional decisions, follow this strict order of authority:
1. **Defense Feedback & Professor Review** ([`defense-feedback.md`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/defense-feedback.md)) — *Highest Priority*.
2. **Operational Guardrails & Negative Constraints** ([`c-lacts-operational-guardrails.md`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/rules/c-lacts-operational-guardrails.md)).
3. **Approved Project Proposal** ([`c-lacts-project-proposal.md`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/proposals-and-specs/c-lacts-project-proposal.md)).
4. **Technical Specs & Domain Notes** ([`starla-tables/`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/starla-tables/), [`routing-and-workflow/`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/routing-and-workflow/), [`migration/`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/migration/), [`_suggestions.md`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/_suggestions.md)).
5. **Architectural & Schema Diagrams** ([`.agents/docs/diagrams/`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/diagrams/)).
6. **Workspace Rules** ([`.agents/rules/`](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/rules/)).

---

## Architectural & Development Standards

- **Framework & Backend:** Laravel 10/11 (PHP 8.2+), Eloquent ORM, MySQL database engine.
- **Frontend & Views:** Blade templates / Tailwind CSS v4 with strict **4px grid** (`p-1`, `p-2`, `p-3`, `p-4`, `p-6`, `p-8`). Zero half-steps and zero arbitrary pixel sizes.
- **UI Aesthetics:** Clean, tactile GovTech design with minimal screens and progressive disclosure. Single primary brand color, neutral grays, and semantic status colors.
- **Icons:** Phosphor Icons / Lucide fallback.
- **Security & Authorization:** Laravel Gates, Policies (`LeaveApplicationPolicy`, `RoutingLogPolicy`), and Middleware based on the 4 RBAC tiers.
