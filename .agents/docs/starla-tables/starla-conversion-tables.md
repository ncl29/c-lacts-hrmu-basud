# CSC "Starla" Conversion Tables (Official HRMU Basud Reference)

> **Document Class:** Official Primary Reference & Computation Matrix  
> **Source Issuance:** Civil Service Commission (CSC) Omnibus Rules on Leave (Rule XVI of Executive Order No. 292, CSC MC No. 41, s. 1998, CSC MC No. 14, s. 1999).  
> **Primary Physical Evidence:** Verified scans of the physical binder officially titled **"⭐ STARLA ⭐"** filed at the Human Resource Management Unit (HRMU), Municipality of Basud, Camarines Norte.

---

## 1. Physical Artifact Verification

The physical folder in HRMU Basud contains:
- **Cover Page:** Explicitly titled `⭐ STARLA ⭐`, noting `SPECIAL PRIVILEGE LEAVE (3 DAYS)` and `MANDATORY FORCE LEAVE (5 DAYS)`.
- **Page 13:** `Table II: Vacation and Sick Leave Credits Earned on a Daily Basis` (1–30 days).
- **Page 14:** `Table III: Leave Credits Earned in a Month by Official/Employee Without Any Vacation Leave Credits Left` (0.00–30.00 days LWOP in 0.50 increments).
- **Page 15:** `Table IV: Conversion of Working Hours/Minutes into Fractions of a Day` (8-Hour Workday; 1–8 Hours and 1–60 Minutes).

---

## 2. Table II: Vacation and Sick Leave Credits Earned on a Daily Basis (Page 13)

Used to compute leave credits earned for partial months of service. Vacation Leave and Sick Leave accrue at identical rates.

| Number of Days | Vacation Leave Earned | Sick Leave Earned | Number of Days | Vacation Leave Earned | Sick Leave Earned |
|:---:|:---:|:---:|:---:|:---:|:---:|
| **1** | .042 | .042 | **16** | .667 | .667 |
| **2** | .083 | .083 | **17** | .708 | .708 |
| **3** | .125 | .125 | **18** | .750 | .750 |
| **4** | .167 | .167 | **19** | .792 | .792 |
| **5** | .208 | .208 | **20** | .833 | .833 |
| **6** | .250 | .250 | **21** | .875 | .875 |
| **7** | .292 | .292 | **22** | .917 | .917 |
| **8** | .333 | .333 | **23** | .958 | .958 |
| **9** | .375 | .375 | **24** | 1.000 | 1.000 |
| **10** | .417 | .417 | **25** | 1.042 | 1.042 |
| **11** | .458 | .458 | **26** | 1.083 | 1.083 |
| **12** | .500 | .500 | **27** | 1.125 | 1.125 |
| **13** | .542 | .542 | **28** | 1.167 | 1.167 |
| **14** | .583 | .583 | **29** | 1.208 | 1.208 |
| **15** | .625 | .625 | **30** | 1.250 | 1.250 |

---

## 3. Table III: Leave Credits Earned in a Month by Official/Employee Without Any Vacation Leave Credits Left (Page 14)

> **Operational Deprecation Note (HRMU Basud):**  
> Per municipal client confirmation, Table III is **omitted** from the active software system and database. In practice, HRMU Basud exclusively utilizes **Table II** (Daily Basis) and **Table IV** (Hours/Minutes).  
> **Mathematical Parity:** Table III is mathematically identical to Table II: credits earned for $N$ days present in Table III ($N/30 \times 1.250$) directly equals the Table II value for $N$ days of service (e.g., 29 days present / 1.00 day LWOP in Table III = 1.208, identical to 29 days in Table II = 1.208). Thus, removing Table III eliminates redundant UI complexity while preserving 100% calculation accuracy.

Used historically when an employee incurs Leave Without Pay (LWOP) during a month:

