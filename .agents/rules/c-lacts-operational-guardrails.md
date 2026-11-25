# C-LACTS Operational Guardrails & Negative Constraints

> **Scope:** Mandatory Architectural Constraints for C-LACTS (Municipality of Basud HRMU)  
> **Framework:** Laravel (PHP) / MySQL  
> **Authority:** Non-negotiable system boundaries. No feature, model, migration, controller, or UI component may violate these rules.

---

### 1. STRICTLY NO RAW DTR / BIOMETRIC INGESTION
- **Prohibited:** Creating tables, models, file uploaders, or APIs for Daily Time Records (Civil Service Form 48), biometric logs, time-card scans, or daily time-in/time-out punch entries.
- **Operational Reality:** Physical DTRs remain manual and are submitted on paper to Accounting for payroll.
- **Enforcement:** The system only accepts pre-verified, semi-monthly aggregate summaries input manually by HR Staff in `ATTENDANCE_DEDUCTIONS`:
  - Allowed fields: `pay_period_id`, `employee_id`, `days_absent` (decimal), `late_hours` (integer), `late_minutes` (integer).
  - No automated clock synchronization or DTR file parsers.

---

### 2. STRICTLY NO DIGITAL / ELECTRONIC SIGNATURES
- **Prohibited:** Digital signatures, signature pads, cryptographic signing, certificate-based signing, or in-app approval buttons that bypass physical paperwork.
- **Operational Reality:** Municipal policy requires physical wet signatures on Civil Service Form No. 6 by the Department Head, HR Officer, and Municipal Mayor.
- **Enforcement:**
  - System generates printable, pre-filled Civil Service Form No. 6.
  - System tracks only the **physical whereabouts** of that paper form via `ROUTING_LOGS` (`office_id`, `action`, `scanned_by`, `timestamp`).
  - Status updates (`Routed to Mayor`, `Received by HR`) represent the physical location of the document, NOT an electronic legal endorsement.

---

### 3. MANDATORY ZERO-HARDWARE FALLBACK (QR & ALPHANUMERIC CODE)
- **Requirement:** Never assume specialized 2D barcode scanners or dedicated camera hardware are connected to municipal desk computers.
- **Enforcement:**
  - Every generated Application for Leave must display a machine-readable **QR Code** alongside a prominent, human-readable **Alphanumeric Reference Code** (e.g., `LV-2026-0104`).
  - All receiving/routing views must provide a unified input interface accepting:
    1. Automated string input from a USB scanner or mobile camera.
    2. Manual keyboard text entry with standard submit/enter key triggers.
  - Both methods hit the exact same backend lookup endpoint (`reference_code`).

---

### 4. DATA PRIVACY ACT OF 2012 COMPLIANCE (QR PAYLOAD CONSTRAINTS)
- **Prohibited:** Encoding sensitive personal data, medical details, or leave reasons inside the QR code graphic.
- **Operational Reality:** Anyone with a smartphone camera can scan a printed QR code. Embedding personal data violates the proportionality principle of RA 10173.
- **Enforcement:**
  - The QR code payload must contain **strictly** the unique string identifier (e.g., `LV-2026-0042` or UUID token).
  - Scanning the QR code simply passes the identifier to the application routing endpoint. The system then authenticates the user and checks role permissions before exposing any document data.

---

### 5. STRICTLY NO PAYROLL CALCULATIONS OR MONETIZATION
- **Prohibited:** Calculating monetary salary deductions, cash equivalents, gross/net pay, or terminal leave cash values in system logic.
- **Operational Reality:** C-LACTS is strictly an attendance-deduction and leave-credit tracking tool. Financial payroll computation belongs exclusively to the Accounting Office.
- **Enforcement:**
  - All ledger balances, accruals, and deductions must be recorded strictly in units of **days** (expressed to 3 decimal places, e.g., `1.250`, `0.062`, `0.500`).
  - Do NOT store employee basic salaries or hourly currency rates in the database schema.

---

### 6. DOUBLE-DEDUCTION PREVENTION LOGIC
- **Requirement:** An employee must never have their leave balance deducted twice for the same absence.
- **Enforcement:**
  - When HR encodes semi-monthly absence totals into `ATTENDANCE_DEDUCTIONS`, the system must query `LEAVE_APPLICATIONS`.
  - If an approved leave application covers dates within that pay period, those days must NOT be counted again as unexcused absences.
  - Display a clear UI warning to HR Staff highlighting approved leaves within the selected pay period.

---

### 7. IMMUTABILITY OF THE "STARLA" COMPUTATION ENGINE
- **Prohibited:** Hardcoding arbitrary decimal multipliers directly into controller functions.
- **Enforcement:**
  - All conversions for tardiness minutes, hours, and daily credits must query the `STARLA_REFERENCE` table (Tables II, III, and IV).
  - Only users with the `admin` role can update or adjust reference matrices. Standard HR Staff can trigger computations, but cannot manually alter the underlying conversion constants.
