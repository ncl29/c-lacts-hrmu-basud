# Project Proposal: C-LACTS

**Course:** IT 116: System Analysis and Design  
**Project Title:** Centralized Leave Application and Credit Tracking System (C-LACTS): A Hybrid Form-Generation, Automated "Starla" Computation, and Physical Routing Traceability System for Human Resource Management Unit of the Municipality of Basud  
**Proponents:**
- Chavez Johann Jethro O.
- Nañez Mikylla Nicole M.
- Ponciano Divine Mae S.
- BSIT 3A  
**Adviser:** Dr. Mary Grace P. Bolos  

---

## I. Project Title
**Centralized Leave Application and Credit Tracking System (C-LACTS): A Hybrid Form-Generation, Automated "Starla" Computation, and Physical Routing Traceability System for Human Resource Management Unit of the Municipality of Basud**

---

## II. Proponents
- Chavez Johann Jethro O., Nañez Mikylla Nicole M., Ponciano Divine Mae S.
- BSIT 3A
- Dr. Mary Grace P. Bolos

---

## III. Introduction

Local government units are mandated to comply with civil service rules on attendance and official hours of work. In accordance with Section 2, Rule XVII of the Omnibus Rules Implementing Book V of Executive Order No. 292 (Administrative Code of 1987), agency heads are required to require the keeping of daily attendance reports on all officers and employees, whether in the field or at sea, using the proper form. The civil service form No. 48 which is the Daily Time Record (DTR), is the prescribed form for the keeping of daily attendance. Section 4 of the same rule provides that falsification or irregularities in time-keeping may lead to administrative accountability and even criminal charges, depending on the circumstances. Civil Service Commission Memorandum Circular No. 47, s. 1993 requires that local government units shall keep to the prescribed office hours and shall keep the proper record of daily attendance. In Basud, the Human Resource Management Unit (HRMU) is responsible for receiving, verifying, computing, and maintaining attendance records. Based on correspondence with HRMU staff, employees submit their DTRs to the HRMU every half of the month for payroll purposes. The DTRs are kept in hard copy because employees require a personal copy and the Accounting Office requires the physical copy for payroll. The HRMU has therefore advised that the DTR need not be entered into the system and that no online transaction is required for it.

Leave of absence is governed by Rule XVI of the Omnibus Rules Implementing Book V of Executive Order No. 292 (Administrative Code of 1987), as amended by applicable Civil Service Commission (CSC) issuances. Under the applicable leave rules, eligible government employees generally earn 15 days of vacation leave (VL) and 15 days of sick leave (SL) annually, equivalent to 1.25 days for each type of leave per month, subject to applicable rules and conditions. The leave computation reference provided to the proponents contains three tables relevant to the proposed system:
1. **Table II, “Vacation and Sick Leave Credits Earned on a Daily Basis,”** presents the leave credits earned for one to 30 days of service, with 15 days corresponding to 0.625 day and 30 days corresponding to 1.250 days.
2. **Table III, “Leave Credits Earned in a Month by Official/Employee Without Any Vacation Leave Credits Left,”** shows the corresponding monthly leave credits based on the number of days present and the number of days on leave without pay.
3. **Table IV, “Conversion of Working Hours/Minutes into Fractions of a Day,”** provides the decimal equivalents of working hours and minutes based on an eight-hour workday. For example, the table lists one hour as 0.125 day, four hours as 0.500 day, and eight hours as 1.000 day. It also provides minute-level equivalents, such as 15 minutes as 0.031 day and 60 minutes as 0.125 day.

According to the HRMU interview, the office refers to its printed leave-computation reference as the “Starla” table; this term is used throughout the paper. The proposed system will digitize the applicable conversion values and computation references after validation by the HRMU, so that leave accruals and deductions can be checked against the office’s accepted procedures. The system will not replace the HRMU’s responsibility to verify attendance records and authorize leave-related transactions.

In addition to these leave types, the leave book of the employee must also provide for other forms of leaves as well. According to Section 25 of Rule XVI of Omnibus Rules on Leave, all officials and employees having a total of ten (10) days of vacation leave credits should take at least five (5) working days of forced or mandatory vacation leave in each year. This form of leave normally expires in case it is not taken in that particular year, with certain exceptions. Under Section 21, employees can enjoy up to three (3) days of Special Leave Privileges in a year. Moreover, under CSC Memorandum Circular No. 01, s. 2026, as per the CSC Resolution No. 2501292, government agencies, including local government units, can grant up to five (5) days of Wellness Leave to their employees in a year. Wellness Leave differs from vacation leave, sick leave, and special leave in many ways as it is non-commutable, non-cumulative, and expires in case it is not taken in that particular calendar year. Any information concerning the state of mind of an employee with regard to Wellness Leave is deemed to be confidential in keeping with the provisions of the Data Privacy Act of 2012.

