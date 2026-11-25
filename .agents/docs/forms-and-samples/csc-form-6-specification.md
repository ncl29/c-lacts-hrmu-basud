# CSC Form No. 6 (Revised 2020) Technical Specification & Pre-Filling Guide

> **Document Class:** Official Document Template & Pre-filling Specification  
> **Agency:** Municipality of Basud, Province of Camarines Norte  
> **Primary Source:** Scanned official copies of Civil Service Form No. 6 (Revised 2020) currently utilized by HRMU Basud.

---

## 1. Document Architecture & Dimensions

- **Standard Size:** Government Folio / Long bond paper ($8.5 \times 13\text{ in}$) or A4 ($8.27 \times 11.69\text{ in}$), printed in duplicate as required by CSC rules.
- **Header:**
  - Left: Municipal Seal of Basud (`public/images/basud-seal.png`).
  - Center:
    ```
    Republic of the Philippines
    Province of Camarines Norte
    Municipality of Basud
    
    APPLICATION FOR LEAVE
    ```
  - Top-Right: Designated bounding box titled `Stamp of Date of Receipt`.
    - **Strict Legal Form Compliance:** By Civil Service law and municipal mandate, the format of Civil Service Form No. 6 on **Page 1 (Front)** and **Page 2 (Back: Instructions and Requirements)** must remain **100% identical to the official scanned version and completely untampered**. No digital QR codes are embedded inside the form itself.
    - **C-LACTS Physical Routing Solution (Page 3 Cutout Slip):** The dynamic **Scannable QR Code** and alphanumeric tracking code (e.g., `LV-2026-0042`) are printed on an accompanying **Page 3 (C-LACTS QR Tracking & Stapling Cutout Slip)**. Municipal personnel cut this slip along the dashed line and staple it over the upper-right corner of CS Form No. 6. Receiving clerks then scan the stapled slip without modifying the official document.

---

## 2. Detailed Field Mapping & Pre-Filling Logic

### Box 1–5: Personal & Employment Details
| Field # | Field Label | Data Source | Pre-Filling Rule |
|---|---|---|---|
| **1** | `OFFICE/DEPARTMENT` | `employees.office_id` $\rightarrow$ `offices.office_name` | Auto-populated from employee master (e.g. "MTO", "HRMU", "Mayor's Office") |
| **2** | `NAME: (Last) (First) (Middle)` | `employees.last_name`, `first_name`, `middle_name` | Auto-populated in standard CSC uppercase format aligned directly under columns (Last), (First), and (Middle) |
| **3** | `DATE OF FILING` | `leave_applications.filing_date` | Auto-populated with current system date (e.g., `October 29, 2026`) |
| **4** | `POSITION` | `employees.position` | Official plantilla / appointment title (e.g., "Administrative Aide IV") |
| **5** | `SALARY` | `employees` / Salary Grade step | Current monthly basic salary |

---

### Box 6: Details of Application

#### 6.A Type of Leave to be Availed Of
Checkbox selection auto-checked based on `leave_applications.leave_type_id`:
- `[ ] Vacation Leave (Sec. 51, Rule XVI, Omnibus Rules Implementing E.O. No. 292)`
- `[ ] Mandatory/Forced Leave (Sec. 25, Rule XVI, Omnibus Rules Implementing E.O. No. 292)`
- `[ ] Sick Leave (Sec. 43, Rule XVI, Omnibus Rules Implementing E.O. No. 292)`
- `[ ] Maternity Leave (R.A. No. 11210 / IRR issued by CSC, DOLE and SSS)`
- `[ ] Paternity Leave (R.A. No. 8187 / CSC MC No. 71, s. 1998, as amended)`
- `[ ] Special Privilege Leave (Sec. 21, Rule XVI, Omnibus Rules Implementing E.O. No. 292)`
- `[ ] Solo Parent Leave (RA No. 8972 / CSC MC No. 8, s. 2004)`
- `[ ] Study Leave (Sec. 68, Rule XVI, Omnibus Rules Implementing E.O. No. 292)`
- `[ ] 10-Day VAWC Leave (RA No. 9262 / CSC MC No. 15, s. 2005)`
- `[ ] Rehabilitation Privilege (Sec. 55, Rule XVI, Omnibus Rules Implementing E.O. No. 292)`
- `[ ] Special Leave Benefits for Women (RA No. 9710 / CSC MC No. 25, s. 2010)`
- `[ ] Special Emergency (Calamity) Leave (CSC MC No. 2, s. 2012, as amended)`
- `[ ] Adoption Leave (R.A. No. 8552)`
- `[ ] Others: Wellness Leave (CSC MC No. 01, s. 2026 / Res. No. 2501292)`

