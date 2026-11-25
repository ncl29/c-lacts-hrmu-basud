# C-LACTS Role-Based Access Control (RBAC) & Authorization Specification

> **Document Class:** Security & Authorization Blueprint  
> **Agency:** Human Resource Management Unit (HRMU), Municipality of Basud  
> **Compliance:** Republic Act No. 10173 (Data Privacy Act of 2012), Civil Service Commission Omnibus Rules on Leave.

This specification outlines the exact permissions, route restrictions, policies, and UI visibility rules for C-LACTS.

---

### 1. User Roles Overview

The system defines four (4) distinct user roles stored in the `USERS` table (`role` attribute). In alignment with HRMU Basud's operational policy that **any personnel on duty in an office can act as the receiving/routing clerk**, the `routing_clerk` capability is a **functional role** accessible to all authenticated office personnel (`employee`, `routing_clerk`, `hrmu_staff`, `admin`) for their respective office station, while keeping Section 7.A credit certification and ledger adjustments strictly restricted to `hrmu_staff` and `admin`:

| Role Key | Display Name | Access Tier | Primary Operational Function |
| :--- | :--- | :--- | :--- |
| `employee` | Standard Employee (Also Office Receiver) | View, Track & Office Receive Level | Personal balance inquiry, CS Form 6 application generation, physical routing tracking, and ability to act as Receiving/Routing Clerk for incoming physical forms at their office. |
| `hrmu_staff` | HR Staff / Record Keeper | Processing Level | Attendance deduction encoding (semi-monthly), Starla automated computations, Section 7.A credit certification, 11-column ledger updates, transmittals, and HR-station routing. |
| `routing_clerk` | Routing / Receiving Clerk | Status Updater Level | Functional/dedicated receiving account at Mayor's Office & Department Offices. QR code scanning or manual reference code input to update physical document location. |
| `admin` | System Administrator / HR Head | Configuration Level | User management, audit log inspection, system reports, and Starla conversion matrix configuration. |

---

### 2. High-Level Permissions Matrix

| Feature / Action | Standard Employee (`employee`) | HRMU Staff (`hrmu_staff`) | Routing Clerk (`routing_clerk`) | System Admin (`admin`) |
| :--- | :---: | :---: | :---: | :---: |
| **Personal Dashboard & Certified Balances** | View (Own only) | View (All) | View (Own only) | View (All) |
| **Leave Ledger History (CS Form 41 View)** | View (Own only) | View & Manage | View (Own only) | View & Audit |
| **File Leave Application Details** | Yes (Own only) | Yes (For any) | Yes (Own only) | Yes |
| **Generate Pre-filled CS Form 6 (PDF/Print)** | Yes (Own only) | Yes (Any) | Yes (Own only) | Yes |
| **Encode Semi-Monthly DTR Summaries** | ❌ Denied | Yes | ❌ Denied | Override only |
| **Trigger Starla Accrual / Deductions** | ❌ Denied | Yes | ❌ Denied | Yes |
| **Certify CS Form 6 Section 7.A Leave Credits** | ❌ Denied | Yes (HRMU Only) | ❌ Denied | Yes |
| **Generate Batch Transmittal Letters** | ❌ Denied | Yes | ❌ Denied | Yes |
| **Track Application Status (Dashboard View)** | Yes (Own only) | Yes (All) | Yes (Own only) | Yes (All) |
| **Scan QR / Input Alphanumeric Reference Code**| Yes (As Office Receiver) | Yes (HR Office) | Yes (Dept/Mayor) | Yes |
| **Update Document Physical Routing Location** | Yes (Records `user_id` & `office_id`) | Yes (HR Office) | Yes (Records `user_id` & `office_id`) | Yes |
| **Modify Other Employees' Leave Ledgers** | ❌ Strictly Denied | Yes (Verified) | ❌ Strictly Denied | Yes (Audited) |
| **Generate Operational & Summary Reports** | ❌ Denied | Yes (Leave/Routing) | ❌ Denied | Yes (All + Audit) |
| **User Account & Role Management** | ❌ Denied | ❌ Denied | ❌ Denied | Full Control |
| **Starla Conversion Table Calibration** | ❌ Denied | ❌ Denied | ❌ Denied | Full Control |
| **View Audit Trail & System Logs** | ❌ Denied | ❌ Denied | ❌ Denied | Full Control |

---

### 3. Route & Endpoint Authorization Mapping

#### A. Authentication & Profile Routes
* `/login`, `/logout`: Accessible to all (Guest / Authenticated).
* `/dashboard`:
  * Redirects dynamically based on role:
    * `employee` $\rightarrow$ `/employee/dashboard`
    * `hrmu_staff` $\rightarrow$ `/hrmu/dashboard`
    * `routing_clerk` $\rightarrow$ `/routing/scan`
    * `admin` $\rightarrow$ `/admin/dashboard`