Accurate record of leave is important since the accumulated leave credits have both legal and financial importance. Under Sections 22 to 24 of the Omnibus Rules on Leave, eligible government employees can cash out their accumulated leave credits provided that certain conditions will apply. When they retire, voluntarily resign, or separate from their duties under certain circumstances, employees are also entitled to terminal leave compensation based on their accumulated vacation and sick leave credits and highest monthly salary. Based on the computation provided by the amendment in CSC Memorandum Circular No. 02, s. 2016, the amount for terminal leave is computed by multiplying the highest monthly salary, number of accumulated leave credits, and constant of 0.0481927:
$$\text{Terminal Leave Benefit} = \text{Highest Monthly Salary} \times \text{Accumulated Leave Credits} \times 0.0481927$$
Thus, any mistake in the computation of leave credits will definitely affect the financial future of the employee.

Based on our interview with the HRMU, the computation and recording of leave are still done manually. HR staff compare the printed Daily Time Records (DTRs) and the printed DTR checklist against the printed conversion charts in the reference folder known in the office as “Starla,” type the decimals into calculators, and post accruals and deductions on the physical Leave Record ledger cards of approximately 158 employees. Because employees cannot see their own credits, they must go to the HR office to ask for their balances. For every leave application, the same data is written or typed again in the Application for Leave form, the Leave Record, and the transmittal letters, and the form is then routed on paper to the Department Head, the HR Officer, and the Mayor for signature. About 28 leave applications are filed in a single week.

Because the signatures are still obtained on the printed form, the main problem is not the signing itself but that no one can see where a document is. HR staff noted that routing documents individually increases the chance of papers getting lost or mixed in with other priority documents, so applications are held and routed in batches, which delays approval. This matters because, under Section 49 of Rule XVI (CSC Memorandum Circular No. 41, s. 1998), a leave application that is not acted upon by the head of agency or the authorized representative within five working days after receipt is deemed approved, and a paper trail makes it hard to tell how long an application has been waiting. To address this, the proposed system will generate a unique QR for each Application for Leave. The QR is scanned at every hand-off, so the date, time, and receiving office are recorded, and HR and the employee can see the current location and status of the document. Signing and approval remain on the printed form, in keeping with the municipality’s existing policy, and the system only tracks the physical movement of the document.

Digitizing the records and tracking the documents is supported by law and by policy. The Electronic Commerce Act of 2000 (Republic Act No. 8792) recognizes the legal effect of electronic documents under Section 7 and encourages the use of electronic documents in government transactions, including those of local government units, under Section 27. Because attendance and leave records contain personal data and sensitive information such as health-related details, the system must also be aligned with the Data Privacy Act of 2012 (Republic Act No. 10173). Under Section 11, personal information must be processed according to the principles of transparency, legitimate purpose, and proportionality, and the QR follows the proportionality principle because it carries only a reference number and no personal or medical data. Likewise, the system does not store DTRs, and HR encodes only the verified tardiness and absence totals needed to compute leave credits. Section 20 requires reasonable and appropriate organizational, physical, and technical security measures, which the system meets through role-based access control that allows only authorized HR personnel to encode verified tardiness and absence deductions or change leave credits and limits employees to viewing their own records.

---

## IV. Problem Statement

The HRMU's manual DTR and leave management process causes the following specific and measurable problems:

