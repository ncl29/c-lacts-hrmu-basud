# Official Batch Transmittal Letter Specification

> **Document Class:** Report & Document Routing Artifact  
> **Agency:** Human Resource Management Unit (HRMU), Municipality of Basud  
> **Primary Source:** Scanned copy of the official municipal **Transmittal Letter** (October 21, 2026) utilized by HRMU Basud to forward DTRs and Leave Applications to Accounting and the Mayor's Office.

---

## 1. Purpose & Workflow Context

In the municipal workflow of Basud:
- Individual leave applications and DTRs are not routed one-by-one because single sheets risk getting lost among priority files.
- Instead, HRMU compiles applications into **semi-monthly batches** and issues an official **Batch Transmittal Letter** forwarding them to the destination office (e.g., Accounting Office for payroll, or Mayor's Office for approval).
- **C-LACTS Automation:** In Process 4.0 (`Generate Forms and Transmittals`), HR staff can select routed applications and click **"Generate Transmittal Manifest"** to automatically output this pre-formatted document.

---

## 2. Layout, Header & Typography

- **Paper Size:** Standard Letter ($8.5 \times 11\text{ in}$) or A4.
- **Header:**
  - Municipal Seal (`public/images/basud-seal.png`)
  - Center:
    ```
    Republic of the Philippines
    Province of Camarines Norte
    MUNICIPALITY OF BASUD
    ```
- **Date:** Dynamic generation date (e.g., `October 21, 2026`).
- **Addressee:**
  - `To [Recipient Name / Title],`
  - *Example from actual artifact:* `To Mr. Jan Mark De Vera,`
- **Standard Transmittal Body:**
  > *"I respectfully transmit the copy of DTR and Application for Leave of Regular Employees for the MONTH of [Month Period, e.g. September 1-15 / October 1-15] as follows:"*

---

## 3. Two-Column Departmental Roster Layout

The manifest automatically groups all forwarded employees under their respective municipal departments in a clean, balanced two-column format:

### Left Column:
- **Office of the Municipal Mayor**
  - Obusan, Noel L.
  - De Vera, Lizette
  - Barrameda Jr., Ramir M.
  - Sta. Clara, Eugenio Q.
  - Genorga, Craton Z.
  - Fulgueras, Desiree D.
  - De Vera, Conrado L.
  - Abina, Mylah N.
  - Zabala, Nancy E.
  - Cepeda, Eunice R.
  - Sta. Clara, Ramir
  - Gutierrez Jr., Wilson
- **Municipal Persons with Disability Affairs Office (PDAO)**
  - Amaro, Grazelle A.
- **Municipal Environment and Natural Resources Office (MENRO)**
  - Navarro, Jobelle O.
- **Municipal Legal Office (MLO)**
  - Tulalian, Loren Bea

### Right Column:
- **General Service Office (GSO)**
  - Asis, Cherry A.
- **Business Permit Licensing Office (BPLO)**
  - Canaria, Joanna Z.
  - Solver, Renesson
- **Municipal Library**
  - Rodriguez, Ariane Joy M.
  - Odi, Clarissa A.
- **Human Resource Management Office (HRMO)**
  - Alvaro, Vicky C.
  - Mendoza, Mariane Joy S.
- **Local Youth Development Office (LYDO)**
  - De Vera, Shalie Mar
- **Municipal Planning and Development Office (MPDO)**
  - Santillan, Marjory A.
  - Guerrero, Shirley B.
  - Gomez, Gladina M.
  - Vega, Rose Ann D.
  - Almadrones, Joy P.

---

## 4. C-LACTS Enhanced Transmittal Features (QR Integration)

In addition to replicating this physical letter format, C-LACTS enhances it with modern GovTech traceability:
1. **Unique Transmittal Batch Number:** e.g., `TR-2026-10-0042` printed at the top-right.
2. **Master Transmittal QR Code:** A single QR code printed on the transmittal header.
   - When receiving clerks at Accounting or the Mayor's Office scan this **single master QR**, C-LACTS automatically updates the routing status of **all attached leave applications** in one click (e.g. "Received Batch TR-2026-10-0042 at Mayor's Office").
3. **Application Reference Codes:** The tracking reference (e.g. `LA-2026-000123`) is printed beside each employee's name for cross-referencing.
4. **Receiving Acknowledgement Block:**
   ```
   Received by: _______________________
   Date & Time: _______________________
   Signature:   _______________________
   ```
