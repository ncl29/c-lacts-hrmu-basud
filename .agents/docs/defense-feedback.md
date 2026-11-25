# Defense Panel & Professor Review: HRU Basud (C-LACTS)

> **Authority Level:** HIGHEST PRIORITY (per `.agents/rules/project-documentation.md`). All architectural, schema, and implementation decisions must satisfy these conditions first.

---

## Secondary Defense: Panel & Professor Review Directives

### Condition 1: Actual "Starla" Conversion Tables & Accurate Leave Calculation
- [ ] **Digitize Starla Reference Tables:**
  - Table II: Vacation and Sick Leave Credits Earned on a Daily Basis (1 to 30 days of service, 15 days = 0.625 day, 30 days = 1.250 days).
  - Table III: Leave Credits Earned in a Month by Official/Employee Without Any Vacation Leave Credits Left (based on days present and LWOP days).
  - Table IV: Conversion of Working Hours/Minutes into Fractions of a Day (8-hour day equivalents; e.g., 1 hr = 0.125 day, 15 min = 0.031 day, 60 min = 0.125 day).
- [ ] **Calculation Engine Compliance:**
  - Accurately calculate standard monthly accrual of 1.25 Vacation Leave (VL) and 1.25 Sick Leave (SL).
  - Automatically calculate decimal deductions for tardiness and unexcused absences using Starla Table IV based on semi-monthly totals encoded by HR.
  - Implement CSC rule on tardiness deduction priority (deducted from VL; if exhausted, converted to LWOP).
  - Guarantee non-double-deduction: encoded absences must exclude days already covered by an approved leave application.
  - Track special leaves: Mandatory Forced Leave (5 days), Special Privilege Leave (3 days), and Wellness Leave (5 days per CSC MC No. 01, s. 2026).
  - Detail document: [starla-conversion-tables.md](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/starla-tables/starla-conversion-tables.md).

### Condition 2: Complete Leave Application Workflow & QR-Based Physical Routing
- [ ] **Pre-filled Form Generation:**
  - Employee self-service entry of leave type, dates, and particulars to generate standard CSC Form 6 (Application for Leave).
  - Auto-populate applicant bio-data, certified leave credit balances as of application date, and signature blocks.
  - Embed a unique, scannable QR Code and human-readable alphanumeric tracking reference (e.g., `LA-2026-000123`).
  - Auto-generate batch transmittal letters for forwarding documents between offices.
- [ ] **QR-Based Physical Document Traceability:**
  - Physical form is signed manually as mandated by municipal policy.
  - At each hand-off (Department Head -> HRMU -> Mayor's Office -> HRMU), the receiving clerk or HR staff scans the QR or enters the reference code.
  - System captures: receiving timestamp, receiving office/clerk identity, and status updates.
  - Real-time tracking log visible to both employee and HRMU.
  - Track the 5-working-day statutory threshold (Section 49, Rule XVI) with SLA alerts.
  - Detail document: [leave-workflow-and-qr-routing.md](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/routing-and-workflow/leave-workflow-and-qr-routing.md).

### Condition 3: Accuracy of Existing Leave Balances & Migration of ~158 Employees
- [ ] **Data Migration & Validation Plan:**
  - Define structured CSV/Excel migration template capturing personal details and certified baseline balances (VL, SL, Forced Leave, SPL, Wellness Leave).
  - Multi-tier validation protocol:
    1. Digital staging & encoding from physical cardstock Leave Record cards.
    2. Verification check by HRMU Record Keeper.
    3. Final certification and baseline lock by HR Head with SHA-256 integrity hash.
  - Discrepancy resolution procedure: reconcile historical mathematical anomalies before committing baseline balances to database.
  - Detail document: [158-employee-migration-plan.md](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/migration/158-employee-migration-plan.md).

### Condition 4: Approved Deployment Arrangement & Operational Accessibility
- [ ] **Deployment Architecture:**
  - Target environment: Cloud VPS (Linux / Ubuntu with Nginx reverse proxy, PostgreSQL, SSL, automated backups) as practical fallback for DICT GWHS.
  - Connectivity: Accessible over Municipal LGU Network and secure web interface for office clerks and employees.
- [ ] **Operational Readiness Before Defense:**
  - Host a publicly accessible or LAN-accessible live demo instance prior to defense.
  - Pre-load validated baseline data for the 158 personnel.
  - Detail document: [deployment-and-revised-schedule.md](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/deployment-and-schedule/deployment-and-revised-schedule.md).

### Condition 5: Implementation Schedule Clarification (Pre-Defense Live Pilot & Training)
- [ ] **Resolve Course Requirement Conflict:**
  - Adjust the Gantt chart and milestone delivery to ensure that the **live pilot run, user orientation/training, and ISO 25010 evaluation occur BEFORE the final defense in December**, meeting IT 116 course prerequisites.
  - Reschedule:
    - Sprints 1 & 2 + 158 employee migration complete by Week 5/6 (late November).
    - Deployment, User Training, and Live Pilot executed in Week 7 (Nov 30 - Dec 6).
    - Final Defense in Week 8 (Dec 7+) backed by empirical pilot data and signed user acceptance results.
  - Detail document: [deployment-and-revised-schedule.md](file:///d:/User/OneDrive/Desktop/HRU%20Basud/.agents/docs/deployment-and-schedule/deployment-and-revised-schedule.md).

### Condition 6: File & Documentation Organization
- [x] Create dedicated modular directories in `.agents/docs/` (`starla-tables/`, `routing-and-workflow/`, `migration/`, `deployment-and-schedule/`, `proposals-and-specs/`, `forms-and-samples/`, `diagrams/`).
- [x] Keep project files clean, uncluttered, and cross-referenced.