| Days Present | Days on LWOP | Credits Earned | Days Present | Days on LWOP | Credits Earned |
|:---:|:---:|:---:|:---:|:---:|:---:|
| **30.00** | 0.00 | 1.250 | **14.50** | 15.50 | 0.604 |
| **29.50** | 0.50 | 1.229 | **14.00** | 16.00 | 0.583 |
| **29.00** | 1.00 | 1.208 | **13.50** | 16.50 | 0.562 |
| **28.50** | 1.50 | 1.188 | **13.00** | 17.00 | 0.542 |
| **28.00** | 2.00 | 1.167 | **12.50** | 17.50 | 0.521 |
| **27.50** | 2.50 | 1.146 | **12.00** | 18.00 | 0.500 |
| **27.00** | 3.00 | 1.125 | **11.50** | 18.50 | 0.479 |
| **26.50** | 3.50 | 1.104 | **11.00** | 19.00 | 0.458 |
| **26.00** | 4.00 | 1.083 | **10.50** | 19.50 | 0.437 |
| **25.50** | 4.50 | 1.063 | **10.00** | 20.00 | 0.417 |
| **25.00** | 5.00 | 1.042 | **9.50** | 20.50 | 0.396 |
| **24.50** | 5.50 | 1.021 | **9.00** | 21.00 | 0.375 |
| **24.00** | 6.00 | 1.000 | **8.50** | 21.50 | 0.354 |
| **23.50** | 6.50 | 0.979 | **8.00** | 22.00 | 0.333 |
| **23.00** | 7.00 | 0.958 | **7.50** | 22.50 | 0.312 |
| **22.50** | 7.50 | 0.938 | **7.00** | 23.00 | 0.292 |
| **22.00** | 8.00 | 0.917 | **6.50** | 23.50 | 0.271 |
| **21.50** | 8.50 | 0.896 | **6.00** | 24.00 | 0.250 |
| **21.00** | 9.00 | 0.875 | **5.50** | 24.50 | 0.229 |
| **20.50** | 9.50 | 0.854 | **5.00** | 25.00 | 0.208 |
| **20.00** | 10.00 | 0.833 | **4.50** | 25.50 | 0.187 |
| **19.50** | 10.50 | 0.813 | **4.00** | 26.00 | 0.167 |
| **19.00** | 11.00 | 0.792 | **3.50** | 26.50 | 0.146 |
| **18.50** | 11.50 | 0.771 | **3.00** | 27.00 | 0.125 |
| **18.00** | 12.00 | 0.750 | **2.50** | 27.50 | 0.104 |
| **17.50** | 12.50 | 0.729 | **2.00** | 28.00 | 0.083 |
| **17.00** | 13.00 | 0.708 | **1.50** | 28.50 | 0.062 |
| **16.50** | 13.50 | 0.687 | **1.00** | 29.00 | 0.042 |
| **16.00** | 14.00 | 0.667 | **0.50** | 29.50 | 0.021 |
| **15.50** | 14.50 | 0.646 | **0.00** | 30.00 | 0.000 |
| **15.00** | 15.00 | 0.625 | — | — | — |

---

## 4. Table IV: Conversion of Working Hours/Minutes into Fractions of a Day (Page 15)

Based on standard Civil Service 8-Hour Workday.

### 4.1 Working Hours
| Hours | Equivalent Day |
|:---:|:---:|
| **1** | .125 |
| **2** | .250 |
| **3** | .375 |
| **4** | .500 |
| **5** | .625 |
| **6** | .750 |
| **7** | .875 |
| **8** | 1.000 |

### 4.2 Working Minutes (1 to 60 Minutes)
*Crucial Note:* These exact discrete values from the physical scan must be used in the software lookup to eliminate rounding discrepancies against HR calculators.

| Minutes | Equiv. Day | Minutes | Equiv. Day | Minutes | Equiv. Day | Minutes | Equiv. Day |
|:---:|:---:|:---:|:---:|:---:|:---:|:---:|:---:|
| **1** | .002 | **16** | .033 | **31** | .065 | **46** | .096 |
| **2** | .004 | **17** | .035 | **32** | .067 | **47** | .098 |
| **3** | .006 | **18** | .037 | **33** | .069 | **48** | .100 |
| **4** | .008 | **19** | .040 | **34** | .071 | **49** | .102 |
| **5** | .010 | **20** | .042 | **35** | .073 | **50** | .104 |
| **6** | .012 | **21** | .044 | **36** | .075 | **51** | .106 |
| **7** | .015 | **22** | .046 | **37** | .077 | **52** | .108 |
| **8** | .017 | **23** | .048 | **38** | .079 | **53** | .110 |
| **9** | .019 | **24** | .050 | **39** | .081 | **54** | .112 |
| **10** | .021 | **25** | .052 | **40** | .083 | **55** | .115 |
| **11** | .023 | **26** | .054 | **41** | .085 | **56** | .117 |
| **12** | .025 | **27** | .056 | **42** | .087 | **57** | .119 |
| **13** | .027 | **28** | .058 | **43** | .090 | **58** | .121 |
| **14** | .029 | **29** | .060 | **44** | .092 | **59** | .123 |
| **15** | .031 | **30** | .062 | **45** | .094 | **60** | .125 |