#### B. Standard Employee Endpoints (`guard: role === 'employee'`)
* `GET /employee/dashboard`: Shows current certified VL/SL balances, active leave applications, and recent routing logs.
* `GET /employee/leave-applications/create`: Form to input requested leave dates and leave type.
* `POST /employee/leave-applications`: Stores leave request; generates alphanumeric `reference_code` and QR payload; redirects to printable view.
* `GET /employee/leave-applications/[id]/print`: Pre-fills standard Civil Service Form No. 6 layout with generated QR code in header.
* `GET /employee/leave-applications/[id]/track`: View-only timeline showing physical document stops.

#### C. HRMU Staff Endpoints (`guard: role in ['hrmu_staff', 'admin']`)
* `GET /hrmu/dashboard`: Summary counts of pending leaves, unposted pay periods, and ledger status.
* `GET /hrmu/attendance-deductions`: List of semi-monthly attendance deduction batches.
* `GET /hrmu/attendance-deductions/create`: Form to input verified tardiness/absence summaries per employee (from physical DTR checklist).
* `POST /hrmu/attendance-deductions`: Stores deduction records and triggers backend Starla decimal conversion.
* `POST /hrmu/leave-credits/accrue-monthly`: Triggers the standard 1.25 VL / 1.25 SL monthly credit accrual job.
* `GET /hrmu/transmittals`: Batch management for routing documents.
* `POST /hrmu/transmittals`: Generates printable transmittal sheet grouping multiple pending applications.
* `GET /hrmu/ledger/[employeeId]`: Full digital leave card (CS Form 41) view and history adjustments.
* `GET /hrmu/reports`: Filter and export leave balances, accruals, and routing summaries.

#### D. Routing & Receiving Clerk Endpoints (`guard: role in ['employee', 'routing_clerk', 'hrmu_staff', 'admin']`)
* `GET /clerk/scanner`: Camera/scanner QR input screen with fallback manual input form for `reference_code` and "Receiver / Staff on Duty" selector. Accessible to any authenticated personnel on duty in an office.
* `GET /api/routing/scan`: Validates `reference_code` and returns routing custody state, SLA tracker, and office-governed allowed actions.
* `POST /api/routing/{id}/status`: Submits action (`RECEIVED`, `DISPATCHED`, `RECOMMENDED`, `RECOMMENDED_DISAPPROVAL`, `APPROVED_PHYSICAL`, `DISAPPROVED_PHYSICAL`).
  * Writes permanent record to `ROUTING_LOGS` (`application_id`, `office_id`, `scanned_by`, `action`, `method: QR_SCAN|MANUAL_ENTRY`, `scanned_at`), capturing both the station office and the specific employee/staff member who received or dispatched the physical paper.
* `POST /api/routing/{id}/certify`: Restricted strictly to `role in ['hrmu_staff', 'admin']`. Certifies Civil Service Form 6 Section 7.A leave credits once the physical paper arrives at HRMU.

#### E. System Admin & HR Head Endpoints (`guard: role === 'admin'`)
* `GET /admin/dashboard`: System health, active users count, audit activity feed.
* `CRUD /admin/users`: Management for user accounts, credential resets, and role assignments.
* `GET /admin/starla-tables`: Configuration viewer for Tables II, III, and IV conversion constants.
* `PUT /admin/starla-tables/[id]`: Edit conversion values (requires admin password confirmation + audit log write).
* `GET /admin/audit-logs`: Searchable system activity trail (`AUDIT_LOGS`).

---

### 4. UI Component & Navigation Visibility Rules

| Navigation Item / Action Button | Visible to Roles | Hidden from Roles |
| :--- | :--- | :--- |
| **"Self-Service & My Leave Record"** | `employee`, `routing_clerk`, `hrmu_staff`, `admin` | *(None — all personnel are employees)* |
| **"QR Document Scanner (Office Receiver)"** | `employee`, `routing_clerk`, `hrmu_staff`, `admin` | *(None — any office staff on duty can receive/dispatch)* |
| **"Certify Section 7.A Now" Button** | `hrmu_staff`, `admin` | `employee`, `routing_clerk` |
| **"Leave Records (11-Column Card) & Starla Engine"** | `hrmu_staff`, `admin` | `employee`, `routing_clerk` |
| **"Batch Transmittal Generator"** | `hrmu_staff`, `admin` | `employee`, `routing_clerk` |
| **"Admin Control & System Audit Logs"** | `admin` | `employee`, `hrmu_staff`, `routing_clerk` |

---

### 5. Critical Security & Privacy Guardrails

1. **Data Privacy Act (RA 10173) Proportionality Rule:**
   * The QR code string format must be strictly an alphanumeric reference: `CLACTS-REF-{YEAR}-{ID}`.
   * Never encode employee names, sick leave diagnoses, or personal contact numbers in the raw QR payload.
2. **Field Stripping for Clerks:**
   * API responses returned to `routing_clerk` must explicitly omit sensitive columns: `reason_details`, `illness_description`, `leave_type_id`, and `balances`.
3. **Immutability of Ledger Postings:**
   * Once posted to `LEAVE_LEDGER_ENTRIES`, records cannot be edited or deleted via standard CRUD operations. Corrective adjustments must create an offsetting ledger entry with `entry_type = 'ADJUSTMENT'` and record a permanent entry in `AUDIT_LOGS`.