1. **Manual Calculation Errors & Delays:** The computation of leave credits and deductions is entirely manual and relies on a physical reference guide, leading to calculation errors and severe processing delays during peak HR workloads. HR staff manually calculate the 1.25 days of vacation and sick leave earned per month. To process deductions from tardiness or absences, they must manually cross-reference printed Daily Time Records (DTRs) and a printed DTR checklist against physical fractional conversion charts compiled in a reference folder locally known as "Starla". Typing these decimals from the Starla charts into physical calculators frequently causes typographical errors. Furthermore, recalculating an individual employee's credits using these manual charts takes 2 to 3 days to complete if HR is concurrently handling payroll or DTR verification workloads.
2. **Information Bottleneck & Administrative Interruptions:** The absence of a centralized, employee-facing database creates an information bottleneck, forcing employees to rely entirely on HR for basic ledger inquiries and causing continuous administrative interruptions. Leave balances are recorded strictly on physical "Leave Record" ledger cards. Because employees cannot view their own credits, they must physically visit the HR office. With approximately 28 leave applications filed in a single week, these walk-ins force unplannable context-switching. While retrieving an updated physical folder takes an HR staff member about 2 minutes, the employee is forced into a 2- to 3-day waiting period if their physical record requires a manual recalculation.
3. **Redundant Physical Data Entry:** The current workflow requires transcribing the exact same employee data across multiple physical documents, preventing real-time record keeping and compounding administrative backlogs. To process a single leave application, the same data must be manually written or typed into multiple places: the Application for Leave Form, the individual Leave Record ledger, and manual transmittal letters used to forward DTRs and leave applications to different offices. Processing roughly 28 leave applications a week using redundant physical data entry consumes massive administrative bandwidth.
4. **Untracked Physical Routing & Loss Risk:** The manual routing of physical leave applications to multiple signatories creates a high risk of document misplacement and prevents any real-time tracking of the application's status. The Application for Leave Form must be physically routed to the department head, the HR Officer, and the Mayor for approval signatures. Because there is no centralized tracking system for this physical hand-off, routing documents individually increases the chance of papers getting lost or mixed in with other priority documents. Consequently, HR is forced to hold applications and route them in batches to prevent loss, which inherently delays the approval process.

---

## V. Project Objectives

### General Objective
To design and develop a centralized Leave Application and Credit Tracking System for the HRMU that automates leave computations, provides on-demand employee access to records, eliminates redundant data entry, and digitally tracks physical document routing to improve overall data integrity and operational efficiency.

### Specific Objectives
1. To develop a comprehensive Leave Application System featuring automated computation algorithms based on the "Starla" fractional conversion tables, a self-service employee dashboard for on-demand balance inquiries, centralized data entry that auto-populates required physical documents like the Application for Leave and transmittals, and a QR-based document traceability module to track physical routing.
2. To establish a secure, centralized relational database populated through the validated encoding of current leave balances for approximately 158 employees, providing a reliable digital baseline for automated computations.
3. To significantly reduce administrative processing time by eliminating the 2 to 3-day backlog required for manual leave recalculations and the 2-minute unplannable walk-in interruptions, shifting the HR workflow from manual computation to simple data verification.
4. To implement strict Role-Based Access Control (RBAC) ensuring that only authorized HR personnel can encode verified tardiness and absence deductions or alter leave credits, while restricting standard employees to view-only access of their personal dashboards to maintain data privacy and integrity.
5. To evaluate the developed system's functionality, usability, reliability, and performance through standardized IT testing methods (such as ISO 25010 software quality standards) conducted with the HRMU staff and a sample of LGU employees.

---

## VI. Scope and Limitations

### Scope
The project focuses strictly on the design, development, and implementation of a centralized Leave Application and Credit Tracking System for the Human Resource Management Unit (HRMU) of Basud.
- Target population: Approximately **158 regular employees and elected officials**.
- Four distinct user roles:
  1. **Standard Employee (View & Track Level):** Granted secure, view-only access to their personal dashboard to check certified leave balances. They can input their requested leave details (such as dates and leave type) solely to generate a pre-filled Application for Leave Form with a tracking QR and track their application's physical routing status. They do not input DTR data or alter balances.
  2. **HR Staff / Record Keeper (Processing Level):** Authorized to encode only the tardiness and absence totals verified from the physical DTRs, trigger the automated Starla-based computations, manage the digital ‘Leave Record’ ledgers, auto-generate office transmittal letters, update document routing status at the HR Officer’s office, and generate reports. DTRs are not entered, uploaded, or stored in the system.
  3. **Routing/Receiving Clerk (Status Updater Level):** Any authorized personnel on duty at the Department Head’s, HRMU, or Mayor’s offices acting as the receiving/routing staff. Rather than restricting receiving duties to a single individual per office, any active office personnel can access the document traceability terminal to scan the QR code or enter the reference code and update the physical location of the routing document—with their exact user account (`scanned_by`) and office station (`office_id`) recorded in the audit log. When acting in this capacity, standard office personnel cannot alter leave balances or view other employees' confidential ledgers.
  4. **System Administrator / HR Head (Configuration Level):** Holds full audit and override access. Authorized to manage user accounts, oversee data logs, and adjust backend conversion matrices should Civil Service Commission policies change.
