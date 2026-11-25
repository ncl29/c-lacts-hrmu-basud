# Project Documentation & Specifications: HRU Basud (C-LACTS)

This directory is the source of truth for the **Centralized Leave Application and Credit Tracking System (C-LACTS)** for the Human Resource Management Unit (HRMU) of the Municipality of Basud, Camarines Norte.

## Directory Structure

```
.agents/docs/
├── defense-feedback.md                    # Panel / Professor Review & Conditions for Defense (HIGHEST PRIORITY)
├── _suggestions.md                        # Architecture notes, suggestions, and domain edge cases
├── AGENTS.md                              # Documentation policy and governance
├── proposals-and-specs/
│   ├── README.md                          # Specs overview and catalog
│   └── c-lacts-project-proposal.md        # Complete approved Project Proposal transcript
├── starla-tables/
│   └── starla-conversion-tables.md        # Digitized Starla Tables II, III, IV & CSC calculation rules
├── routing-and-workflow/
│   └── leave-workflow-and-qr-routing.md   # CSC Form 6 workflow, QR tracking, and receiving clerk flow
├── migration/
│   └── 158-employee-migration-plan.md     # Migration, ledger encoding & validation for 158 personnel
├── deployment-and-schedule/
│   └── deployment-and-revised-schedule.md # Cloud VPS/LAN deployment setup & revised pre-defense timeline
├── diagrams/                              # ERD, DFD Context, DFD Level 1, and System Architecture
└── forms-and-samples/                     # Scanned sample ledger cards, CSC Form 6, Starla references
```

## Authority Precedence
1. **`defense-feedback.md`** — Panel / Professor's Conditions for Secondary Defense (*Highest Priority*).
2. **`proposals-and-specs/c-lacts-project-proposal.md`** — Approved Project Proposal.
3. **`_suggestions.md`** & Module Specifications.
4. **`diagrams/`** — ERD, DFDs, System Architecture.
5. Workspace rules in `.agents/rules/`.
