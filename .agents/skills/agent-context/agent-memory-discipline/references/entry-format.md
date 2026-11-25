# Agent Memory Entry Format Reference

This reference defines the standard structure, lifecycle, and examples for storing persistent agent memories in file-based or MCP memory backends.

---

## 1. Core Entry Structure

Every stored memory entry must be concise, factual, and include its provenance. Format each entry as:

```text
[<STATUS> <EFFECTIVE_DATE>] <FACT_STATEMENT>. Reason: <REASON>. Source: <ORIGIN>. Applies to: <SCOPE>.
```

### Fields
* **Status:** `active` (default) or `closed <CLOSING_DATE>, replaced by "<NEW_ENTRY_SUMMARY>"`
* **Effective Date:** ISO date (`YYYY-MM-DD`) when the fact became true or was decided.
* **Fact Statement:** A single clear sentence defining the decision, rule, or learned observation.
* **Reason:** Why the decision was made or what incident necessitated it.
* **Source:** File, commit, PR, user instruction, or benchmark run where the fact originated.
* **Applies to:** Repository, workspace, directory, or module scope.

---

## 2. Status Lifecycle & Superseding Entries

### Rule: Close the Past, Do Not Overwrite
When a decision changes or is superseded, never delete the historical entry. Mark the existing entry `closed` and append the pointer to the new memory entry.

#### Example: Superseded Decision
```text
[closed 2026-10-09, replaced by "Use Bun as primary package manager and runtime"] Project uses pnpm, not npm. Stated by user on 2026-08-11 after a lockfile conflict. Applies to all packages.
[active 2026-10-09] Project uses Bun (bun@1.4.1) for package management and scripts. Reason: Project migrated to Bun for Turbopack alignment and execution speed. Source: package.json packageManager field. Applies to whole repo.
```

---

## 3. Evidence vs. Policy Entries

### Evidence Entry (Observations)
Observations from test runs, compiler output, or terminal failures:
```text
[active 2026-10-09] Turbopack fails build if custom webpack config is present without --webpack flag. Reason: Next.js 16 defaults to Turbopack for production builds. Source: Next.js 16 release notes and build trial. Applies to next.config.ts.
```

### Policy Entry (Human-Confirmed Rules)
Decisions explicitly established by the user or project architecture:
```text
[active 2026-10-09] Next.js 16 dynamic parameters (params, searchParams) must always be awaited asynchronously. Reason: Synchronous access was completely removed in Next 16. Source: AGENTS.md and Next.js 16 docs. Applies to app router pages and layouts.
```

---

## 4. Local File-Based Storage Convention

When no external MCP memory server is attached, memories can be maintained in markdown files:
- Directory: `.agents/skills/agent-context/agent-memory-discipline/memory/` or `.agents/memory/`
- One category or domain per file (e.g., `stack-decisions.md`, `architecture.md`, `user-preferences.md`).
- Entries are organized as a markdown list adhering to the one-line structure above.
