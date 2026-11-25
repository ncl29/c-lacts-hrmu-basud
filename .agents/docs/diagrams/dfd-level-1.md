# C-LACTS Level 1 Data Flow Diagram (DFD)

> **Document Class:** System Analysis & Design Specification  
> **Course:** IT 116: System Analysis and Design  
> **Source of Truth:** Level 1 DFD verified for Secondary Defense, decomposed from Level 0 Context Diagram.

---

## 1. Level 1 DFD Architecture Overview

The Level 1 Data Flow Diagram decomposes the C-LACTS system into **seven core functional processes** and **five operational data stores**, maintaining strict balancing with the 4 external entities from the Level 0 Context Diagram.

```mermaid
flowchart TD
    %% External Entities
    EMP[Employee<br>Standard User]
    HR[HRMU Staff<br>Record Keeper]
    RC[Routing / Receiving Clerk<br>Status Updater]
    ADMIN[System Administrator / HR Head<br>Config & Audit]

    %% Data Stores
    D1[(D1 | Employee Master Data)]
    D2[(D2 | Leave Records)]
    D3[(D3 | Leave Computation Rules Starla)]
    D4[(D4 | Document Routing Logs)]
    D5[(D5 | System Logs & Audit Trail)]

    %% Processes
    P1((1.0<br>Manage User Accounts<br>& Configuration))
    P2((2.0<br>Manage Leave Applications))
    P3((3.0<br>Compute Leave Credits<br>Starla Engine))
    P4((4.0<br>Generate Forms &<br>Transmittals))
    P5((5.0<br>Track Document Routing<br>QR / Ref Code))
    P6((6.0<br>View Balances &<br>Application Status))
    P7((7.0<br>Generate Reports))

    %% Employee Interactions
    EMP -->|Leave Application Details| P2
    EMP -->|Balance Inquiry Request| P6
    P6 -->|Certified Balances and Application Status| EMP
    P4 -->|Pre-filled Form CS Form 6| EMP

    %% Application Filing & Form Generation
    P2 -->|Pending Application Data| D2
    D2 -->|Application & Ledger Data| P4
    P4 -->|Printable Transmittal Letters| HR

    %% Balance & Routing Inquiry Flow (Process 6.0)
    D2 -->|Historical Leave Ledger| P6
    D4 -->|Current Routing Status| P6

    %% Starla Computation Flow
    HR -->|Verified Tardiness & Absence Totals| P3
    D2 -->|Current Leave Balances| P3
    D3 -->|Conversion Factors Tables II, III, IV| P3
    P3 -->|Starla Query| D3
    P3 -->|Updated Balance & Deductions| D2

    %% Document Routing Flow
    RC -->|QR Scan / Reference Code| P5
    P5 -->|Routing Confirmation| RC
    HR -->|HR Document Dispatch / Receipt| P5
    P5 -->|Location & Timestamp| D4
    D4 -->|Routing Status History| P5

    %% Reporting & Audit Flow
    HR -->|Report Request Parameters| P7
    P7 -->|Summary & Status Reports| HR
    ADMIN -->|Audit Query Parameters| P7
    P7 -->|System Audit Logs & Reports| ADMIN
    D2 -->|Leave Data| P7
    D4 -->|Routing Logs| P7
    D5 -->|Audit Logs| P7

    %% User Account & System Configuration Flow
    ADMIN -->|User Credentials & Permissions| P1
    P1 -->|Account Profile Details| D1
    P1 -->|Admin Action Log Entry| D5
```

---

## 2. Processes, Data Stores & Decomposition

| Process ID | Process Name | Primary Inputs | Primary Outputs |
|---|---|---|---|
| **1.0** | Manage User Accounts | User credentials and permissions from Admin | Account records to `D1`, action logs to `D5` |
| **2.0** | Manage Leave Applications | Leave application details from Employee | Pending application data to `D2` |
| **3.0** | Compute Leave Credits (Starla) | Semi-monthly tardiness & absence totals from HRMU, current balances from `D2`, factors from `D3` | Updated balances and deductions to `D2` |
| **4.0** | Generate Forms and Transmittals | Application and ledger data from `D2` | Pre-filled CSC Form 6 to Employee, batch transmittal letters to HRMU |
| **5.0** | Track Document Routing (QR / Ref Code) | QR scan/reference code from Receiving Clerk (any authorized office staff on duty) and HR Staff, logs from `D4` | Routing confirmation to Receiving Staff, location (`office_id`), receiver identity (`scanned_by`), & timestamp logs to `D4` |
| **6.0** | View Balances & Application Status | Balance inquiry request from Employee, historical ledger from `D2`, current routing status from `D4` | Certified balances and application status to Employee |
| **7.0** | Generate Reports | Report parameters from HRMU and Admin, data from `D2`, `D4`, `D5` | Summary & status reports to HRMU, system audit logs & reports to Admin |

---

## 3. Data Stores Breakdown

- **`D1 | Employee Master Data`**: Profiles of ~158 employees (ID, name, position, office, status).
- **`D2 | Leave Records`**: Leave applications, per-type balances (VL, SL, FL, SPL, Wellness), and immutable ledger transaction entries.
- **`D3 | Leave Computation Rules (Starla)`**: Digitized Table II (daily accruals), Table III (LWOP month accruals), and Table IV (hours/minutes fractional equivalents).
- **`D4 | Document Routing Logs`**: Timestamps, receiving office stations (`office_id`), specific receiving staff user IDs (`scanned_by` — allowing any authenticated personnel in the office to receive/dispatch), and actions (`RECEIVED`, `DISPATCHED`, `RECOMMENDED`, `APPROVED_PHYSICAL`, `DISAPPROVED_PHYSICAL`).
- **`D5 | System Logs & Audit Trail`**: System security logs and user activity audit trails with SHA-256 integrity hashes.

---

## 4. Academic Defense Validation (100% Compliant)

- **Strict DFD Rule Adherence:** `D4 | Document Routing Logs` feeds directly into **`Process 6.0 (View Balances & Application Status)`** via the outer routing line `Current Routing Status`. It does not connect directly to `Employee`. Process 6.0 correctly acts as the mediator, combining `D2` and `D4` data to produce the output `Certified Balances and Application Status` back to `Employee`.
- **Zero Black Holes & Zero Miracles:** All 7 processes possess valid inputs, data store transformations, and verified outputs.
- **Boundary & Scope Integrity:** Completely free of out-of-scope biometric or daily timekeeping clutter, honoring the approved C-LACTS GovTech boundaries.