#### 6.B Details of Leave
Conditional text fields pre-filled based on applicant inputs:
- **Vacation / Special Privilege:**
  - `[ ] Within the Philippines` $\rightarrow$ Specify location
  - `[ ] Abroad` $\rightarrow$ Specify foreign destination
- **Sick Leave:**
  - `[ ] In Hospital` $\rightarrow$ Specify illness / hospital
  - `[ ] Out Patient` $\rightarrow$ Specify illness
- **Special Leave for Women:** Specify illness / surgical procedure
- **Study Leave:** `[ ] Completion of Master's Degree` | `[ ] BAR/Board Examination Review`
- **Other Purpose:** `[ ] Monetization of Leave Credits` | `[ ] Terminal Leave`

#### 6.C Working Days & Dates
- **Number of Working Days Applied For:** e.g., `3 Days`
- **Inclusive Dates:** e.g., `November 3, 2026 - November 5, 2026`

#### 6.D Commutation
- `[ ] Not Requested` (Default)
- `[ ] Requested`
- **Signature of Applicant:** Physical line for wet signature by the employee.

---

### Box 7: Details of Action on Application

#### 7.A Certification of Leave Credits (HRMU)
- **Mandatory Blank State Upon Employee Filing:**
  - Per Civil Service Commission (CSC) Omnibus Rules on Leave (Rule XVI), Section 7.A **MUST NOT** be automatically pre-filled when an employee files or creates their leave application.
  - When the employee prints CS Form No. 6 from their Self-Service Dashboard, `As of`, `Total Earned`, `Less this application`, and `Balance` remain strictly **BLANK** (`&nbsp;`).
  - Employees have no legal authority to certify their own leave credits.
- **HRMU Verification & Custody Certification:**
  - Section 7.A is populated only after the physical document travels through the routing chain and is officially logged as received at the **Human Resource Management Unit (HRMU)**.
  - HRMU staff / HRMO II reviews the verified digital ledger balances and triggers Section 7.A Certification via the physical custody terminal (`POST /api/routing/{id}/certify`) or upon official transmittal dispatch.
- **Post-Certification Fields:**
  - `As of [Certified Date]`
  - **Table:**
    | Column | Vacation Leave | Sick Leave |
    |---|:---:|:---:|
    | **Total Earned** | Certified VL earned | Certified SL earned |
    | **Less this application** | Applied VL days | Applied SL days |
    | **Balance** | Net remaining VL balance | Net remaining SL balance |
- **Official Certified Signatory (Pre-Printed):**
  ```
  MRS. VICKY C. ALVARO
  Administrative Officer IV - HRMO II
  ```

#### 7.B Recommendation (Department Head)
- `[ ] For approval`
- `[ ] For disapproval due to: _________________`
- **Signatory:** `(Authorized Officer)` (Department Head wet signature line)

#### 7.C Approval / Disapproval (Municipal Mayor)
- `7.C APPROVED FOR:`
  - `_____ days with pay`
  - `_____ days without pay`
  - `_____ others (Specify)`
- `7.D DISAPPROVED DUE TO:` `_________________`
- **Official Approving Authority (Pre-Printed):**
  ```
  HON. SHUNN FREDERICK S. DAVOCO
  MUNICIPAL MAYOR
  ```

---

## 3. Printable HTML / CSS Print Styling Guidelines

When rendering this document for printing (`window.print()` / PDF export):
- Use `@media print` with exact millimeter dimensions (`@page { size: legal; margin: 10mm; }`).
- Font family: Arial or Helvetica (Civil Service standard).
- Exact box outlines using `border-collapse: collapse; border: 1px solid black;`.
- Render QR code via dynamic SVG or high-DPI Canvas (`next-qrcode` or `@zxing/library`).