- **Dedicated Data Entry for Verified DTR Totals:** HR encodes verified semi-monthly tardiness and absence totals. Absences covered by approved leaves are excluded to prevent double deductions.
- **Starla Reference Digitization:** 1.25 monthly VL and SL accrual, and decimal deductions calculated via Starla.
- **Automated Form and Transmittal Generation:** Printable pre-filled Application for Leave Form, digital Leave Record ledgers, and transmittal letters.
- **QR-Based Physical Traceability:** Unique QR code and alphanumeric reference code per application. Scan at each receiving station to update digital status log without specialized hardware.
- **Reports:** Employee leave balance reports, leave transaction histories, and document routing status reports.
- **Initial Baseline Migration:** Encoding and validating existing Leave Record ledger cards for approximately 158 employees.

### Limitations
1. **DTR Submission Stays Physical:** The system does not digitize, replace, or handle the physical Civil Service Form No. 48 (DTR) submission process. DTR filling, verification, checklist cross-referencing, and accounting transmittals remain manual and outside the system.
2. **No Biometric Integration:** The system does not interface with biometric hardware. Undertime is not encoded. Balances reflect the last deduction entry posted by HR.
3. **Physical Routing Retained:** Physical signing and manual approval remain mandatory per municipal policy. The system tracks movement via QR, but does not execute digital signing or electronic approvals.
4. **Exclusively Leave Tracking:** Does not compute monetary payroll, handle cash monetization of credits, process salary adjustments, or manage recruitment/SPMS.
5. **Connectivity:** Requires active local area network (LAN) or internet connectivity within municipal offices.

---

## VII. Methodology

- **Iterative Development Model:** Smaller increments: requirements analysis, system design, development, testing, orientation/training, UAT, pilot, and revision.
- **Starla Validation:** Table II (Daily credits), Table III (Leave credits without VL), and Table IV (Hours/minutes to fractions of a day) validated with HRMU before engine implementation.
- **RBAC Enforcement:** Strict boundary separation between Employee, HR Staff, Receiving Clerk, and System Administrator.
- **Record Migration:** 158 current physical ledger cards encoded in coordination with HRMU personnel.
- **Testing:** Unit, integration, and UAT using ISO/IEC 25010:2023 quality characteristics (functional suitability, usability, reliability, performance efficiency) using a 5-point rating scale.

---

## VIII. Expected Outputs

1. Operational database with validated baseline balances for ~158 employees.
2. Backend processing module automating Starla 1.25 accruals and decimal deductions.
3. Secure employee self-service portal for on-demand balance inquiries.
4. Automated form generator producing printable CSC Form 6 with QR code and batch transmittals.
5. Hybrid QR physical routing module with receiving clerk status updater.
6. Reporting module for leave balances, ledger histories, and routing status logs.
7. Post-development package including HRMU user manual and ISO 25010 evaluation report.

---

## IX. Financial Analysis (Cost-Benefit & ROI)

- **Total First-Year Project Cost:** ₱48,148.00 (₱40,000 labor valuation at ₱200/hr + ₱8,148 Cloud VPS annual hosting).
- **Annual Quantifiable Benefits:** ₱89,517.57 / year based on HR time saved benchmarked against Salary Grade 10 (₱124.68/hr).
- **Estimated First-Year ROI:** 85.92% with a payback period of ~6.5 months. (If hosted on free DICT GWHS: ROI = 123.79%, payback ~5.4 months).

---

## X. Legal and Regulatory References
- E.O. 292 (Administrative Code of 1987) Book V, Rules XVI & XVII.
- CSC MC No. 47, s. 1993 (Office Hours and Attendance in LGUs).
- CSC MC No. 41, s. 1998 (Omnibus Rules on Leave, as amended).
- CSC MC No. 14, s. 1999 (Additional Amendments to Omnibus Rules on Leave).
- CSC MC No. 02, s. 2016 (Constant Factor 0.0481927 for Terminal Leave / Monetization).
- CSC MC No. 05, s. 2021 (Leave Amendments).
- CSC MC No. 01, s. 2026 / Res. No. 2501292 (Wellness Leave Policy).
- R.A. No. 8792 (Electronic Commerce Act of 2000).
- R.A. No. 10173 (Data Privacy Act of 2012).
- R.A. No. 11466 (Salary Standardization Law of 2019).
- ISO/IEC 25010:2023 (Systems and Software Quality Requirements and Evaluation).