---

## 5. Discrete Rounding Observations vs. Continuous Formulas

Notice the specific discrete values printed on the official CSC sheet:
- **30 minutes:** Prints **`.062`** (not $.063$ or $.0625$).
- **18 minutes:** Prints **`.037`** (not $.038$ or $.0375$).
- **42 minutes:** Prints **`.087`** (not $.088$ or $.0875$).
- **54 minutes:** Prints **`.112`** (not $.113$ or $.1125$).

> **Implementation Directive:**  
> The C-LACTS computation engine will use an exact lookup table seeded directly from these digitized values rather than pure continuous floating-point arithmetic. This ensures **$100\%$ zero-error parity** with the HRMU's physical calculator checks during defense testing.

---

## 6. Monthly Accrual & Table II Prorated Credits Rules (CSC MC No. 41, s. 1998)

### 6.1 Automated Monthly Accrual (1st of Every Month)
- **Schedule:** Runs automatically at midnight on the 1st of every month (`00:00` via Laravel Task Scheduler `leave:accrue-monthly`).
- **Standard Rates:** Every active, eligible regular employee automatically earns **1.250 days Vacation Leave (VL)** and **1.250 days Sick Leave (SL)**.
- **Additive Balance Integrity:** New credits are strictly added to current balances (`bcadd($currentBalance, $earned, 3)`), never overwriting previous balances.
- **Strict Idempotency Guardrail:**
  - Tracked via dedicated `accrual_month` attribute (`YYYY-MM`, e.g., `2026-11`) in `leave_records`.
  - The engine verifies whether an employee has already accrued credits for the target month. Duplicate runs are safely skipped.

### 6.2 Table II Manual Prorated Entry (Partway / Newly Eligible Personnel)
- **Target Cases:** Employees regularized partway through a month, transferred personnel, or personnel resuming from extended Leave Without Pay (LWOP).
- **Table II Selection:** HRMU staff selects the exact number of days served (1 to 30 days) from the digitized Table II reference or enters the exact fractional credit.
- **Zero Rounding or Truncation:** Decimal values must be preserved exactly as entered (e.g., `0.042`, `0.417`, `0.625`, `1.250`) without truncation or continuous floating-point rounding.
- **Cross-Protection:** Prorated entries set `is_prorated = true` and record the corresponding `accrual_month`. The automated batch scheduler respects this and will not overwrite or double-credit the employee for that month.

### 6.3 Month-Start "Earned" Precedence & Chronological Ledger Ordering
- **Month-Start Rule:** In accordance with Civil Service Form 41 guidelines, every active month in an employee's Leave Record must begin with an `"Earned"` entry (`particular`: `"Earned"`, `vl_earned`: `1.25`, `sl_earned`: `1.25`).
- **Strict Intra-Month Priority Hierarchy:**
  - **Priority 0:** `CERTIFIED BASELINE OPENING BALANCE` (opening month only).
  - **Priority 1:** `"Earned"` (Monthly Accrual / Table II Prorated Accrual) — placed at the **START** of the month.
  - **Priority 2:** Deductions, tardiness (`L`), undertime (`UT`), and approved leave applications (`VL`, `SL`, `FL`, `SPL`) — strictly posted **AFTER** the month's earned credits.
- **Routing Approval Interceptor:** When an application is approved by the Municipal Mayor, the system verifies if the target month already has its `"Earned"` credits. If not, the engine automatically creates the month-start `"Earned"` row before debiting the approved leave.
- **Full Ledger Recalculation:** The engine recalculates sequential running balances (`bcadd` / `bcsub`) across the sorted timeline, ensuring 100% balance consistency between `leave_records` and `leave_balances`.

